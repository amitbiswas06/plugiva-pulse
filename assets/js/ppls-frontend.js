(function () {
	'use strict';

    /** Initialize pulse form */

	const initPulse = (form) => {
		if (form.dataset.pplsBound === '1') {
			return;
		}
		form.dataset.pplsBound = '1';

		form.addEventListener('submit', (event) => {
			event.preventDefault();
			submitPulse(form);
		});
	};

    // Submit pulse form via AJAX
	const submitPulse = (form) => {
        const data = new FormData(form);
        const url  = form.getAttribute('action');

        const submitBtn = form.querySelector('[type="submit"]');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.dataset.originalText = submitBtn.textContent;
            submitBtn.textContent = `${PPLS.i18n.submitting}`;
        }

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data,
        })
            .then((response) => response.json())
            .then((result) => {
                if (result && result.success) {
                    showSuccess(form);
                } else {
                    restoreSubmit(form);
                    showError(form);
                }
            })
            .catch(() => {
                restoreSubmit(form);
                showError(form);
            });
    };

    // Restore submit button state
    const restoreSubmit = (form) => {
        const submitBtn = form.querySelector('[type="submit"]');

        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = false;

        if (submitBtn.dataset.originalText) {
            submitBtn.textContent = submitBtn.dataset.originalText;
            delete submitBtn.dataset.originalText;
        }
    };

    // Show success message
	const showSuccess = (form) => {
		const wrapper = form.closest('.ppls-pulse');
		if (!wrapper) {
			return;
		}

		wrapper.innerHTML = `
			<div class="ppls-success">
				<p>${PPLS.i18n.thank_you}</p>
			</div>
		`;
	};

    // Show error message
	const showError = (form) => {
		if (form.querySelector('.ppls-error')) {
			return;
		}

		const error = document.createElement('div');
		error.className = 'ppls-error';
		error.innerHTML = `<p>${PPLS.i18n.error}</p>`;

		form.appendChild(error);
	};

    // Initialize all pulse forms on the page
	const initAll = () => {
		document
			.querySelectorAll('.ppls-pulse-form')
			.forEach(initPulse);
	};

    // Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})();
