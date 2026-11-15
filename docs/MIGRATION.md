# Local ↔ production parity: migration runbook

One `docker-compose.yml` and one `wp-config.php`, the same files on both machines. Only `.env` differs.
PHP stays on **8.3**. The production Nginx config and the local Caddyfile are **not** changed.

```
docker-compose.yml        shared (caddy/node only start with COMPOSE_PROFILES=local / local,node)
wp-config.php             shared, mounted read-only
docker/wpcli/Dockerfile   shared
docker/composer/Dockerfile  (your existing one)
uploads.ini               shared: diff the local and production copies and keep one
Caddyfile, certs/         local only, unchanged
.env                      per machine: env.local.example / env.production.example
```

After the migration, both machines use the same names:

| | local | production (before → after) |
|---|---|---|
| project | `camisetasparatodos` | `wordpress` → `camisetasparatodos` |
| containers | `camisetasparatodos_mysql`, `_wordpress`, `_phpmyadmin`, (`_caddy`, `_node`) | `wp_db`, `wp_app`, `wp_phpmyadmin` → same as local |
| DB volume | `camisetasparatodos_mysql_data` | `wordpress_db_data` → `camisetasparatodos_mysql_data` |
| DB service / host | `mysql:3306` | `db:3306` → `mysql:3306` |
| WP files folder | `./wordpress` | `./wp_data` → `./wordpress` |
| logs in container | `/var/log/wordpress` (outside the web root) | `/var/www/html/logs` → `/var/log/wordpress` |

Docker can't rename a volume. Production therefore gets a **new, empty** `camisetasparatodos_mysql_data`, loaded from a dump. The old `wordpress_db_data` stays untouched as your rollback.

---

## 0. Do this today, whatever else happens

Production logs are currently mounted *inside* the web root (`/var/www/html/logs`), and Nginx proxies every path to WordPress. Check whether anyone can download them:


```bash
curl -s -o /dev/null -w '%{http_code}\n' https://camisetasparatodos.es/logs/error.log
```
If that prints `200`, block it in Nginx right away 


```bash
vi /etc/nginx/sites-available/wordpress.conf
...
	location ^~ /logs/ { return 404; }
...

sudo nginx -t  
sudo nginx -s reload

## Check it
curl -s -o /dev/null -w '%{http_code}\n' https://camisetasparatodos.es/logs/error.log
---> 404
```

or move the files out. They can contain paths, SQL errors and sometimes personal data. The new setup fixes this permanently.

Your `/root/.gitconfig` suggests `wp_data` may be a git repository. If it is, the `.git` folder is in the web root too:

```bash
ls -d /srv/docker/wordpress/wp_data/.git
curl -s -o /dev/null -w '%{http_code}\n' https://camisetasparatodos.es/.git/HEAD
```
If that prints `200`, the whole repository (history, any committed secrets) can be downloaded. Block it the same way (`location ~ /\.git { return 404; }`), or remove the folder.

> **Done 2026-11-15:** it returned `200`, so `wp_data/.git` was removed. Anything that was ever committed there (e.g. a `wp-config.php` with DB passwords or salts) must be treated as public, which is another reason the passwords and salts are rotated in P2. To see whether anyone downloaded it:
> ```bash
> docker logs wp_app 2>&1 | grep -E '"(GET|HEAD) /\.git/' | awk '{print $1}' | sort | uniq -c | sort -rn | head
> ```
> The future repo lives in `/srv/docker/wordpress/` itself, which is **outside** the web root (`./wordpress`), so it can't be served.

## Production

### P1. Collect facts (no downtime)

```bash
cd /srv/docker/wordpress

# Check compose version. It needs Compose v2
docker compose version                       
 	Docker Compose version v2.37.3

# Check MySql version -> already set in docker-compose.yml (mysql:8.0.44)
docker exec wp_db mysqld --version           
 	/usr/sbin/mysqld  Ver 8.0.44 for Linux on x86_64 (MySQL Community Server - GPL)

# Check  WORDPRESS_TABLE_PREFIX, it it returns wp_options, the table prefix is WORDPRESS_TABLE_PREFIX=wp_ 
docker exec wp_db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SHOW TABLES LIKE \"%options\"" "$MYSQL_DATABASE"'
   wp_options

# Check if there are any backup scripts etc. that use the old names                                              
crontab -l; sudo crontab -l; grep -rn 'wp_data\|wp_db\|wp_app' /etc/cron* ~/ 2>/dev/null | grep -v backups
   # The check found nothing that would break,
   
   
# Check if there are any CPU limits
docker inspect wp_app wp_db --format '{{.Name}} cpus={{.HostConfig.NanoCpus}} mem={{.HostConfig.Memory}}'
	/wp_app cpus=3000000000 mem=0
	/wp_db cpus=1500000000 mem=0
   # -> already in docker-compose.yml (cpus: 3.0 / cpus: 1.5), no memory limits
                                              
```

