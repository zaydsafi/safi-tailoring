"""Fetch free-licence photo candidates from Openverse for the shop catalogue.

Writes thumbnails to a scratch folder and prints a JSON manifest so a human
can pick one picture per product before anything is written into the app.
"""

from __future__ import annotations

import json
import sys
import urllib.parse
import urllib.request
from pathlib import Path

OUT = Path(sys.argv[1] if len(sys.argv) > 1 else "tmp/candidates")
QUERIES = json.loads(Path(sys.argv[2]).read_text(encoding="utf-8"))

API = "https://api.openverse.org/v1/images/"
UA = "safi-tailoring-seed/1.0 (student project)"


def search(query: str, limit: int = 6) -> list[dict]:
    params = {
        "q": query,
        "license": "cc0,pdm,by,by-sa",
        "page_size": limit,
        "size": "large",
    }
    url = API + "?" + urllib.parse.urlencode(params)
    request = urllib.request.Request(url, headers={"User-Agent": UA})

    with urllib.request.urlopen(request, timeout=30) as response:
        return json.load(response).get("results", [])


def download(url: str, target: Path) -> bool:
    try:
        request = urllib.request.Request(url, headers={"User-Agent": UA})
        with urllib.request.urlopen(request, timeout=40) as response:
            data = response.read()

        if len(data) < 8_000:
            return False

        target.write_bytes(data)
        return True
    except Exception:
        return False


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    manifest: dict[str, list[dict]] = {}

    for key, query in QUERIES.items():
        results = search(query)
        saved = []

        for index, item in enumerate(results):
            thumb = item.get("thumbnail") or item.get("url")
            if not thumb:
                continue

            target = OUT / f"{key}-{index}.jpg"
            if not target.exists() and not download(thumb, target):
                continue

            saved.append({
                "file": str(target),
                "title": item.get("title"),
                "license": item.get("license"),
                "creator": item.get("creator"),
                "page": item.get("foreign_landing_url"),
                "url": item.get("url"),
                "width": item.get("width"),
                "height": item.get("height"),
            })

        manifest[key] = saved
        print(f"{key}: {len(saved)} candidates for {query!r}")

    (OUT / "manifest.json").write_text(json.dumps(manifest, indent=2), encoding="utf-8")


if __name__ == "__main__":
    main()
