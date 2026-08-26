"""
کارهای مشترک روی متن فارسی: نرمال‌سازی و تشخیص نقش دسته.

منطق تشخیص نقش دقیقا معادل تابع dolat_detect_role_from_name در قالب است،
تا اسکریپت و سایت یک برداشت از «اخبار / آموزش / استعلام» داشته باشند.
"""

import re

# ی و ک عربی، همزه‌ها، تای گرد و نیم‌فاصله
_TRANS = str.maketrans({
    "ي": "ی",
    "ك": "ک",
    "أ": "ا",
    "إ": "ا",
    "ٱ": "ا",
    "ة": "ه",
    "ۀ": "ه",
    "‌": " ",   # نیم‌فاصله
    "‎": "",
    "‏": "",
    "﻿": "",
})

# اعراب و کشیده
_DIACRITICS = re.compile(r"[ً-ْـٰ]")


def norm(text):
    """نرمال‌سازی متن فارسی برای مقایسه."""
    if not text:
        return ""
    out = str(text).translate(_TRANS)
    out = _DIACRITICS.sub("", out)
    return re.sub(r"\s+", " ", out).strip()


ROLE_KEYWORDS = {
    "news": ["اخبار", "خبر"],
    "edu": ["آموزش", "اموزش", "راهنما", "ثبت نام"],
    "estelam": ["استعلام", "استعلامات"],
}

ROLE_LABELS = {
    "news": "اخبار",
    "edu": "آموزش",
    "estelam": "استعلام",
}


def detect_role(name):
    """
    نقش یک دسته را از روی نامش تشخیص می‌دهد.
    اول شروع نام بررسی می‌شود، بعد هر جای نام — مثل خود قالب.
    """
    n = norm(name)
    if not n:
        return ""

    for role, keywords in ROLE_KEYWORDS.items():
        for kw in keywords:
            if n.startswith(norm(kw)):
                return role

    for role, keywords in ROLE_KEYWORDS.items():
        for kw in keywords:
            if norm(kw) in n:
                return role

    return ""


def strip_role_words(name):
    """«استعلام یارانه‌ها» → «یارانه ها» تا با نام دسته مادر مقایسه شود."""
    n = norm(name)
    for keywords in ROLE_KEYWORDS.values():
        for kw in keywords:
            k = norm(kw)
            if n.startswith(k):
                n = n[len(k):].strip()
                break
    return n


def same_topic(a, b):
    """
    آیا این دو نام به یک موضوع اشاره می‌کنند؟
    «یارانه» و «یارانه‌ها» و «یارانه ها» یکی حساب می‌شوند.
    """
    x, y = norm(a), norm(b)
    if not x or not y:
        return False
    if x == y:
        return True

    # پسوند جمع «ها» را نادیده بگیر
    def base(s):
        s = re.sub(r"\s*ها$", "", s).strip()
        return re.sub(r"\s+", "", s)

    return base(x) == base(y)