### P2. Prepare in a staging folder (no downtime)
Don't put the new `docker-compose.yml` straight into `/srv/docker/wordpress`. It would overwrite the old one, which you still need in P3 to stop the old stack and as your rollback.


```bash
# --- on the SERVER ---
mkdir -p /srv/docker/wordpress/_new/docker/wpcli /srv/docker/wordpress/_new/docker/composer

# --- on your LAPTOP --- (same files as your local repo, minus Caddyfile/certs)
cd ~/Dockers/camisetasparatodos/
SRV=DigitalOcean10DesignRoot:/srv/docker/wordpress/_new
scp docker-compose.yml wp-config.php uploads.ini env.production.example $SRV/
scp docker/wpcli/Dockerfile    $SRV/docker/wpcli/
scp docker/composer/Dockerfile $SRV/docker/composer/

# --- on the SERVER ---
cd /srv/docker/wordpress/_new
mv env.production.example .env   # then edit: NEW passwords,  ACF if needed

# Add new salt values
cd /srv/docker/wordpress/_new
# 1. Remove the 8 placeholder lines
sed -i -E '/^WORDPRESS_(AUTH|SECURE_AUTH|LOGGED_IN|NONCE)_(KEY|SALT)=/d' .env
# 2. Fetch new salts, turn define('X', '...'); into WORDPRESS_X='...', append to .env
curl -s https://api.wordpress.org/secret-key/1.1/salt/ \
  | sed -E "s/^define\('([A-Z_]+)',[[:space:]]*'(.*)'\);$/WORDPRESS_\1='\2'/" >> .env
# 3. Check: should print 8
grep -cE '^WORDPRESS_(AUTH|SECURE_AUTH|LOGGED_IN|NONCE)_(KEY|SALT)=' .env


# Update permissions
chown -R www-data:www-data /srv/docker/wordpress/_new
find /srv/docker/wordpress/_new -type d -exec chmod 755 {} \;
find /srv/docker/wordpress/_new -type f -exec chmod 644 {} \;

chmod 600 .env

# Check all files. Expect: .env, docker-compose.yml, uploads.ini, wp-config.php, docker/composer/Dockerfile, docker/wpcli/Dockerfile
 find . -type f | sort
./.env
./docker-compose.yml
./docker/composer/Dockerfile
./docker/wpcli/Dockerfile
./uploads.ini
./wp-config.php
```

- [ ] Keep `MYSQL_DATABASE` and `MYSQL_USER` the same as in today's `.env`.
- [ ] Set **new** `MYSQL_PASSWORD` and `MYSQL_ROOT_PASSWORD`. The new volume creates both users from `.env` when it starts, so this is a free moment to rotate them, and the old root password is in your bash history.
- [ ] Generate **new** salts. The current ones are in the old `wp-config.php`, which the attacker could read. Everyone gets logged out once.
- [ ] Check the rendered config and pre-pull the images (still no downtime):

```bash
# Check Doicker volumes. Expect: expect camisetasparatodos_* names, 127.0.0.1 on 8090/8091, NO PMA_USER
cd /srv/docker/wordpress/_new

docker compose config | grep -nE 'container_name|name: camisetas|127.0.0.1|PMA_USER'
1:name: camisetasparatodos
5:    container_name: camisetasparatodos_mysql
30:    container_name: camisetasparatodos_phpmyadmin
43:        host_ip: 127.0.0.1
52:    container_name: camisetasparatodos_wordpress
82:        host_ip: 127.0.0.1
110:    name: camisetasparatodos_default
113:    name: camisetasparatodos_mysql_data

# Build package
docker compose pull mysql wordpress phpmyadmin
docker compose build wpcli
```

### P3. Switch over (maintenance window, a few minutes)

