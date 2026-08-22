import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    
    cmd = """
    cat /var/www/bizcommand-os/.env 2>/dev/null | grep -E 'KEY|SECRET|AI|OPENAI'
    cat /var/www/ngt_website/backend/.env 2>/dev/null | grep -E 'KEY|SECRET|AI|OPENAI|GEMINI'
    cat /var/www/ashrafi/.env 2>/dev/null | grep -E 'KEY|SECRET|AI|OPENAI|GEMINI'
    cat /var/www/liara-assistant/.env 2>/dev/null
    """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    
    with open("more_server_keys.txt", "w", encoding="utf-8") as f:
        f.write(out)
        
    print("Saved more_server_keys.txt!")
    ssh.close()
except Exception as e:
    print("Error:", e)
