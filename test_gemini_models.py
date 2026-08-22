import requests

keys = [
    "AIzaSyCl9s_p-0oalWX7thj9VMGZNVmTsu9-MF8",
    "AIzaSyBUY1ZIL98tetSYwARxe8z56MsG1vRMGjc"
]

models = ["gemini-1.5-flash", "gemini-1.5-pro", "gemini-pro"]

for k in keys:
    for m in models:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{m}:generateContent?key={k}"
        payload = {"contents": [{"parts": [{"text": "Hello"}]}]}
        try:
            r = requests.post(url, json=payload, timeout=5)
            print(f"Key {k[:10]} | Model {m} -> Status {r.status_code}")
            if r.status_code == 200:
                print("   SUCCESS!", r.json()['candidates'][0]['content']['parts'][0]['text'])
                break
        except Exception as e:
            print("   Error:", e)
