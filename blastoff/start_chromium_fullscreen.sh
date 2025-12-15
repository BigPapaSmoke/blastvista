#!/usr/bin/env bash
set -euo pipefail

URL="${1:-http://127.0.0.1:8000/}"

declare -a browsers

if [[ -n "${BROWSER:-}" ]]; then
	browsers+=("$BROWSER")
fi

browsers+=("firefox" "firefox-esr" "chromium" "chromium-browser")

for candidate in "${browsers[@]}"; do
	if [[ -z "$candidate" ]]; then
		continue
	fi
	if command -v "$candidate" >/dev/null 2>&1; then
		case "$candidate" in
			firefox|firefox-esr)
				"$candidate" --kiosk "$URL" &
				;;
			chromium|chromium-browser)
				"$candidate" --start-fullscreen "$URL" &
				;;
			*)
				"$candidate" "$URL" &
				;;
		esac
		exit 0
	fi
done

echo "No supported browser (firefox/firefox-esr/chromium/chromium-browser) found in PATH." >&2
exit 1
