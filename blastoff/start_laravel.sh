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

php artisan serve --host=0.0.0.0 --port=8000
