<?php
define('DB_PATH', __DIR__ . '/data/zylosoft.sqlite');
define('SITE_NAME', 'ZyloSoft');
define('SITE_DOMAIN', 'nimbx.site');
define('SERVER_IP', '20.2.80.253');
define('VAULT_KEYWORD', 'jani');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);
define('BAN_THRESHOLD', 10);

function db(): PDO {
    static $db = null;
    if ($db === null) {
        if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0750, true);
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA journal_mode=WAL');
        db_seed($db);
    }
    return $db;
}

function db_seed(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            plain_password TEXT,
            role TEXT DEFAULT 'user',
            status TEXT DEFAULT 'active',
            vpn_ip TEXT,
            vpn_pubkey TEXT,
            minetest_pass TEXT,
            ts_pass TEXT,
            cloud_pass TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            last_login_ip TEXT,
            last_login_country TEXT
        );
        CREATE TABLE IF NOT EXISTS activity (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            username TEXT,
            action TEXT,
            detail TEXT,
            ip TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS visitor_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            username TEXT,
            email TEXT,
            action TEXT,
            ip TEXT,
            country TEXT,
            city TEXT,
            lat REAL,
            lon REAL,
            timezone TEXT,
            isp TEXT,
            user_agent TEXT,
            browser TEXT,
            browser_version TEXT,
            os TEXT,
            device TEXT,
            referrer TEXT,
            page TEXT,
            local_ip TEXT,
            mac_hint TEXT,
            screen TEXT,
            language TEXT,
            region TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
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
    ");
    if ($db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $db->exec("INSERT INTO users (username,email,password,plain_password,role,vpn_ip,minetest_pass,ts_pass,cloud_pass)
            VALUES ('admin','admin@nimbx.site','$hash','admin123','admin','10.0.0.1',
            '".gen_pass()."','".gen_pass()."','".gen_pass()."')");
    }
}

function get_real_ip(): string {
    $headers = ['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_ip_blocked(string $ip): bool {
    $db = db();
    $row = $db->prepare("SELECT * FROM blocked_ips WHERE ip=? AND (expires_at IS NULL OR expires_at > datetime('now'))");
    $row->execute([$ip]);
    return (bool)$row->fetch();
}

function block_ip(string $ip, string $reason, bool $auto = true, int $minutes = 0): void {
    $db = db();
    $expires = $minutes > 0 ? date('Y-m-d H:i:s', time() + $minutes * 60) : null;
    $db->prepare("INSERT OR REPLACE INTO blocked_ips (ip,reason,auto_ban,expires_at) VALUES (?,?,?,?)")
       ->execute([$ip, $reason, $auto ? 1 : 0, $expires]);
    log_security('ip_blocked', $ip, '', "IP blocked: $reason", '', 'high');
}

function check_rate_limit(string $ip, string $username = ''): array {
    $db = db();
    // Count failed attempts in last 10 minutes
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip=? AND success=0 AND created_at > datetime('now','-10 minutes')");
    $stmt->execute([$ip]);
    $attempts = (int)$stmt->fetchColumn();

    // Auto-ban after threshold
    if ($attempts >= BAN_THRESHOLD) {
        block_ip($ip, "Auto-banned: $attempts failed login attempts", true, 60);
        return ['blocked' => true, 'attempts' => $attempts, 'reason' => 'Your IP has been blocked due to too many failed attempts.'];
    }

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        return ['blocked' => true, 'attempts' => $attempts, 'reason' => "Too many failed attempts. Please wait " . LOCKOUT_MINUTES . " minutes."];
    }

    return ['blocked' => false, 'attempts' => $attempts];
}

function record_login_attempt(string $ip, string $username, bool $success): void {
    db()->prepare("INSERT INTO login_attempts (ip,username,success) VALUES (?,?,?)")
       ->execute([$ip, $username, $success ? 1 : 0]);
}

function check_location_anomaly(array $user, string $ip, string $country): bool {
    if (empty($user['last_login_country']) || empty($country)) return false;
    if ($user['last_login_country'] === $country) return false;
    // Check if last login was within 1 hour
    if (empty($user['last_login'])) return false;
    $lastLogin = strtotime($user['last_login']);
    if (time() - $lastLogin > 3600) return false;
    return true; // Different country within 1 hour — anomaly!
}

function check_suspicious_ip(string $ip): array {
    $suspicious = false; $reason = '';
    try {
        $data = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=proxy,hosting,vpn,tor,status,isp,country,city"), true);
        if ($data && $data['status'] === 'success') {
            if (!empty($data['proxy'])) { $suspicious = true; $reason = 'Proxy detected'; }
            if (!empty($data['hosting'])) { $suspicious = true; $reason = 'Hosting/datacenter IP'; }
            if (!empty($data['vpn'])) { $suspicious = true; $reason = 'VPN detected'; }
            if (!empty($data['tor'])) { $suspicious = true; $reason = 'Tor exit node'; }
        }
    } catch(Exception $e) {}
    return ['suspicious' => $suspicious, 'reason' => $reason];
}

function log_security(string $type, string $ip, string $username, string $detail, string $country = '', string $severity = 'medium'): void {
    db()->prepare("INSERT INTO security_events (type,ip,username,detail,country,severity) VALUES (?,?,?,?,?,?)")
       ->execute([$type, $ip, $username, $detail, $country, $severity]);
}

function gen_csrf(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function gen_pass(int $len = 12): string {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$';
    return substr(str_shuffle(str_repeat($chars, 4)), 0, $len);
}

function next_vpn_ip(): string {
    $db = db();
    $used = $db->query("SELECT vpn_ip FROM users WHERE vpn_ip IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    for ($i = 2; $i <= 254; $i++) {
        $ip = "10.0.0.$i";
        if (!in_array($ip, $used)) return $ip;
    }
    return "10.0.1.1";
}

function auth(string $role = 'user'): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
    $u = $_SESSION['user'];
    if ($role === 'admin' && $u['role'] !== 'admin') { header('Location: /dashboard.php'); exit; }
    if ($role === 'admin_only' && $u['role'] !== 'admin') { header('Location: /panel/index.php'); exit; }
    return $u;
}

function log_act(int $uid, string $username, string $action, string $detail = ''): void {
    $ip = get_real_ip();
    db()->prepare("INSERT INTO activity (user_id,username,action,detail,ip) VALUES (?,?,?,?,?)")
       ->execute([$uid, $username, $action, $detail, $ip]);
}

function log_visit(string $action, ?array $user = null, string $page = '', string $local_ip = '', string $mac_hint = '', string $screen = '', string $language = ''): void {
    $ip = get_real_ip();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ref = $_SERVER['HTTP_REFERER'] ?? '';

    $browser = 'Unknown'; $bver = '';
    if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)[\/ ]?([\d.]+)?/i', $ua, $m)) {
        $browser = $m[1]; $bver = $m[2] ?? '';
        if ($browser === 'Trident') $browser = 'IE';
    }

    $os = 'Unknown';
    if (preg_match('/Windows NT ([\d.]+)/i', $ua, $m)) {
        $os = $m[1] == '10.0' ? 'Windows 10/11' : ($m[1] == '6.1' ? 'Windows 7' : 'Windows');
    } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) { $os = 'macOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/Android ([\d.]+)/i', $ua, $m)) { $os = 'Android '.$m[1];
    } elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) { $os = 'iOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/Linux/i', $ua)) { $os = 'Linux'; }

    $device = 'Desktop';
    if (preg_match('/Mobile|Android|iPhone/i', $ua)) $device = 'Mobile';
    if (preg_match('/iPad/i', $ua)) $device = 'Tablet';

    $country = $city = $timezone = $isp = $region = ''; $lat = $lon = 0;
    try {
        $geo = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=country,regionName,city,lat,lon,timezone,isp"), true);
        if ($geo && !empty($geo['country'])) {
            $country = $geo['country']; $city = $geo['city'];
            $lat = $geo['lat']; $lon = $geo['lon'];
            $timezone = $geo['timezone']; $isp = $geo['isp'];
            $region = $geo['regionName'] ?? '';
        }
    } catch(Exception $e) {}

    db()->prepare("INSERT INTO visitor_logs (user_id,username,email,action,ip,country,city,lat,lon,timezone,isp,user_agent,browser,browser_version,os,device,referrer,page,local_ip,mac_hint,screen,language,region) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
       ->execute([
           $user['id'] ?? null, $user['username'] ?? null, $user['email'] ?? null,
           $action, $ip, $country, $city, $lat, $lon, $timezone, $isp,
           $ua, $browser, $bver, $os, $device, $ref, $page,
           $local_ip, $mac_hint, $screen, $language, $region
       ]);
}

