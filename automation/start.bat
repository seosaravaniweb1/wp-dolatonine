@echo off
chcp 65001 >nul
title Dolat Online - Content Automation
cd /d "%~dp0"

where py >nul 2>nul
if %errorlevel%==0 (set PY=py -3) else (set PY=python)

%PY% --version >nul 2>nul
if errorlevel 1 (
  echo.
  echo   [!] Python not found.
  echo       Install Python 3.10+ from python.org
  echo       and TICK "Add Python to PATH" during setup.
  echo.
  pause
  exit /b 1
)

if not exist ".venv\Scripts\python.exe" (
  echo.
  echo   First run - installing dependencies, please wait...
  echo.
  %PY% -m venv .venv
  if errorlevel 1 goto fail
  ".venv\Scripts\python.exe" -m pip install --upgrade pip --quiet
  ".venv\Scripts\python.exe" -m pip install -r requirements.txt
  if errorlevel 1 goto fail
  echo.
  echo   Ready.
  echo.
)

".venv\Scripts\python.exe" app.py
if errorlevel 1 goto fail
exit /b 0

:fail
echo.
echo   [!] Failed. Please send the message above.
echo.
pause
exit /b 1
