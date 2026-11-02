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
	var check = 'M8 12l2.5 2.5L16 9';
	var length = 2 * Math.PI * progress.r.baseVal.value;
	var waiting = false;

	progress.style.strokeDasharray = length;

	// Read progress: how much of the post's text has come into view.
	function update() {
		waiting = false;
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
	window.addEventListener('resize', schedule);
	update();
})();
