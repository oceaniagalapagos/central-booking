const fileInput = document.getElementById(formCouponStatus.fileInputId);
const codeInput = document.getElementById(formCouponStatus.codeInputId);
const amountInput = document.getElementById(formCouponStatus.amountInputId);
const statusSelect = document.getElementById(formCouponStatus.statusSelectId);
const checkPassengerClass = document.getElementsByClassName(formCouponStatus.checkPassengerClass);

for (const option of statusSelect.options) {
    if (formCouponStatus.statusToRemove.includes(option.value)) {
        option.remove();
    }
}

fileInput.addEventListener('change', function () {
    const fileNameDisplay = document.getElementById('proof_payment_name_display');
    if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = fileInput.files[0].name;
    } else {
        fileNameDisplay.textContent = 'No file selected';
    }
});

function validateFile() {
    if (!formCouponStatus.fileRequiredIn.includes(statusSelect.value)) {
        return true;
    }
    const input = document.getElementById('form-coupon-status').querySelector('input[name="has_previous"]');
    if (input.value === 'true') {
        return true;
    }
    if (fileInput.files.length === 0) {
        return false;
    }
    return true;
}

function validatePartial() {
    if (statusSelect.value === 'partial') {
        for (const check of checkPassengerClass) {
            if (check.checked) {
                return true;
            }
        }
        return false;
    }
    return true;
}
