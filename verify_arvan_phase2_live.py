import requests
import json
import re
import paramiko

# 1. Fetch nonce from server via PHP to test live AJAX accurately
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)

stdin, stdout, stderr = ssh.exec_command("php -r \"require_once('/var/www/arvan-wp/wp-load.php'); echo wp_create_nonce('arvan_frontend_nonce');\"")
nonce = stdout.read().decode().strip()
ssh.close()

ajax_url = "https://arvan.shop4bit.ir/wp-admin/admin-ajax.php"
results = []

results.append(f"Generated Nonce: {nonce}")

# Test 1: Create S3 Bucket
bucket_name = "test-store-prod"
r1 = requests.post(ajax_url, data={
    "action": "arvan_customer_create_bucket",
    "nonce": nonce,
    "bucket_name": bucket_name,
    "region": "ir-thr-at1",
    "acl": "private"
}, timeout=10)

results.append(f"\n1. Create S3 Bucket ({bucket_name}): Status {r1.status_code}")
results.append(r1.text)

# Test 2: List S3 Buckets
r2 = requests.post(ajax_url, data={
    "action": "arvan_customer_list_buckets",
    "nonce": nonce
}, timeout=10)

results.append(f"\n2. List S3 Buckets: Status {r2.status_code}")
results.append(r2.text)

# Test 3: Check Frontend HTML for Floating AI Widget
r3 = requests.get("https://arvan.shop4bit.ir/", timeout=10)
has_fab = "ar_floating_ai_widget" in r3.text
has_drawer = "ar_floating_ai_drawer" in r3.text
results.append(f"\n3. Frontend HTML Widget Check: has_fab={has_fab}, has_drawer={has_drawer}")

# Test 4: Floating AI Assistant Interaction
r4 = requests.post(ajax_url, data={
    "action": "arvan_ai_chat_message",
    "nonce": nonce,
    "message": "بهترین باکت S3 با سرعت بالا در تهران برای مدیا چیه؟"
}, timeout=10)
results.append(f"\n4. AI Copilot S3 Query: Status {r4.status_code}")
results.append(r4.text)

output_text = "\n".join(results)
with open("arvan_phase2_live_results.txt", "w", encoding="utf-8") as f:
    f.write(output_text)

print("ArvanCloud Phase 2 verification complete!")
