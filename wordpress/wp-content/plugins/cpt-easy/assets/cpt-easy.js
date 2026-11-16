/**
 * CPT Easy form: live total, shipping toggle, file names, AJAX submit.
 * Data comes from window.cptEasy (prices per Calidad × Impresión, VAT, limits).
 */
(function () {
	'use strict';

	const cfg = window.cptEasy;
	if (!cfg) return;

	const money = (n) =>
		n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '€';

	/** Unit price for quality/print/qty, or null below the first tier. */
	function unitPrice(quality, print, qty) {
		const tiers = (cfg.prices[quality] || {})[print] || [];
		let price = null;
		tiers.forEach((t) => {
			if (qty >= t.min) price = t.price;
		});
		return price;
	}
	// Exposed so the landing price table can reuse it.
	cfg.unitPrice = unitPrice;

	function loadRecaptcha() {
		if (!cfg.recaptcha || window.grecaptcha) return;
		const s = document.createElement('script');
		s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(cfg.recaptcha);
		s.async = true;
		document.head.appendChild(s);
	}

	function recaptchaToken() {
		if (!cfg.recaptcha || !window.grecaptcha) return Promise.resolve('');
		return new Promise((resolve) => {
			window.grecaptcha.ready(() => {
				window.grecaptcha
					.execute(cfg.recaptcha, { action: 'cpt_easy' })
					.then(resolve, () => resolve(''));
			});
		});
	}

	function init(form) {
		const $ = (sel) => form.querySelector(sel);
		const $$ = (sel) => Array.from(form.querySelectorAll(sel));
		const submit = $('button[type="submit"]');
		const response = $('.cpt-f-response');

		const state = () => ({
			quality: ($('input[name="cpt_calidad_ui"]:checked') || {}).value,
			print: $('select[name="impresion"]').value,
			qty: parseInt($('input[name="cantidad"]').value, 10) || 0,
		});

		function updateTotal() {
			const s = state();
			const unit = unitPrice(s.quality, s.print, s.qty);
			const ok = unit !== null && s.qty >= cfg.minQty && s.qty <= cfg.maxQty;
			const net = ok ? Math.round(unit * s.qty * 100) / 100 : null;
			$('[data-cpt-sin]').textContent = ok ? money(net) : '—';
			// Round the VAT to cents like WooCommerce does (297,50 × 21% = 62,48).
			const vat = ok ? Math.round(net * cfg.vat) / 100 : 0;
			$('[data-cpt-con]').textContent = ok ? money(net + vat) : '—';
		}

		function toggleShipping() {
			const same = $('input[name="envio_igual"]').checked;
			$$('[data-cpt-ship]').forEach((el) => (el.hidden = same));
		}

		function clearErrors() {
			$$('.cpt-f-invalid').forEach((el) => el.classList.remove('cpt-f-invalid'));
			$$('[data-cpt-msg]').forEach((el) => (el.textContent = ''));
			response.hidden = true;
			response.textContent = '';
		}

		function showErrors(fields, message) {
			let first = null;
			Object.keys(fields || {}).forEach((name) => {
				const msg = form.querySelector('[data-cpt-msg="' + name + '"]');
				const input = form.querySelector('[name="' + name + '"]');
				if (msg) msg.textContent = fields[name];
				const wrap = input && input.closest('.cpt-f-field, .cpt-f-legal, .cpt-f-cards');
				if (wrap) wrap.classList.add('cpt-f-invalid');
				const details = input && input.closest('details');
				if (details) details.open = true;
				if (!first && input) first = input;
			});
			response.textContent = message || cfg.i18n.error;
			response.hidden = false;
			(first || response).scrollIntoView({ behavior: 'smooth', block: 'center' });
			if (first) first.focus({ preventScroll: true });
		}

		form.addEventListener('input', updateTotal);
		form.addEventListener('change', (e) => {
			updateTotal();
			if (e.target.name === 'envio_igual') toggleShipping();
			if (e.target.name === 'privacidad') submit.disabled = !e.target.checked;
			if (e.target.type === 'file') {
				const name = e.target.closest('.cpt-f-filebox').querySelector('.cpt-f-filename');
				const file = e.target.files[0];
				name.textContent = file ? file.name : '';
				if (file && file.size > cfg.maxFileMb * 1024 * 1024) {
					e.target.value = '';
					name.textContent = '';
					showErrors({ [e.target.name]: cfg.i18n.tooBig });
				}
			}
		});

		form.addEventListener('submit', async (e) => {
			e.preventDefault();
			if (form.classList.contains('is-sending')) return;
			clearErrors();
			form.classList.add('is-sending');
			submit.disabled = true;
			const label = submit.textContent;
			submit.textContent = cfg.i18n.sending;

			try {
				const data = new FormData(form);
				data.set('recaptcha', await recaptchaToken());
				const res = await fetch(cfg.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
				const json = await res.json().catch(() => null);

				if (json && json.success && json.data.redirect) {
					window.location.assign(json.data.redirect);
					return; // Keep the button disabled while leaving.
				}
				const d = (json && json.data) || {};
				showErrors(d.fields, d.message);
			} catch (err) {
				showErrors({}, cfg.i18n.network);
			}
			form.classList.remove('is-sending');
			submit.disabled = !$('input[name="privacidad"]').checked;
			submit.textContent = label;
		});

		// Landing hooks: buttons with data-cpt-calidad="Basic|Medium|Premium" pick the quality and
		// scroll to the form; the existing data-caras="1|2" switch also sets the print select.
		document.addEventListener('click', (e) => {
			const pick = e.target.closest('[data-cpt-calidad]');
			if (pick) {
				const radio = form.querySelector(
					'input[name="cpt_calidad_ui"][value="' + pick.dataset.cptCalidad + '"]'
				);
				if (radio) {
					e.preventDefault();
					radio.checked = true;
					updateTotal();
					form.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			}
			const caras = e.target.closest('[data-caras]');
			if (caras && !form.contains(caras)) {
				$('select[name="impresion"]').value = caras.dataset.caras === '2' ? '2 caras' : '1 cara';
				updateTotal();
			}
		});

		form.addEventListener('focusin', loadRecaptcha, { once: true });
		toggleShipping();
		updateTotal();
	}

	document.querySelectorAll('form[data-cpt-easy]').forEach(init);
})();
