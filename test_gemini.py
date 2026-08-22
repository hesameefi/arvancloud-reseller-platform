import requests
import json

gemini_key = "AIzaSyDzjoC4od8nwlXdaeQ_hp92leCOqYhNLq8"
url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={gemini_key}"

payload = {
    "contents": [
        {
            "parts": [
                {"text": "سلام! تو دستیار ابری آروان کلود هستی. کاربر پرسیده: 'تفاوت سرور ۱ گیگ و ۴ گیگ برای لاراول چیه؟'. به صورت حرفه‌ای، جذاب و دقیق به فارسی پاسخ بده."}
            ]
        }
    ]
}

r = requests.post(url, json=payload, timeout=10)
print(f"Status: {r.status_code}")
if r.status_code == 200:
    res = r.json()
    text = res['candidates'][0]['content']['parts'][0]['text']
    print("\n--- GEMINI RESPONSE ---")
    print(text[:400])
    with open("gemini_test_out.txt", "w", encoding="utf-8") as f:
        f.write(text)
else:
    print("Error:", r.text)
