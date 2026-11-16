# Change Log

## [0.3.3] - 2026-11-16

### Removed
- CPT Easy no longer hides cash on delivery and cheque on the payment page: CPT Easy orders offer every payment
  method enabled in WooCommerce.

## [0.3.2] - 2026-11-16

### Added
- Works with "WC – APG NIF/CIF/NIE Field": same NIF keys (order `_billing_nif`, user `billing_nif`), the form reads the
  customer's NIF from the classic checkout/My Account or the Checkout block (`_wc_billing/apg/nif`), and CPT Easy no
  longer prints the NIF itself in the admin and emails when APG is active (APG adds it to the address).

## [0.3.1] - 2026-11-16

### Added
- Logged-in customers get the billing and shipping fields pre-filled from their account (`cpt_easy_prefill` filter).
- CPT Easy orders of logged-in customers save the addresses and the NIF (`billing_nif` user meta) to the account.

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
