import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    
    stdin, stdout, stderr = ssh.exec_command("pm2 show liara-assistant; echo '---'; pm2 show aiolexa-content-bot; echo '---'; pm2 show ai-telegram-bot")
    out = stdout.read().decode('utf-8', errors='replace')
    
    with open("pm2_ai_inspect.txt", "w", encoding="utf-8") as f:
        f.write(out)
        
    print("Saved pm2_ai_inspect.txt!")
    ssh.close()
except Exception as e:
    print("Error:", e)
