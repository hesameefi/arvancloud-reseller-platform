import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    
    cmd = """
    head -n 40 /var/www/starcoach/02_چالش_دوم_لیارا/server.py 2>/dev/null
    head -n 40 /var/www/aiolexa-content-bot/bot.py 2>/dev/null
    head -n 40 /var/www/ai-operator/server.py 2>/dev/null
    """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    
    with open("ai_source_inspect.txt", "w", encoding="utf-8") as f:
        f.write(out)
        
    print("Saved ai_source_inspect.txt!")
    ssh.close()
except Exception as e:
    print("Error:", e)
