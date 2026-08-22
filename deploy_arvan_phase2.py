import paramiko
import os
import requests
import json

local_base = r"C:\Users\farsh\.gemini\antigravity-ide\scratch\arvan-reseller"
remote_base = "/var/www/arvan-wp/wp-content/plugins/arvan-reseller"

files_to_sync = [
    ("includes/class-arvan-db.php", "includes/class-arvan-db.php"),
    ("includes/class-arvan-api-client.php", "includes/class-arvan-api-client.php"),
    ("includes/class-arvan-frontend.php", "includes/class-arvan-frontend.php"),
    ("includes/class-arvan-ai-agent.php", "includes/class-arvan-ai-agent.php"),
    ("templates/customer-dashboard.php", "templates/customer-dashboard.php"),
    ("assets/css/sorkhab-theme.css", "assets/css/sorkhab-theme.css"),
    ("assets/js/sorkhab-app.js", "assets/js/sorkhab-app.js"),
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print("1. Connecting to Server Hesam...")
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)
    sftp = ssh.open_sftp()
    
    print("2. Uploading updated files...")
    for rel_local, rel_remote in files_to_sync:
        loc = os.path.join(local_base, rel_local.replace("/", os.sep))
        rem = f"{remote_base}/{rel_remote}"
        print(f"   Uploading {rel_local} -> {rem}")
        sftp.put(loc, rem)
        
    sftp.close()
    
    print("3. Creating tables if needed via WP-CLI or PHP...")
    stdin, stdout, stderr = ssh.exec_command("php -r \"require_once('/var/www/arvan-wp/wp-load.php'); Arvan_DB::create_tables(); echo 'Tables updated successfully.';\"")
    print("   Output:", stdout.read().decode())
    
    ssh.close()
    print("Files synced and database schema initialized successfully!")
    
except Exception as e:
    print("Deployment Error:", e)
