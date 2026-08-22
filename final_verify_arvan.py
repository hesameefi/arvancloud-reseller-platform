import paramiko
import requests

local_frontend = r"C:\Users\farsh\.gemini\antigravity-ide\scratch\arvan-reseller\includes\class-arvan-frontend.php"
remote_frontend = "/var/www/arvan-wp/wp-content/plugins/arvan-reseller/includes/class-arvan-frontend.php"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)
sftp = ssh.open_sftp()
sftp.put(local_frontend, remote_frontend)
sftp.close()
ssh.close()
print("Uploaded class-arvan-frontend.php.")

r = requests.get("https://arvan.shop4bit.ir/", timeout=10)
has_widget = "ar_floating_ai_widget" in r.text
has_drawer = "ar_floating_ai_drawer" in r.text
has_buckets = "ar_s3_buckets_list" in r.text

print(f"Verification Results:")
print(f"  - Status Code: {r.status_code}")
print(f"  - Has Floating Widget: {has_widget}")
print(f"  - Has Drawer: {has_drawer}")
print(f"  - Has S3 Buckets UI: {has_buckets}")

with open("arvan_final_check.txt", "w", encoding="utf-8") as f:
    f.write(f"Status: {r.status_code}\nWidget: {has_widget}\nDrawer: {has_drawer}\nBuckets: {has_buckets}\n")
