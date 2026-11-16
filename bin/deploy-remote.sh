#!/usr/bin/env bash
#
# bin/deploy-remote.sh — runs ON THE SERVER. bin/deploy.sh uploads it with every release and calls it;
# you don't normally run it by hand.
#
#   deploy-remote.sh install  <release-dir> <wp-content> <backups-dir> [name…]
#   deploy-remote.sh rollback <backup-dir>  <wp-content>
#   deploy-remote.sh latest   <backups-dir>      (newest backup not rolled back yet)
#   deploy-remote.sh list     <releases-dir>
#   deploy-remote.sh prune    <releases-dir> <keep>
#
# install: checks the SHA-256 of every zip, unzips ALL of them first (nothing changes if one fails),
# then for each component moves the current folder to <backups-dir>/<stamp>/ and the new one into
# wp-content (a rename: no half-copied plugin), and sets owner and permissions.
#
set -euo pipefail

OWNER="${DEPLOY_OWNER:-www-data:www-data}"

die()  { printf '✗ %b\n' "$*" >&2; exit 1; }
info() { echo "  $*"; }

unzip_to() { # zip dir
	if command -v unzip >/dev/null 2>&1; then
		unzip -q "$1" -d "$2"
	elif command -v python3 >/dev/null 2>&1; then
		python3 -m zipfile -e "$1" "$2"
	else
		die "Neither unzip nor python3 on the server (apt install unzip)"
	fi
}

sha256() {
	if command -v sha256sum >/dev/null 2>&1; then sha256sum "$1" | cut -d' ' -f1
	else shasum -a 256 "$1" | cut -d' ' -f1; fi
}

fix_perms() { # dir
	chown -R "$OWNER" "$1" 2>/dev/null || echo "  ⚠ could not chown $1 to $OWNER"
	find "$1" -type d -exec chmod 755 {} +
	find "$1" -type f -exec chmod 644 {} +
}

cmd_install() {
	local release="$1" wpc="$2" backups="$3"; shift 3
	local only=" $* "
	[ -f "$release/manifest.tsv" ] || die "No manifest.tsv in $release"
	[ -d "$wpc/plugins" ] && [ -d "$wpc/themes" ] || die "$wpc is not a wp-content folder"

	local version stamp staging bk
	version="$(sed -n 's/^version=//p' "$release/release.txt" 2>/dev/null || true)"
	# Unique backup folder, even for two deploys in the same second.
	stamp="$(date +%Y%m%d-%H%M%S)"
	local n=1 base="$stamp"
	while [ -e "$backups/$stamp" ]; do n=$((n + 1)); stamp="$base-$n"; done
	staging="$release/staging"
	bk="$backups/$stamp"

	# 1. checksums + unzip everything before touching the site
	rm -rf "$staging"
	mkdir -p "$staging"
	local rows=() row component type name file sum
	while IFS=$'\t' read -r component type name file sum; do
		[ "$component" = component ] && continue
		[ "$only" = "  " ] || case "$only" in *" $name "*|*" $component "*) ;; *) continue ;; esac
		[ "$(sha256 "$release/$file")" = "$sum" ] || die "Checksum mismatch: $file"
		unzip_to "$release/$file" "$staging/$component"
		[ -d "$staging/$component/$name" ] || die "$file has no $name/ folder"
		rows+=("$component	$type	$name")
	done < "$release/manifest.tsv"
	[ "${#rows[@]}" -gt 0 ] || die "No component matched:$only"

	# 2. swap
	mkdir -p "$bk/themes" "$bk/plugins"
	{
		echo "version=$version"
		echo "date=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
	} > "$bk/INFO"

	for row in "${rows[@]}"; do
		IFS=$'\t' read -r component type name <<< "$row"
		local target="$wpc/${type}s/$name" status=new
		if [ -e "$target" ]; then
			mv "$target" "$bk/${type}s/$name"
			status=replaced
		fi
		mv "$staging/$component/$name" "$target"
		fix_perms "$target"
		echo "component=$type $name $status" >> "$bk/INFO"
		info "✓ $type $name ($status)"
	done

	rm -rf "$staging"
	echo "BACKUP=$stamp"
}

cmd_rollback() {
	local bk="$1" wpc="$2"
	[ -f "$bk/INFO" ] || die "No backup at $bk"
	grep -q '^rolled_back=' "$bk/INFO" && die "$bk was already rolled back"

	local undo
	undo="$bk.undone-$(date +%Y%m%d-%H%M%S)"
	mkdir -p "$undo/themes" "$undo/plugins"

	local type name status
	while read -r type name status; do
		local target="$wpc/${type}s/$name"
		[ -e "$target" ] && mv "$target" "$undo/${type}s/$name"
		if [ "$status" = replaced ]; then
			mv "$bk/${type}s/$name" "$target"
			info "✓ $type $name restored"
		else
			info "✓ $type $name removed (it was new in that release)"
		fi
	done < <(sed -n 's/^component=//p' "$bk/INFO")

	echo "rolled_back=$(date -u +%Y-%m-%dT%H:%M:%SZ)" >> "$bk/INFO"
	info "The removed versions are in $undo"
}

cmd_latest() { # newest backup that has not been rolled back
	local backups="$1" b latest=""
	for b in $(find "$backups" -mindepth 1 -maxdepth 1 -type d ! -name '*.undone-*' 2>/dev/null | sort); do
		grep -q '^rolled_back=' "$b/INFO" 2>/dev/null || latest="$(basename "$b")"
	done
	[ -n "$latest" ] || die "No backup left to roll back"
	echo "$latest"
}

cmd_list() {
	local releases="$1"
	echo "Releases:"
	find "$releases" -mindepth 1 -maxdepth 1 -type d ! -name backups -printf '  %f\n' 2>/dev/null | sort || true
	echo "Backups (newest last):"
	local b
	for b in $(find "$releases/backups" -mindepth 1 -maxdepth 1 -type d ! -name '*.undone-*' 2>/dev/null | sort); do
		printf '  %s  %s  %s\n' "$(basename "$b")" \
			"$(sed -n 's/^version=//p' "$b/INFO")" \
			"$(grep -q '^rolled_back=' "$b/INFO" && echo '(rolled back)')"
	done
}

cmd_prune() { # keep the newest <keep> releases and <keep> backups
	local releases="$1" keep="$2" d
	for d in $(find "$releases" -mindepth 1 -maxdepth 1 -type d ! -name backups -printf '%T@ %p\n' | sort -rn | tail -n +$((keep + 1)) | cut -d' ' -f2-); do
		rm -rf "$d" && info "removed $d"
	done
	for d in $(find "$releases/backups" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' 2>/dev/null | sort -rn | tail -n +$((keep + 1)) | cut -d' ' -f2-); do
		rm -rf "$d" && info "removed $d"
	done
}

case "${1:-}" in
	install)  shift; cmd_install "$@" ;;
	rollback) shift; cmd_rollback "$@" ;;
	list)     shift; cmd_list "$@" ;;
	latest)   shift; cmd_latest "$@" ;;
	prune)    shift; cmd_prune "$@" ;;
	*) sed -n '2,15p' "$0" | sed 's/^# \{0,1\}//'; exit 1 ;;
esac
