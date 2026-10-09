import 'dotenv/config';
import crypto from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import express from 'express';
import session from 'express-session';
import { rateLimit } from 'express-rate-limit';
import { db, nowSqlite, sessionStore } from './db.js';
import { escapeHtml, sendMail } from './mailer.js';

const app = express();
const port = Number(process.env.PORT || 3000);
const production = process.env.APP_ENV === 'production' || process.env.NODE_ENV === 'production';
const appUrl = (process.env.APP_URL || `http://localhost:${port}`).replace(/\/$/, '');
const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

app.set('view engine', 'ejs');
app.set('views', path.join(projectRoot, 'resources', 'js-views'));
app.use(express.urlencoded({ extended: false, limit: '1mb' }));
app.use(express.json({ limit: '1mb' }));
app.use(express.static(path.join(projectRoot, 'public')));
app.use(session({
    name: 'food_vendor_session',
    secret: process.env.SESSION_SECRET || process.env.APP_KEY || 'replace-this-session-secret-before-production',
    resave: false,
    saveUninitialized: false,
    store: sessionStore(session),
    cookie: { httpOnly: true, sameSite: 'lax', secure: production, maxAge: 2 * 60 * 60 * 1000 },
}));

function csrf(req, res, next) {
    if (!req.session.csrfToken) req.session.csrfToken = crypto.randomBytes(32).toString('hex');
    res.locals.csrfToken = req.session.csrfToken;
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(req.method)) {
        const submitted = req.body?._csrf;
        const submittedBuffer = Buffer.from(submitted || '');
        const expectedBuffer = Buffer.from(req.session.csrfToken);
        if (submittedBuffer.length !== expectedBuffer.length || !crypto.timingSafeEqual(submittedBuffer, expectedBuffer)) {
            return res.status(419).send('Your session has expired. Refresh the page and try again.');
        }
    }
    next();
}

app.use(csrf);
app.use((req, res, next) => {
    res.locals.user = req.session.userId
        ? db.prepare('SELECT id, name, email, role, email_verified_at FROM users WHERE id = ?').get(req.session.userId)
        : null;
    res.locals.status = req.query.status || req.session.flashStatus || null;
    res.locals.error = req.session.flashError || null;
    delete req.session.flashStatus;
    delete req.session.flashError;
    res.locals.path = req.path;
    res.locals.appName = process.env.APP_NAME || 'Food Vending System';
    next();
});

function render(res, title, data = {}) {
    res.render('page', { title, ...data });
}

function redirectStatus(req, res, url, status, error = false) {
    if (error) req.session.flashError = status;
    else req.session.flashStatus = status;
    res.redirect(url);
}

function requireAuth(req, res, next) {
    if (!res.locals.user) return res.redirect('/login');
    next();
}

function requireRole(role) {
    return (req, res, next) => {
        if (!res.locals.user) return res.redirect('/login');
        if (res.locals.user.role !== role) return res.status(403).send('Access denied.');
        next();
    };
}

function hashPassword(password) {
    const salt = crypto.randomBytes(16).toString('hex');
    return `${salt}:${crypto.scryptSync(password, salt, 64).toString('hex')}`;
}

function makeToken() {
    return crypto.randomBytes(32).toString('hex');
}

function dashboardUrl(role) {
    return role === 'admin' ? '/admin/dashboard' : role === 'vendor' ? '/vendor/dashboard' : '/customer/home';
}

const authLimiter = rateLimit({ windowMs: 60_000, limit: 5, standardHeaders: true, legacyHeaders: false });
const tokenLimiter = rateLimit({ windowMs: 60_000, limit: 10, standardHeaders: true, legacyHeaders: false });

app.get('/', (req, res) => {
    const foods = db.prepare("SELECT foods.*, vendors.company_name FROM foods LEFT JOIN vendors ON foods.vendor_id = vendors.id WHERE foods.status = 'in_stock' ORDER BY foods.created_at DESC LIMIT 8").all();
    render(res, 'Welcome', { kind: 'food-list', foods, publicPage: true });
});

app.get('/dashboard', requireAuth, (req, res) => res.redirect(dashboardUrl(res.locals.user.role)));

