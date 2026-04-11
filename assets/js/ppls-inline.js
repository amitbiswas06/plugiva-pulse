(function () {
	'use strict';

	/**
	 * Initialize existing state (on page load)
	 */
	document.addEventListener('DOMContentLoaded', function () {

		document.querySelectorAll('.ppls-inline-question').forEach(wrapper => {

			const qid  = wrapper.dataset.qid;
			const hash = wrapper.dataset.hash;

			if (!qid || !hash) return;

			const key = 'ppls_' + qid + '_' + hash;

			if (localStorage.getItem(key)) {
				wrapper.style.display = 'none';
			}
		});
	});


	/**
	 * Handle click
	 */
	document.addEventListener('click', function (e) {

		const btn = e.target.closest('.ppls-option-btn');
		if (!btn) return;

		const wrapper = btn.closest('.ppls-inline-question');
		if (!wrapper) return;

		if (wrapper.classList.contains('is-submitting') || wrapper.classList.contains('ppls-done')) {
			return;
		}

		const buttons = wrapper.querySelectorAll('.ppls-option-btn');

		// --- Selection ---
		buttons.forEach(b => b.classList.remove('is-selected'));
		btn.classList.add('is-selected');

		const q_type   = wrapper.dataset.qtype || 'yesno';
		const qid      = wrapper.dataset.qid;
		const post_id  = wrapper.dataset.post;
		const hash     = wrapper.dataset.hash;
		const started  = wrapper.dataset.started;
		const answer   = btn.dataset.value;
		const question = wrapper.querySelector('.ppls-q-text')?.textContent || '';
		const nonce    = wrapper.querySelector('[name="ppls_nonce"]')?.value || '';
		const url      = wrapper.dataset.ajax;

		if (!answer || !url) return;

		wrapper.classList.add('is-submitting', 'is-loading');

		buttons.forEach(b => b.disabled = true);

		const data = new FormData();
		data.append('action', 'ppls_submit_pulse');
		data.append('type', 'question');
		data.append('qid', qid);
		data.append('question', question);
		data.append('answer', answer);
		data.append('q_type', q_type);
		data.append('post_id', post_id);

		data.append('meta[hash]', hash);
		data.append('meta[started_at]', started);
		data.append('meta[ppls_hp]', '');
		data.append('ppls_nonce', nonce);

		fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		})
		.then(res => res.json())
		.then(res => {

			if (!res.success) {
				throw new Error(res.data?.message || 'Request failed');
			}

			// --- SUCCESS ---
			const key = 'ppls_' + qid + '_' + hash;
			localStorage.setItem(key, '1');

			wrapper.classList.remove('is-loading');
			wrapper.classList.add('ppls-done');

			const options = wrapper.querySelector('.ppls-options');
			const feedback = wrapper.querySelector('.ppls-feedback');

			if (options) options.style.display = 'none';

			if (feedback) {
				feedback.hidden = false;
				feedback.classList.add('is-visible');
			}

		})
		.catch((err) => {

			wrapper.classList.remove('is-loading', 'is-submitting');

			buttons.forEach(b => b.disabled = false);

			const feedback = wrapper.querySelector('.ppls-feedback');

			if (feedback) {
				feedback.hidden = false;
				feedback.classList.add('is-visible');
				feedback.textContent = '⚠ Session expired. Please refresh.';
			}

		});

	});

})();