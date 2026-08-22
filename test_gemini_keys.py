import requests

keys = [
    "AIzaSyCl9s_p-0oalWX7thj9VMGZNVmTsu9-MF8",
    "AIzaSyBUY1ZIL98tetSYwARxe8z56MsG1vRMGjc"
]

for k in keys:
    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={k}"
    payload = {
        "contents": [{"parts": [{"text": "سلام! یک جمله کوتاه بگو تو کی هستی."}]}]
    }
    try:
        r = requests.post(url, json=payload, timeout=8)
        print(f"Key {k[:12]}... -> Status {r.status_code}")
        if r.status_code == 200:
            print("   SUCCESS:", r.json()['candidates'][0]['content']['parts'][0]['text'])
    except Exception as e:
        print("   Error:", e)
