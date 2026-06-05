
# ICT171 Cloud Server Project — Complete Setup Guide

**Student Name:** Muhammad Rumman  
**Student ID:** 36006663  
**Unit:** ICT171 — Introduction to Server Environments and Architectures  
**Semester:** 2026 Semester 1  
**Tested on:** Azure Ubuntu 24.04 LTS — all steps verified working

> This guide is written from real hands-on experience. Every warning box describes a problem that actually happened and how to fix it. Follow the steps in order and you will have all five services running in under an hour.

---

## Quick Reference

| Item | Value |
|------|-------|
| Public IP Address | `20.2.80.253` |
| Domain Name | `zylosoft.online` |
| GitHub Repository | `https://github.com/rumibro0/ICT171-Final-Project` |
| Video Explainer | `https://youtu.be/[your-video-id]` |
| Status Page | `https://zylosoft.online/status.html` |

---

## Project Overview

This project builds a multi-purpose cloud server on Microsoft Azure running Ubuntu 24.04 LTS. The server hosts five integrated services — a website, VPN, voice chat, game server, and private cloud storage — all accessible via subdomains of a single domain name, secured with HTTPS, and monitored by a live status dashboard.

### Services

| Service | Technology | Address |
|---------|------------|---------|
| Website | Nginx | `https://zylosoft.online` |
| VPN | WireGuard | `vpn.zylosoft.online:51820` |
| Voice chat | TeamSpeak 3 | `ts.zylosoft.online:9987` |
| Game server | Minetest | `game.zylosoft.online:30000` |
| File storage | Nextcloud | `https://cloud.zylosoft.online` |
| Status dashboard | Bash + cron | `https://zylosoft.online/status.html` |

### Infrastructure

| Setting | Value |
|---------|-------|
| Cloud provider | Microsoft Azure |
| Region | East Asia (your choice) |
| VM size | Standard_B1s (1 vCPU, 1 GB RAM) |
| OS | Ubuntu Server 24.04 LTS |
| Network interface | `eth0` |
| PHP version | 8.3 |
| Storage | 30 GB OS disk (~25 GB free after setup) |

---

## Step 1 — Create the Azure VM

### 1.1 Create a free account

