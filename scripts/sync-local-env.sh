#!/usr/bin/env bash
# Copy local Harbor Forge secrets into this worktree's .env.
# Requires HARBOR_LOCAL_ENV to point at the machine-local source .env file.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TARGET="${ROOT}/.env"
FORCE=false

for arg in "$@"; do
  case "$arg" in
    --force) FORCE=true ;;
    -h|--help)
      cat <<'EOF'
Usage: HARBOR_LOCAL_ENV=/path/to/.env ./scripts/sync-local-env.sh [--force]

Copy Forge / local Harbor env from HARBOR_LOCAL_ENV into this worktree when
.env is missing or FORGE_TOKEN is empty.

  HARBOR_LOCAL_ENV   Required. Absolute path to the source .env file.
  --force            Replace this worktree's .env even when FORGE_TOKEN is set
EOF
      exit 0
      ;;
    *)
      echo "Unknown argument: $arg" >&2
      exit 1
      ;;
  esac
done

if [[ -z "${HARBOR_LOCAL_ENV:-}" ]]; then
  echo "HARBOR_LOCAL_ENV is required (path to your machine-local Harbor .env)." >&2
  echo "Example: HARBOR_LOCAL_ENV=/path/to/.env ./scripts/sync-local-env.sh" >&2
  exit 1
fi

SOURCE="${HARBOR_LOCAL_ENV}"

if [[ ! -f "${SOURCE}" ]]; then
  echo "Local env source not found: ${SOURCE}" >&2
  echo "Set HARBOR_LOCAL_ENV to an existing .env file path." >&2
  exit 1
fi

forge_token_filled() {
  local file="$1"
  [[ -f "${file}" ]] || return 1
  local value
  value="$(
    # shellcheck disable=SC2002
    sed -nE 's/^FORGE_TOKEN=["'\'']?([^"'\'']*)["'\'']?$/\1/p' "${file}" | head -n 1
  )"
  [[ -n "${value}" ]]
}

if [[ -f "${TARGET}" ]] && forge_token_filled "${TARGET}" && [[ "${FORCE}" != true ]]; then
  echo "Keeping existing ${TARGET} (FORGE_TOKEN already set)."
  echo "Tip: pass --force to replace from ${SOURCE}."
  exit 0
fi

cp "${SOURCE}" "${TARGET}"
echo "Synced local env from ${SOURCE} → ${TARGET}"
