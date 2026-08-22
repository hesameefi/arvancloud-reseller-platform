import paramiko
import requests
import json
import re

local_ai_agent = r"C:\Users\farsh\.gemini\antigravity-ide\scratch\arvan-reseller\includes\class-arvan-ai-agent.php"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    print("1. Connecting to Server Hesam...")
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)
    sftp = ssh.open_sftp()
    
    print("2. Uploading updated class-arvan-ai-agent.php...")
    sftp.put(local_ai_agent, "/var/www/arvan-wp/wp-content/plugins/arvan-reseller/includes/class-arvan-ai-agent.php")
    sftp.close()
    print("   -> File uploaded successfully!")
    
    print("3. Updating WordPress options with the provided Arvan API Key...")
    stdin, stdout, stderr = ssh.exec_command('wp option set arvan_api_key "02237c13-7cc4-502f-8e73-5a626e733bc0" --path=/var/www/arvan-wp --allow-root')
    print("   ->", stdout.read().decode().strip())
    
    stdin, stdout, stderr = ssh.exec_command('wp option set arvan_mode "live" --path=/var/www/arvan-wp --allow-root')
    print("   ->", stdout.read().decode().strip())
    
    ssh.close()
    print("Server updated successfully!\n")
    
    print("--- 4. LIVE VERIFICATION OF AI COPILOT ON HTTPS://ARVAN.SHOP4BIT.IR ---")
    
    # Get live nonce from customer dashboard
    r_page = requests.get("https://arvan.shop4bit.ir/", timeout=10)
    nonce_match = re.search(r'"nonce":\s*"([a-f0-9]+)"', r_page.text)
    nonce = nonce_match.group(1) if nonce_match else ""
    print(f"Extracted Nonce: {nonce}")
    
    test_prompts = [
        "سلام چطوری؟",
        "دیتاسنتر شهریار با هلند چه فرقی داره؟",
        "چطور امنیت سرور رو با SSH بالا ببرم؟",
        "یه سرور برای سایت فروشگاهی با ووکامرس میخوام"
    ]
    
    for p in test_prompts:
        print(f"\n💬 Test Prompt: '{p}'")
        res = requests.post(
            "https://arvan.shop4bit.ir/wp-admin/admin-ajax.php",
            data={
                "action": "arvan_ai_chat_message",
                "nonce": nonce,
                "message": p
            },
            timeout=10
        )
        print(f"Status: {res.status_code}")
        if res.status_code == 200:
            data = res.json()
            reply = data.get("data", {}).get("reply", "")
            action_card = data.get("data", {}).get("action_card")
            print("Response Snippet:\n", reply[:250], "...")
            print("Action Card attached:", bool(action_card))
            
except Exception as e:
    print("Error:", e)
