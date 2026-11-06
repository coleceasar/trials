document.addEventListener('DOMContentLoaded', function () {
    const managerSelect = document.getElementById('manager_id');
    const propertySelect = document.getElementById('property_select');
    const branchTableBody = document.getElementById('branch_table_body');

    // 1. Manager selection triggers property fetch
    managerSelect.addEventListener('change', function () {
        const managerId = this.value;
        propertySelect.innerHTML = '<option value="">-- Loading Properties... --</option>';
        propertySelect.disabled = true;
        branchTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Select a property above to load branches</td></tr>';

        if (!managerId) {
            propertySelect.innerHTML = '<option value="">-- Select Manager First --</option>';
            return;
        }

        fetch(`?get_unassigned_properties=1`)
            .then(res => res.json())
            .then(data => {
                propertySelect.innerHTML = '<option value="">-- Select a Property --</option>';
                if (!Array.isArray(data) || data.length === 0) {
                    propertySelect.innerHTML = '<option value="">Create a property first</option>';
                    return;
                }
                data.forEach(prop => {
                    const opt = document.createElement('option');
                    opt.value = prop.property_id;
                    opt.textContent = prop.property_name;
                    propertySelect.appendChild(opt);
                });
                propertySelect.disabled = false;
            })
            .catch(err => {
                console.error(err);
                propertySelect.innerHTML = '<option value="">Error loading properties</option>';
            });
    });

    // 2. Property selection triggers branch fetch
    propertySelect.addEventListener('change', function () {
        const propertyId = this.value;

        if (!propertyId) {
            branchTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Select a property above to load branches</td></tr>';
            return;
        }

        branchTableBody.innerHTML = '<tr><td colspan="2" class="text-center">Loading branches...</td></tr>';

        fetch(`?get_branches=1&property_id=${propertyId}`)
            .then(res => res.json())
            .then(data => {
                branchTableBody.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    branchTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No branches found for this property.</td></tr>';
                    return;
                }

                data.forEach(branch => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${branch.branch_name}</td>
                        <td>${branch.location || 'N/A'}</td>
                    `;
                    branchTableBody.appendChild(row);
                });
            })
            .catch(err => {
                console.error('Error fetching branches:', err);
                branchTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error loading branches</td></tr>';
            });
    });
});