```bash
cd /srv/docker/wordpress
mkdir -p ~/backups _old && TS=$(date +%F-%H%M)

# 1. Stop WordPress first so no orders arrive after the dump
docker compose stop wordpress

# 2. Final dump
docker exec wp_db sh -c 'exec mysqldump --single-transaction --no-tablespaces --routines --triggers \
  -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' | gzip > ~/backups/db-$TS.sql.gz
gunzip -t ~/backups/db-$TS.sql.gz && ls -lh ~/backups/db-$TS.sql.gz
-rw-r--r-- 1 root root 22M Nov 15 20:21 /root/backups/db-2026-11-15-2021.sql.gz

# 3. Remove the old containers (NO -v: the old volume stays as rollback)
docker compose down

# 4. Swap files
mv docker-compose.yml .env wpcli _old/
mv uploads.ini _old/ 2>/dev/null || true
mv wp_data wordpress
mv _new/* _new/.env . && rmdir _new

# 5. Fresh MySQL volume + import
docker compose up -d mysql
## Wait loop: it pauses the script until MySQL is ready to accept connections, so the import on the next line doesn’t run too early.
until [ "$(docker inspect -f '{{.State.Health.Status}}' camisetasparatodos_mysql)" = healthy ]; do sleep 2; done
gunzip -c ~/backups/db-$TS.sql.gz | docker compose exec -T mysql sh -c \
  'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'

# 6. Everything else
docker compose up -d
docker compose ps
NAME                            IMAGE                     COMMAND                  SERVICE      CREATED         STATUS                   PORTS
camisetasparatodos_mysql        mysql:8.0.44              "docker-entrypoint.s…"   mysql        2 minutes ago   Up 2 minutes (healthy)   3306/tcp, 33060/tcp
camisetasparatodos_phpmyadmin   phpmyadmin:5.2            "/docker-entrypoint.…"   phpmyadmin   8 seconds ago   Up 7 seconds             127.0.0.1:8091->80/tcp
camisetasparatodos_wordpress    wordpress:php8.3-apache   "docker-entrypoint.s…"   wordpress    8 seconds ago   Up 7 seconds             127.0.0.1:8090->80/tcp

# 7. Delete the commands with passwords typed in clear from the bash history
#    (by pattern, not line number: bash trims the oldest lines, so numbers shift)
# see what will be removed
grep -n 'mysqldump -u root -p' ~/.bash_history      
1027:docker exec wp_db mysqldump -u root -p47qKPi@jkGDm%RAs wpuser > ~/evidence-db-$(date +%F).sql
1028:docker exec wp_db mysqldump -u root -prootpass wordpress > ~/evidence-db-$(date +%F).sql
# remove it
sed -i '/mysqldump -u root -p/d' ~/.bash_history
```

