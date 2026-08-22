import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)

stdin, stdout, stderr = ssh.exec_command(r"""php -r "require_once('/var/www/arvan-wp/wp-load.php'); \$p = get_post(5); echo 'Title: ' . \$p->post_title . '\nContent:\n' . \$p->post_content;" """)
res = stdout.read().decode('utf-8')
with open("page5_content.txt", "w", encoding="utf-8") as f:
    f.write(res)
ssh.close()
print("Saved to page5_content.txt")
