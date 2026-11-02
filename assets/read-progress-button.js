/* Read Progress Button. Copyright (c) 2026 Stijn Vogels. All rights reserved. */
(function () {
	var button = document.querySelector('.rpb');
	var content = document.querySelector('.entry-content');
	if (!button || !content) {
		return;
	}
	var header = document.querySelector('.site-header');
	var switcher = document.querySelector('.deluminator'); // Deluminator's light/dark switch, when it's there.
	var progress = button.querySelector('.rpb__progress');
	var icon = button.querySelector('.rpb__icon');
	var cross = icon.getAttribute('d');
	var check = 'M7 12.5l3.2 3.2L17 9';
	var length = 2 * Math.PI * progress.r.baseVal.value;
	var waiting = false;

	progress.style.strokeDasharray = length;

	// Clear the admin bar while it's on screen (on phones it scrolls away with the page).
	function placeBelowAdminBar() {
		var bar = document.getElementById('wpadminbar');
		button.style.setProperty('--rpb-top', (bar ? Math.max(0, Math.round(bar.getBoundingClientRect().bottom)) : 0) + 'px');
	}

	// Bottom corners: stay above a menu fixed to the bottom of the screen, like WP Mobile Bottom Menu.
	function placeAboveBottomMenu() {
		var lift = 0;
		for (var el = document.elementFromPoint(window.innerWidth / 2, window.innerHeight - 2); el && el !== document.body; el = el.parentElement) {
			if ('fixed' === window.getComputedStyle(el).position) {
				lift = Math.max(0, Math.round(window.innerHeight - el.getBoundingClientRect().top));
				break;
			}
		}
		button.style.setProperty('--rpb-bottom', lift + 'px');
	}

	// Slide away and back with Deluminator's switch, which does so only where it would cover the text
	// (phones). At the end of the post the check mark stays.
	function followSwitch() {
		var tucked = !!switcher && switcher.classList.contains('is-tucked') && !button.classList.contains('rpb--done');
		button.classList.toggle('rpb--tucked', tucked);
	}

	// Read progress: how much of the post's text has come into view.
	function update() {
		waiting = false;
		placeBelowAdminBar();
		var box = content.getBoundingClientRect();
		var read = Math.min(1, Math.max(0, (window.innerHeight - box.top) / box.height));
		var done = read >= 1;
		var past = window.scrollY > 50 && (!header || header.getBoundingClientRect().bottom < button.getBoundingClientRect().bottom);

		button.classList.toggle('rpb--visible', past);
		if (done !== button.classList.contains('rpb--done')) {
			button.classList.toggle('rpb--done', done);
			icon.setAttribute('d', done ? check : cross);
			followSwitch();
		}
		progress.style.strokeDashoffset = length * (1 - read);
	}

	function schedule() {
		if (!waiting) {
			waiting = true;
			window.requestAnimationFrame(update);
		}
	}

	if (switcher && window.MutationObserver) {
		new MutationObserver(followSwitch).observe(switcher, { attributes: true, attributeFilter: ['class'] });
	}
	window.addEventListener('scroll', schedule, { passive: true });
	window.addEventListener('resize', function () {
		placeAboveBottomMenu();
		schedule();
	});
	window.addEventListener('load', placeAboveBottomMenu);
	placeAboveBottomMenu();
	update();
})();
