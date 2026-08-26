"""
کارتابل و ضدتکرار — یک پایگاه داده SQLite کنار اسکریپت.

هر آیتم یک «کلید یکتا» دارد:
  اخبار   → آدرس خبر منبع
  آموزش   → عنوان ردیف شیت
  استعلام → عنوان ردیف شیت
اگر کلیدی قبلا با موفقیت درج شده باشد، دوباره پردازش نمی‌شود.
"""

import os
import sqlite3
import threading
import time

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DB_PATH = os.path.join(BASE_DIR, "data.sqlite3")

_lock = threading.Lock()

SCHEMA = """
CREATE TABLE IF NOT EXISTS items (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    kind        TEXT NOT NULL,
    key         TEXT NOT NULL,
    title       TEXT,
    section     TEXT,
    status      TEXT NOT NULL,
    wp_post_id  INTEGER,
    wp_url      TEXT,
    message     TEXT,
    created_at  INTEGER NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS items_kind_key ON items (kind, key);
CREATE INDEX IF NOT EXISTS items_created ON items (created_at DESC);
"""


def _connect():
    conn = sqlite3.connect(DB_PATH, timeout=30)
    conn.row_factory = sqlite3.Row
    return conn


def init():
    with _lock, _connect() as conn:
        conn.executescript(SCHEMA)


# وضعیت‌هایی که نهایی‌اند و دوباره پردازش نمی‌شوند
FINAL = ("done", "rejected")


def already_done(kind, key):
    """
    آیا این آیتم تعیین تکلیف شده؟
    done     → منتشر شد
    rejected → عمدا کنار گذاشته شد (خبر نبود، یا عنوانش در سایت هست)
    skipped و error دوباره امتحان می‌شوند، چون ممکن است خطای موقت بوده باشد.
    """
    with _lock, _connect() as conn:
        row = conn.execute(
            "SELECT status FROM items WHERE kind = ? AND key = ?", (kind, key)
        ).fetchone()
    return bool(row) and row["status"] in FINAL


def record(kind, key, status, title="", section="", wp_post_id=None, wp_url="", message=""):
    """ثبت یا به‌روزرسانی وضعیت یک آیتم."""
    with _lock, _connect() as conn:
        conn.execute(
            """
            INSERT INTO items (kind, key, title, section, status, wp_post_id, wp_url, message, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT(kind, key) DO UPDATE SET
                title      = excluded.title,
                section    = excluded.section,
                status     = excluded.status,
                wp_post_id = excluded.wp_post_id,
                wp_url     = excluded.wp_url,
                message    = excluded.message,
                created_at = excluded.created_at
            """,
            (kind, key, title, section, status, wp_post_id, wp_url, message, int(time.time())),
        )


def recent(limit=200, kind=""):
    sql = "SELECT * FROM items"
    args = []
    if kind:
        sql += " WHERE kind = ?"
        args.append(kind)
    sql += " ORDER BY created_at DESC LIMIT ?"
    args.append(int(limit))

    with _lock, _connect() as conn:
        rows = conn.execute(sql, args).fetchall()
    return [dict(r) for r in rows]


def counts():
    with _lock, _connect() as conn:
        rows = conn.execute(
            "SELECT kind, status, COUNT(*) AS n FROM items GROUP BY kind, status"
        ).fetchall()
    out = {}
    for r in rows:
        out.setdefault(r["kind"], {})[r["status"]] = r["n"]
    return out


def forget(item_id):
    """حذف یک ردیف از کارتابل تا بتوان دوباره پردازشش کرد."""
    with _lock, _connect() as conn:
        conn.execute("DELETE FROM items WHERE id = ?", (int(item_id),))


def forget_failed(kind=""):
    sql = "DELETE FROM items WHERE status NOT IN ('done', 'rejected')"
    args = []
    if kind:
        sql += " AND kind = ?"
        args.append(kind)
    with _lock, _connect() as conn:
        conn.execute(sql, args)
