// Email validation regex
const emailRegex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i;

// IC number validation regex (Malaysian format)
const icRegex = /^\d{6}-\d{2}-\d{4}$/;

// Phone number validation regex (Malaysian format)
const phoneRegex = /^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/;

function validateEmail(email) {
    if (!email) return 'Email is required';
    if (!emailRegex.test(email)) return 'Invalid email address';
    return null;
}

function validatePassword(password) {
    if (!password) return 'Password is required';
    if (password.length < 8) return 'Password must be at least 8 characters';
    return null;
}

function validateIC(ic) {
    if (!ic) return 'IC number is required';
    if (!icRegex.test(ic)) return 'Invalid IC format (e.g., 900101-12-3456)';
    return null;
}

function validatePhone(phone) {
    if (!phone) return 'Phone number is required';
    if (!phoneRegex.test(phone)) return 'Invalid phone number format';
    return null;
}

function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') return `${fieldName} is required`;
    return null;
}