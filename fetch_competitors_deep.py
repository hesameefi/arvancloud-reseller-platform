import requests
import json
import time

repos_to_inspect = [
    # Zarinpal
    "zhaleh197/Zarinpal",
    "MostafaMashhadi/ZarinPalMerchantDashboard",
    "parsanaderidev/zarinpal",
    "sina-04/ZarinPal-Challenge",
    "NedaKhosravi/zarinpal-hackaton",
    "Hoomanghkhani/ZarinPal-Merchant-Pulse",
    # Arvan
    "NedaKhosravi/arvan-reseller-engine",
    "yazdan2727/arvan-reseller",
    "hadi78m/arvan-reseller-plugin"
]

headers = {
    "Accept": "application/vnd.github.v3+json",
    "User-Agent": "Deep-Comparator-Agent"
}

detailed_analysis = {}

for repo_full in repos_to_inspect:
    print(f"Fetching {repo_full}...")
    repo_data = {"full_name": repo_full, "readme": "", "tree": []}
    
    # 1. Fetch README
    readme_url = f"https://api.github.com/repos/{repo_full}/readme"
    try:
        r = requests.get(readme_url, headers=headers, timeout=10)
        if r.status_code == 200:
            import base64
            content = r.json().get("content", "")
            readme_text = base64.b64decode(content).decode('utf-8', errors='replace')
            repo_data["readme"] = readme_text[:4000]
    except Exception as e:
        print(f"  Error fetching readme for {repo_full}: {e}")
        
    # 2. Fetch File tree
    tree_url = f"https://api.github.com/repos/{repo_full}/git/trees/main?recursive=1"
    try:
        r = requests.get(tree_url, headers=headers, timeout=10)
        if r.status_code != 200:
            tree_url = f"https://api.github.com/repos/{repo_full}/git/trees/master?recursive=1"
            r = requests.get(tree_url, headers=headers, timeout=10)
        if r.status_code == 200:
            files = [item["path"] for item in r.json().get("tree", []) if item["type"] == "blob"]
            repo_data["tree"] = files[:50]
    except Exception as e:
        print(f"  Error fetching tree for {repo_full}: {e}")
        
    detailed_analysis[repo_full] = repo_data
    time.sleep(1)

with open("competitor_deep_details.json", "w", encoding="utf-8") as f:
    json.dump(detailed_analysis, f, ensure_ascii=False, indent=2)

print("Saved competitor_deep_details.json successfully!")
