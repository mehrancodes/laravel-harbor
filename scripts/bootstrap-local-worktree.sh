#!/usr/bin/env bash
# Bootstrap a new Orca worktree: sync Forge .env and install Composer deps.
# Requires HARBOR_LOCAL_ENV to point at the machine-local source .env file.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

for arg in "$@"; do
  case "$arg" in
    -h|--help)
      cat <<'EOF'
Usage: HARBOR_LOCAL_ENV=/path/to/.env ./scripts/bootstrap-local-worktree.sh

1. Sync .env from HARBOR_LOCAL_ENV into this worktree
2. Install Composer dependencies (including require-dev)

Orca setup hook (Settings → Repository → Hooks):
  HARBOR_LOCAL_ENV=/path/to/.env ./scripts/bootstrap-local-worktree.sh
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
  echo "Example: HARBOR_LOCAL_ENV=/path/to/.env ./scripts/bootstrap-local-worktree.sh" >&2
  exit 1
fi

cd "${ROOT}"

echo "==> Syncing local .env"
"${ROOT}/scripts/sync-local-env.sh"

if ! grep -qE '^FORGE_TOKEN=.+' .env; then
  echo "Warning: FORGE_TOKEN is empty in .env — add it to ${HARBOR_LOCAL_ENV}" >&2
fi

echo "==> Installing Composer dependencies"
composer install --no-interaction

echo "Bootstrap complete."
echo "Tip: run ./harbor provision with your Forge env, or refresh secrets with HARBOR_LOCAL_ENV=... ./scripts/sync-local-env.sh --force"
