# docker/wpcli/Dockerfile — same in local and production.
# Pinned to PHP 8.3 to match the wordpress:php8.3-apache image.
FROM wordpress:cli-php8.3

USER root
# MySQL 8 users default to caching_sha2_password; the Alpine mariadb client
# needs this connector's auth plugin for `wp db export/import/query`.
RUN apk add --no-cache mariadb-connector-c

# uid/gid 33 = www-data in the wordpress Apache image -> no root-owned files
USER 33:33