app.get('/register', (req, res) => render(res, 'Create account', { kind: 'register' }));
app.get('/register/customer', (req, res) => render(res, 'Register customer', { kind: 'register-customer' }));
app.get('/register/vendor', (req, res) => render(res, 'Register vendor', { kind: 'register-vendor' }));

app.post('/register/customer', authLimiter, (req, res) => register(req, res, 'customer'));
app.post('/register/vendor', authLimiter, (req, res) => register(req, res, 'vendor'));

function register(req, res, role) {
    const { name, email, password, password_confirmation: confirmation, company_name: companyName } = req.body;
    if (!name?.trim() || !email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || !password || password !== confirmation
        || password.length < 8 || (role === 'vendor' && !companyName?.trim())) {
        return render(res, 'Create account', { kind: 'register', error: 'Enter valid details and matching passwords.' });
    }
    if (db.prepare('SELECT id FROM users WHERE email = ?').get(email.toLowerCase())) {
        return render(res, 'Create account', { kind: 'register', error: 'An account with this email already exists.' });
    }

    const timestamp = nowSqlite();
    db.exec('BEGIN');
    let userId;
    try {
        const result = db.prepare('INSERT INTO users (name, email, email_verified_at, password, remember_token, role, created_at, updated_at) VALUES (?, ?, NULL, ?, NULL, ?, ?, ?)')
            .run(name.trim(), email.toLowerCase(), hashPassword(password), role, timestamp, timestamp);
        userId = Number(result.lastInsertRowid);
        if (role === 'vendor') {
            db.prepare('INSERT INTO vendors (user_id, company_name, created_at, updated_at) VALUES (?, ?, ?, ?)')
                .run(userId, companyName.trim(), timestamp, timestamp);
        }
        db.exec('COMMIT');
    } catch (error) {
        db.exec('ROLLBACK');
        throw error;
    }

    req.session.userId = userId;
    const verifyToken = makeToken();
    const verifyExpires = new Date(Date.now() + 60 * 60_000).toISOString().slice(0, 19).replace('T', ' ');
    db.prepare(`INSERT INTO email_verification_tokens (user_id, token_hash, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET token_hash = excluded.token_hash, expires_at = excluded.expires_at, updated_at = excluded.updated_at`)
        .run(userId, crypto.createHash('sha256').update(verifyToken).digest('hex'), verifyExpires, timestamp, timestamp);
    const verifyUrl = `${appUrl}/email/verify/${userId}/${verifyToken}`;
    req.session.flashStatus = `Account created successfully. A verification link is being sent to ${email}.`;
    res.redirect('/email/verify');
    sendMail({
        to: email,
        subject: 'Verify your email address',
        text: `Verify your email address: ${verifyUrl}`,
        html: `<p>Verify your email address to activate your account.</p><p><a href="${verifyUrl}">Verify email address</a></p>`,
    }).catch((error) => console.error('Verification email delivery failed:', error));
}

app.get('/login', (req, res) => render(res, 'Sign in', { kind: 'login' }));
app.post('/login', authLimiter, (req, res) => {
    const email = String(req.body.email || '').trim().toLowerCase();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return render(res, 'Sign in', { kind: 'login', error: 'Enter a valid email address.', email });
    }

    const user = db.prepare('SELECT id, email FROM users WHERE email = ?').get(email);
    if (user) {
        const token = makeToken();
        const timestamp = nowSqlite();
        const expiresAt = new Date(Date.now() + 15 * 60_000).toISOString().slice(0, 19).replace('T', ' ');
        db.prepare(`INSERT INTO login_link_tokens (user_id, token_hash, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?)
            ON CONFLICT(user_id) DO UPDATE SET token_hash = excluded.token_hash, expires_at = excluded.expires_at, updated_at = excluded.updated_at`)
            .run(user.id, crypto.createHash('sha256').update(token).digest('hex'), expiresAt, timestamp, timestamp);
        const url = `${appUrl}/login/link/${user.id}/${token}`;
        sendMail({
            to: user.email,
            subject: 'Your sign-in link',
            text: `Sign in using this one-time link. It expires in 15 minutes: ${url}`,
            html: `<p>Your one-time sign-in link expires in 15 minutes.</p><p><a href="${url}">Sign in</a></p>`,
        }).catch((error) => console.error('Sign-in email delivery failed:', error));
    }
    redirectStatus(req, res, '/login', 'If an account exists for that email, a sign-in link is on its way.');
});

