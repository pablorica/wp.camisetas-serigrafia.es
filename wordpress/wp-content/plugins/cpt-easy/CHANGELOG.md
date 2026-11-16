# Change Log

## [0.3.0] - 2026-11-16

### Added
- ACF PRO blocks for the landing: Hero, Precios, Opiniones, Pedido, Categorías and FAQ (category "CPT Easy").
- `assets/landing.css` and `assets/landing.js` (moved from the HTML prototype into the plugin).
- `wp cpt-easy landing <page-id>`: builds a page with the blocks and the prototype texts.

## [0.2.0] - 2026-11-16

### Added
- `[cpt_easy_precios calidad="…"]`: landing price rows (1 and 2 caras) rendered from the variations.
- `[cpt_easy_minimo]`: minimum quantity.
- Cash on delivery and cheque are hidden on the payment page of CPT Easy orders (`cpt_easy_excluded_gateways`).

## [0.1.0] - 2026-11-16

### Added
- CPT Easy variable product (Calidad × Impresión) created by `wp cpt-easy install`, with the landing prices.
- Quantity tiers per variation, editable in the variation panel.
- `[cpt_easy_form]` shortcode: AJAX form that creates a pending order and redirects to the payment page.
- Design/mockup uploads, NIF on the order, province from the postcode, payment-link email.
- Nonce, honeypot, rate limit and reCAPTCHA v3 (Contact Form 7 keys).
