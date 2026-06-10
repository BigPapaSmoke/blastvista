#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

pass_count=0
warn_count=0
fail_count=0

pass() {
  echo "[PASS] $1"
  pass_count=$((pass_count + 1))
}

warn() {
  echo "[WARN] $1"
  warn_count=$((warn_count + 1))
}

fail() {
  echo "[FAIL] $1"
  fail_count=$((fail_count + 1))
}

check_cmd_absent() {
  local name="$1"
  if command -v "$name" >/dev/null 2>&1; then
    warn "Command present: $name"
  else
    pass "Command absent as expected: $name"
  fi
}

check_file_exists() {
  local path="$1"
  if [[ -e "$path" ]]; then
    pass "Found: $path"
  else
    fail "Missing: $path"
  fi
}

check_service_enabled_system() {
  local service="$1"
  if systemctl is-enabled "$service" >/dev/null 2>&1; then
    pass "System service enabled: $service"
  else
    warn "System service not enabled: $service"
  fi
}

check_user_not_sudo() {
  local user_name="$1"
  if id -nG "$user_name" 2>/dev/null | tr ' ' '\n' | grep -qx sudo; then
    fail "User '$user_name' is in sudo group"
  else
    pass "User '$user_name' is not in sudo group"
  fi
}

check_ssh_keys_absent_for_user() {
  local user_name="$1"
  local home_dir
  home_dir="$(getent passwd "$user_name" | cut -d: -f6 || true)"

  if [[ -z "$home_dir" ]]; then
    warn "Cannot resolve home directory for user '$user_name'"
    return
  fi

  shopt -s nullglob
  local keys=("$home_dir"/.ssh/id_* "$home_dir"/.ssh/*.pem)
  shopt -u nullglob

  if [[ ${#keys[@]} -gt 0 ]]; then
    warn "Potential private keys found for '$user_name' under $home_dir/.ssh"
  else
    pass "No obvious private keys found for '$user_name'"
  fi
}

echo "Running kiosk post-clone safety checks..."

check_file_exists "$PROJECT_ROOT/artisan"
check_file_exists "$PROJECT_ROOT/blastoff/start_laravel.sh"
check_file_exists "$PROJECT_ROOT/blastoff/start_chromium_fullscreen.sh"

# Runtime kiosks should typically not have developer tools.
check_cmd_absent code
check_cmd_absent git

# Service checks (system-level replication timer and scheduler are optional by role).
check_service_enabled_system kiosk-video-replication.timer

RUNTIME_USER="${RUNTIME_USER:-kiosk}"
if id "$RUNTIME_USER" >/dev/null 2>&1; then
  check_user_not_sudo "$RUNTIME_USER"
  check_ssh_keys_absent_for_user "$RUNTIME_USER"
else
  warn "Runtime user '$RUNTIME_USER' not found; set RUNTIME_USER=... if different"
fi

# Data sanity snapshot
if command -v php >/dev/null 2>&1; then
  counts="$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make(Illuminate\\Contracts\\Console\\Kernel::class); $kernel->bootstrap(); $total=App\\Models\\Video::count(); $upc=0; foreach(App\\Models\\Video::query()->get(["barcode"]) as $v){ if(preg_match("/^[0-9]{8,14}$/", trim((string)$v->barcode))===1){$upc++;}} echo "videos_total={$total} upc_ready={$upc}";' 2>/dev/null || true)"
  if [[ -n "$counts" ]]; then
    pass "DB snapshot: $counts"
  else
    warn "Could not read DB snapshot via php"
  fi
else
  warn "php command not found; skipped DB snapshot"
fi

echo ""
echo "Summary: PASS=$pass_count WARN=$warn_count FAIL=$fail_count"

if [[ "$fail_count" -gt 0 ]]; then
  exit 1
fi

exit 0
