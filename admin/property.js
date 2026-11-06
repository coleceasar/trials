document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('branch-container');
    const addBtn = document.getElementById('add-branch-btn');

    // Add dynamic branch rows
    addBtn.addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-3 align-items-end branch-row';
        newRow.innerHTML = `
            <div class="col-md-6">
                <input type="text" maxlength="20" name="branch_name[]" class="form-control" placeholder="Branch Name" required>
            </div>
            <div class="col-md-5">
                <input type="text" maxlength="20" name="branch_location[]" class="form-control" placeholder="Location">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger remove-branch-btn w-100">&times;</button>
            </div>
        `;
        container.appendChild(newRow);
        updateRemoveButtons();
    });

    // Remove branch row
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-branch-btn')) {
            const row = e.target.closest('.branch-row');
            if (container.querySelectorAll('.branch-row').length > 1) {
                row.remove();
                updateRemoveButtons();
            }
        }
    });

    // Disable remove button if only one row remains
    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.branch-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-branch-btn');
            btn.disabled = rows.length === 1;
        });
    }
});