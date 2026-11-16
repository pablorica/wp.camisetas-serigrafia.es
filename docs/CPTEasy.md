# CPT Easy

Quick-order system for camisetasparatodos.es: one variable product, a short form, straight to payment.

## How it works

- **Product** `CPT Easy` (SKU `CPT-EASY`), variable, hidden from the shop. Two local attributes
  (not `pa_` taxonomies, so they stay out of the YITH filters):
  - **Calidad**: Basic, Medium, Premium
  - **Impresión**: 1 cara, 2 caras
- **Price** = Calidad × Impresión × quantity tier. Each of the 6 variations has a
  *"CPT Easy · precios por cantidad (sin IVA)"* box (Products → CPT Easy → Variations):
  ```
  10=8.50
  25=6.95
  50=4.95
  100=3.95
  ```
  One line per tier: minimum quantity = unit price without VAT. The variation's regular price is set to the first tier.
  The lowest first tier is the form minimum (10). Maximum 5000.
- **Colour and sizes** are free text, saved on the order line (`Color`, `Tallas`), not variations.
- **Form** `[cpt_easy_form]` replaces the Contact Form 7 prototype (same field names and `.cpt-f-*` classes).
  On submit (AJAX) it validates, uploads the files, creates a **pending** order with billing/shipping,
  NIF, VAT, the line item and its meta, emails the customer the payment link (WooCommerce "Customer invoice"),
  and redirects to the **order-pay** page.
- The product cannot be added to the normal cart; the CG Woo Grid / Sadoje cart price hooks never touch these orders.

## Install

```bash
docker compose run --rm wpcli plugin activate cpt-easy
docker compose run --rm wpcli cpt-easy install        # creates the product + 6 variations with the landing prices
docker compose run --rm wpcli cpt-easy prices         # shows the price matrix
docker compose run --rm wpcli cpt-easy install --reset-prices   # overwrite tiers with the landing prices
```

Then on the landing page replace the CF7 shortcode with `[cpt_easy_form]`.

## Landing page

The landing lives in the page content (Custom HTML blocks). Dynamic pieces:

- `[cpt_easy_precios calidad="Basic"]` — the `<ul class="cpt-rows">` of a price card (`.c1` = 1 cara, `.c2` = 2 caras),
  built from the variation tiers, so changing a price in the product changes the landing.
- `[cpt_easy_minimo]` — minimum quantity.
- `[cpt_easy_form]` — the order form.

On the payment page of a CPT Easy order, cash on delivery and cheque are hidden (`cpt_easy_excluded_gateways`).

## Landing page hooks (optional)

- `window.cptEasy.prices` has the full matrix and `window.cptEasy.unitPrice(calidad, impresion, qty)` the unit price,
  so the price table can be rendered from the real product data.
- Any element with `data-cpt-calidad="Basic|Medium|Premium"` selects that quality and scrolls to the form
  (use it on the "Elegir Basic" buttons).
- The existing `data-caras="1|2"` buttons also switch the form's *Impresión*.

## Files

Uploads go to `wp-content/uploads/cpt-easy/YYYY/MM/` with a random prefix.
Allowed: PDF, AI, EPS, PSD, PNG, JPG, TIFF, WEBP, max 25 MB each (`cpt_easy_allowed_mimes` filter).
The folder gets an `.htaccess` (no listing, no scripts). Links appear in the order admin, My Account and emails.

## Anti-spam

Nonce, honeypot, 5 orders/hour per IP, and reCAPTCHA v3 using the keys already saved in
Contact Form 7 → Integration (skipped if none).

## Filters / actions

| Hook | Use |
|---|---|
| `cpt_easy_valid_nif` | Accept other tax IDs (DNI, NIE and CIF formats by default). |
| `cpt_easy_allowed_mimes` | File types. |
| `cpt_easy_send_invoice_email` | `false` to not email the payment link. |
| `cpt_easy_redirect_url` | Where to send the customer after the order is created. |
| `cpt_easy_order_created` | Runs after the order is created (`$order, $data, $files`). |
