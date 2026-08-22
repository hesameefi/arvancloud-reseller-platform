import requests

api_key = "02237c13-7cc4-502f-8e73-5a626e733bc0"
# In Arvancloud, auth header can be "Apikey ..." or "Bearer ..." or "ApiKey ..."

headers_list = [
    {"Authorization": f"Apikey {api_key}"},
    {"Authorization": f"ApiKey {api_key}"},
    {"Authorization": f"{api_key}"},
    {"Authorization": f"Bearer {api_key}"}
]

endpoints = [
    "https://napi.arvancloud.ir/user/v1/profile",
    "https://napi.arvancloud.ir/user/v1/user",
    "https://napi.arvancloud.ir/ecc/v1/regions",
    "https://napi.arvancloud.ir/cdn/4.0/domains",
    "https://napi.arvancloud.ir/storage/v1/buckets"
]

print("--- TESTING ARVANCLOUD API KEY ---")
for h in headers_list:
    auth_fmt = h["Authorization"][:15]
    print(f"\nTrying header format: {auth_fmt}...")
    for ep in endpoints:
        try:
            r = requests.get(ep, headers=h, timeout=5)
            print(f"  [{r.status_code}] {ep}")
            if r.status_code in [200, 201]:
                print(f"    -> SUCCESS: {r.text[:200]}")
        except Exception as e:
            print(f"    -> Error connecting to {ep}: {e}")
