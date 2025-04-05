
document.getElementById('student-signin').addEventListener('click', function () {
    if (validateForm()) {
        window.location.href = 'student.html'; // Redirect to student.html
    }
});

document.getElementById('staff-signin').addEventListener('click', function () {
    if (validateForm()) {
        window.location.href = 'staff.html'; // Redirect to staff.html
    }
});

// Function to validate the form
function validateForm() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    document.getElementById('email-error').textContent = '';
    document.getElementById('password-error').textContent = '';

    // Regex patterns
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email validation
    const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/; // Password requirements

    // Validate email
    if (!emailRegex.test(email)) {
        document.getElementById('email-error').textContent = 'Please enter a valid email address.';
        return false;
    }

    // Validate password
    if (!passwordRegex.test(password)) {
        document.getElementById('password-error').textContent = 'Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, a number, and a special character.';
        return false;
    }
    return true;
}