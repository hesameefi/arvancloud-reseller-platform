import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=15)

stdin, stdout, stderr = ssh.exec_command("php -r \"require_once('/var/www/arvan-wp/wp-load.php'); echo 'Active Theme: ' . get_stylesheet() . ' | Home Page ID: ' . get_option('page_on_front') . ' | Show on front: ' . get_option('show_on_front');\"")
print(stdout.read().decode())

stdin, stdout, stderr = ssh.exec_command("php -r \"require_once('/var/www/arvan-wp/wp-load.php'); var_dump(has_action('wp_footer', array(Arvan_Frontend::get_instance(), 'render_floating_ai_widget')));\"")
print(stdout.read().decode())

ssh.close()
