import requests
import json
import time

queries = [
    # ZarinPal queries
    "zarinpal",
    "zarinpal-sdk",
    "zarinpal analytics",
    "zarinpal challenge",
    # ArvanCloud queries
    "arvancloud",
    "arvan cloud",
    "arvan-reseller",
    "arvan challenge",
    "arvancloud-sdk",
    "arvancloud-cli",
    "arvan paas",
    "arvan iaas"
]

all_repos = {}

headers = {
    "Accept": "application/vnd.github.v3+json",
    "User-Agent": "Arvan-Zarin-Research-Agent"
}

for q in queries:
    url = f"https://api.github.com/search/repositories?q={q}&sort=updated&order=desc&per_page=20"
    try:
        r = requests.get(url, headers=headers, timeout=10)
        if r.status_code == 200:
            items = r.json().get("items", [])
            for item in items:
                repo_id = item["full_name"]
                if repo_id not in all_repos:
                    all_repos[repo_id] = {
                        "name": item["name"],
                        "full_name": item["full_name"],
                        "description": item["description"],
                        "html_url": item["html_url"],
                        "updated_at": item["updated_at"],
                        "created_at": item["created_at"],
                        "pushed_at": item["pushed_at"],
                        "stargazers_count": item["stargazers_count"],
                        "language": item["language"],
                        "topics": item.get("topics", []),
                        "query_match": q
                    }
        else:
            print(f"Search for {q} returned {r.status_code}")
        time.sleep(1)
    except Exception as e:
        print(f"Error querying {q}: {e}")

with open("github_arvan_zarin_repos.json", "w", encoding="utf-8") as f:
    json.dump(all_repos, f, ensure_ascii=False, indent=2)

print(f"Found {len(all_repos)} unique repositories!")
