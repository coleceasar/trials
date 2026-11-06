document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('complaintForm');
    const openModalBtn = document.getElementById('openModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    const modal = document.getElementById('confirmModal');

    if (openModalBtn) {
        openModalBtn.addEventListener('click', function () {
            // Validate form input fields first
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Retrieve form values
            const typeSelect = document.getElementById('complainttype');
            const prioritySelect = document.getElementById('priority');

            // Inject content into modal elements
            document.getElementById('confirmType').textContent = typeSelect.options[typeSelect.selectedIndex].text;
            document.getElementById('confirmPriority').textContent = prioritySelect.options[prioritySelect.selectedIndex].text;
            document.getElementById('confirmSubject').textContent = document.getElementById('subject').value;
            document.getElementById('confirmDescription').textContent = document.getElementById('description').value;

            // Show confirmation popup
            modal.style.display = 'flex';
        });
    }

    // Hide modal on edit/cancel
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    // Submit form after user confirmation
    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function () {
            form.submit();
        });
    }
});