app.get('/login/link/:user/:token', tokenLimiter, (req, res) => {
    const id = Number(req.params.user);
    const user = db.prepare('SELECT id, role FROM users WHERE id = ?').get(id);
    let valid = false;
    if (user && /^[a-f0-9]{64}$/.test(req.params.token)) {
        db.exec('BEGIN IMMEDIATE');
        try {
            const record = db.prepare('SELECT token_hash, expires_at FROM login_link_tokens WHERE user_id = ?').get(id);
            const hash = crypto.createHash('sha256').update(req.params.token).digest('hex');
            valid = Boolean(record && record.expires_at > nowSqlite() && crypto.timingSafeEqual(Buffer.from(record.token_hash), Buffer.from(hash)));
            if (valid) {
                db.prepare('DELETE FROM login_link_tokens WHERE user_id = ?').run(id);
                db.prepare('UPDATE users SET email_verified_at = COALESCE(email_verified_at, ?), updated_at = ? WHERE id = ?')
                    .run(nowSqlite(), nowSqlite(), id);
            }
            db.exec('COMMIT');
        } catch (error) {
            db.exec('ROLLBACK');
            throw error;
        }
    }
    if (!valid) return render(res, 'Sign-in link expired', { kind: 'expired-link', error: 'This link is invalid or has expired. Request another sign-in link.' });
    req.session.regenerate((error) => {
        if (error) return res.status(500).send('Could not start a secure session.');
        req.session.userId = id;
        res.redirect(dashboardUrl(user.role));
    });
});

app.post('/logout', requireAuth, (req, res, next) => {
    req.session.destroy((error) => {
        if (error) return next(error);
        res.clearCookie('food_vendor_session');
        res.redirect('/');
    });
});

app.get('/email/verify', requireAuth, (req, res) => render(res, 'Verify your email', { kind: 'verify-email' }));
app.get('/email/verify/:id/:hash', (req, res) => {
    const id = Number(req.params.id);
    const user = db.prepare('SELECT id, email, role FROM users WHERE id = ?').get(id);
    const record = user && db.prepare('SELECT token_hash, expires_at FROM email_verification_tokens WHERE user_id = ?').get(id);
    const token = Buffer.from(req.params.hash);
    const expected = Buffer.from(record?.token_hash || '');
    if (!record || token.length !== 64 || !crypto.timingSafeEqual(expected, crypto.createHash('sha256').update(token).digest())) {
        return res.status(403).send('Verification link is invalid.');
    }
    if (record.expires_at <= nowSqlite()) return res.status(410).send('Verification link has expired. Sign in to request another link.');
    db.exec('BEGIN IMMEDIATE');
    try {
        db.prepare('DELETE FROM email_verification_tokens WHERE user_id = ?').run(id);
        db.prepare('UPDATE users SET email_verified_at = ?, updated_at = ? WHERE id = ?').run(nowSqlite(), nowSqlite(), user.id);
        db.exec('COMMIT');
    } catch (error) {
        db.exec('ROLLBACK');
        throw error;
    }
    if (req.session.userId !== user.id) req.session.userId = user.id;
    res.redirect(dashboardUrl(user.role));
});
app.post('/email/verification-notification', requireAuth, tokenLimiter, (req, res) => {
    const user = res.locals.user;
    const token = makeToken();
    const timestamp = nowSqlite();
    const expiresAt = new Date(Date.now() + 60 * 60_000).toISOString().slice(0, 19).replace('T', ' ');
    db.prepare(`INSERT INTO email_verification_tokens (user_id, token_hash, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET token_hash = excluded.token_hash, expires_at = excluded.expires_at, updated_at = excluded.updated_at`)
        .run(user.id, crypto.createHash('sha256').update(token).digest('hex'), expiresAt, timestamp, timestamp);
    const url = `${appUrl}/email/verify/${user.id}/${token}`;
    sendMail({ to: user.email, subject: 'Verify your email address', text: `Verify your email address: ${url}` })
        .catch((error) => console.error('Verification email delivery failed:', error));
    redirectStatus(req, res, '/email/verify', 'Verification email requested.');
});

