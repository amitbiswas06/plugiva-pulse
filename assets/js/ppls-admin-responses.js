document.addEventListener('DOMContentLoaded', () => {

	const form = document.querySelector('#ppls-responses-form');
	if (!form) {
		return;
	}

	form.addEventListener('submit', (event) => {

		const actionSelect = form.querySelector('select[name="action"]');
		if (!actionSelect || actionSelect.value !== 'delete') {
			return;
		}

		const checkedItems = form.querySelectorAll(
			'input[name="response_ids[]"]:checked'
		);

		// No items selected → do nothing
		if (checkedItems.length === 0) {
			return;
		}

		const confirmed = window.confirm(
			PPLS.i18n.confirmDelete
		);

		if (!confirmed) {
			event.preventDefault();
		}
	});
});
