# docker/composer/Dockerfile
# Same PHP as production (wordpress:php8.3-apache): Composer resolves dependencies
# against this PHP version, so an 8.5 image could install packages that break on 8.3.
FROM wordpress:php8.3-apache
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
ENTRYPOINT ["composer"]