function svc_status(string $service): string {
    $out = shell_exec("systemctl is-active " . escapeshellarg($service) . " 2>/dev/null");
    return trim($out ?? '') === 'active' ? 'online' : 'offline';
}

function all_services(): array {
    return [
        ['key'=>'nginx',           'name'=>'Website',    'icon'=>'website', 'color'=>'#6366f1', 'desc'=>'Hosted website & web apps',      'url'=>'https://nimbx.site'],
        ['key'=>'wg-quick@wg0',   'name'=>'VPN',         'icon'=>'vpn',     'color'=>'#10b981', 'desc'=>'Private WireGuard VPN tunnel',   'url'=>'vpn.nimbx.site:51820'],
        ['key'=>'minetest-server', 'name'=>'Game Server', 'icon'=>'game',    'color'=>'#f59e0b', 'desc'=>'Minetest multiplayer server',   'url'=>'game.nimbx.site:30000'],
        ['key'=>'teamspeak',       'name'=>'Voice Chat',  'icon'=>'voice',   'color'=>'#3b82f6', 'desc'=>'TeamSpeak 3 voice server',      'url'=>'ts.nimbx.site:9987'],
        ['key'=>'php8.3-fpm',     'name'=>'Cloud',       'icon'=>'cloud',   'color'=>'#8b5cf6', 'desc'=>'Nextcloud private file storage', 'url'=>'https://cloud.nimbx.site'],
    ];
}

function svc_icon(string $key, string $color = 'currentColor', int $size = 20): string {
    $icons = [
        'website'  => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'vpn'      => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        'game'     => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="13" r="1" fill="'.$color.'"/><circle cx="17" cy="11" r="1" fill="'.$color.'"/><path d="M2 6h20v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6z"/></svg>',
        'voice'    => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>',
        'cloud'    => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>',
        'dashboard'=> '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'users'    => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'status'   => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        'logs'     => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        'vault'    => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="18" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M12 9v-2M12 17v-2M9 12H7M17 12h-2"/></svg>',
        'security' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'settings' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    ];
    return $icons[$key] ?? '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
}
