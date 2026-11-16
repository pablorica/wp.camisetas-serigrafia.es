/**
 * CPT Easy · Landing blocks: 1/2 caras switch (Precios) and reviews carousel (Opiniones).
 * The order form has its own script (cpt-easy.js).
 */
(function () {
	'use strict';

	/* Precios: 1 cara / 2 caras ------------------------------------------- */
	document.querySelectorAll('.cpt-precios').forEach(function (sec) {
		var buttons = sec.querySelectorAll('.cpt-toggle button');
		var set = function (two) {
			sec.classList.toggle('is-2', two);
			buttons.forEach(function (b) {
				b.setAttribute('aria-pressed', (b.dataset.caras === '2') === two ? 'true' : 'false');
			});
		};
		buttons.forEach(function (b) {
			b.addEventListener('click', function () {
				set(b.dataset.caras === '2');
			});
		});
		// The form's "Impresión" select switches the table too.
		document.addEventListener('change', function (e) {
			if (e.target.matches && e.target.matches('.cpt-pedido select[name="impresion"]')) {
				set(e.target.value === '2 caras');
			}
		});
	});

	/* Opiniones: carousel ------------------------------------------------- */
	document.querySelectorAll('.cpt-slider').forEach(function (sl) {
		var track = sl.querySelector('.cpt-track');
		var view = sl.querySelector('.cpt-view');
		var slides = [].slice.call(sl.querySelectorAll('.cpt-slide'));
		var arrows = sl.querySelectorAll('.cpt-arr');
		var dots = [].slice.call(sl.querySelectorAll('.cpt-dot'));
		var i = 0;
		var timer;

		if (slides.length < 2) {
			arrows.forEach(function (a) { a.hidden = true; });
			return;
		}

		var fit = function () {
			view.style.height = slides[i].offsetHeight + 'px';
		};
		var show = function (n) {
			i = (n + slides.length) % slides.length;
			track.style.transform = 'translateX(' + -100 * i + '%)';
			fit();
			slides.forEach(function (s, k) {
				s.setAttribute('aria-hidden', k === i ? 'false' : 'true');
				if ('inert' in s) s.inert = k !== i;
			});
			dots.forEach(function (d, k) {
				if (k === i) d.setAttribute('aria-current', 'true');
				else d.removeAttribute('aria-current');
			});
		};
		var auto = function () {
			clearInterval(timer);
			if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				timer = setInterval(function () { show(i + 1); }, 6000);
			}
		};
		var stop = function () { clearInterval(timer); };

		window.addEventListener('resize', fit);
		arrows[0].addEventListener('click', function () { show(i - 1); auto(); });
		arrows[1].addEventListener('click', function () { show(i + 1); auto(); });
		dots.forEach(function (d, k) {
			d.addEventListener('click', function () { show(k); auto(); });
		});
		sl.addEventListener('mouseenter', stop);
		sl.addEventListener('mouseleave', auto);
		sl.addEventListener('focusin', stop);

		var x0 = null;
		sl.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
		sl.addEventListener('touchend', function (e) {
			if (x0 === null) return;
			var dx = e.changedTouches[0].clientX - x0;
			if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1));
			x0 = null;
			auto();
		});

		show(0);
		auto();
	});
})();
