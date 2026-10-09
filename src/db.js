import fs from 'node:fs';
import path from 'node:path';
import { DatabaseSync } from 'node:sqlite';

const configuredPath = process.env.DB_DATABASE;
const databasePath = configuredPath
    ? path.resolve(process.cwd(), configuredPath)
    : path.resolve(process.cwd(), 'database/database.sqlite');

fs.mkdirSync(path.dirname(databasePath), { recursive: true });

export const db = new DatabaseSync(databasePath);
db.exec('PRAGMA foreign_keys = ON');
db.exec(`
    CREATE TABLE IF NOT EXISTS login_link_tokens (
        user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        token_hash TEXT NOT NULL,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS email_verification_tokens (
        user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        token_hash TEXT NOT NULL,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS app_sessions (
        sid TEXT PRIMARY KEY,
        expires_at INTEGER NOT NULL,
        data TEXT NOT NULL
    );
`);

export function sessionStore(session) {
    return new (class extends session.Store {
        get(sid, callback) {
            try {
                const row = db.prepare('SELECT expires_at, data FROM app_sessions WHERE sid = ?').get(sid);
                if (!row) return callback(null, null);
                if (row.expires_at <= Date.now()) {
                    db.prepare('DELETE FROM app_sessions WHERE sid = ?').run(sid);
                    return callback(null, null);
                }
                callback(null, JSON.parse(row.data));
            } catch (error) {
                callback(error);
            }
        }

        set(sid, value, callback = () => {}) {
            try {
                const expiresAt = value.cookie?.expires
                    ? new Date(value.cookie.expires).getTime()
                    : Date.now() + 2 * 60 * 60 * 1000;
                db.prepare(`
                    INSERT INTO app_sessions (sid, expires_at, data) VALUES (?, ?, ?)
                    ON CONFLICT(sid) DO UPDATE SET expires_at = excluded.expires_at, data = excluded.data
                `).run(sid, expiresAt, JSON.stringify(value));
                callback(null);
            } catch (error) {
                callback(error);
            }
        }

        destroy(sid, callback = () => {}) {
            try {
                db.prepare('DELETE FROM app_sessions WHERE sid = ?').run(sid);
                callback(null);
            } catch (error) {
                callback(error);
            }
        }

        touch(sid, value, callback = () => {}) {
            this.set(sid, value, callback);
        }
    })();
}

export function nowSqlite() {
    return new Date().toISOString().slice(0, 19).replace('T', ' ');
}
