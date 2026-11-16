#!/usr/bin/env bash
#
# bin/release.sh — build and zip the custom theme and plugins from a git tag (or commit).
#
#   bin/release.sh v1.2.0              # tag → releases/v1.2.0/
#   bin/release.sh HEAD                # last commit (uncommitted changes are NOT included)
#   bin/release.sh v1.2.0 cpt-easy     # only some components: name ("sadoje" = theme + plugin)
#                                      # or type-name (theme-sadoje, plugin-sadoje)
#
# For every component in bin/components.conf:
#   1. takes the files from git (git archive) — never from your working folder
#   2. builds it (npm / composer) in Docker, or locally with BUILD_WITH=local
#   3. leaves out development files (DEFAULT_EXCLUDES + the component's .distignore)
#   4. zips it as <type>-<name>-<version>.zip, with the folder <name>/ inside
#      (the same zip can be uploaded from WordPress → Plugins/Themes → Add new)
# and writes releases/<version>/manifest.tsv (files + SHA-256) used by bin/deploy.sh.
#
# Requires: git, zip, rsync, and Docker (or node/npm and composer with BUILD_WITH=local).
# Docs: docs/Deployment.md
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONF="$ROOT/bin/components.conf"
OUT_BASE="${RELEASES_DIR:-$ROOT/releases}"
BUILD_WITH="${BUILD_WITH:-docker}"          # docker | local
NODE_IMAGE="${NODE_IMAGE:-node:20-alpine}"
COMPOSER_IMAGE="${COMPOSER_IMAGE:-composer:2}"
PHP_PLATFORM="${PHP_PLATFORM:-8.3}"          # production PHP, for composer

# Left out of every zip (rsync patterns; a leading / = only at the component root).
DEFAULT_EXCLUDES='
.git
.github
.gitignore
.gitattributes
.distignore
.editorconfig
.browserslistrc
.babelrc
.DS_Store
.phpunit.cache
.phpunit.result.cache
node_modules
/dist
/tests
package.json
package-lock.json
composer.json
composer.lock
phpunit.xml.dist
phpunit-integration.xml.dist
phpcs.xml.dist
*.md
*.orig
*.log
'

# Never allowed in a zip (checked after staging).
FORBIDDEN_REGEX='(^|/)(\.git|node_modules|\.env|\.env\..*|phpunit|tests)(/|$)'

die()  { printf '✗ %b\n' "$*" >&2; exit 1; }
info() { echo "› $*"; }

sha256() {
	if command -v sha256sum >/dev/null 2>&1; then sha256sum "$1" | cut -d' ' -f1
	else shasum -a 256 "$1" | cut -d' ' -f1; fi
}

usage() {
	sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'
	exit "${1:-0}"
}

# --------------------------------------------------------------------------- args
[ $# -ge 1 ] || usage 1
case "$1" in -h|--help) usage 0 ;; esac

REF="$1"; shift
ONLY=" $* "

for cmd in git zip rsync; do
	command -v "$cmd" >/dev/null 2>&1 || die "$cmd not found"
done
if [ "$BUILD_WITH" = docker ]; then
	command -v docker >/dev/null 2>&1 || die "docker not found (or run with BUILD_WITH=local)"
fi

cd "$ROOT"
COMMIT="$(git rev-parse --verify --quiet "${REF}^{commit}")" || die "Unknown git ref: $REF"
SHORT="$(git rev-parse --short "$COMMIT")"

if git rev-parse --verify --quiet "refs/tags/$REF" >/dev/null; then
	VERSION="$REF"
else
	VERSION="$(date +%Y%m%d)-$SHORT"
	info "$REF is not a tag: version $VERSION"
	if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
		echo "  ⚠ You have uncommitted changes: they are NOT in this release (it is built from $SHORT)."
	fi
fi

OUT="$OUT_BASE/$VERSION"
WORK="$OUT_BASE/.build-$VERSION"
[ -e "$OUT" ] && die "$OUT already exists (delete it to rebuild this version)"

DONE=""
cleanup() { rm -rf "$WORK"; [ -n "$DONE" ] || rm -rf "$OUT"; }  # no half-built releases
trap cleanup EXIT

mkdir -p "$WORK/src" "$WORK/stage" "$OUT"
info "Exporting $REF ($SHORT) from git…"
git archive --format=tar "$COMMIT" | tar -x -C "$WORK/src"

printf '%s\n' "$DEFAULT_EXCLUDES" | sed '/^$/d' > "$WORK/excludes.default"

# --------------------------------------------------------------------------- builds
build_npm() {
	local dir="$1"
	[ -f "$dir/package.json" ] || die "$dir: build npm but no package.json"
	local install='npm install --no-audit --no-fund'
	[ -f "$dir/package-lock.json" ] && install='npm ci --no-audit --no-fund'
	if [ "$BUILD_WITH" = local ]; then
		( cd "$dir" && $install && npm run build )
	else
		docker run --rm -e HOME=/tmp -u "$(id -u):$(id -g)" -v "$dir":/app -w /app "$NODE_IMAGE" \
			sh -c "$install && npm run build"
	fi
}

