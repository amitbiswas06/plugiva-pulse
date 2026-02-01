document.addEventListener('DOMContentLoaded', () => {

	const form = document.querySelector('#ppls-responses-form');
	if (!form) {
		return;
	}

	form.addEventListener('submit', (event) => {

		const actionSelect = form.querySelector('select[name="action"]');
		if (!actionSelect) {
			return;
		}

		if (actionSelect.value !== 'delete') {
			return;
		}

		const confirmed = window.confirm(
			pplsResponses.confirmDelete
		);

		if (!confirmed) {
			event.preventDefault();
		}
	});
});
