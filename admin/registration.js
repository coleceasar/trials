function updateBlocks() {
    const propertyId = document.getElementById('property_id').value;
    const blockSelect = document.getElementById('block_id');
    const unitSelect = document.getElementById('unit_id');
    const managerDisplay = document.getElementById('assigned_manager');

    blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
    unitSelect.innerHTML = '<option value="">-- Select Available Unit --</option>';
    blockSelect.disabled = true;
    unitSelect.disabled = true;
    document.getElementById('unit_details_section').style.display = 'none';
    managerDisplay.value = managersByProperty[propertyId] || '';

    if (!propertyId) return;

    const blocks = blocksByProperty[propertyId] || [];
    if (blocks.length) {
        blocks.forEach(function(block) {
            const option = document.createElement('option');
            option.value = block.id;
            option.textContent = block.name;
            blockSelect.appendChild(option);
        });
        blockSelect.disabled = false;
    }
}

function updateUnits() {
    const blockId = document.getElementById('block_id').value;
    const unitSelect = document.getElementById('unit_id');

    unitSelect.innerHTML = '<option value="">-- Select Available Unit --</option>';
    unitSelect.disabled = true;
    document.getElementById('unit_details_section').style.display = 'none';

    if (!blockId) return;

    const units = unitsByBlock[blockId] || [];
    if (units.length) {
        units.forEach(function(unit) {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.label;
            unitSelect.appendChild(option);
        });
        unitSelect.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const unitSelect = document.getElementById('unit_id');
    const propertySelect = document.getElementById('property_id');

    if (unitSelect) {
        unitSelect.addEventListener('change', function() {
            const unitId = this.value;
            const detailsSection = document.getElementById('unit_details_section');

            if (!unitId) {
                detailsSection.style.display = 'none';
                return;
            }

            fetch('../api/get_unit_details.php?unit_id=' + encodeURIComponent(unitId))
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('unit_type_display').textContent = data.unit_details;
                        document.getElementById('charge_per_sqft_display').textContent = 'Ksh ' + data.charge_per_sqft;
                        document.getElementById('monthly_rent_display').textContent = 'Ksh ' + Number(data.calculated_rent).toLocaleString();
                        detailsSection.style.display = 'block';
                    } else {
                        detailsSection.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    detailsSection.style.display = 'none';
                });
        });
    }

    if (propertySelect && propertySelect.value) {
        updateBlocks();
    }
});