build_composer() {
	local dir="$1"
	[ -f "$dir/composer.json" ] || die "$dir: build composer but no composer.json"
	local args="install --no-dev --no-interaction --no-progress --optimize-autoloader"
	if [ "$BUILD_WITH" = local ]; then
		( cd "$dir" && COMPOSER_ALLOW_SUPERUSER=1 composer config platform.php "$PHP_PLATFORM" && composer $args )
	else
		docker run --rm -e HOME=/tmp -u "$(id -u):$(id -g)" -v "$dir":/app -w /app "$COMPOSER_IMAGE" \
			sh -c "composer config platform.php $PHP_PLATFORM && composer $args"
	fi
}

# --------------------------------------------------------------------------- components
printf 'component\ttype\tname\tfile\tsha256\n' > "$OUT/manifest.tsv"
COUNT=0

# fd 3: builds (npm, docker) must not eat the rest of components.conf from stdin.
while read -r TYPE NAME PATH_IN_REPO BUILD <&3; do
	case "$TYPE" in ''|\#*) continue ;; esac
	[ "$ONLY" = "  " ] || case "$ONLY" in *" $NAME "*|*" $TYPE-$NAME "*) ;; *) continue ;; esac
	case "$TYPE" in theme|plugin) ;; *) die "components.conf: bad type '$TYPE'" ;; esac

	SRC="$WORK/src/$PATH_IN_REPO"
	[ -d "$SRC" ] || die "$PATH_IN_REPO is not in $REF"
	info "$TYPE $NAME (build: $BUILD)"

	case "$BUILD" in
		-) ;;
		npm|npm-dist) build_npm "$SRC" </dev/null ;;
		composer) build_composer "$SRC" </dev/null ;;
		*) die "components.conf: bad build '$BUILD' for $NAME" ;;
	esac

	# npm-dist: the plugin's own build writes the production copy to dist/ (sources left out,
	# PHP pointing to the .min files): package that folder.
	if [ "$BUILD" = npm-dist ]; then
		[ -d "$SRC/dist" ] || die "$NAME: npm run build made no dist/ folder"
		SRC="$SRC/dist"
	fi

	# Excludes: defaults + .distignore; vendor/ stays only for composer builds.
	EXCL="$WORK/excludes.$TYPE-$NAME"
	cp "$WORK/excludes.default" "$EXCL"
	[ "$BUILD" = composer ] || echo '/vendor' >> "$EXCL"
	[ -f "$SRC/.distignore" ] && grep -v '^[[:space:]]*\(#\|$\)' "$SRC/.distignore" >> "$EXCL"

	STAGE="$WORK/stage/$TYPE-$NAME"
	mkdir -p "$STAGE/$NAME"
	rsync -a --exclude-from="$EXCL" "$SRC/" "$STAGE/$NAME/"

	if BAD="$(cd "$STAGE" && find "$NAME" | grep -E "$FORBIDDEN_REGEX" | head -5)" && [ -n "$BAD" ]; then
		die "$NAME would ship development files:\n$BAD"
	fi
	if [ "$TYPE" = theme ]; then
		[ -f "$STAGE/$NAME/style.css" ] || die "theme $NAME has no style.css"
	else
		grep -l 'Plugin Name:' "$STAGE/$NAME"/*.php >/dev/null 2>&1 || die "plugin $NAME has no main file (Plugin Name:)"
	fi

	FILE="$TYPE-$NAME-$VERSION.zip"
	( cd "$STAGE" && zip -qr -X "$OUT/$FILE" "$NAME" )
	printf '%s\t%s\t%s\t%s\t%s\n' "$TYPE-$NAME" "$TYPE" "$NAME" "$FILE" "$(sha256 "$OUT/$FILE")" >> "$OUT/manifest.tsv"
	echo "  → $FILE ($(cd "$STAGE" && find "$NAME" -type f | wc -l | tr -d ' ') files, $(du -h "$OUT/$FILE" | cut -f1))"
	COUNT=$((COUNT + 1))
done 3< "$CONF"

[ "$COUNT" -gt 0 ] || die "No component matched: $ONLY"

{
	echo "version=$VERSION"
	echo "ref=$REF"
	echo "commit=$COMMIT"
	echo "built=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
	echo "builder=$(git config user.name 2>/dev/null || whoami)"
	echo "build_with=$BUILD_WITH"
} > "$OUT/release.txt"

DONE=1
echo
echo "✓ Release $VERSION: $COUNT component(s) in ${OUT#"$ROOT"/}"
echo "  Next: bin/deploy.sh $VERSION"
