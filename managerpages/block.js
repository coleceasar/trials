document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('blocks-container');
    const addMoreBtn = document.getElementById('add-more-btn');

    // Enable/Disable remove buttons depending on row count
    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.block-row');
        rows.forEach(row => {
            const removeBtn = row.querySelector('.remove-row');
            removeBtn.disabled = (rows.length === 1);
        });
    }

    // Dynamic Row Creation
    addMoreBtn.addEventListener('click', () => {
        const newRow = document.createElement('div');
        newRow.className = 'row align-items-end mb-3 block-row';
        newRow.innerHTML = `
            <div class="col-md-6">
                <label class="form-label fw-bold">Block Name / Letter *</label>
                <input type="text" name="block_name[]" class="form-control" placeholder="e.g. Block B" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Number of Floors *</label>
                <input type="number" name="number_of_floors[]" class="form-control" placeholder="e.g. 5" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">Remove</button>
            </div>
        `;
        container.appendChild(newRow);
        updateRemoveButtons();
    });

    // Dynamic Row Removal
    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-row')) {
            const row = e.target.closest('.block-row');
            if (container.querySelectorAll('.block-row').length > 1) {
                row.remove();
                updateRemoveButtons();
            }
        }
    });

    updateRemoveButtons();
});