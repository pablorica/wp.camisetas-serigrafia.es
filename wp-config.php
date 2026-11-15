<?php
/**
 * wp-config.php — ONE file for local and production.
 * Mounted read-only by compose. Everything that differs per machine comes from .env.
 */

// === Environment ===
$app_env = getenv('APP_ENV') ?: 'production';
define('WP_ENVIRONMENT_TYPE', in_array($app_env, ['local', 'development', 'staging', 'production'], true) ? $app_env : 'production');

// === Database (from .env via compose) ===
define('DB_NAME',     getenv('WORDPRESS_DB_NAME'));
define('DB_USER',     getenv('WORDPRESS_DB_USER'));
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
define('DB_HOST',     getenv('WORDPRESS_DB_HOST') ?: 'mysql:3306');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

$table_prefix = getenv('WORDPRESS_TABLE_PREFIX') ?: 'wp_';

// === HTTPS behind a reverse proxy (Caddy locally, Nginx in production) ===
// strpos instead of === so "https,http" lists from chained proxies still match
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}

// === Site URLs ===
// Production: WORDPRESS_HOME is used as-is (Nginx serves 443).
// Local: append WORDPRESS_SSL_PORT unless WORDPRESS_HOME already has a port.
$wphome = getenv('WORDPRESS_HOME');
if (WP_ENVIRONMENT_TYPE === 'local' && !preg_match('#^https?://[^/]+:\d+#', $wphome)) {
    $wphome .= ':' . (getenv('WORDPRESS_SSL_PORT') ?: '443');
}
define('WP_HOME',    $wphome);
define('WP_SITEURL', $wphome);
define('FORCE_SSL_ADMIN', true);

// === Salts (from .env). If empty, WordPress falls back to keys stored in the DB. ===
define('AUTH_KEY',         getenv('WORDPRESS_AUTH_KEY') ?: '');
define('SECURE_AUTH_KEY',  getenv('WORDPRESS_SECURE_AUTH_KEY') ?: '');
define('LOGGED_IN_KEY',    getenv('WORDPRESS_LOGGED_IN_KEY') ?: '');
define('NONCE_KEY',        getenv('WORDPRESS_NONCE_KEY') ?: '');
define('AUTH_SALT',        getenv('WORDPRESS_AUTH_SALT') ?: '');
define('SECURE_AUTH_SALT', getenv('WORDPRESS_SECURE_AUTH_SALT') ?: '');
define('LOGGED_IN_SALT',   getenv('WORDPRESS_LOGGED_IN_SALT') ?: '');
define('NONCE_SALT',       getenv('WORDPRESS_NONCE_SALT') ?: '');

// === Page cache (NitroPack) ===
// NitroPack normally writes WP_CACHE into wp-config.php itself; this file is read-only,
// so it's controlled here instead: WORDPRESS_CACHE=true in production, false locally.
define('WP_CACHE', getenv('WORDPRESS_CACHE') === 'true');

// === Debugging & logs ===
// Default is OFF: debug is only on when WORDPRESS_DEBUG=true.
// Logs live in /var/log/wordpress (host ./logs), OUTSIDE the web root.
define('WP_DEBUG',         getenv('WORDPRESS_DEBUG') === 'true');
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG',     '/var/log/wordpress/debug.log');
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
@ini_set('error_log', '/var/log/wordpress/php-error.log');

// === Hardening ===
// No theme/plugin code editor in wp-admin (plugin installs and updates still work)
define('DISALLOW_FILE_EDIT', true);

// === ACF PRO licence (from .env). Activates the licence automatically. ===
$acf_pro_license = getenv('ACF_PRO_LICENSE');
if ($acf_pro_license && !defined('ACF_PRO_LICENSE')) {
    define('ACF_PRO_LICENSE', $acf_pro_license);
}
unset($acf_pro_license, $app_env, $wphome);

// === Bootstrap ===
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
