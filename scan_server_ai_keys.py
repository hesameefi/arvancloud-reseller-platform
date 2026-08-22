import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect("62.238.29.248", port=3232, username="root", password="HH443115##dd", timeout=10)
    
    # Search for all API keys, OpenRouter, OpenAI, Groq, Gemini, DeepSeek in /var/www
    cmd = """
    for f in $(find /var/www -name ".env*" -o -name "config.js" -o -name "config.py" -o -name "settings.py" 2>/dev/null); do
        grep -H -E '(API_KEY|OPENAI|GEMINI|GROQ|DEEPSEEK|CLAUDE|OPENROUTER|AI_KEY)' "$f" 2>/dev/null
    done
    """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    
    with open("server_ai_keys.txt", "w", encoding="utf-8") as f:
        f.write(out)
        
    print("Saved server_ai_keys.txt!")
    ssh.close()
except Exception as e:
    print("Error:", e)
