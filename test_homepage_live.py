import requests
import json
import time

t0 = time.time()
r = requests.get("https://arvan.shop4bit.ir/", timeout=10)
latency = round((time.time() - t0) * 1000, 2)

has_widget = "ar_floating_ai_widget" in r.text
has_drawer = "ar_floating_ai_drawer" in r.text
has_buckets = "ar_s3_buckets_list" in r.text
has_theme = "sorkhab-theme.css" in r.text

print(f"Status Code: {r.status_code}")
print(f"Page Load Latency: {latency}ms")
print(f"Has Floating AI Widget: {has_widget}")
print(f"Has Drawer: {has_drawer}")
print(f"Has S3 Buckets Container: {has_buckets}")
print(f"Has Sorkhab Theme: {has_theme}")

with open("arvan_live_test_summary.json", "w", encoding="utf-8") as f:
    json.dump({
        "status_code": r.status_code,
        "latency_ms": latency,
        "has_floating_widget": has_widget,
        "has_drawer": has_drawer,
        "has_s3_buckets": has_buckets,
        "has_theme": has_theme
    }, f, ensure_ascii=False, indent=2)

print("Saved to arvan_live_test_summary.json")
