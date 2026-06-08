#!/usr/bin/env bash
set -euo pipefail

# Resolve project root relative to this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

if [[ ! -f artisan ]]; then
	echo "Laravel project root not found under $PROJECT_ROOT." >&2
	exit 1
fi

if ! command -v php >/dev/null 2>&1; then
	echo "php executable not found in PATH." >&2
	exit 1
fi

HOST="0.0.0.0"
PORT="8000"
TAKEOVER_PORT_IF_IN_USE="${TAKEOVER_PORT_IF_IN_USE:-0}"

stop_listener_on_port() {
	local pids
	pids="$(lsof -tiTCP:"$PORT" -sTCP:LISTEN -n -P 2>/dev/null || true)"
	if [[ -z "$pids" ]]; then
		return
	fi

	kill $pids 2>/dev/null || true
	for _ in 1 2 3 4 5; do
		if ! lsof -iTCP:"$PORT" -sTCP:LISTEN -n -P >/dev/null 2>&1; then
			return
		fi
		sleep 0.2
	done

	kill -9 $pids 2>/dev/null || true
}

# If the port is already in use, avoid failing with a confusing error.
if command -v lsof >/dev/null 2>&1 && lsof -iTCP:"$PORT" -sTCP:LISTEN -n -P >/dev/null 2>&1; then
	if [[ "$TAKEOVER_PORT_IF_IN_USE" == "1" ]]; then
		echo "Port $PORT is already in use. Taking over for managed service startup..."
		stop_listener_on_port
	fi

	if lsof -iTCP:"$PORT" -sTCP:LISTEN -n -P >/dev/null 2>&1; then
	echo "Port $PORT is already in use."
	echo "If your Laravel server is already running, open: http://127.0.0.1:$PORT"
	if command -v tailscale >/dev/null 2>&1; then
		TS_IP="$(tailscale ip -4 2>/dev/null | head -n 1 || true)"
		if [[ -n "$TS_IP" ]]; then
			echo "Tailscale URL: http://$TS_IP:$PORT"
		fi
	fi
	echo "To restart cleanly: kill the process on port $PORT, then run this script again."
	exit 0
	fi
fi

php artisan serve --host="$HOST" --port="$PORT"
