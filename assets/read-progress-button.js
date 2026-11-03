/* Read Progress Button. Copyright (c) 2026 Stijn Vogels. All rights reserved. */
(function () {
	var button = document.querySelector('.rpb');
	var content = document.querySelector('.entry-content');
	if (!button || !content) {
		return;
	}
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

	// Read progress: how much of the post's text has come into view. The button shows once the reader
	// has scrolled 50 px and stays on screen while they scroll.
	function update() {
		waiting = false;
		placeBelowAdminBar();
		var box = content.getBoundingClientRect();
		var read = Math.min(1, Math.max(0, (window.innerHeight - box.top) / box.height));
		var done = read >= 1;

		button.classList.toggle('rpb--visible', window.scrollY > 50);
		if (done !== button.classList.contains('rpb--done')) {
			button.classList.toggle('rpb--done', done);
			icon.setAttribute('d', done ? check : cross);
		}
		progress.style.strokeDashoffset = length * (1 - read);
	}

	function schedule() {
		if (!waiting) {
			waiting = true;
			window.requestAnimationFrame(update);
		}
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
