#!/usr/bin/env bash
#
# bin/deploy.sh — upload a release built by bin/release.sh to production and install it.
#
#   bin/deploy.sh v1.2.0                  # every component of the release
#   bin/deploy.sh v1.2.0 cpt-easy         # only some (name, or type-name: plugin-sadoje)
#   bin/deploy.sh v1.2.0 --dry-run        # show what would happen, change nothing
#   bin/deploy.sh --rollback              # undo the last deploy not undone yet (or: --rollback 20261116-113000)
#   bin/deploy.sh --list                  # releases and backups on the server
#   bin/deploy.sh --prune 5               # keep the newest 5 releases and 5 backups on the server
#
# Steps: check the zips' SHA-256 → upload to <root>/releases/<version>/ → install
# (backup of the current folders, swap, owner/permissions) → health check (WP-CLI loads
# WordPress with every plugin; the home page answers) → on failure, offers to roll back.
#
# Settings (environment variables, or bin/deploy.env):
#   DEPLOY_HOST   ssh host                      DigitalOcean10DesignRoot   ("local" = this machine, for tests)
#   DEPLOY_ROOT   folder with docker-compose    /srv/docker/wordpress
#   DEPLOY_WPC    wp-content on the server      $DEPLOY_ROOT/wordpress/wp-content
#   DEPLOY_URL    site to check after deploy    https://camisetasparatodos.es   (empty: skip)
#   DEPLOY_OWNER  owner of the files            www-data:www-data
#
# Docs: docs/Deployment.md
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
[ -f "$ROOT/bin/deploy.env" ] && . "$ROOT/bin/deploy.env"

DEPLOY_HOST="${DEPLOY_HOST:-DigitalOcean10DesignRoot}"
DEPLOY_ROOT="${DEPLOY_ROOT:-/srv/docker/wordpress}"
DEPLOY_WPC="${DEPLOY_WPC:-$DEPLOY_ROOT/wordpress/wp-content}"
DEPLOY_URL="${DEPLOY_URL-https://camisetasparatodos.es}"   # empty = no HTTP check
DEPLOY_OWNER="${DEPLOY_OWNER:-www-data:www-data}"
RELEASES_DIR="${RELEASES_DIR:-$ROOT/releases}"
REMOTE_RELEASES="$DEPLOY_ROOT/releases"
SKIP_HEALTH="${SKIP_HEALTH:-}"

die()  { printf '✗ %b\n' "$*" >&2; exit 1; }
info() { echo "› $*"; }
q()    { printf '%q' "$1"; }

# Run on the server (or here, with DEPLOY_HOST=local).
remote() {
	if [ "$DEPLOY_HOST" = local ]; then bash -c "$1"
	else ssh -o BatchMode=yes "$DEPLOY_HOST" "$1"; fi
}
upload() { # local files… remote-dir
	local dest="${!#}" files=("${@:1:$#-1}")
	if [ "$DEPLOY_HOST" = local ]; then cp "${files[@]}" "$dest/"
	else scp -q -o BatchMode=yes "${files[@]}" "$DEPLOY_HOST:$dest/"; fi
}
remote_script() { # command args…
	local args="" a
	for a in "$@"; do args="$args $(q "$a")"; done
	remote "DEPLOY_OWNER=$(q "$DEPLOY_OWNER") bash $(q "$REMOTE_RELEASES/.deploy-remote.sh")$args"
}
push_remote_script() {
	remote "mkdir -p $(q "$REMOTE_RELEASES")"
	if [ "$DEPLOY_HOST" = local ]; then cp "$ROOT/bin/deploy-remote.sh" "$REMOTE_RELEASES/.deploy-remote.sh"
	else scp -q -o BatchMode=yes "$ROOT/bin/deploy-remote.sh" "$DEPLOY_HOST:$REMOTE_RELEASES/.deploy-remote.sh"; fi
}

sha256() {
	if command -v sha256sum >/dev/null 2>&1; then sha256sum "$1" | cut -d' ' -f1
	else shasum -a 256 "$1" | cut -d' ' -f1; fi
}

confirm() {
	[ -n "$YES" ] && return 0
	[ -t 0 ] || die "Not interactive: add --yes"
	read -r -p "$1 [y/N] " answer
	case "$answer" in y|Y|s|S|yes|si|sí) return 0 ;; *) return 1 ;; esac
}

health_check() {
	[ -n "$SKIP_HEALTH" ] && { info "Health check skipped (SKIP_HEALTH)"; return 0; }
	local ok=0
	info "Health check: WordPress loads with every plugin (WP-CLI)…"
	if remote "cd $(q "$DEPLOY_ROOT") && docker compose run --rm -T wpcli eval 'echo \"WP OK \" . get_bloginfo(\"version\") . PHP_EOL;'" ; then
		remote "cd $(q "$DEPLOY_ROOT") && docker compose run --rm -T wpcli plugin list --status=active --fields=name,version --format=table" || true
	else
		echo "  ✗ WP-CLI could not load WordPress (fatal error?)"
		ok=1
	fi
	if command -v curl >/dev/null 2>&1 && [ -n "$DEPLOY_URL" ]; then
		local code
		code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$DEPLOY_URL/")" || true
		case "$code" in ''|*[!0-9]*) code=000 ;; esac
		if [ "$code" -ge 200 ] && [ "$code" -lt 400 ]; then
			info "Home page: HTTP $code"
		else
			echo "  ✗ Home page: HTTP $code ($DEPLOY_URL)"
			ok=1
		fi
	fi
	return $ok
}

