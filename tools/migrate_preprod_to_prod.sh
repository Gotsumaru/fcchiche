#!/usr/bin/env bash
set -euo pipefail

# shellcheck disable=SC2034
SCRIPT_NAME="$(basename "$0")"
ROOT_DIR="$(git rev-parse --show-toplevel)"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PYTHON_TOOL="$SCRIPT_DIR/process_assets.py"
PREPROD_BRANCH="${1:-preprod}"
PROD_BRANCH="${2:-prod}"
PREPROD_DOC_PATH="docs/preprod/README.md"
TMP_BRANCH="migration/preprod-to-prod"

log() {
    printf '[migrate] %s\n' "$1"
}

ensure_clean_worktree() {
    if [ -n "$(git status --porcelain)" ]; then
        printf 'Working tree must be clean before migration.\n' >&2
        exit 1
    fi
}

ensure_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        printf 'Required command %s is not available.\n' "$1" >&2
        exit 1
    fi
}

restore_branch() {
    local target_branch="$1"
    if git rev-parse --verify "$target_branch" >/dev/null 2>&1; then
        git checkout "$target_branch" >/dev/null 2>&1 || true
    fi
}

cleanup_temp_branch() {
    if git rev-parse --verify "$TMP_BRANCH" >/dev/null 2>&1; then
        git branch -D "$TMP_BRANCH" >/dev/null 2>&1 || true
    fi
}

main() {
    ensure_command git
    ensure_command python3
    ensure_clean_worktree
    if [ ! -f "$PYTHON_TOOL" ]; then
        printf 'Processing tool %s not found.\n' "$PYTHON_TOOL" >&2
        exit 1
    fi

    local current_branch
    current_branch="$(git rev-parse --abbrev-ref HEAD)"
    trap 'restore_branch "$current_branch"; cleanup_temp_branch' EXIT

    if ! git rev-parse --verify "$PREPROD_BRANCH" >/dev/null 2>&1; then
        printf 'Preprod branch %s is missing.\n' "$PREPROD_BRANCH" >&2
        exit 1
    fi
    if ! git rev-parse --verify "$PROD_BRANCH" >/dev/null 2>&1; then
        printf 'Prod branch %s is missing.\n' "$PROD_BRANCH" >&2
        exit 1
    fi

    log "Checking out $PREPROD_BRANCH"
    git checkout "$PREPROD_BRANCH" >/dev/null 2>&1

    cleanup_temp_branch
    log "Creating temporary branch $TMP_BRANCH"
    git checkout -b "$TMP_BRANCH" "$PREPROD_BRANCH" >/dev/null 2>&1

    log "Processing assets via $PYTHON_TOOL"
    python3 "$PYTHON_TOOL" "$ROOT_DIR" --exclude "$PREPROD_DOC_PATH"

    if [ -f "$ROOT_DIR/$PREPROD_DOC_PATH" ]; then
        log "Removing preprod-only documentation"
        rm -f "$ROOT_DIR/$PREPROD_DOC_PATH"
        if git ls-files --error-unmatch "$PREPROD_DOC_PATH" >/dev/null 2>&1; then
            git rm -f "$PREPROD_DOC_PATH" >/dev/null 2>&1 || true
        fi
    fi

    git add -A
    if git diff --cached --quiet; then
        log "No changes detected after processing"
    else
        git commit -m "chore: prepare production snapshot from $PREPROD_BRANCH" >/dev/null 2>&1
    fi

    log "Switching to $PROD_BRANCH"
    git checkout "$PROD_BRANCH" >/dev/null 2>&1
    if git merge --ff-only "$TMP_BRANCH" >/dev/null 2>&1; then
        log "Prod branch updated"
    else
        printf 'Unable to fast-forward merge temporary branch into %s.\n' "$PROD_BRANCH" >&2
        exit 1
    fi

    cleanup_temp_branch
    restore_branch "$current_branch"
    log "Migration completed"
}

main "$@"
