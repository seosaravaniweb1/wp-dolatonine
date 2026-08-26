"""
خواندن صفحات بیرونی:
  • extract_article  → عنوان، متن اصلی و تصویر شاخص یک صفحه
  • listing_links    → لینک خبرهای یک صفحه لیستینگ

استخراج متن با یک روش ساده و مقاوم انجام می‌شود: تگ‌های اضافه حذف
می‌شوند و بلوکی که بیشترین متن پیوسته را دارد به‌عنوان بدنه انتخاب می‌گردد.
"""

import re
from urllib.parse import urljoin, urlparse

from bs4 import BeautifulSoup

import net

_DROP = ("script", "style", "nav", "header", "footer", "aside", "form",
         "noscript", "iframe", "svg", "button")

_BAD_PATH = re.compile(
    r"/(tag|tags|category|categories|author|search|login|register|about|contact|privacy"
    r"|rss|feed|page|archive)(/|$)", re.I
)

_JUNK_LINE = re.compile(
    r"(کلیه حقوق|تمامی حقوق|کپی\s*رایت|انتشار مطالب|منبع\s*:|خبرگزاری\s|©|all rights reserved)",
    re.I,
)


def _soup(html):
    try:
        return BeautifulSoup(html, "lxml")
    except Exception:
        return BeautifulSoup(html, "html.parser")


def fetch(cfg, url):
    s = net.session(cfg, "scrape")
    resp = s.get(url, timeout=cfg.get("request_timeout", 45))
    resp.raise_for_status()
    resp.encoding = resp.apparent_encoding or resp.encoding or "utf-8"
    return resp.text


def _clean(soup):
    for tag in soup(list(_DROP)):
        tag.decompose()
    for tag in soup.find_all(attrs={"class": re.compile(r"(comment|share|related|sidebar|advert|banner)", re.I)}):
        tag.decompose()
    return soup


def _best_block(soup):
    """بلوکی که بیشترین متن پاراگرافی را دارد."""
    best, best_len = None, 0
    candidates = soup.find_all(["article", "main", "div", "section"])
    for node in candidates:
        paragraphs = node.find_all("p", recursive=True)
        if len(paragraphs) < 3:
            continue
        length = sum(len(p.get_text(" ", strip=True)) for p in paragraphs)
        if length > best_len:
            best, best_len = node, length
    return best or soup.body or soup


def _meta(soup, *names):
    for name in names:
        tag = soup.find("meta", attrs={"property": name}) or soup.find("meta", attrs={"name": name})
        if tag and tag.get("content"):
            return tag["content"].strip()
    return ""


def extract_article(cfg, url):
    """
    خروجی: {"url", "title", "text", "image", "published"}
    text متن ساده و تمیز مقاله است — همان چیزی که به مدل داده می‌شود.
    """
    html = fetch(cfg, url)
    soup = _soup(html)

    title = _meta(soup, "og:title", "twitter:title")
    if not title:
        h1 = soup.find("h1")
        if h1:
            title = h1.get_text(" ", strip=True)
    if not title and soup.title:
        title = soup.title.get_text(" ", strip=True)

    image = _meta(soup, "og:image", "twitter:image")
    if image:
        image = urljoin(url, image)

    published = _meta(soup, "article:published_time", "og:updated_time", "date")

    soup = _clean(soup)
    block = _best_block(soup)

    lines = []
    for node in block.find_all(["h2", "h3", "h4", "p", "li"]):
        text = node.get_text(" ", strip=True)
        is_heading = node.name in ("h2", "h3", "h4")
        # تیترها طبیعتا کوتاه‌اند، پس آستانه‌شان پایین‌تر است
        if len(text) < (6 if is_heading else 25):
            continue
        if _JUNK_LINE.search(text):
            continue
        if is_heading:
            lines.append("## " + text)
        elif node.name == "li":
            lines.append("- " + text)
        else:
            lines.append(text)

    text = "\n\n".join(dict.fromkeys(lines))  # حذف تکراری‌ها با حفظ ترتیب

    if len(text) < 200:
        # افتادن به روش ساده‌تر
        text = re.sub(r"\n{3,}", "\n\n", block.get_text("\n", strip=True))

    return {
        "url": url,
        "title": (title or "").strip(),
        "text": text.strip()[:20000],
        "image": image,
        "published": published,
    }


def listing_links(cfg, url, limit=25):
    """
    لینک مطالب یک صفحه لیستینگ را درمی‌آورد.
    فقط لینک‌های همان دامنه، با متن معنادار، و بدون مسیرهای تکراری.
    """
    html = fetch(cfg, url)
    soup = _soup(html)
    soup = _clean(soup)

    host = urlparse(url).netloc
    seen, out = set(), []

    for a in soup.find_all("a", href=True):
        href = urljoin(url, a["href"].strip())
        parsed = urlparse(href)

        if parsed.scheme not in ("http", "https"):
            continue
        if parsed.netloc != host:
            continue

        path = parsed.path.rstrip("/")
        if not path or path.count("/") < 1:
            continue
        if _BAD_PATH.search(path):
            continue

        clean = parsed.scheme + "://" + parsed.netloc + path
        if clean == url.rstrip("/") or clean in seen:
            continue

        label = a.get_text(" ", strip=True)
        if len(label) < 15:
            continue

        seen.add(clean)
        out.append({"url": clean, "title": label})
        if len(out) >= limit:
            break

    return out


def download_image(cfg, url, max_bytes=4 * 1024 * 1024):
    """تصویر شاخص را دانلود می‌کند. اگر نشد، None."""
    if not url:
        return None
    try:
        s = net.session(cfg, "scrape")
        resp = s.get(url, timeout=cfg.get("request_timeout", 45), stream=True)
        resp.raise_for_status()

        mime = (resp.headers.get("Content-Type") or "").split(";")[0].strip()
        if not mime.startswith("image/"):
            return None

        data = b""
        for chunk in resp.iter_content(65536):
            data += chunk
            if len(data) > max_bytes:
                return None
        if len(data) < 4096:
            return None

        return {"data": data, "mime": mime, "url": url}
    except Exception:
        return None
