document.addEventListener('DOMContentLoaded', function () {
    const propertySelect = document.getElementById('property_id');
    const branchSelect   = document.getElementById('branch_id');
    const blockSelect    = document.getElementById('block_id');
    const unitSelect     = document.getElementById('unit_id');
    const unitTypeInput  = document.getElementById('unit_type');

    // 1. When Property changes -> Filter both Branch and Block options
    function handlePropertyChange() {
        const selectedPropertyId = propertySelect ? propertySelect.value : '';

        // Filter Branches (linked to Property)
        if (branchSelect) {
            let hasBranches = false;
            for (let option of branchSelect.options) {
                if (option.value === "") continue;
                const optionPropertyId = option.getAttribute('data-property-id');

                if (optionPropertyId === selectedPropertyId) {
                    option.style.display = "";
                    hasBranches = true;
                } else {
                    option.style.display = "none";
                }
            }
            branchSelect.value = "";
            branchSelect.disabled = !selectedPropertyId || !hasBranches;
        }

        // Filter Blocks (linked to Property)
        if (blockSelect) {
            let hasBlocks = false;
            for (let option of blockSelect.options) {
                if (option.value === "") continue;
                const optionPropertyId = option.getAttribute('data-property-id');

                if (optionPropertyId === selectedPropertyId) {
                    option.style.display = "";
                    hasBlocks = true;
                } else {
                    option.style.display = "none";
                }
            }
            blockSelect.value = "";
            blockSelect.disabled = !selectedPropertyId || !hasBlocks;
        }

        // Reset downstream units
        filterUnitsByBlock();
    }

    // 2. When Block changes -> Filter Units (linked to Block)
    function filterUnitsByBlock() {
        const selectedBlockId = blockSelect ? blockSelect.value : '';

        if (unitSelect) {
            let hasUnits = false;
            for (let option of unitSelect.options) {
                if (option.value === "") continue;
                const optionBlockId = option.getAttribute('data-block-id');

                if (optionBlockId === selectedBlockId) {
                    option.style.display = "";
                    hasUnits = true;
                } else {
                    option.style.display = "none";
                }
            }
            unitSelect.value = "";
            unitSelect.disabled = !selectedBlockId || !hasUnits;
        }

        displayUnitType();
    }

    // 3. Auto-populate Unit Type
    function displayUnitType() {
        if (!unitSelect || !unitTypeInput) return;

        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        if (selectedOption && selectedOption.value !== "") {
            const unitType = selectedOption.getAttribute('data-unit-type');
            unitTypeInput.value = unitType ? unitType : 'N/A';
        } else {
            unitTypeInput.value = "";
        }
    }

    // Event Listeners
    if (propertySelect) propertySelect.addEventListener('change', handlePropertyChange);
    if (blockSelect)    blockSelect.addEventListener('change', filterUnitsByBlock);
    if (unitSelect)     unitSelect.addEventListener('change', displayUnitType);
});