### P4. Verify
- [ ] `curl -sI -H 'Host: camisetasparatodos.es' -H 'X-Forwarded-Proto: https' http://127.0.0.1:8090/` returns 200, or a single 301 that doesn't loop.
- [ ] The home page, a product, the cart, checkout and the wp-admin login all work. You'll need to log in again because the salts changed.
- [ ] `docker compose run --rm wpcli option get home` returns `https://camisetasparatodos.es`.
- [ ] `docker compose run --rm wpcli db export - | head -c 200` works (tests WP-CLI's DB connector).
- [ ] NitroPack is still connected in wp-admin, and you've purged its cache once.
- [ ] `/phpmyadmin/`: basic auth, then log in with `wpuser` or `root`.
- [ ] `curl -s -o /dev/null -w '%{http_code}\n' https://camisetasparatodos.es/logs/error.log` returns 404, and new log lines appear in `./logs/`.
- [ ] `docker compose logs --tail=50 wordpress` shows no PHP fatals.
- [ ] CPU limits carried over: `docker inspect camisetasparatodos_wordpress camisetasparatodos_mysql --format '{{.Name}} {{.HostConfig.NanoCpus}}'` shows 3000000000 and 1500000000.
- [ ] Remove the stale git setting (that repo is gone): `git config --global --unset-all safe.directory '/srv/docker/wordpress/wp_data'`.
  When you create the new repo in `/srv/docker/wordpress`, git will tell you if it needs a `safe.directory` entry (only when the folder's owner isn't the user running git).

**Rollback:** run `docker compose down`, then `mkdir _new && mv docker-compose.yml wp-config.php docker uploads.ini .env _new/` (keeps the new files for a second try), `mv wordpress wp_data`, move `_old/*` and `_old/.env` back, then `docker compose up -d`. That brings back the old containers on the untouched `wordpress_db_data`. Orders placed after the switch would be missing, so decide quickly.

### P5. Tidy up (after a week without problems)
- [ ] `docker volume rm wordpress_db_data`
- [ ] `rm -rf _old`
- [ ] `rm wordpress/wp-config.php`: the old generated file, hidden by the mount. It holds the old salts and the `WORDPRESS_CONFIG_EXTRA` eval.
- [ ] Update any backup scripts or cron jobs found in P1 to use the new names.

---

## Local: rebuild on a copy of production

### L1. Get the dump
Copy the dump from P3 into `./backups/` in your project folder. That's **outside** `./wordpress`, so it's never in the web root and never deployed with rsync. Add `backups/` to `.gitignore` and to your rsync excludes.
```bash
cd ~/Dockers/camisetasparatodos && mkdir -p backups
scp server:~/backups/db-<TS>.sql.gz backups/
DUMP=backups/db-<TS>.sql.gz      # the real file name; used below
```
> The dump holds real customer data (GDPR). Keep it on an encrypted disk, out of git, and delete it once it's imported.

### L2. Remove the old local stack and its volumes
Run this with the **old** files still in place:


```bash
docker compose --profile dev down
docker volume ls | grep -E 'mysql_data|theme_node_modules'
docker volume rm <old>_mysql_data <old>_theme_node_modules # docker volume rm camisetasparatodos_mysql_data
rm -rf mysql-lowmem.cnf          # the empty FOLDER Docker created; no longer used
ls -la wp-config.php uploads.ini Caddyfile   # all three must be FILES (not folders)
```

### L3. Swap files
- [ ] Replace `docker-compose.yml` with the new one, and add `wp-config.php` (replacing the old local one) and `docker/wpcli/Dockerfile`.
- [ ] Rebuild `.env` from `env.local.example`, keeping your passwords and salts.
- [ ] Use the same `uploads.ini` as production.


The MySQL version is already set in `docker-compose.yml` (`mysql:8.0.44`). To double-check it against the dump:

```bash
zcat "$DUMP" | head -5 | grep -i 'server version'
-- Server version       8.0.44
```

### L4. Import

```bash
cd ~/Dockers/camisetasparatodos
docker compose build wpcli
docker compose up -d mysql      # wait until "healthy" in `docker compose ps`

# Import with WP-CLI (reads the dump from stdin, so it can stay outside ./wordpress)
gunzip -c "$DUMP" | docker compose run --rm -T wpcli db import -

# Another way, with the mysql client in the DB container
gunzip -c "$DUMP" | docker compose exec -T mysql sh -c \
  'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```

### L5. Search and replace, then sanity checks

```bash
LOCAL=https://camisetasparatodos.localhost:8467
WP="docker compose run --rm wpcli"

$WP search-replace 'https://camisetasparatodos.es' "$LOCAL" --all-tables-with-prefix --skip-columns=guid --precise --report-changed-only --dry-run
$WP search-replace 'https://camisetasparatodos.es' "$LOCAL" --all-tables-with-prefix --skip-columns=guid --precise --report-changed-only
$WP search-replace 'https:\/\/camisetasparatodos.es' "${LOCAL//\//\\/}" --all-tables-with-prefix --skip-columns=guid --precise --report-changed-only   # JSON-escaped
# Repeat for http:// or www. variants if the site ever used them.
# Don't replace the bare domain: it would also rewrite emails like info@camisetasparatodos.es.

$WP user list --role=administrator     # any admin you don't recognise = leftover from the compromise
$WP cache flush && $WP rewrite flush
```

### L6. Stop the local copy talking to the real world
- [ ] `$WP plugin deactivate nitropack`: it's tied to the production domain and account.
- [ ] Put payment gateways in test or sandbox mode, or deactivate them.
- [ ] Block outgoing mail, with a mail-catcher plugin or a Mailpit container.

### L7. Bring everything up

```bash
docker compose up -d     # mysql, wordpress, phpmyadmin + caddy (COMPOSE_PROFILES=local)
```
- [ ] `https://camisetasparatodos.localhost:8467` loads, the production login works, and uploads come through Caddy's fallback.
- [ ] `https://pma.camisetasparatodos.localhost:8467` asks for a login now (no more auto-login).
