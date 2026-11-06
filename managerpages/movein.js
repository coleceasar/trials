document.addEventListener('DOMContentLoaded', function () {
    const tenantSelect = document.getElementById('tenant_id');
    const unitSelect = document.getElementById('unit_id');
    const rentInput = document.getElementById('monthly_rent');
    const depositInput = document.getElementById('tenant_deposit');
    const serviceInput = document.getElementById('service_cost');
    const payableInput = document.getElementById('amount_payable');
    const paidInput = document.getElementById('amount_paid');
    const balanceInput = document.getElementById('balance');
    const phoneInput = document.getElementById('phone_number');

    // Autofill phone number from selected tenant
    if (tenantSelect) {
        tenantSelect.addEventListener('change', function () {
            const selectedTenant = this.options[this.selectedIndex];
            const phone = selectedTenant.getAttribute('data-phone');
            if (phoneInput && phone) {
                phoneInput.value = phone;
            }
        });
    }

    // Auto calculate totals
    function calculateTotals() {
        const rent = parseFloat(rentInput.value) || 0;
        const deposit = parseFloat(depositInput.value) || 0;
        const service = parseFloat(serviceInput.value) || 0;

        const totalPayable = rent + deposit + service;
        payableInput.value = totalPayable.toFixed(2);

        const paid = parseFloat(paidInput.value) || 0;
        const balance = totalPayable - paid;
        balanceInput.value = balance.toFixed(2);
    }

    if (unitSelect) {
        unitSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value !== '') {
                rentInput.value = parseFloat(selectedOption.getAttribute('data-rent') || 0).toFixed(2);
                depositInput.value = parseFloat(selectedOption.getAttribute('data-deposit') || 0).toFixed(2);
                serviceInput.value = parseFloat(selectedOption.getAttribute('data-services') || 0).toFixed(2);
            } else {
                rentInput.value = '0.00';
                depositInput.value = '0.00';
                serviceInput.value = '0.00';
            }
            calculateTotals();
        });
    }

    if (paidInput) {
        paidInput.addEventListener('input', calculateTotals);
    }
});