Go to [https://azure.microsoft.com/en-au/get-started/azure-portal](https://azure.microsoft.com/en-au/get-started/azure-portal) and create an account.

> If you have a Student ID then go to [azure.microsoft.com/en-au/free/students](https://azure.microsoft.com/en-au/free/students) and sign in with your university email. You receive $100 credit with no credit card required.

### 1.2 Create the virtual machine

1. Log in to [portal.azure.com](https://portal.azure.com)
2. Navigate to **Virtual Machines → Create → Azure Virtual Machine**
3. Use these exact settings:

| Setting | Value |
|---------|-------|
| Image | Ubuntu Server 24.04 LTS |
| Size | Standard_B1s |
| Authentication type | SSH public key |
| Region | East Asia (your choice) |

4. Download the generated `.pem` key file when prompted. Keep it safe — you cannot SSH in without it.

### 1.3 Set a static public IP

> **Do this before anything else.** Azure assigns a dynamic IP by default, which changes every time the VM restarts and breaks all your DNS records.

1. Azure portal → your VM → **Networking → Public IP address**
2. Click the IP name link → **Configuration**
3. Change **Assignment** from **Dynamic** to **Static**
4. Click **Save**

Write down the IP — this is `20.2.80.253` for the rest of this guide.

### 1.4 Open firewall ports in Azure NSG

Azure portal → your VM → **Networking → Add inbound port rule**. Add all of these:

| Port | Protocol | Service |
|------|----------|---------|
| 22 | TCP | SSH (already open) |
| 80 | TCP | HTTP |
| 443 | TCP | HTTPS |
| 9987 | UDP | TeamSpeak 3 |
| 51820 | UDP | WireGuard VPN |
| 30000 | UDP | Minetest game server |

> **Important:** Set Source to **Any** and Action to **Allow** for each rule. These rules must be present or your server will time out even when Nginx is running correctly.

### 1.5 Connect via SSH

On Windows (PowerShell) or macOS/Linux terminal:

```bash
ssh -i C:\Users\YourName\Downloads\your-key.pem azureuser@20.2.80.253
```

> **Three things must be correct or SSH will fail:**
> - **Key filename** — the `.pem` file name must exactly match what you downloaded. Check with `dir` in PowerShell to confirm the exact filename before using it.
> - **IP address** — use the current static IP shown in the Azure portal, not an old IP from a previous VM.
> - **Username** — always specify `azureuser@IP`. Running `ssh -i key.pem IP` without the username fails with permission denied because SSH uses your local Windows username instead.

> **Windows permissions error on the .pem file:** Right-click → Properties → Security → Advanced → disable inheritance → give only your user account Read permission.

### 1.6 Update the system

Always run this immediately after first login:

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.7 Enable the host firewall (UFW)

> **Critical:** Run these commands in this exact order. Always allow OpenSSH first or you will lock yourself out.

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw allow 9987/udp
sudo ufw allow 51820/udp
sudo ufw allow 30000/udp
sudo ufw enable
sudo ufw status
```

> **`Nginx Full` is required.** This opens both port 80 and 443. If you skip this, the browser shows `ERR_CONNECTION_REFUSED` even though Nginx is running and the Azure NSG rule is correct. Both Azure NSG and UFW are independent firewalls — both must allow port 80.

---

## Step 2 — Install Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

**Check Nginx is running:**

```bash
sudo systemctl status nginx
```

The status should show `Active: active (running)` in green. If it does not, run `sudo systemctl start nginx` and check again.

**Verify it works locally on the server:**

```bash
curl http://localhost
```

This should return HTML. If it does, Nginx is working correctly on the server side.

> **If the browser shows `ERR_CONNECTION_TIMED_OUT` after installing Nginx**, there are two independent firewalls that both need to allow port 80 — Azure NSG and UFW. Check both:
>
> **Check 1 — Azure NSG:** Azure Portal → your VM → Networking. Confirm an inbound rule exists for port 80 TCP with Action = Allow. If missing, add it.
>
> **Check 2 — UFW:** Run `sudo ufw status` on the server. If port 80 is not listed, run `sudo ufw allow 'Nginx Full'`. This is why Step 1.7 must be completed before installing Nginx.
>
> A common sign that both firewalls need fixing: the error changes from `ERR_CONNECTION_TIMED_OUT` (Azure NSG blocking) to `ERR_CONNECTION_REFUSED` (UFW blocking) after you add the NSG rule. Fix UFW next and the site will load.

> **Browser shows "site can't be reached" from your PC but works on mobile data.** This is common on university and corporate WiFi that blocks outbound port 80. Test on your phone with WiFi off — if it loads, your server is fine.

### 2.1 Deploy a custom website

> **Do this immediately after installing Nginx.** If you delete the default `index.html` without replacing it, visiting your domain will show `403 Forbidden` because Nginx has no file to serve.

```bash
sudo nano /var/www/html/index.html
```

Paste this minimal page as a starting point — you can expand it later:

```html
<!DOCTYPE html>
<html>
<head><title>zylosoft.online</title></head>
<body>
  <h1>zylosoft.online Server</h1>
  <p>Services running on this server:</p>
  <ul>
    <li><a href="https://cloud.zylosoft.online">Nextcloud File Storage</a></li>
    <li>VPN: vpn.zylosoft.online:51820</li>
    <li>TeamSpeak: ts.zylosoft.online:9987</li>
    <li>Game Server: game.zylosoft.online:30000</li>
    <li><a href="/status.html">Server Status</a></li>
  </ul>
</body>
</html>
```

Save with `Ctrl+O` → Enter → `Ctrl+X`, then set correct ownership:

```bash
sudo chown -R www-data:www-data /var/www/html
```

> **403 Forbidden on your domain** means Nginx has no index file to serve. Either the file was never created or was accidentally deleted. Recreate it with the commands above.

---

## Step 3 — Configure DNS

Log in to your domain registrar and add the following A records, all pointing to your Azure static IP:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | `@` | `20.2.80.253` | 300 |
| A | `www` | `20.2.80.253` | 300 |
| A | `ts` | `20.2.80.253` | 300 |
| A | `vpn` | `20.2.80.253` | 300 |
| A | `game` | `20.2.80.253` | 300 |
| A | `cloud` | `20.2.80.253` | 300 |

DNS changes take 5–30 minutes to propagate. Verify before moving to SSL:

```bash
nslookup zylosoft.online
nslookup cloud.zylosoft.online
```

Both should return your Azure public IP.

---

## Step 4 — Install SSL with Let's Encrypt

> **Prerequisite:** All subdomains must be resolving to your IP in DNS before running Certbot. Run the nslookup checks above first.

```bash
sudo apt install certbot python3-certbot-nginx -y
```

Obtain certificates for all web-facing subdomains in one command:

```bash
sudo certbot --nginx \
  -d zylosoft.online \
  -d www.zylosoft.online \
  -d cloud.zylosoft.online
```

Follow the prompts — enter your email address and accept the terms.

> **Important — remember your certificate path.** Certbot stores all domains under one certificate named after the **first domain you gave it**. The paths will be:
> - `/etc/letsencrypt/live/zylosoft.online/fullchain.pem`
> - `/etc/letsencrypt/live/zylosoft.online/privkey.pem`
>
> When setting up Nextcloud in Step 8, use these exact paths. Do **not** use `/etc/letsencrypt/live/cloud.zylosoft.online/` — that path does not exist and will cause Nginx to fail.

Verify auto-renewal:

```bash
sudo certbot renew --dry-run
```

---

## Step 5 — WireGuard VPN

### 5.1 Install WireGuard

```bash
sudo apt install wireguard -y
```

### 5.2 Generate server keys

```bash
wg genkey | sudo tee /etc/wireguard/server_private.key | \
  wg pubkey | sudo tee /etc/wireguard/server_public.key
sudo chmod 600 /etc/wireguard/server_private.key
```

### 5.3 Confirm your network interface

```bash
ip route | grep default | awk '{print $5}'
```

On Azure Ubuntu 24.04 this returns `eth0`. Use whatever this command returns in the config below.

### 5.4 Create the server config

```bash
sudo cat /etc/wireguard/server_private.key
sudo nano /etc/wireguard/wg0.conf
```

Paste this — replace `SERVER_PRIVATE_KEY` with the key you just copied:

```ini
[Interface]
PrivateKey = SERVER_PRIVATE_KEY
Address = 10.0.0.1/24
ListenPort = 51820
PostUp = iptables -A FORWARD -i wg0 -j ACCEPT; iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
PostDown = iptables -D FORWARD -i wg0 -j ACCEPT; iptables -t nat -D POSTROUTING -o eth0 -j MASQUERADE
```

Save with `Ctrl+O` → Enter → `Ctrl+X`.

### 5.5 Enable IP forwarding and fix UFW for VPN traffic

Both parts are required or VPN clients will connect but have no internet access.

**Part A — Enable IP forwarding:**

```bash
echo "net.ipv4.ip_forward=1" | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

**Part B — Allow UFW to forward traffic:**

```bash
sudo nano /etc/default/ufw
```

Find `DEFAULT_FORWARD_POLICY="DROP"` and change it to:

```
DEFAULT_FORWARD_POLICY="ACCEPT"
```

Save and apply — **both commands required, in this order:**

```bash
sudo systemctl restart ufw
sudo systemctl restart wg-quick@wg0
```

> **Both restarts are required.** Restarting UFW alone is not enough — WireGuard must also be restarted after the UFW change so it re-applies the iptables rules with the new forward policy in effect. If you restart UFW but not WireGuard, the VPN will still have no internet.

**Verify WireGuard started correctly:**

```bash
sudo systemctl status wg-quick@wg0
```

`Active: active (exited)` with `status=0/SUCCESS` is correct — this is normal for WireGuard.

> **This step is not optional — skipping it will break internet on the VPN.** After connecting, the WireGuard handshake will succeed and the tunnel will show as active, but all internet traffic will silently drop. Websites will time out, DNS will fail, nothing will load. The reason: UFW's default forward policy is DROP, which blocks WireGuard from forwarding your traffic out to the internet even though the iptables MASQUERADE rule in PostUp is correct. Changing `DEFAULT_FORWARD_POLICY` to `ACCEPT` is what actually allows traffic to flow through the tunnel. If you connect and lose internet, run the two restart commands above and reconnect.

### 5.6 Add a client device

Generate client keys on your Windows laptop (PowerShell):

```powershell
cd ~\Downloads
wg genkey | tee client_private.key | wg pubkey > client_public.key
Get-Content client_private.key
Get-Content client_public.key
```

Add the client as a peer on the server:

```bash
sudo nano /etc/wireguard/wg0.conf
```

Append below the `[Interface]` section:

```ini
[Peer]
PublicKey = CLIENT_PUBLIC_KEY
AllowedIPs = 10.0.0.2/32
```

Restart WireGuard:

```bash
sudo systemctl restart wg-quick@wg0
sudo wg show
```

Get the server public key:

```bash
sudo cat /etc/wireguard/server_public.key
```

Create `client.conf` on your laptop:

```ini
[Interface]
PrivateKey = CLIENT_PRIVATE_KEY
Address = 10.0.0.2/24
DNS = 1.1.1.1

[Peer]
PublicKey = SERVER_PUBLIC_KEY
Endpoint = 20.2.80.253:51820
AllowedIPs = 0.0.0.0/0
PersistentKeepalive = 25
```

> **Replace `20.2.80.253` in the Endpoint with your actual IP address.** If you leave the placeholder text `20.2.80.253` in the config, WireGuard will show "No such host is known" when you try to connect. The Endpoint line must be the real IP, for example `57.158.25.39:51820`. Use the raw IP until DNS is set up in Step 3, then you can change it to `vpn.zylosoft.online:51820`.

Install the [WireGuard app](https://www.wireguard.com/install/), import `client.conf`, and activate. Verify at `whatismyip.com` — it should show your Azure IP.

> **HTTPS does not work on raw IP addresses.** SSL certificates only work with domain names. Always access your server via `https://zylosoft.online` in the browser. SSH connections to the raw IP (`ssh -i key.pem azureuser@20.2.80.253`) are already encrypted — SSH is always secure regardless of IP or domain.

---

## Step 6 — TeamSpeak 3

### 6.1 Install bzip2 and create a dedicated user

```bash
sudo apt install bzip2 -y
sudo adduser --disabled-login teamspeak
```

When prompted for Full Name, type anything (e.g. `admin`) and press Enter through the rest, then type `y` to confirm.

### 6.2 Download and extract TeamSpeak

```bash
cd /tmp
wget https://files.teamspeak-services.com/releases/server/3.13.7/teamspeak3-server_linux_amd64-3.13.7.tar.bz2
tar xjf teamspeak3-server_linux_amd64-3.13.7.tar.bz2
```

### 6.3 Install and set permissions

```bash
sudo mv /tmp/teamspeak3-server_linux_amd64 /home/teamspeak/ts3
sudo chown -R teamspeak:teamspeak /home/teamspeak/ts3
sudo chmod 755 /home/teamspeak
sudo chmod 755 /home/teamspeak/ts3
sudo chmod +x /home/teamspeak/ts3/ts3server
```

### 6.4 Accept the license

```bash
sudo -u teamspeak touch /home/teamspeak/ts3/.ts3server_license_accepted
```

### 6.5 First run — collect the admin token

> **Read this fully before running the command. The token only appears once on a fresh database.**

```bash
cd /home/teamspeak/ts3
sudo -u teamspeak ./ts3server
```

After about 5 seconds you will see:

```
------------------------------------------------------------------
                      I M P O R T A N T
------------------------------------------------------------------
      ServerAdmin privilege key created, please use it to gain
      serveradmin rights for your virtualserver.

       token=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
------------------------------------------------------------------
```

**Copy the full token string immediately**, then press `Ctrl+C`.

> **Common mistake — running from the wrong directory.** Running `sudo -u teamspeak ./ts3server` from `/tmp` or any other directory gives `command not found`. Always `cd /home/teamspeak/ts3` first before running the server.

### 6.6 Create a systemd service

```bash
sudo nano /etc/systemd/system/teamspeak.service
```

```ini
[Unit]
Description=TeamSpeak 3 Server
After=network.target

[Service]
User=teamspeak
WorkingDirectory=/home/teamspeak/ts3
ExecStart=/home/teamspeak/ts3/ts3server
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable teamspeak
sudo systemctl start teamspeak
sudo systemctl status teamspeak
```

Connect via TeamSpeak 3 client — address: `20.2.80.253`, port: `9987`. Paste the token when prompted.

### TeamSpeak token recovery / crash fix

If TeamSpeak is crashing, failing to start, or you missed the token, this is almost always caused by a ghost ts3server process still holding port 30033. The systemd service keeps restarting faster than you can kill it, which corrupts the database. Follow these steps exactly — **do not skip the disable step**:

```bash
# Step 1: Stop AND disable the service so it stops restarting
sudo systemctl stop teamspeak
sudo systemctl disable teamspeak

# Step 2: Kill every ts3server process and free the port
sudo pkill -9 ts3server
sudo fuser -k 30033/tcp
sleep 2

# Step 3: Confirm nothing is running (only the grep line should appear)
ps aux | grep ts3server

# Step 4: Delete the database
sudo rm -f /home/teamspeak/ts3/ts3server.sqlitedb
sudo rm -f /home/teamspeak/ts3/ts3server.sqlitedb-wal
sudo rm -f /home/teamspeak/ts3/ts3server.sqlitedb-shm

# Step 5: Run manually from the correct directory
cd /home/teamspeak/ts3
sudo -u teamspeak ./ts3server
```

Watch for the `token=` line, copy it, press `Ctrl+C`, then re-enable:

```bash
sudo systemctl enable teamspeak
sudo systemctl start teamspeak
```

> **Why `systemctl disable` is required before killing.** If you only run `systemctl stop` and then `pkill`, systemd immediately restarts the process because `Restart=always` is set in the service file. The new process grabs port 30033 before you can run the next command, causing the same `bind failed` error again. Disabling the service first prevents systemd from restarting it so `pkill` actually sticks.

> **Symptom of this problem:** TeamSpeak status shows `Start request repeated too quickly` and `Failed with result 'exit-code'`. The logs show `bind failed on 0.0.0.0:30033: Address already in use` or `disk I/O error`. Both are caused by the same root issue — a ghost process holding the port.

---

## Step 7 — Minetest Game Server

```bash
sudo apt install minetest-server -y
```

Configure the server:

```bash
sudo nano /etc/minetest/minetest.conf
```

```ini
server_name = My Game Server
server_description = ICT171 Cloud Project
server_address = game.zylosoft.online
port = 30000
max_users = 10
enable_damage = true
```

```bash
sudo systemctl enable minetest-server
sudo systemctl start minetest-server
sudo systemctl status minetest-server
```

Connect: **Join Game → Address:** `20.2.80.253` **Port:** `30000`.

---

## Step 8 — Nextcloud File Server

### 8.1 Install PHP 8.3 and extensions

> **Do not run `sudo apt install php` alone.** It pulls in Apache2 which conflicts with Nginx on port 80 and breaks your website. Install only the packages listed below.

```bash
sudo apt install php8.3 php8.3-fpm php8.3-curl php8.3-gd php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-sqlite3 php8.3-intl php8.3-bcmath php8.3-gmp -y
```

Start PHP-FPM:

```bash
sudo systemctl enable php8.3-fpm
sudo systemctl start php8.3-fpm
```

Confirm the socket path:

```bash
sudo find /run/php/ -name "*.sock"
```

Should print `/run/php/php8.3-fpm.sock`. Use this exact path in the Nginx config below.

### 8.2 Download Nextcloud

```bash
cd /tmp
wget https://download.nextcloud.com/server/releases/latest.tar.bz2
tar xjf latest.tar.bz2
sudo mv nextcloud /var/www/cloud
sudo chown -R www-data:www-data /var/www/cloud
sudo chmod -R 755 /var/www/cloud
```

### 8.3 Create the Nginx server block

> **Use the correct SSL certificate path.** Certbot stores the certificate under the first domain name you gave it (`zylosoft.online`), not under `cloud.zylosoft.online`. Use `/etc/letsencrypt/live/zylosoft.online/` in both ssl_certificate lines — the `cloud.zylosoft.online` path does not exist and will crash Nginx.

```bash
sudo nano /etc/nginx/sites-available/nextcloud
```

Paste this — replace `zylosoft.online` with your actual domain:

```nginx
server {
    listen 443 ssl;
    server_name cloud.zylosoft.online;

    ssl_certificate /etc/letsencrypt/live/zylosoft.online/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/zylosoft.online/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;

    root /var/www/cloud;
    index index.php index.html;

    client_max_body_size 512M;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ ^/(?:index|remote|public|cron|core/ajax/update|status|ocs/v[12]|updater/.+|oc[ms]-provider/.+)\.php(?:$|/) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ \.php$ {
        return 404;
    }
}

server {
    listen 80;
    server_name cloud.zylosoft.online;
    return 301 https://$host$request_uri;
}
```

Enable the nextcloud config:

```bash
sudo ln -s /etc/nginx/sites-available/nextcloud /etc/nginx/sites-enabled/
```

**Before reloading Nginx, fix the default config — this step is always required.** Certbot adds `cloud.zylosoft.online` to the default config when it issues the certificate, which causes the default config to intercept Nextcloud traffic and serve "Hello World" instead of Nextcloud. You must remove it manually:

```bash
sudo grep -n "cloud.zylosoft.online" /etc/nginx/sites-enabled/default
```

This will show lines like:

```
115:    server_name zylosoft.online cloud.zylosoft.online www.zylosoft.online;
173:    server_name zylosoft.online cloud.zylosoft.online www.zylosoft.online;
```

Open the default config and remove `cloud.zylosoft.online` from every `server_name` line it appears in:

```bash
sudo nano /etc/nginx/sites-available/default
```

Change every occurrence of:
```
server_name zylosoft.online cloud.zylosoft.online www.zylosoft.online;
```
to:
```
server_name zylosoft.online www.zylosoft.online;
```

Save, then test and reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

`nginx -t` should show no warnings. If it still shows `conflicting server name`, check the default config again for any remaining `cloud.zylosoft.online` entries.

> **Nginx fails after editing default config:** If you see `cannot load certificate "/etc/letsencrypt/live/zylosoft.online/..."` or any other wrong domain in the error, it means the default config still has old domain names in the ssl_certificate paths. Open `/etc/nginx/sites-available/default` and replace every occurrence of the old domain with your actual domain.

### 8.4 Complete the web installer

Visit `https://cloud.zylosoft.online`:

1. Enter an admin username
2. Enter a strong password
3. Leave data folder as `/var/www/cloud/data`
4. Leave database as **SQLite**
5. Click **Install**

---

## Step 9 — Server Status Script

### 9.1 Create the script

```bash
sudo nano /usr/local/bin/server-status.sh
```

Paste the following — replace `zylosoft.online` with your actual domain:

```bash
#!/bin/bash
OUTPUT="/var/www/html/status.html"
DOMAIN="zylosoft.online"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S %Z')
HOSTNAME=$(hostname)

check_service() {
    systemctl is-active --quiet "$1" && echo "Online" || echo "Offline"
}

NGINX_STATUS=$(check_service nginx)
WG_STATUS=$(check_service wg-quick@wg0)
TS_STATUS=$(check_service teamspeak)
GAME_STATUS=$(check_service minetest-server)
CLOUD_STATUS=$(check_service php8.3-fpm)

WG_PEERS=$(sudo wg show wg0 peers 2>/dev/null | wc -l)
UPTIME_DAYS=$(awk '{printf "%d", $1/86400}' /proc/uptime)
LOAD=$(cut -d ' ' -f1 /proc/loadavg)
DISK=$(df -h / | awk 'NR==2{print $5}')
MEM_USED=$(free -m | awk 'NR==2{print $3}')
MEM_TOTAL=$(free -m | awk 'NR==2{print $2}')

badge() {
    if [ "$1" = "Online" ]; then
        echo "<span class='on'>&#x25CF; Online</span>"
    else
        echo "<span class='off'>&#x25CF; Offline</span>"
    fi
}

cat > "$OUTPUT" <<EOF
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="300">
  <title>Server status — ${DOMAIN}</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #f5f5f4; --surface: #fff; --border: rgba(0,0,0,0.1);
      --text: #1c1c1a; --muted: #6b6b67; --hint: #9c9c97;
      --green-bg: #eaf3de; --green-fg: #27500a;
      --red-bg: #fcebeb; --red-fg: #791f1f;
    }
    @media (prefers-color-scheme: dark) {
      :root {
        --bg: #1a1a18; --surface: #242422; --border: rgba(255,255,255,0.1);
        --text: #e8e8e4; --muted: #9c9c97; --hint: #6b6b67;
        --green-bg: #173404; --green-fg: #c0dd97;
        --red-bg: #501313; --red-fg: #f09595;
      }
    }
    body { font-family: system-ui, sans-serif; background: var(--bg); color: var(--text); padding: 2rem 1rem; }
    .wrap { max-width: 760px; margin: 0 auto; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 12px; }
    h1 { font-size: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
    .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--green-fg); flex-shrink: 0; }
    .ts { font-size: 12px; color: var(--hint); margin-top: 4px; }
    h2 { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.07em; color: var(--hint); margin-bottom: 10px; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 2rem; }
    .card { background: var(--surface); border: 0.5px solid var(--border); border-radius: 12px; padding: 1rem; }
    .card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .card-name { font-size: 13px; font-weight: 500; }
    .card-addr { font-size: 11px; color: var(--hint); font-family: monospace; margin-top: 2px; }
    .on { font-size: 11px; font-weight: 500; padding: 3px 8px; border-radius: 6px; background: var(--green-bg); color: var(--green-fg); }
    .off { font-size: 11px; font-weight: 500; padding: 3px 8px; border-radius: 6px; background: var(--red-bg); color: var(--red-fg); }
    .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 2rem; }
    .metric { background: var(--surface); border: 0.5px solid var(--border); border-radius: 12px; padding: 0.9rem 1rem; }
    .metric-label { font-size: 11px; color: var(--hint); margin-bottom: 4px; }
    .metric-val { font-size: 22px; font-weight: 500; }
    .metric-unit { font-size: 13px; font-weight: 400; color: var(--muted); }
    .footer { font-size: 12px; color: var(--hint); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-top: 1rem; border-top: 0.5px solid var(--border); }
    .footer a { color: #185fa5; text-decoration: none; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <h1><div class="dot"></div>${DOMAIN}</h1>
      <div class="ts">Last updated: ${TIMESTAMP} &mdash; refreshes every 5 minutes</div>
    </div>
  </div>
  <h2>Services</h2>
  <div class="cards">
    <div class="card">
      <div class="card-top"><span class="card-name">Website (Nginx)</span>$(badge "$NGINX_STATUS")</div>
      <div class="card-addr">ports 80 / 443</div>
    </div>
    <div class="card">
      <div class="card-top"><span class="card-name">VPN (WireGuard)</span>$(badge "$WG_STATUS")</div>
      <div class="card-addr">vpn.${DOMAIN}:51820<br>${WG_PEERS} peer(s)</div>
    </div>
    <div class="card">
      <div class="card-top"><span class="card-name">TeamSpeak 3</span>$(badge "$TS_STATUS")</div>
      <div class="card-addr">ts.${DOMAIN}:9987</div>
    </div>
    <div class="card">
      <div class="card-top"><span class="card-name">Game server</span>$(badge "$GAME_STATUS")</div>
      <div class="card-addr">game.${DOMAIN}:30000</div>
    </div>
    <div class="card">
      <div class="card-top"><span class="card-name">Nextcloud</span>$(badge "$CLOUD_STATUS")</div>
      <div class="card-addr">cloud.${DOMAIN}</div>
    </div>
  </div>
  <h2>System metrics</h2>
  <div class="metrics">
    <div class="metric"><div class="metric-label">Uptime</div><div class="metric-val">${UPTIME_DAYS}<span class="metric-unit"> days</span></div></div>
    <div class="metric"><div class="metric-label">Memory used</div><div class="metric-val">${MEM_USED}<span class="metric-unit"> / ${MEM_TOTAL} MB</span></div></div>
    <div class="metric"><div class="metric-label">Disk used (/)</div><div class="metric-val">${DISK}</div></div>
    <div class="metric"><div class="metric-label">Load avg (1 min)</div><div class="metric-val">${LOAD}</div></div>
    <div class="metric"><div class="metric-label">VPN peers</div><div class="metric-val">${WG_PEERS}</div></div>
  </div>
  <div class="footer">
    <span>Generated by server-status.sh on ${HOSTNAME}</span>
    <span><a href="/">Home</a> &mdash; <a href="https://github.com/rumibro/ICT171-Final-Project">GitHub docs</a></span>
  </div>
</div>
</body>
</html>
EOF
```

### 9.2 Install and schedule the script

```bash
sudo chmod +x /usr/local/bin/server-status.sh

# Allow wg show without password prompt
echo "azureuser ALL=(ALL) NOPASSWD: /usr/bin/wg" | sudo tee /etc/sudoers.d/wg-status

# Test manually
sudo /usr/local/bin/server-status.sh

# Confirm the page was written
curl -I http://localhost/status.html

# Schedule via cron
sudo crontab -e

> **Status page shows a service as Offline even though it is running.** The page only updates every 5 minutes via cron. If you just started or restarted a service, the status page will show it as Offline until the next cron run. Force an immediate update at any time by running:
> ```bash
> sudo /usr/local/bin/server-status.sh
> ```
> Then refresh the page in your browser.
```

Select nano (option 1), add this line at the bottom:

```
*/5 * * * * /usr/local/bin/server-status.sh
```

Save with `Ctrl+O` → Enter → `Ctrl+X`.

> **Status page shows wrong domain name.** If the status page shows a different domain (e.g. `zylosoft.online` instead of your domain), edit the script and update the DOMAIN variable: `sudo nano /usr/local/bin/server-status.sh`, then run `sudo /usr/local/bin/server-status.sh` to regenerate immediately.

---

## Step 10 — Verify Everything

```bash
for svc in nginx wg-quick@wg0 teamspeak minetest-server php8.3-fpm; do
  printf "%-25s %s\n" "$svc:" "$(systemctl is-active $svc)"
done
```

Expected output:

```
nginx:                    active
wg-quick@wg0:             active
teamspeak:                active
minetest-server:          active
php8.3-fpm:               active
```

Check all ports listening:

```bash
sudo ss -tulnp | grep -E '80|443|9987|51820|30000'
```

Test HTTPS:

```bash
curl -sI https://zylosoft.online | head -5
```

Should return `HTTP/1.1 200 OK`.

---

## Step 11 — ZyloSoft Web Portal

### 11.1 Install unzip first

> **`unzip` is not installed by default on Ubuntu.** Install it before trying to deploy the portal or you will get `sudo: unzip: command not found`.

```bash
sudo apt install unzip -y
```

### 11.2 Upload the zip from your local PC

On your **local Windows PC** (PowerShell):

```powershell
scp -i C:\Users\YourName\Downloads\your-key.pem C:\path\to\zylosoft.zip azureuser@20.2.80.253:/home/azureuser/
```

> **The zip must exist on your local PC first.** The `scp` command copies from your PC to the server. If the file is not on your PC, the command fails with `No such file or directory`. Locate the zip on your PC before running scp.

### 11.3 Deploy on the server

```bash
sudo unzip -o ~/zylosoft.zip -d /tmp/zs/
sudo cp -r /tmp/zs/zylosoft/* /var/www/html/
sudo chown -R www-data:www-data /var/www/html/
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;
```

### 11.4 Remove the default index files

```bash
sudo rm -f /var/www/html/index.html
sudo rm -f /var/www/html/index.nginx-debian.html
sudo systemctl reload nginx
```

### 11.5 Replace the Nginx default config completely

> **Do not just edit the default config — replace it entirely.** The default Ubuntu Nginx config has PHP commented out and points to `php7.4` instead of `php8.3`. If you only edit domain names without fixing the PHP block, the portal will show `403 Forbidden` because PHP files cannot be executed. Use the full replacement below.

```bash
sudo tee /etc/nginx/sites-available/default << 'NGINXEOF'
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /var/www/html;
        index index.php index.html index.htm;
        server_name _;
        location / {
                try_files $uri $uri/ =404;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        }
}

server {
        listen [::]:443 ssl ipv6only=on;
        listen 443 ssl;
        root /var/www/html;
        index index.php index.html index.htm;
        server_name www.20.2.80.253zylosoft.online 20.2.80.253zylosoft.online;
        ssl_certificate /etc/letsencrypt/live/20.2.80.253zylosoft.online/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/20.2.80.253zylosoft.online/privkey.pem;
        include /etc/letsencrypt/options-ssl-nginx.conf;
        ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
        location / {
                try_files $uri $uri/ =404;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        }
}

server {
        if ($host = www.20.2.80.253zylosoft.online) {
                return 301 https://$host$request_uri;
        }
        if ($host = 20.2.80.253zylosoft.online) {
                return 301 https://$host$request_uri;
        }
        listen 80;
        listen [::]:80;
        server_name www.20.2.80.253zylosoft.online 20.2.80.253zylosoft.online;
        return 404;
}
NGINXEOF
sudo nginx -t && sudo systemctl reload nginx
```

> **Also remove `index.html` if portal still shows 403.** Nginx serves `index.html` before `index.php`. If a Hello World `index.html` exists in `/var/www/html`, it will be served instead of the portal:
> ```bash
> sudo rm -f /var/www/html/index.html
> sudo systemctl reload nginx
> ```

### 11.6 First login

Visit `https://20.2.80.253zylosoft.online`. Admin panel at `https://20.2.80.253zylosoft.online/panel/`:

- **Username:** `admin`
- **Password:** `admin123`

Change the admin password immediately.

---

## Common Problems Reference

| Symptom | Cause | Fix |
|---------|-------|-----|
| `403 Forbidden` on domain | No index.html in `/var/www/html` | Create one with `sudo nano /var/www/html/index.html` |
| `ERR_CONNECTION_TIMED_OUT` | Azure NSG port 80 not open | Add HTTP inbound rule in Azure Portal → Networking |
| `ERR_CONNECTION_REFUSED` | UFW not allowing port 80 | `sudo ufw allow 'Nginx Full'` |
| Browser fails but phone (mobile data) works | University WiFi blocking port 80 | Normal — server is fine, use domain name |
| `ssh: No such file or directory` | Wrong key filename | Check exact filename with `dir` in PowerShell before using it |
| `ssh: Permission denied (publickey)` | Wrong IP, wrong username, or wrong key | Use correct key filename, `azureuser@IP`, and the current Azure static IP |
| TeamSpeak `disk I/O error` | Corrupted database from interrupted run | Delete all `.sqlitedb*` files, run manually to get fresh token |
| TeamSpeak `port 30033 in use` | Ghost ts3server process still running | Disable service first, then `sudo pkill -9 ts3server && sudo fuser -k 30033/tcp` |
| `command not found` running ts3server | Wrong working directory | Always `cd /home/teamspeak/ts3` before running `./ts3server` |
| Nextcloud SSL cert not found | Wrong cert path in config | Use `/etc/letsencrypt/live/20.2.80.253zylosoft.online/` not `cloud.20.2.80.253zylosoft.online/` |
| Nginx `conflicting server_name` | Certbot added cloud domain to default config | Remove `cloud.20.2.80.253zylosoft.online` from `/etc/nginx/sites-available/default` |
| Nginx fails with wrong domain in cert path | Old domain left in default config | Replace all old domain references in `/etc/nginx/sites-available/default` |
| VPN connects but no internet | UFW forward policy is DROP | Set `DEFAULT_FORWARD_POLICY="ACCEPT"` in `/etc/default/ufw` |
| HTTPS doesn't work on raw IP | SSL certs require domain names | Always use `https://20.2.80.253zylosoft.online` — raw IP cannot be HTTPS |
| `unzip: command not found` | unzip not installed by default | `sudo apt install unzip -y` |
| `scp: No such file or directory` | Zip file not on local PC | Locate the zip on your PC before running scp |
| Status page shows wrong domain | DOMAIN variable not updated in script | Edit `DOMAIN=` in `/usr/local/bin/server-status.sh` then rerun it |
| Status page shows service Offline but it is running | Page not updated yet — cron runs every 5 min | Run `sudo /usr/local/bin/server-status.sh` then refresh browser |

---

## References

- Ubuntu Server documentation: [https://ubuntu.com/server/docs](https://ubuntu.com/server/docs)
- Nginx documentation: [https://nginx.org/en/docs/](https://nginx.org/en/docs/)
- Certbot / Let's Encrypt: [https://certbot.eff.org/docs/](https://certbot.eff.org/docs/)
- WireGuard quick start: [https://www.wireguard.com/quickstart/](https://www.wireguard.com/quickstart/)
- TeamSpeak 3 Linux server: [https://www.teamspeak.com/en/downloads/](https://www.teamspeak.com/en/downloads/)
- Minetest server setup: [https://wiki.minetest.net/Setting_up_a_server](https://wiki.minetest.net/Setting_up_a_server)
- Nextcloud installation: [https://docs.nextcloud.com/server/latest/admin_manual/installation/](https://docs.nextcloud.com/server/latest/admin_manual/installation/)
- Microsoft Azure VM documentation: [https://docs.microsoft.com/en-us/azure/virtual-machines/](https://docs.microsoft.com/en-us/azure/virtual-machines/)

---

## Generative AI Use Declaration

In accordance with Murdoch University's policy on the use of generative AI tools, the following AI assistance was used in this project:

| Tool | Purpose | Sections Affected |
|------|---------|-------------------|
| Claude (Anthropic) | Website and portal UI design assistance — generating HTML/CSS layout and styling for the web portal and status dashboard | Step 2.1 (index.html), Step 9 (status page HTML), Step 11 (ZyloSoft portal) |
| Claude (Anthropic) | Troubleshooting guidance during server setup — diagnosing configuration errors and suggesting fixes | Documentation throughout |

All server configuration, commands, and infrastructure decisions were made and verified by the student. AI-generated content was reviewed, tested, and adapted before use. No AI tool was used to generate assessment answers, reports, or academic writing submitted for marking.

---

*ICT171 — Murdoch University — 2026 Semester 1*

