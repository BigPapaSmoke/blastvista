#!/usr/bin/env bash
set -euo pipefail
manifest="${1:?Usage: restore_variant_archive.sh <manifest_csv_path>}"
while IFS=, read -r filename normalized_key db_matches sha256 source_path archive_path; do
  if [[ "$filename" == "filename" ]]; then
    continue
  fi
  source_path="${source_path%\"}"
  source_path="${source_path#\"}"
  archive_path="${archive_path%\"}"
  archive_path="${archive_path#\"}"
  mkdir -p "$(dirname "$source_path")"
  if [[ -f "$archive_path" ]]; then
    mv -f "$archive_path" "$source_path"
  fi
done < "$manifest"
echo "Restore complete from $manifest"
