import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    print("SSH Connected!")
    
    stdin, stdout, stderr = ssh.exec_command("grep -E '(GEMINI|OPENAI|GROQ|DEEPSEEK|ANTHROPIC)_API_KEY' /var/www/*/.env /var/www/*/*/.env 2>/dev/null")
    out = stdout.read().decode('utf-8', errors='replace')
    print("Found Keys:\n", out)
    
    ssh.close()
except Exception as e:
    print("Error:", e)