app.get('/customer/home', requireRole('customer'), (req, res) => res.redirect('/customer/foods'));
app.get('/customer/foods', requireRole('customer'), (req, res) => {
    const foods = db.prepare(`SELECT foods.*, vendors.company_name FROM foods
        LEFT JOIN vendors ON foods.vendor_id = vendors.id
        WHERE (? = '' OR foods.name LIKE ?) AND (? = '' OR foods.category = ?)
        ORDER BY foods.created_at DESC`).all(req.query.search || '', `%${req.query.search || ''}%`, req.query.category || '', req.query.category || '');
    render(res, 'Browse food', { kind: 'food-list', foods, search: req.query.search });
});

app.get('/cart', requireAuth, (req, res) => {
    const carts = db.prepare(`SELECT carts.*, foods.name, foods.price, foods.image FROM carts
        JOIN foods ON foods.id = carts.food_id WHERE carts.user_id = ?`).all(res.locals.user.id);
    render(res, 'Your cart', { kind: 'cart', carts });
});

app.post('/cart/add/:food_id', requireAuth, (req, res) => {
    const food = db.prepare('SELECT id FROM foods WHERE id = ?').get(Number(req.params.food_id));
    const quantity = Math.max(1, Number.parseInt(req.body.quantity || '1', 10));
    if (!food || !Number.isFinite(quantity)) return res.status(422).json({ error: 'Invalid food or quantity.' });
    const existing = db.prepare('SELECT id FROM carts WHERE user_id = ? AND food_id = ?').get(res.locals.user.id, food.id);
    if (existing) {
        db.prepare('UPDATE carts SET quantity = quantity + ?, updated_at = ? WHERE id = ?').run(quantity, nowSqlite(), existing.id);
    } else {
        db.prepare('INSERT INTO carts (user_id, food_id, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?)')
            .run(res.locals.user.id, food.id, quantity, nowSqlite(), nowSqlite());
    }
    res.json({ success: true, count: db.prepare('SELECT COUNT(*) AS count FROM carts WHERE user_id = ?').get(res.locals.user.id).count });
});
app.post('/cart/update/:id', requireAuth, (req, res) => {
    const quantity = Number.parseInt(req.body.quantity, 10);
    if (!Number.isInteger(quantity) || quantity < 1) return res.status(422).json({ error: 'Quantity must be at least one.' });
    db.prepare('UPDATE carts SET quantity = ?, updated_at = ? WHERE id = ? AND user_id = ?').run(quantity, nowSqlite(), req.params.id, res.locals.user.id);
    redirectStatus(req, res, '/cart', 'Cart updated.');
});
app.post('/cart/remove/:id', requireAuth, (req, res) => {
    db.prepare('DELETE FROM carts WHERE id = ? AND user_id = ?').run(req.params.id, res.locals.user.id);
    redirectStatus(req, res, '/cart', 'Item removed.');
});

