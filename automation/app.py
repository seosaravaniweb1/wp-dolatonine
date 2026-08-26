"""
پنل گرافیکی اتوماسیون محتوای «دولت آنلاین».

اجرا: روی start.bat دابل‌کلیک کنید. مرورگر خودکار باز می‌شود.
سرور فقط روی 127.0.0.1 گوش می‌دهد؛ از بیرون قابل دسترسی نیست.
"""

import os
import socket
import threading
import webbrowser

from flask import Flask, jsonify, render_template, request

import ai
import config
import jobs
import sheets
import store
from wp import WordPress, WPError

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

app = Flask(__name__, template_folder="templates", static_folder="static")
app.config["JSON_AS_ASCII"] = False


def _ok(**kwargs):
    out = {"ok": True}
    out.update(kwargs)
    return jsonify(out)


def _fail(message, code=200):
    return jsonify({"ok": False, "error": str(message)}), code


@app.after_request
def _no_cache(resp):
    resp.headers["Cache-Control"] = "no-store"
    return resp


@app.route("/")
def index():
    return render_template("index.html")


# ═══════════════ تنظیمات ═══════════════

@app.route("/api/config", methods=["GET"])
def api_config_get():
    cfg = config.load()
    safe = dict(cfg)
    # کلیدها را ماسک نمی‌کنیم چون فقط روی همین کامپیوتر باز می‌شود،
    # ولی وجودشان را جدا هم اعلام می‌کنیم تا پنل بتواند وضعیت نشان دهد.
    return _ok(config=safe, missing=config.is_ready(cfg))


@app.route("/api/config", methods=["POST"])
def api_config_set():
    data = request.get_json(silent=True) or {}
    cfg = config.save(data)
    return _ok(config=cfg, missing=config.is_ready(cfg))


@app.route("/api/check-wp", methods=["POST"])
def api_check_wp():
    cfg = config.load()
    try:
        wpc = WordPress(cfg)
        name = wpc.check()
        sections = wpc.sections(refresh=True)
        return _ok(user=name, sections=sections)
    except WPError as exc:
        return _fail(exc)
    except Exception as exc:
        return _fail(exc)


@app.route("/api/models", methods=["GET"])
def api_models():
    cfg = config.load()
    try:
        return _ok(models=ai.models(cfg))
    except ai.AIError as exc:
        return _fail(exc)


@app.route("/api/check-sheet", methods=["POST"])
def api_check_sheet():
    cfg = config.load()
    url = (request.get_json(silent=True) or {}).get("url", "")
    if not url:
        return _fail("لینک وارد نشده است.")
    try:
        rows = sheets.fetch_rows(cfg, url)
        columns = list(rows[0].keys()) if rows else []
        return _ok(count=len(rows), columns=columns, preview=rows[:5])
    except Exception as exc:
        return _fail(exc)


# ═══════════════ ساختار سایت ═══════════════

@app.route("/api/sections", methods=["GET"])
def api_sections():
    cfg = config.load()
    try:
        wpc = WordPress(cfg)
        refresh = request.args.get("refresh") == "1"
        return _ok(sections=wpc.sections(refresh=refresh))
    except Exception as exc:
        return _fail(exc)


# ═══════════════ اجرا ═══════════════

@app.route("/api/run/<kind>", methods=["POST"])
def api_run(kind):
    if kind not in ("edu", "news", "estelam"):
        return _fail("نوع کار نامعتبر است.")

    cfg = config.load()
    missing = config.is_ready(cfg)
    if missing:
        return _fail("این موارد در تنظیمات خالی است: " + "، ".join(missing))

    sheet_key = {"edu": "sheet_edu", "news": "sheet_news", "estelam": "sheet_estelam"}[kind]
    if not cfg.get(sheet_key):
        return _fail("لینک گوگل شیت این بخش در تنظیمات وارد نشده است.")

    options = request.get_json(silent=True) or {}
    started, message = jobs.start(kind, cfg, options)
    if not started:
        return _fail(message)
    return _ok()


@app.route("/api/status", methods=["GET"])
def api_status():
    return _ok(state=jobs.state(), counts=store.counts())


@app.route("/api/cancel", methods=["POST"])
def api_cancel():
    return _ok(cancelled=jobs.cancel())


# ═══════════════ کارتابل ═══════════════

@app.route("/api/items", methods=["GET"])
def api_items():
    kind = request.args.get("kind", "")
    limit = int(request.args.get("limit", 200))
    return _ok(items=store.recent(limit=limit, kind=kind))


@app.route("/api/items/<int:item_id>", methods=["DELETE"])
def api_item_delete(item_id):
    store.forget(item_id)
    return _ok()


@app.route("/api/items/clear-failed", methods=["POST"])
def api_items_clear_failed():
    kind = (request.get_json(silent=True) or {}).get("kind", "")
    store.forget_failed(kind)
    return _ok()


# ═══════════════ راه‌اندازی ═══════════════

def _free_port(preferred=8733):
    for port in range(preferred, preferred + 20):
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            if s.connect_ex(("127.0.0.1", port)) != 0:
                return port
    return preferred


def main():
    store.init()
    port = _free_port()
    url = "http://127.0.0.1:{}/".format(port)

    # مرورگر را بعد از بالا آمدن سرور باز کن
    if os.environ.get("WERKZEUG_RUN_MAIN") != "true":
        threading.Timer(1.2, lambda: webbrowser.open(url)).start()

    print("\n" + "─" * 52)
    print("  پنل اتوماسیون دولت آنلاین")
    print("  آدرس: " + url)
    print("  برای بستن، این پنجره را ببندید یا Ctrl+C بزنید.")
    print("─" * 52 + "\n")

    app.run(host="127.0.0.1", port=port, debug=False, use_reloader=False, threaded=True)


if __name__ == "__main__":
    main()
