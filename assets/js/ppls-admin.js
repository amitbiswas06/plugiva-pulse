document.addEventListener('DOMContentLoaded', () => {

	/* Responses bulk delete confirm */
	const responsesForm = document.querySelector('#ppls-responses-form');

	if (responsesForm) {
		responsesForm.addEventListener('submit', event => {

			const actionSelect = responsesForm.querySelector('select[name="action"]');
			if (!actionSelect || actionSelect.value !== 'delete') {
				return;
			}

			const checkedItems = responsesForm.querySelectorAll(
				'input[name="response_ids[]"]:checked'
			);

			if (checkedItems.length === 0) {
				return;
			}

			if (!window.confirm(PPLS.i18n.confirmDelete)) {
				event.preventDefault();
			}
		});
	}

	/* Pulse single delete confirm */
	document.querySelectorAll('.ppls-pulse-delete-form').forEach(form => {

		form.addEventListener('submit', event => {

			if (!window.confirm(PPLS.i18n.confirmDelete)) {
				event.preventDefault();
			}
		});
	});
});
