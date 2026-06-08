#!/usr/bin/env bash
set -euo pipefail

URL="${1:-}"

CHROMIUM_PROFILE_DIR="${CHROMIUM_USER_DATA_DIR:-$HOME/blastvista-chromium-profile}"

ensure_writable_chromium_profile_dir() {
	if mkdir -p "$CHROMIUM_PROFILE_DIR" 2>/dev/null && [[ -w "$CHROMIUM_PROFILE_DIR" ]]; then
		return
	fi

	local runtime_root
	runtime_root="${XDG_RUNTIME_DIR:-/tmp}"
	CHROMIUM_PROFILE_DIR="$(mktemp -d "$runtime_root/blastvista-chromium-profile.XXXXXX")"
}

cleanup_stale_chromium_locks() {
	# Chromium leaves singleton lock files behind after unclean shutdowns.
	if pgrep -f -- "--user-data-dir=$CHROMIUM_PROFILE_DIR" >/dev/null 2>&1; then
		return
	fi

	rm -f "$CHROMIUM_PROFILE_DIR/SingletonLock" \
		"$CHROMIUM_PROFILE_DIR/SingletonSocket" \
		"$CHROMIUM_PROFILE_DIR/SingletonCookie"
}

declare -a browsers

if [[ -n "${BROWSER:-}" ]]; then
	browsers+=("$BROWSER")
fi

browsers+=("chromium" "chromium-browser" "firefox" "firefox-esr")

for candidate in "${browsers[@]}"; do
	if [[ -z "$candidate" ]]; then
		continue
	fi
	if command -v "$candidate" >/dev/null 2>&1; then
		if [[ "$candidate" == "chromium" || "$candidate" == "chromium-browser" ]]; then
			ensure_writable_chromium_profile_dir
			cleanup_stale_chromium_locks
			declare -a chromium_args
			chromium_args=(--user-data-dir="$CHROMIUM_PROFILE_DIR" --no-first-run --no-default-browser-check)
			if [[ -n "$URL" ]]; then
				"$candidate" "${chromium_args[@]}" "$URL" &
			else
				"$candidate" "${chromium_args[@]}" &
			fi
			exit 0
		fi

		if [[ -n "$URL" ]]; then
			"$candidate" "$URL" &
		else
			"$candidate" &
		fi
		exit 0
	fi
done

echo "No supported browser (firefox/firefox-esr/chromium/chromium-browser) found in PATH." >&2
exit 1