# --------------------------------------------------------------------------- args
YES=""; DRY=""; MODE=deploy; VERSION=""; ONLY=(); ARG=""
while [ $# -gt 0 ]; do
	case "$1" in
		-h|--help) sed -n '2,24p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
		-y|--yes) YES=1 ;;
		-n|--dry-run) DRY=1 ;;
		--rollback) MODE=rollback; [ $# -gt 1 ] && [ "${2#-}" = "$2" ] && { ARG="$2"; shift; } ;;
		--list) MODE=list ;;
		--prune) MODE=prune; ARG="${2:-5}"; [ $# -gt 1 ] && shift ;;
		-*) die "Unknown option $1" ;;
		*) if [ -z "$VERSION" ]; then VERSION="$1"; else ONLY+=("$1"); fi ;;
	esac
	shift
done

if [ "$DEPLOY_HOST" != local ]; then
	remote "true" 2>/dev/null || die "Can't ssh to $DEPLOY_HOST (ssh $DEPLOY_HOST must work without a password prompt)"
fi

# --------------------------------------------------------------------------- list / prune / rollback
case "$MODE" in
	list)
		push_remote_script
		remote_script list "$REMOTE_RELEASES"
		exit 0 ;;
	prune)
		push_remote_script
		confirm "Delete all but the newest $ARG releases and backups on $DEPLOY_HOST?" || exit 1
		remote_script prune "$REMOTE_RELEASES" "$ARG"
		exit 0 ;;
	rollback)
		push_remote_script
		if [ -z "$ARG" ]; then
			ARG="$(remote_script latest "$REMOTE_RELEASES/backups")" || die "No backup left to roll back"
		fi
		remote "cat $(q "$REMOTE_RELEASES/backups/$ARG/INFO")" || die "No backup $ARG"
		confirm "Roll back backup $ARG on $DEPLOY_HOST?" || exit 1
		remote_script rollback "$REMOTE_RELEASES/backups/$ARG" "$DEPLOY_WPC"
		health_check || true
		exit 0 ;;
esac

# --------------------------------------------------------------------------- deploy
[ -n "$VERSION" ] || die "Which version? (bin/deploy.sh <version>, see releases/)"
LOCAL="$RELEASES_DIR/$VERSION"
[ -f "$LOCAL/manifest.tsv" ] || die "No release $VERSION in ${RELEASES_DIR#"$ROOT"/} (run bin/release.sh first)"

info "Release $VERSION ($(sed -n 's/^commit=//p' "$LOCAL/release.txt" | cut -c1-8))"
FILES=("$LOCAL/manifest.tsv" "$LOCAL/release.txt")
PLAN=""
while IFS=$'\t' read -r component type name file sum; do
	[ "$component" = component ] && continue
	if [ ${#ONLY[@]} -gt 0 ]; then
		case " ${ONLY[*]} " in *" $name "*|*" $component "*) ;; *) continue ;; esac
	fi
	[ "$(sha256 "$LOCAL/$file")" = "$sum" ] || die "Checksum mismatch: $file (rebuild the release)"
	FILES+=("$LOCAL/$file")
	PLAN="$PLAN  $type $name → $DEPLOY_WPC/${type}s/$name\n"
done < "$LOCAL/manifest.tsv"
[ -n "$PLAN" ] || die "No component matched: ${ONLY[*]}"

echo "  to $DEPLOY_HOST:"
printf "%b" "$PLAN"

info "Checking the server…"
remote "test -d $(q "$DEPLOY_WPC/plugins") && test -d $(q "$DEPLOY_WPC/themes")" || die "$DEPLOY_WPC is not wp-content on $DEPLOY_HOST"
remote "command -v unzip >/dev/null || command -v python3 >/dev/null" || die "Install unzip on the server (apt install unzip)"

if [ -n "$DRY" ]; then
	info "Dry run: nothing uploaded or changed."
	exit 0
fi
confirm "Deploy $VERSION to $DEPLOY_HOST?" || exit 1

REMOTE_DIR="$REMOTE_RELEASES/$VERSION"
info "Uploading to $REMOTE_DIR…"
remote "mkdir -p $(q "$REMOTE_DIR")"
upload "${FILES[@]}" "$REMOTE_DIR"
push_remote_script

info "Installing…"
OUTPUT="$(remote_script install "$REMOTE_DIR" "$DEPLOY_WPC" "$REMOTE_RELEASES/backups" "${ONLY[@]+"${ONLY[@]}"}")" || { echo "$OUTPUT"; die "Install failed: the site was not changed if this happened before the swap (see above)"; }
echo "$OUTPUT" | grep -v '^BACKUP='
BACKUP="$(echo "$OUTPUT" | sed -n 's/^BACKUP=//p')"
info "Backup of the previous version: $REMOTE_RELEASES/backups/$BACKUP"

if health_check; then
	echo
	echo "✓ $VERSION deployed. Undo with: bin/deploy.sh --rollback $BACKUP"
else
	echo
	echo "✗ The health check failed."
	if confirm "Roll back to the previous version now?"; then
		remote_script rollback "$REMOTE_RELEASES/backups/$BACKUP" "$DEPLOY_WPC"
		health_check || true
	else
		echo "  Undo later with: bin/deploy.sh --rollback $BACKUP"
	fi
	exit 1
fi
