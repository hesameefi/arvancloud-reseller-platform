import paramiko
import requests

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)

sftp = ssh.open_sftp()
sftp.put(r"C:\Users\farsh\.gemini\antigravity-ide\scratch\arvan-reseller\includes\class-arvan-ai-agent.php", "/var/www/arvan-wp/wp-content/plugins/arvan-reseller/includes/class-arvan-ai-agent.php")
sftp.close()

stdin, stdout, stderr = ssh.exec_command("wp eval \"echo wp_create_nonce('arvan_frontend_nonce');\" --path=/var/www/arvan-wp --allow-root")
nonce = stdout.read().decode().strip()
ssh.close()

test_prompts = [
    "سلام چطوری؟",
    "دیتاسنتر شهریار با هلند چه فرقی داره؟",
    "چطور امنیت سرور رو با SSH بالا ببرم؟",
    "یه سرور برای سایت فروشگاهی با ووکامرس میخوام",
    "قیمت و تعرفه سرورها چطوریه؟"
]

results = [f"=== TEST RESULTS (Nonce: {nonce}) ==="]

for p in test_prompts:
    res = requests.post(
        "https://arvan.shop4bit.ir/wp-admin/admin-ajax.php",
        data={
            "action": "arvan_ai_chat_message",
            "nonce": nonce,
            "message": p
        },
        timeout=10
    )
    results.append(f"\n--- PROMPT: {p} (Status: {res.status_code}) ---")
    if res.status_code == 200:
        data = res.json()
        reply = data.get("data", {}).get("reply", "")
        action_card = data.get("data", {}).get("action_card")
        results.append(f"Reply:\n{reply}")
        results.append(f"Has Action Card: {bool(action_card)}")

with open("arvan_ai_test_results.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(results))

print("Completed deployment and live testing!")
