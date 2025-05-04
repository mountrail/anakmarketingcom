
document.addEventListener('DOMContentLoaded', function () {
    // Get references to the phone input fields
    const countryCodeInput = document.getElementById('phone_country_code');
    const phoneNumberInput = document.getElementById('phone_number');

    // Auto-focus to phone number field when country code reaches max length
    countryCodeInput.addEventListener('input', function () {
        if (this.value.length >= 4) {
            phoneNumberInput.focus();
        }
    });

    // Prevent non-numeric input
    [countryCodeInput, phoneNumberInput].forEach(input => {
        input.addEventListener('keypress', function (e) {
            if (!/^\d$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e
                .key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
            }
        });

        // Clean up on paste
        input.addEventListener('paste', function (e) {
            setTimeout(() => {
                this.value = this.value.replace(/[^0-9]/g, '');
            }, 0);
        });
    });
});