app.get('/checkout', requireAuth, (req, res) => {
    const carts = db.prepare('SELECT carts.quantity, foods.name, foods.price FROM carts JOIN foods ON foods.id = carts.food_id WHERE carts.user_id = ?').all(res.locals.user.id);
    render(res, 'Checkout', { kind: 'checkout', carts });
});
app.post('/checkout', requireAuth, (req, res) => {
    const userId = res.locals.user.id;
    const items = db.prepare('SELECT carts.food_id, carts.quantity, foods.price FROM carts JOIN foods ON foods.id = carts.food_id WHERE carts.user_id = ?').all(userId);
    if (!items.length) return redirectStatus(req, res, '/cart', 'Your cart is empty.', true);
    const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const orderNumber = `ORD-${Date.now()}-${crypto.randomInt(1000, 9999)}`;
    const timestamp = nowSqlite();
    const transaction = db;
    transaction.exec('BEGIN');
    try {
        const order = transaction.prepare(`INSERT INTO orders (order_number, user_id, total_amount, status, payment_status, delivery_address, phone, created_at, updated_at)
            VALUES (?, ?, ?, 'pending_payment', 'unpaid', ?, ?, ?, ?)`)
            .run(orderNumber, userId, total, req.body.delivery_address || '', req.body.phone || '', timestamp, timestamp);
        const insertItem = transaction.prepare('INSERT INTO order_items (order_id, food_id, quantity, price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        for (const item of items) insertItem.run(order.lastInsertRowid, item.food_id, item.quantity, item.price, timestamp, timestamp);
        transaction.exec('COMMIT');
        res.redirect(`/payment/pay?order=${order.lastInsertRowid}`);
    } catch (error) {
        transaction.exec('ROLLBACK');
        throw error;
    }
});

app.get('/customer/orders', requireRole('customer'), (req, res) => {
    const orders = db.prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC').all(res.locals.user.id);
    render(res, 'My orders', { kind: 'orders', orders });
});
app.get('/customer/orders/:id', requireRole('customer'), (req, res) => {
    const order = db.prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?').get(req.params.id, res.locals.user.id);
    if (!order) return res.sendStatus(404);
    order.items = db.prepare('SELECT order_items.*, foods.name FROM order_items JOIN foods ON foods.id = order_items.food_id WHERE order_id = ?').all(order.id);
    render(res, `Order ${order.order_number}`, { kind: 'order-detail', order });
});
app.get('/customer/transactions', requireRole('customer'), (req, res) => {
    const orders = db.prepare("SELECT * FROM orders WHERE user_id = ? AND payment_status = 'paid' ORDER BY created_at DESC").all(res.locals.user.id);
    render(res, 'Transactions', { kind: 'orders', orders });
});

app.get('/payment/pay', requireRole('customer'), async (req, res, next) => {
    try {
        const order = db.prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?').get(req.query.order, res.locals.user.id);
        if (!order) return res.sendStatus(404);
        if (!process.env.PAYSTACK_SECRET_KEY) return redirectStatus(req, res, '/customer/orders', 'Payment provider is not configured.', true);
        const response = await fetch('https://api.paystack.co/transaction/initialize', {
            method: 'POST',
            headers: { authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY}`, 'content-type': 'application/json' },
            body: JSON.stringify({ email: res.locals.user.email, amount: Math.round(order.total_amount * 100), callback_url: `${appUrl}/payment/callback`, metadata: { order_id: order.id } }),
            signal: AbortSignal.timeout(15000),
        });
        const result = await response.json();
        if (!response.ok || !result.status || !result.data?.authorization_url) return redirectStatus(req, res, '/customer/orders', 'Unable to initialize payment.', true);
        res.redirect(result.data.authorization_url);
    } catch (error) {
        next(error);
    }
});

app.get('/payment/callback', requireRole('customer'), async (req, res, next) => {
    try {
        if (!process.env.PAYSTACK_SECRET_KEY || !req.query.reference) return res.redirect('/customer/orders');
        const response = await fetch(`https://api.paystack.co/transaction/verify/${encodeURIComponent(req.query.reference)}`, {
            headers: { authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY}` },
            signal: AbortSignal.timeout(15000),
        });
        const result = await response.json();
        if (response.ok && result.status && result.data?.status === 'success') {
            const orderId = result.data.metadata?.order_id;
            db.prepare("UPDATE orders SET payment_status = 'paid', status = 'processing', payment_reference = ?, updated_at = ? WHERE id = ? AND user_id = ?")
                .run(req.query.reference, nowSqlite(), orderId, res.locals.user.id);
            db.prepare('DELETE FROM carts WHERE user_id = ?').run(res.locals.user.id);
            return redirectStatus(req, res, '/customer/orders', 'Payment successful.');
        }
        redirectStatus(req, res, '/customer/orders', 'Payment could not be verified.', true);
    } catch (error) {
        next(error);
    }
});

app.get('/vendor/dashboard', requireRole('vendor'), (req, res) => {
    const vendor = db.prepare('SELECT * FROM vendors WHERE user_id = ?').get(res.locals.user.id);
    if (!vendor) return res.sendStatus(404);
    const stats = {
        foods: db.prepare('SELECT COUNT(*) AS count FROM foods WHERE vendor_id = ?').get(vendor.id).count,
        orders: db.prepare('SELECT COUNT(DISTINCT order_items.order_id) AS count FROM order_items JOIN foods ON foods.id = order_items.food_id WHERE foods.vendor_id = ?').get(vendor.id).count,
        recentOrders: db.prepare(`SELECT orders.* FROM orders JOIN order_items ON order_items.order_id = orders.id
            JOIN foods ON foods.id = order_items.food_id WHERE foods.vendor_id = ? GROUP BY orders.id ORDER BY orders.created_at DESC LIMIT 5`).all(vendor.id),
    };
    render(res, 'Vendor dashboard', { kind: 'dashboard', stats });
});
app.get('/vendor/foods', requireRole('vendor'), (req, res) => {
    const vendor = db.prepare('SELECT id FROM vendors WHERE user_id = ?').get(res.locals.user.id);
    const foods = vendor ? db.prepare('SELECT * FROM foods WHERE vendor_id = ? ORDER BY created_at DESC').all(vendor.id) : [];
    render(res, 'Manage foods', { kind: 'vendor-foods', foods });
});
app.get('/vendor/foods/create', requireRole('vendor'), (req, res) => render(res, 'Add food', { kind: 'food-form' }));
app.post('/vendor/foods/store', requireRole('vendor'), (req, res) => {
    const vendor = db.prepare('SELECT id FROM vendors WHERE user_id = ?').get(res.locals.user.id);
    if (!vendor) return res.sendStatus(404);
    db.prepare(`INSERT INTO foods (vendor_id, name, category, price, quantity, expiry_date, status, image, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)`)
        .run(vendor.id, req.body.name, req.body.category || 'Others', Number(req.body.price), Number(req.body.quantity), req.body.expiry_date || null, req.body.status || 'in_stock', nowSqlite(), nowSqlite());
    redirectStatus(req, res, '/vendor/foods', 'Food added.');
});
app.post('/vendor/foods/delete/:id', requireRole('vendor'), (req, res) => {
    const vendor = db.prepare('SELECT id FROM vendors WHERE user_id = ?').get(res.locals.user.id);
    db.prepare('DELETE FROM foods WHERE id = ? AND vendor_id = ?').run(req.params.id, vendor?.id);
    redirectStatus(req, res, '/vendor/foods', 'Food deleted.');
});

app.get('/vendor/orders', requireRole('vendor'), (req, res) => {
    const orders = db.prepare(`SELECT DISTINCT orders.* FROM orders JOIN order_items ON order_items.order_id = orders.id
        JOIN foods ON foods.id = order_items.food_id JOIN vendors ON vendors.id = foods.vendor_id
        WHERE vendors.user_id = ? ORDER BY orders.created_at DESC`).all(res.locals.user.id);
    render(res, 'Vendor orders', { kind: 'orders', orders });
});
app.post('/vendor/orders/:id/status', requireRole('vendor'), (req, res) => {
    const ownsOrder = db.prepare(`SELECT 1 FROM order_items JOIN foods ON foods.id = order_items.food_id
        JOIN vendors ON vendors.id = foods.vendor_id WHERE order_items.order_id = ? AND vendors.user_id = ?`).get(req.params.id, res.locals.user.id);
    const valid = ['pending_payment', 'paid', 'accepted', 'preparing', 'ready', 'completed', 'cancelled'];
    if (!ownsOrder || !valid.includes(req.body.status)) return res.sendStatus(422);
    db.prepare('UPDATE orders SET status = ?, updated_at = ? WHERE id = ?').run(req.body.status, nowSqlite(), req.params.id);
    redirectStatus(req, res, '/vendor/orders', 'Order status updated.');
});

app.get('/admin/dashboard', requireRole('admin'), (req, res) => {
    const stats = {
        customers: db.prepare("SELECT COUNT(*) AS count FROM users WHERE role = 'customer'").get().count,
        vendors: db.prepare("SELECT COUNT(*) AS count FROM users WHERE role = 'vendor'").get().count,
        foods: db.prepare('SELECT COUNT(*) AS count FROM foods').get().count,
        orders: db.prepare('SELECT COUNT(*) AS count FROM orders').get().count,
        revenue: db.prepare("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE payment_status = 'paid'").get().total,
    };
    render(res, 'Admin dashboard', { kind: 'dashboard', stats });
});
app.get('/admin/users', requireRole('admin'), (req, res) => render(res, 'Users', {
    kind: 'users', users: db.prepare('SELECT id, name, email, role, email_verified_at FROM users ORDER BY created_at DESC').all(),
}));
app.get('/admin/foods', requireRole('admin'), (req, res) => render(res, 'All foods', {
    kind: 'food-list', foods: db.prepare('SELECT foods.*, vendors.company_name FROM foods LEFT JOIN vendors ON vendors.id = foods.vendor_id ORDER BY foods.created_at DESC').all(),
}));
app.get('/admin/orders', requireRole('admin'), (req, res) => render(res, 'All orders', {
    kind: 'orders', orders: db.prepare('SELECT orders.*, users.name FROM orders JOIN users ON users.id = orders.user_id ORDER BY orders.created_at DESC').all(),
}));
app.get('/admin/orders/:id', requireRole('admin'), (req, res) => {
    const order = db.prepare('SELECT orders.*, users.name FROM orders JOIN users ON users.id = orders.user_id WHERE orders.id = ?').get(req.params.id);
    if (!order) return res.sendStatus(404);
    order.items = db.prepare('SELECT order_items.*, foods.name FROM order_items JOIN foods ON foods.id = order_items.food_id WHERE order_id = ?').all(order.id);
    render(res, `Order ${order.order_number}`, { kind: 'order-detail', order });
});
app.get('/admin/payments', requireRole('admin'), (req, res) => res.redirect('/admin/orders'));
app.get('/admin/reports', requireRole('admin'), (req, res) => res.redirect('/admin/dashboard'));

app.get('/profile', requireAuth, (req, res) => render(res, 'Profile', { kind: 'profile' }));
app.post('/profile', requireAuth, (req, res) => {
    const email = String(req.body.email || '').trim().toLowerCase();
    const name = String(req.body.name || '').trim();
    if (!name || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return redirectStatus(req, res, '/profile', 'Enter a valid name and email.', true);
    const conflict = db.prepare('SELECT id FROM users WHERE email = ? AND id != ?').get(email, res.locals.user.id);
    if (conflict) return redirectStatus(req, res, '/profile', 'That email is already in use.', true);
    db.prepare('UPDATE users SET name = ?, email = ?, email_verified_at = CASE WHEN email = ? THEN email_verified_at ELSE NULL END, updated_at = ? WHERE id = ?')
        .run(name, email, res.locals.user.email, nowSqlite(), res.locals.user.id);
    redirectStatus(req, res, '/profile', 'Profile updated.');
});

app.get('/forgot-password', (req, res) => render(res, 'Reset password', { kind: 'forgot-password' }));
app.post('/forgot-password', authLimiter, (req, res) => redirectStatus(req, res, '/forgot-password', 'If the address belongs to an account, password reset instructions have been requested.'));
app.get('/reset-password/:token', (req, res) => render(res, 'Reset password', { kind: 'reset-password', token: req.params.token }));
app.post('/reset-password', (req, res) => redirectStatus(req, res, '/login', 'Password resets are unavailable for passwordless accounts.', true));

app.use((req, res) => res.status(404).send('Page not found.'));
app.use((error, req, res, next) => {
    console.error('Request failed:', error);
    if (res.headersSent) return next(error);
    res.status(500).send(production ? 'An unexpected error occurred.' : escapeHtml(error.message));
});

app.listen(port, '0.0.0.0', () => console.info(`Food Vending System listening on port ${port}`));
