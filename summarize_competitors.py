import json

with open("competitor_deep_details.json", "r", encoding="utf-8") as f:
    details = json.load(f)

report = []

for repo_name, d in details.items():
    report.append(f"==================================================")
    report.append(f"REPO: {repo_name}")
    report.append(f"FILES ({len(d['tree'])} files):")
    for f_path in d['tree'][:15]:
        report.append(f"  - {f_path}")
    report.append(f"\nREADME EXCERPT:\n{d['readme'][:1200]}")
    report.append("\n")

with open("competitor_summary_report.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(report))

print("Saved competitor_summary_report.txt successfully!")
