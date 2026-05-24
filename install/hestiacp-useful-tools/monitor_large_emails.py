#!/usr/bin/env python3
import re
import sys
import os
import subprocess
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import datetime

# --- Configuration ---
CONFIG_FILE = "/etc/hestiacp-exim-limit.conf"
HESTIA_DIR = "/usr/local/hestia"
LOG_FILE = "/var/log/exim4/mainlog"
STATE_FILE = "/root/.monitor_large_emails.state"
THRESHOLD_SIZE_MB = 25
VERBOSE = False  # Set to True for debug output, False for silence (cron friendly)

# Regex to match the custom log line we added
LOG_PATTERN = re.compile(r'(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).*Large message rejected: from (.*?) to (.*?) size (\d+)')

def read_config_value(key):
    if not os.path.exists(CONFIG_FILE):
        return ""

    pattern = re.compile(rf'^\s*{re.escape(key)}=(.*)\s*$')
    with open(CONFIG_FILE, "r", encoding="utf-8", errors="ignore") as f:
        for line in f:
            match = pattern.match(line)
            if not match:
                continue
            value = match.group(1).strip().strip('"').strip("'")
            return value
    return ""

def read_hestia_conf_value(path, key):
    if not os.path.exists(path):
        return ""

    pattern = re.compile(rf'^{re.escape(key)}=\'(.*)\'$')
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        for line in f:
            match = pattern.match(line.strip())
            if match:
                return match.group(1)
    return ""

def get_hostname():
    try:
        hostname = subprocess.check_output(["hostname", "-f"], text=True, stderr=subprocess.DEVNULL).strip()
        if hostname:
            return hostname
    except Exception:
        pass
    return os.uname().nodename

def get_admin_email():
    config_email = read_config_value("ADMIN_EMAIL")
    if config_email:
        return config_email

    users_dir = os.path.join(HESTIA_DIR, "data", "users")
    if os.path.isdir(users_dir):
        for username in sorted(os.listdir(users_dir)):
            user_conf = os.path.join(users_dir, username, "user.conf")
            if read_hestia_conf_value(user_conf, "ROLE") == "admin":
                contact = read_hestia_conf_value(user_conf, "CONTACT")
                if contact:
                    return contact

    return read_hestia_conf_value(os.path.join(HESTIA_DIR, "conf", "hestia.conf"), "CONTACT")

HOSTNAME = get_hostname()
ADMIN_EMAIL = get_admin_email()
SENDER_EMAIL = read_config_value("SENDER_EMAIL") or f"exim-monitor@{HOSTNAME}"

def send_email(subject, html_body):
    if not ADMIN_EMAIL:
        if VERBOSE:
            print("No admin email configured; skipping alert email", file=sys.stderr)
        return

    msg = MIMEMultipart("alternative")
    msg["From"] = SENDER_EMAIL
    msg["To"] = ADMIN_EMAIL
    msg["Subject"] = subject
    
    part_html = MIMEText(html_body, "html")
    msg.attach(part_html)

    try:
        # Use exim directly to bypass local SMTP restrictions/auth issues
        process = subprocess.Popen(["/usr/sbin/exim4", "-t"], stdin=subprocess.PIPE)
        process.communicate(input=msg.as_string().encode())
        if process.returncode == 0:
            if VERBOSE:
                print(f"Email sent to {ADMIN_EMAIL}")
        else:
            print(f"Error sending email (exit code {process.returncode})", file=sys.stderr)
    except Exception as e:
        print(f"Error sending email: {e}", file=sys.stderr)

def get_html_template(title, intro, rows):
    hostname = HOSTNAME
    return f"""
    <html>
    <body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
            <h2 style="color: #d9534f; margin-top: 0;">{title}</h2>
            <p><strong>Server:</strong> {hostname}</p>
            <p>{intro}</p>
            
            <table style="border-collapse: collapse; width: 100%; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left;">Date/Time</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left;">Sender</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left;">Recipient</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left;">Size</th>
                    </tr>
                </thead>
                <tbody>
                    {rows}
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
                <strong>Recommended Action:</strong><br>
                Please contact the client and suggest using services like WeTransfer, Google Drive, or OneDrive for sharing large files.
            </div>
            
            <p style="margin-top: 20px; font-size: 0.8em; color: #999; text-align: center;">
                This email was automatically generated by the Exim monitoring system on {hostname}.
            </p>
        </div>
    </body>
    </html>
    """

def send_alert(alerts, is_test=False):
    if is_test:
        title = "⚠️ Test: Large Email Alert"
        intro = "This is a test email to validate the notification system. No action is required."
        subject = "[TEST] Large Email Block Alert"
    else:
        title = "🚫 Blocked Emails (>25MB)"
        intro = "The system detected and blocked the following attempts to send oversized emails:"
        subject = f"[ALERT] {len(alerts)} Large Emails Blocked"

    html_rows = ""
    for alert in alerts:
        try:
            size_mb = f"{int(alert['size']) / 1024 / 1024:.2f} MB"
        except:
            size_mb = f"{alert['size']} bytes"
            
        html_rows += f"""
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee; font-size: 0.9em;">{alert['date']}</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; font-size: 0.9em;">{alert['sender']}</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; font-size: 0.9em;">{alert['recipient']}</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; font-size: 0.9em; color: #d9534f; font-weight: bold;">{size_mb}</td>
        </tr>
        """
    
    html_body = get_html_template(title, intro, html_rows)
    send_email(subject, html_body)

def get_last_position():
    if os.path.exists(STATE_FILE):
        with open(STATE_FILE, "r") as f:
            try:
                return int(f.read().strip())
            except:
                return 0
    return 0

def save_last_position(pos):
    with open(STATE_FILE, "w") as f:
        f.write(str(pos))

def parse_logs():
    last_pos = get_last_position()
    
    if not os.path.exists(LOG_FILE):
        return

    current_size = os.path.getsize(LOG_FILE)
    
    # Log rotated? Reset
    if current_size < last_pos:
        last_pos = 0
        
    alerts = []
    
    with open(LOG_FILE, "r", encoding="utf-8", errors="ignore") as f:
        f.seek(last_pos)
        content = f.read()
        
        # Find all matches in the new content
        for match in LOG_PATTERN.finditer(content):
            alerts.append({
                "date": match.group(1),
                "sender": match.group(2),
                "recipient": match.group(3),
                "size": match.group(4)
            })
        
        new_pos = f.tell()
        
    if alerts:
        send_alert(alerts)
    
    save_last_position(new_pos)

if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "--test":
        # Generate dummy data for testing
        dummy_alerts = [{
            "date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "sender": "test@client.com",
            "recipient": "destination@gmail.com",
            "size": "16777216" # 16MB
        }]
        send_alert(dummy_alerts, is_test=True)
    else:
        parse_logs()
