import json

with open("github_arvan_zarin_repos.json", "r", encoding="utf-8") as f:
    repos = json.load(f)

zarin_repos = []
arvan_repos = []

for full_name, r in repos.items():
    desc = (r.get("description") or "").lower()
    name = r["name"].lower()
    fn = full_name.lower()
    
    is_zarin = "zarin" in fn or "zarin" in desc or "زرین" in desc
    is_arvan = "arvan" in fn or "arvan" in desc or "آروان" in desc
    
    item = {
        "full_name": r["full_name"],
        "description": r["description"],
        "stars": r["stargazers_count"],
        "language": r["language"],
        "updated_at": r["updated_at"],
        "url": r["html_url"],
        "topics": r.get("topics", [])
    }
    
    if is_zarin:
        zarin_repos.append(item)
    if is_arvan:
        arvan_repos.append(item)

print(f"Total Zarin Repos: {len(zarin_repos)}")
print(f"Total Arvan Repos: {len(arvan_repos)}")

with open("categorized_repos.json", "w", encoding="utf-8") as f:
    json.dump({"zarin": zarin_repos, "arvan": arvan_repos}, f, ensure_ascii=False, indent=2)

print("\n--- TOP ZARIN REPOS (by updated/stars) ---")
for r in sorted(zarin_repos, key=lambda x: x["updated_at"], reverse=True)[:10]:
    print(f"[{r['stars']}*] {r['full_name']} ({r['language']}) - Updated: {r['updated_at']}")
    print(f"   Desc: {r['description']}")

print("\n--- TOP ARVAN REPOS (by updated/stars) ---")
for r in sorted(arvan_repos, key=lambda x: x["updated_at"], reverse=True)[:10]:
    print(f"[{r['stars']}*] {r['full_name']} ({r['language']}) - Updated: {r['updated_at']}")
    print(f"   Desc: {r['description']}")
