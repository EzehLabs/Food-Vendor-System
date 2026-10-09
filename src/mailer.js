export async function sendMail({ to, subject, text, html }) {
    const mailer = process.env.MAIL_MAILER || 'log';

    if (mailer === 'log') {
        console.info(`Mail to ${to}: ${subject}\n${text}`);
        return;
    }

    if (mailer !== 'brevo') {
        throw new Error(`Unsupported MAIL_MAILER "${mailer}". Configure brevo or log.`);
    }

    const apiKey = process.env.BREVO_API_KEY;
    const sender = process.env.MAIL_FROM_ADDRESS;
    if (!apiKey || !sender) {
        throw new Error('BREVO_API_KEY and MAIL_FROM_ADDRESS must be configured to send email.');
    }

    const response = await fetch('https://api.brevo.com/v3/smtp/email', {
        method: 'POST',
        headers: {
            accept: 'application/json',
            'content-type': 'application/json',
            'api-key': apiKey,
        },
        body: JSON.stringify({
            sender: { email: sender, name: process.env.MAIL_FROM_NAME || 'Food Vending System' },
            to: [{ email: to }],
            subject,
            textContent: text,
            htmlContent: html || `<p>${escapeHtml(text)}</p>`,
        }),
        signal: AbortSignal.timeout(15000),
    });

    if (!response.ok) {
        const details = await response.text();
        throw new Error(`Brevo email request failed (${response.status}): ${details}`);
    }
}

export function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    })[character]);
}
