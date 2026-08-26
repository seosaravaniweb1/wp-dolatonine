"""کلاینت OpenRouter — یک درگاه برای همه مدل‌ها."""

import json
import re

import net

API = "https://openrouter.ai/api/v1"


class AIError(Exception):
    pass


def models(cfg):
    """فهرست مدل‌های در دسترس، برای پر کردن کشوی انتخاب مدل."""
    s = net.session(cfg, "ai")
    try:
        resp = s.get(API + "/models", timeout=cfg.get("request_timeout", 45))
        resp.raise_for_status()
        data = resp.json().get("data", [])
    except Exception as exc:
        raise AIError("فهرست مدل‌ها گرفته نشد: {}".format(exc))

    out = []
    for m in data:
        out.append({
            "id": m.get("id", ""),
            "name": m.get("name") or m.get("id", ""),
        })
    out.sort(key=lambda x: x["id"])
    return out


def complete(cfg, system, user, expect_json=True):
    """یک درخواست به مدل و برگرداندن متن پاسخ."""
    key = (cfg.get("openrouter_key") or "").strip()
    if not key:
        raise AIError("کلید OpenRouter وارد نشده است.")

    model = (cfg.get("openrouter_model") or "").strip()
    if not model:
        raise AIError("مدل انتخاب نشده است.")

    headers = {
        "Authorization": "Bearer " + key,
        "Content-Type": "application/json",
        "X-Title": "Dolat Online Automation",
    }
    site = (cfg.get("openrouter_site") or cfg.get("wp_url") or "").strip()
    if site:
        headers["HTTP-Referer"] = site

    payload = {
        "model": model,
        "messages": [
            {"role": "system", "content": system},
            {"role": "user", "content": user},
        ],
        "temperature": float(cfg.get("temperature", 0.7)),
        "max_tokens": int(cfg.get("max_tokens", 8000)),
    }
    if expect_json:
        payload["response_format"] = {"type": "json_object"}

    s = net.session(cfg, "ai")
    try:
        resp = s.post(API + "/chat/completions", headers=headers, json=payload,
                      timeout=max(120, cfg.get("request_timeout", 45)))
    except Exception as exc:
        raise AIError("ارتباط با OpenRouter برقرار نشد: {}".format(exc))

    if resp.status_code == 401:
        raise AIError("کلید OpenRouter پذیرفته نشد (۴۰۱).")
    if resp.status_code == 402:
        raise AIError("اعتبار حساب OpenRouter کافی نیست (۴۰۲).")

    if resp.status_code >= 400:
        detail = ""
        try:
            detail = (resp.json().get("error") or {}).get("message") or ""
        except ValueError:
            detail = (resp.text or "")[:200]

        # بعضی مدل‌ها حالت JSON اجباری را پشتیبانی نمی‌کنند
        if expect_json and ("response_format" in detail or "json" in detail.lower()):
            return complete(cfg, system, user, expect_json=False)

        raise AIError("خطای OpenRouter ({}): {}".format(resp.status_code, detail))

    try:
        data = resp.json()
        return data["choices"][0]["message"]["content"] or ""
    except (ValueError, KeyError, IndexError):
        raise AIError("پاسخ OpenRouter قابل خواندن نبود.")


_FENCE = re.compile(r"```(?:json)?\s*(.+?)\s*```", re.S)


def parse_json(text):
    """
    JSON را از پاسخ مدل بیرون می‌کشد، حتی اگر داخل بلوک کد باشد
    یا قبل و بعدش توضیح اضافه نوشته باشد.
    """
    if not text:
        raise AIError("پاسخ مدل خالی بود.")

    candidate = text.strip()

    fence = _FENCE.search(candidate)
    if fence:
        candidate = fence.group(1).strip()

    try:
        return json.loads(candidate)
    except ValueError:
        pass

    start = candidate.find("{")
    end = candidate.rfind("}")
    if start != -1 and end > start:
        try:
            return json.loads(candidate[start:end + 1])
        except ValueError:
            pass

    raise AIError("خروجی مدل JSON معتبر نبود.")


def generate(cfg, system, user):
    """یک تولید JSON کامل: درخواست + تجزیه."""
    return parse_json(complete(cfg, system, user, expect_json=True))
