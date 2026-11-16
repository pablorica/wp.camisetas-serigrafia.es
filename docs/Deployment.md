# Deployment

How the custom theme and plugins go from this repo to production
(`ssh DigitalOcean10DesignRoot`, `/srv/docker/wordpress`).

- [Overview](#overview)
- [Requirements](#requirements)
- [Components](#components)
- [1. Release: build and zip](#1-release-build-and-zip)
- [2. Deploy: upload and install](#2-deploy-upload-and-install)
- [Rollback](#rollback)
- [Other commands](#other-commands)
- [Settings](#settings)
- [What the scripts do NOT deploy](#what-the-scripts-do-not-deploy)
- [First deploy of CPT Easy (checklist)](#first-deploy-of-cpt-easy-checklist)
- [Adding a new theme or plugin](#adding-a-new-theme-or-plugin)
- [Manual alternatives](#manual-alternatives)
- [Testing the scripts without the server](#testing-the-scripts-without-the-server)
- [Troubleshooting](#troubleshooting)
- [Why this approach](#why-this-approach)

---

## Overview

```
 git tag v1.2.0
      │
      ▼
 bin/release.sh v1.2.0            (your computer)
   git archive → build (npm/composer in Docker) → drop dev files → zip + manifest
      │                                            releases/v1.2.0/*.zip, manifest.tsv
      ▼
 bin/deploy.sh v1.2.0
   check SHA-256 → scp to /srv/docker/wordpress/releases/v1.2.0/
   on the server: unzip all → back up current folders → swap → owner/permissions
   health check: WP-CLI loads WordPress + every plugin, the home page answers
   failed? → offers to roll back
```

Three files in `bin/`:

| File | Runs on | What |
|---|---|---|
| `components.conf` | — | List of components: type, name, repo path, build |
| `release.sh` | your computer | Builds and zips the components from a git tag or commit |
| `deploy.sh` | your computer | Uploads a release and drives the server |
| `deploy-remote.sh` | the server | Install / rollback / list / prune (uploaded by `deploy.sh`) |

## Requirements

**Your computer**: `git`, `zip`, `rsync`, `ssh`/`scp`, `curl`, Docker (for the npm/composer builds) and the
SSH alias `DigitalOcean10DesignRoot` working without a password prompt (`ssh DigitalOcean10DesignRoot true`).

**The server**: `unzip` (or `python3`, used as a fallback), Docker Compose, and the `wpcli` image built
(`docker compose build wpcli` in `/srv/docker/wordpress`).

## Components

`bin/components.conf`:

```
# type    name                 path                                              build
theme     sadoje               wordpress/wp-content/themes/sadoje                -
plugin    sadoje               wordpress/wp-content/plugins/sadoje               npm-dist
plugin    cg-woo-grid          wordpress/wp-content/plugins/cg-woo-grid          npm-dist
plugin    cpt-easy             wordpress/wp-content/plugins/cpt-easy             -
plugin    woo-color-importer   wordpress/wp-content/plugins/woo-color-importer   -
```

| build | What happens |
|---|---|
| `-` | Nothing to build: the folder is zipped as it is in git |
| `npm` | `npm ci && npm run build` (Docker `node:20-alpine`), then the folder is zipped |
| `npm-dist` | Same build, then the **`dist/`** folder it creates is zipped. Used by `sadoje` and `cg-woo-grid`: their `dist-build` copies the plugin without sources and, in CG Woo Grid, points the PHP to the `.min.js` / `.min.css` files |
| `composer` | `composer install --no-dev` with PHP 8.3 as the platform, so `vendor/` with runtime packages ships in the zip |

**Left out of every zip** (`DEFAULT_EXCLUDES` in `release.sh`): `.git*`, dotfiles like `.editorconfig`,
`node_modules`, `dist`, `tests`, `vendor` (except `composer` builds), `package*.json`, `composer.*`,
`phpunit*.xml.dist`, `phpcs.xml.dist`, `*.md`, `*.orig`, `*.log`.

Extra exclusions for one component: a `.distignore` file in its folder, one rsync pattern per line
(`/src` = only at the component root, `*.map` = everywhere).

As a last check, the release **fails** if a zip would contain `.git`, `node_modules`, `.env`, `phpunit` or `tests`,
or a theme without `style.css` / a plugin without a `Plugin Name:` file.

## 1. Release: build and zip

```bash
# 1. commit, update CHANGELOG.md, and tag (the tag is the version)
git tag -a v1.2.0 -m "CPT Easy"
git push origin main --tags

# 2. build
bin/release.sh v1.2.0
```

Output in `releases/v1.2.0/` (git-ignored):

```
theme-sadoje-v1.2.0.zip
plugin-sadoje-v1.2.0.zip
plugin-cg-woo-grid-v1.2.0.zip
plugin-cpt-easy-v1.2.0.zip
plugin-woo-color-importer-v1.2.0.zip
manifest.tsv     # component, type, name, file, SHA-256
release.txt      # version, tag, commit, date, who built it
```

- The files come from **git**, not from your folder: uncommitted changes are never released.
  `bin/release.sh HEAD` builds the last commit (version `YYYYMMDD-<commit>`) and warns if you have uncommitted changes.
- Only some components: `bin/release.sh v1.2.0 cpt-easy theme-sadoje`
  (`sadoje` alone = theme **and** plugin; `theme-sadoje` / `plugin-sadoje` = one of them).
- A version is built once. To rebuild it, delete `releases/<version>/`.
- Each zip has the folder `<name>/` inside, so it can also be uploaded from the WordPress admin.

## 2. Deploy: upload and install

```bash
bin/deploy.sh v1.2.0 --dry-run      # see what would change
bin/deploy.sh v1.2.0                # asks for confirmation
bin/deploy.sh v1.2.0 cpt-easy       # only some components of the release
```

What happens:

1. **Local check**: the SHA-256 of every zip matches `manifest.tsv`.
2. **Server check**: `wp-content/plugins` and `wp-content/themes` exist, `unzip` (or `python3`) is there, and
   WP-CLI already loads WordPress **before** anything changes (otherwise the problem is the server, not the release:
   the deploy stops without touching the site).
3. **Upload** (scp) to `/srv/docker/wordpress/releases/<version>/` — outside the web root.
4. **Install** (`deploy-remote.sh install`):
   - checks the SHA-256 again and unzips **all** components first; if one fails, nothing has changed yet;
   - for each component, moves the current folder to `releases/backups/<YYYYMMDD-HHMMSS>/` and
     the new one into `wp-content` — two renames, so there's never a half-copied plugin;
   - owner `www-data:www-data`, folders `755`, files `644`.
5. **Health check**:
   - `docker compose run --rm wpcli eval …`: WP-CLI loads WordPress with every active plugin and the theme,
     so a PHP fatal error shows up here; then it lists the active plugins with their versions;
   - `curl https://camisetasparatodos.es/` must answer 2xx/3xx.
6. If the health check fails, it asks to **roll back** right away.

Server layout after a few deploys:

```
/srv/docker/wordpress/
├── docker-compose.yml, .env, wp-config.php, uploads.ini   (not touched by deploys)
├── wordpress/wp-content/{themes,plugins}/…                (live code)
└── releases/
    ├── .deploy-remote.sh
    ├── v1.2.0/          zips + manifest of each release
    ├── v1.2.1/
    └── backups/
        ├── 20261120-101500/       INFO + the folders replaced by that deploy
        └── 20261127-093000/
```

New components (e.g. `cpt-easy` the first time) are copied but **not activated**: activate them with WP-CLI.

## Rollback

```bash
bin/deploy.sh --rollback                   # undo the last deploy that hasn't been undone
bin/deploy.sh --rollback 20261120-101500   # a specific backup (see --list)
```

It puts back the folders saved by that deploy, and removes the components that deploy added. The versions
it takes out are kept next to the backup (`<stamp>.undone-<date>/`), so a rollback can be reversed by hand.
Running `--rollback` again goes one deploy further back.

Rollback only restores **files**. Database changes (new options, products, pages created by WP-CLI commands)
stay; undo them by hand or restore the database backup.

## Other commands

```bash
bin/deploy.sh --list          # releases and backups on the server
bin/deploy.sh --prune 5       # keep only the newest 5 releases and 5 backups on the server
bin/deploy.sh --help
bin/release.sh --help
```

## Settings

`deploy.sh` reads environment variables, or `bin/deploy.env` (git-ignored) if it exists:

```bash
# bin/deploy.env
DEPLOY_HOST=DigitalOcean10DesignRoot              # ssh alias
DEPLOY_ROOT=/srv/docker/wordpress                 # folder with docker-compose.yml
DEPLOY_WPC=/srv/docker/wordpress/wordpress/wp-content
DEPLOY_URL=https://camisetasparatodos.es          # empty = no HTTP check
DEPLOY_OWNER=www-data:www-data
```

`release.sh`: `BUILD_WITH=local` builds with your own `npm`/`composer` instead of Docker;
`NODE_IMAGE`, `COMPOSER_IMAGE` and `PHP_PLATFORM` (default `8.3`) change the build images and PHP version.
`deploy.sh`: `SKIP_HEALTH=1` skips the health check.

## What the scripts do NOT deploy

These change rarely and are copied by hand (see `MIGRATION.md`):

- `docker-compose.yml`, `wp-config.php`, `uploads.ini`, `docker/` (then `docker compose up -d`, and
  `docker compose build wpcli` if `docker/wpcli/` changed)
- `.env` (different on each machine)
- Third-party plugins and the parent theme (Cartify, WooCommerce, ACF PRO…): updated from the WordPress admin or WP-CLI
- The **database**: pages, settings, products. Changes there are done with WP-CLI commands or in the admin
  after the deploy — list them in the release notes.

Always back up the database before a deploy that comes with database changes:

```bash
ssh DigitalOcean10DesignRoot 'cd /srv/docker/wordpress && docker compose run --rm -T wpcli db export - | gzip > releases/db-$(date +%Y%m%d-%H%M%S).sql.gz'
```

## First deploy of CPT Easy (checklist)

The first release with CPT Easy also needs changes outside the zips. All WP-CLI commands run on the server in
`/srv/docker/wordpress` (prefix: `docker compose run --rm wpcli`).

- [ ] **Database backup** (command above).
- [ ] **`docker-compose.yml`**: copy the new one (the `wpcli` service mounts `uploads.ini`, so WP-CLI gets 1024M),
      then `docker compose up -d`.
- [ ] **Plugins**: install and activate *WC – APG NIF/CIF/NIE Field* (`plugin install wc-apg-nifcifnie-field --activate`);
      check ACF PRO is active; review the APG settings (NIF required or not, VIES off).
- [ ] **Release and deploy**: `bin/release.sh vX.Y.Z` → `bin/deploy.sh vX.Y.Z --dry-run` → `bin/deploy.sh vX.Y.Z`.
- [ ] **Activate CPT Easy**: `plugin activate cpt-easy`.
- [ ] **Product**: `cpt-easy install`, then `cpt-easy prices` to check the price table.
- [ ] **Landing**: create the page (or use the existing one), get its ID
      (`post list --post_type=page --name=cpt-easy --post_status=any --fields=ID,post_title`), then
      `cpt-easy landing <ID>`. Pick in the editor any image the command reports as missing.
- [ ] **Payments**: enable the methods Sergio wants; for bank transfer, fill in the account details (IBAN).
- [ ] **reCAPTCHA**: the keys are in Contact Form 7 → Integration (CPT Easy uses them).
- [ ] **Test order**: one CPT Easy order end to end (form → payment page → email), then delete it.
- [ ] **Publish** the landing page when Sergio approves it, and delete the prototype page and the old CF7 form.

## Adding a new theme or plugin

1. Add a line to `bin/components.conf` (type, folder name, repo path, build).
2. If it builds with npm, make sure `package.json` has a `build` script and `package-lock.json` is committed.
   If its build creates a clean `dist/` copy, use `npm-dist`; otherwise use `npm` and list the source folders
   in a `.distignore` (e.g. `/src`, `/assets/scss`).
3. If it needs Composer packages at runtime, use `composer` (dev packages like PHPUnit are never installed).
4. Make sure the repo `.gitignore` whitelists the folder (`!wordpress/wp-content/plugins/<name>`).
5. `bin/release.sh HEAD <name>` and check the zip: `unzip -l releases/<version>/plugin-<name>-<version>.zip`.

## Manual alternatives

The zips are standard WordPress packages, so if the scripts can't be used:

- **scp + WP-CLI**:
  ```bash
  scp releases/v1.2.0/plugin-cpt-easy-v1.2.0.zip DigitalOcean10DesignRoot:/srv/docker/wordpress/wordpress/
  ssh DigitalOcean10DesignRoot 'cd /srv/docker/wordpress && docker compose run --rm wpcli plugin install plugin-cpt-easy-v1.2.0.zip --force && rm wordpress/plugin-cpt-easy-v1.2.0.zip'
  ```
  (`theme install` for the theme). No backup is kept: download the current folder first.
- **WordPress admin**: Plugins → Add new → Upload plugin (Themes → Add new → Upload theme), choose
  "Replace current with uploaded". Only works if file modifications are allowed in production
  (`DISALLOW_FILE_MODS` not set), which we'd rather keep off.

## Testing the scripts without the server

`DEPLOY_HOST=local` runs the "server" side on your own machine, against any folder that looks like
`/srv/docker/wordpress`:

```bash
mkdir -p /tmp/fakeprod/wordpress/wp-content/{plugins,themes}
DEPLOY_HOST=local DEPLOY_ROOT=/tmp/fakeprod DEPLOY_URL= DEPLOY_OWNER="$(id -un):$(id -gn)" SKIP_HEALTH=1 \
  bin/deploy.sh v1.2.0 --yes
```

Check `bin/*.sh` with `shellcheck -S warning bin/*.sh` after changing them.

## Troubleshooting

| Message | Fix |
|---|---|
| `Can't ssh to DigitalOcean10DesignRoot` | `ssh DigitalOcean10DesignRoot true` must work without a prompt (key in the agent: `ssh-add`). |
| `releases/<v> already exists` | That version was built already. Deploy it, or delete the folder to rebuild. |
| `Unknown git ref` | Create and push the tag first, or use `HEAD`. |
| `npm run build made no dist/ folder` | The plugin is set to `npm-dist` but its build didn't create `dist/`: check `npm run build` in the plugin. |
| `would ship development files` | Something like `tests/` or `.env` is in the component: add it to its `.distignore` (or remove it from git). |
| `Checksum mismatch` | The zip changed after the release (or the upload broke): delete `releases/<v>/` and rebuild. |
| `Install unzip on the server` | `apt install unzip` on the server. |
| `WP-CLI can't load WordPress on the server even BEFORE deploying` | The server, not the release. `Allowed memory size of 134217728 bytes exhausted` = WP-CLI has PHP's default 128M: the server's `docker-compose.yml` must mount `uploads.ini` in the `wpcli` service (copy the repo's one). Check with `docker compose run --rm -T wpcli eval 'echo ini_get("memory_limit");'` → `1024M`. |
| Health check: `WP-CLI could not load WordPress` | After the deploy only: a PHP fatal error, usually in the new code. Roll back, then run the same command locally. |
| Health check: `Home page: HTTP 000` | The site didn't answer in 20 s, or the URL is wrong (`DEPLOY_URL`). |
| A new plugin doesn't show up active | New components are only copied: `docker compose run --rm wpcli plugin activate <name>`. |

## Why this approach

- **Not `git pull` on the server**: the built CSS/JS of `sadoje` and `cg-woo-grid` aren't in git (they'd need
  node on the server), the server would need GitHub credentials, and a `.git` folder near the web root already
  caused trouble once (`/.git/HEAD` was public).
- **Not uploading zips in the admin**: it needs file modifications enabled in production, there's no backup or
  rollback, and it's several manual uploads per deploy.
- **Build locally, from git, in Docker**: what goes to production is exactly a tagged commit plus its build,
  with the same Node/PHP versions every time, and nothing from your working folder.
- **Next step**: GitHub Actions can run `bin/release.sh` when a tag is pushed and attach the zips to a GitHub
  release; `deploy.sh` would then download them instead of building.
