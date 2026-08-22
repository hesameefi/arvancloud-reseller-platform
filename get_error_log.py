import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)

stdin, stdout, stderr = ssh.exec_command("tail -n 25 /var/log/nginx/error.log; tail -n 25 /var/www/arvan-wp/wp-content/debug.log")
out = stdout.read().decode('utf-8', errors='ignore')
ssh.close()

with open("php_error_log.txt", "w", encoding="utf-8") as f:
    f.write(out)

print("Fetched error logs to php_error_log.txt")
