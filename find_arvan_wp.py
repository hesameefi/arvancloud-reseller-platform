import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    
    # Check Nginx config for arvan.shop4bit.ir to see root path
    stdin, stdout, stderr = ssh.exec_command("cat /etc/nginx/sites-enabled/* | grep -B 2 -A 10 'arvan.shop4bit.ir'")
    out = stdout.read().decode('utf-8', errors='replace')
    print("Nginx Arvan:\n", out)
    
    ssh.close()
except Exception as e:
    print("Error:", e)
