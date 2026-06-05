#!/bin/bash
# Run this on the server to add new security tables and columns
DB="/var/www/html/data/zylosoft.sqlite"

sudo sqlite3 $DB "
CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    username TEXT,
    success INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS blocked_ips (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT UNIQUE NOT NULL,
    reason TEXT,
    auto_ban INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME
);
CREATE TABLE IF NOT EXISTS security_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL,
    ip TEXT,
    username TEXT,
    detail TEXT,
    country TEXT,
    severity TEXT DEFAULT 'medium',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
"

# Add new columns to users if not exist
sudo sqlite3 $DB "ALTER TABLE users ADD COLUMN last_login_ip TEXT;" 2>/dev/null || true
sudo sqlite3 $DB "ALTER TABLE users ADD COLUMN last_login_country TEXT;" 2>/dev/null || true

echo "Migration complete!"
