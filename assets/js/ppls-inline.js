document.addEventListener('click', function(e) {

	const btn = e.target.closest('.ppls-inline-question button');
	if (!btn) return;

	const wrapper = btn.closest('.ppls-inline-question');
	if (!wrapper) return;

	if (wrapper.classList.contains('ppls-done')) return;

	const q_type   = wrapper.dataset.qtype || 'yesno';
	const qid      = wrapper.dataset.qid;
	const post_id  = wrapper.dataset.post;
	const hash     = wrapper.dataset.hash;
	const started  = wrapper.dataset.started;
	const answer   = btn.dataset.value;
	const question = wrapper.querySelector('.ppls-q-text')?.textContent || '';
	const nonce    = wrapper.querySelector('[name="ppls_nonce"]')?.value || '';

	if (!answer) return;

	wrapper.classList.add('ppls-done');

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
    const url = wrapper.dataset.ajax;

	fetch(url, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	});
});