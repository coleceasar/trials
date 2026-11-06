document.addEventListener('DOMContentLoaded', function () {
    // Global data passed from PHP
    const blocksData = window.blocksData || {};

    // Elements
    const propertySelect = document.getElementById('property_id');
    const blockSelect = document.getElementById('block_id');
    const unitTypeSelect = document.getElementById('unit_type');

    // Field Wrappers
    const depositWrapper = document.getElementById('unit_deposit_wrapper');
    const depositInput = document.getElementById('unit_deposit');
    
    const sizeWrapper = document.getElementById('size_wrapper');
    const sizeSelect = document.getElementById('unit_size');

    const roomsWrapper = document.getElementById('rooms_wrapper');
    const roomsInput = document.getElementById('number_of_rooms');

    const resRentWrapper = document.getElementById('residential_rent_wrapper');
    const resRentInput = document.getElementById('residential_rent');

    const sqftChargeWrapper = document.getElementById('sqft_charge_wrapper');
    const sqftChargeInput = document.getElementById('charge_per_sqft');

    const autoRentWrapper = document.getElementById('auto_rent_wrapper');
    const autoRentInput = document.getElementById('monthly_rent');

    // 1. Property -> Block Dynamic Filter
    if (propertySelect && blockSelect) {
        propertySelect.addEventListener('change', function () {
            const propertyId = this.value;
            
            // Reset options
            blockSelect.innerHTML = '<option value="">-- None / Select Block --</option>';

            if (propertyId && blocksData[propertyId]) {
                blockSelect.disabled = false;
                blocksData[propertyId].forEach(function (block) {
                    const option = document.createElement('option');
                    option.value = block.block_id;
                    option.textContent = block.block_name;
                    blockSelect.appendChild(option);
                });
            } else {
                blockSelect.disabled = true;
                blockSelect.innerHTML = '<option value="">-- None / Select Property First --</option>';
            }
        });
    }
    // 2. Unit Type Dynamic Input Display 
    if (unitTypeSelect) {
        unitTypeSelect.addEventListener('change', function () {
            const type = this.value;

            // Reset all dynamic wrappers to hidden
            depositWrapper.classList.add('hidden');
            sizeWrapper.classList.add('hidden');
            roomsWrapper.classList.add('hidden');
            resRentWrapper.classList.add('hidden');
            sqftChargeWrapper.classList.add('hidden');
            autoRentWrapper.classList.add('hidden');

            // Reset required states
            depositInput.required = false;
            sizeSelect.required = false;
            resRentInput.required = false;
            sqftChargeInput.required = false;

            if (type !== '') {
                // Deposit is applicable to all valid unit types
                depositWrapper.classList.remove('hidden');
                depositInput.required = true;

                if (type === 'residential') {
                    roomsWrapper.classList.remove('hidden');
                    resRentWrapper.classList.remove('hidden');
                    resRentInput.required = true;
                } else if (type === 'commercial' || type === 'offices') {
                    sizeWrapper.classList.remove('hidden');
                    sqftChargeWrapper.classList.remove('hidden');
                    autoRentWrapper.classList.remove('hidden');

                    sizeSelect.required = true;
                    sqftChargeInput.required = true;

                    populateSizeOptions(type);
                }
            }
        });
    }
    // 3. Size Dropdown Options Generator
    function populateSizeOptions(type) {
        sizeSelect.innerHTML = '<option value="">-- Select Size (Sq Ft) --</option>';
        
        // Define sizes based on type
        const sizes = type === 'offices' 
            ? [250, 500, 750, 1000, 1500, 2000] 
            : [500, 1000, 1500, 2000, 3000, 5000];

        sizes.forEach(function (sqft) {
            const option = document.createElement('option');
            option.value = sqft;
            option.textContent = `${sqft} Sq Ft`;
            sizeSelect.appendChild(option);
        });
    }

    // 4. Automatic Sq Ft Rent Calculation
    function calculateAutoRent() {
        const size = parseFloat(sizeSelect.value) || 0;
        const charge = parseFloat(sqftChargeInput.value) || 0;
        const total = size * charge;

        autoRentInput.value = total > 0 ? total.toFixed(2) : '';
    }

    if (sizeSelect) sizeSelect.addEventListener('change', calculateAutoRent);
    if (sqftChargeInput) sqftChargeInput.addEventListener('input', calculateAutoRent);
});