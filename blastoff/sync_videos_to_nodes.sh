#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
HOSTS_FILE="${REPLICATION_HOSTS_FILE:-$SCRIPT_DIR/video_replication_hosts.txt}"
LOG_DIR="${REPLICATION_LOG_DIR:-$PROJECT_ROOT/storage/logs}"
LOG_FILE="$LOG_DIR/video-replication.log"
LOCK_FILE="${REPLICATION_LOCK_FILE:-$PROJECT_ROOT/storage/framework/video-replication.lock}"
MAX_RETRIES="${REPLICATION_MAX_RETRIES:-3}"
RETRY_DELAY_SEC="${REPLICATION_RETRY_DELAY_SEC:-5}"

mkdir -p "$LOG_DIR" "$(dirname "$LOCK_FILE")"

log() {
	local message
	message="$1"
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] $message" | tee -a "$LOG_FILE"
}

require_command() {
	local command_name
	command_name="$1"
	if ! command -v "$command_name" >/dev/null 2>&1; then
		log "ERROR: Required command '$command_name' is not available in PATH."
		exit 1
	fi
}

get_env_value() {
	local key
	key="$1"
	local value
	value=""

	if [[ -f "$PROJECT_ROOT/.env" ]]; then
		value="$(grep -E "^${key}=" "$PROJECT_ROOT/.env" | tail -n 1 | cut -d'=' -f2- || true)"
	fi

	value="${value%\"}"
	value="${value#\"}"
	value="${value%\'}"
	value="${value#\'}"
	printf '%s' "$value"
}

should_run_on_this_node() {
	local role man_hostname current_hostname
	role="$(get_env_value "NODE_ROLE")"
	man_hostname="$(get_env_value "MAN_HOSTNAME")"
	current_hostname="$(hostname)"

	role="${role,,}"
	if [[ "$role" == "man" ]]; then
		return 0
	fi

	if [[ -n "$man_hostname" && "${man_hostname,,}" == "${current_hostname,,}" ]]; then
		return 0
	fi

	return 1
}

run_with_retry() {
	local attempt command
	attempt=1
	command="$1"

	while true; do
		if eval "$command"; then
			return 0
		fi

		if [[ "$attempt" -ge "$MAX_RETRIES" ]]; then
			return 1
		fi

		log "WARN: Command failed (attempt $attempt/$MAX_RETRIES). Retrying in ${RETRY_DELAY_SEC}s..."
		sleep "$RETRY_DELAY_SEC"
		attempt=$((attempt + 1))
	done
}

if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
	log "ERROR: Laravel project root not found under $PROJECT_ROOT."
	exit 1
fi

require_command rsync
require_command ssh
require_command php

if [[ ! -f "$HOSTS_FILE" ]]; then
	log "ERROR: Host list file not found: $HOSTS_FILE"
	log "Create it from blastoff/video_replication_hosts.example.txt"
	exit 1
fi

if ! should_run_on_this_node; then
	log "INFO: Node is not configured as TheMan. Skipping replication run."
	exit 0
fi

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
	log "INFO: Another replication run is already in progress. Exiting."
	exit 0
fi

LOCAL_VIDEO_DIR="$PROJECT_ROOT/storage/app/public/videos/"
if [[ ! -d "$LOCAL_VIDEO_DIR" ]]; then
	log "ERROR: Local video directory does not exist: $LOCAL_VIDEO_DIR"
	exit 1
fi

success_count=0
failure_count=0

while IFS= read -r raw_line || [[ -n "$raw_line" ]]; do
	line="${raw_line%%#*}"
	line="$(echo "$line" | xargs || true)"
	if [[ -z "$line" ]]; then
		continue
	fi

	target="$(awk '{print $1}' <<< "$line")"
	remote_root="$(awk '{print $2}' <<< "$line")"

	if [[ -z "$target" ]]; then
		continue
	fi

	if [[ -z "$remote_root" ]]; then
		remote_root="$PROJECT_ROOT"
	fi

	remote_video_dir="$remote_root/storage/app/public/videos"
	log "INFO: Replicating videos to $target:$remote_video_dir"

	if ! run_with_retry "ssh -o BatchMode=yes -o ConnectTimeout=10 '$target' 'mkdir -p \"$remote_video_dir\"'"; then
		log "ERROR: Failed to prepare remote directory on $target"
		failure_count=$((failure_count + 1))
		continue
	fi

	if ! run_with_retry "rsync -az --omit-dir-times --no-perms --no-owner --no-group --partial --inplace --human-readable --info=stats1,progress2 -e 'ssh -o BatchMode=yes -o ConnectTimeout=10' '$LOCAL_VIDEO_DIR' '$target:$remote_video_dir/'"; then
		log "ERROR: rsync failed for $target"
		failure_count=$((failure_count + 1))
		continue
	fi

	if ! run_with_retry "ssh -o BatchMode=yes -o ConnectTimeout=10 '$target' 'cd \"$remote_root\" && php artisan videos:sync >/dev/null 2>&1 || php artisan videos:sync --force >/dev/null 2>&1'"; then
		log "ERROR: Remote videos:sync failed on $target"
		failure_count=$((failure_count + 1))
		continue
	fi

	log "INFO: Replication completed for $target"
	success_count=$((success_count + 1))
done < "$HOSTS_FILE"

log "INFO: Replication run finished. Success: $success_count, Failures: $failure_count"

if [[ "$failure_count" -gt 0 ]]; then
	exit 1
fi
