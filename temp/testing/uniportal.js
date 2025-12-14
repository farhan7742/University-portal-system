// UniPortal Authentication JavaScript

// Wait for DOM to be fully loaded before executing script
document.addEventListener('DOMContentLoaded', function () {
    // Log initialization message for debugging
    console.log('UniPortal Authentication loaded');

    // DOM Elements - get references to all required HTML elements
    const loginToggle = document.getElementById('loginToggle'); // Toggle button for login form
    const signupToggle = document.getElementById('signupToggle'); // Toggle button for signup form
    const loginForm = document.getElementById('loginForm'); // Login form container
    const signupForm = document.getElementById('signupForm'); // Signup form container
    const switchToSignup = document.getElementById('switchToSignup'); // Link to switch to signup
    const switchToLogin = document.getElementById('switchToLogin'); // Link to switch to login
    const loginFormElement = document.getElementById('loginFormElement'); // Actual login form element
    const signupFormElement = document.getElementById('signupFormElement'); // Actual signup form element
    const successModal = document.getElementById('successModal'); // Success modal after signup
    const goToLogin = document.getElementById('goToLogin'); // Button in modal to go to login

    // Password visibility toggle elements
    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const toggleSignupPassword = document.getElementById('toggleSignupPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Validate that essential DOM elements exist before proceeding
    if (!loginToggle || !signupToggle || !loginForm || !signupForm) {
        console.error('Required DOM elements not found'); // Log error if elements missing
        return; // Exit early to prevent errors
    }

    /**
     * Function to toggle between login and signup forms
     * @param {string} formToShow - Which form to show ('login' or 'signup')
     */
    function toggleAuthForm(formToShow) {
        // Toggle 'active' class on login toggle button based on which form to show
        loginToggle.classList.toggle('active', formToShow === 'login');
        // Toggle 'active' class on signup toggle button based on which form to show
        signupToggle.classList.toggle('active', formToShow === 'signup');
        // Toggle 'active' class on login form container
        loginForm.classList.toggle('active', formToShow === 'login');
        // Toggle 'active' class on signup form container
        signupForm.classList.toggle('active', formToShow === 'signup');
        clearErrors(); // Clear any previous error messages
        // Reset the appropriate form based on which one is being shown
        formToShow === 'login' ? loginFormElement.reset() : signupFormElement.reset();
    }

    // Event listener for clicking the login toggle button
    loginToggle.addEventListener('click', e => {
        e.preventDefault(); // Prevent default link behavior
        toggleAuthForm('login'); // Show login form
    });

    // Event listener for clicking the signup toggle button
    signupToggle.addEventListener('click', e => {
        e.preventDefault(); // Prevent default link behavior
        toggleAuthForm('signup'); // Show signup form
    });

    // Event listener for "Switch to Signup" link (if element exists)
    switchToSignup?.addEventListener('click', e => {
        e.preventDefault(); // Prevent default link behavior
        toggleAuthForm('signup'); // Show signup form
    });

    // Event listener for "Switch to Login" link (if element exists)
    switchToLogin?.addEventListener('click', e => {
        e.preventDefault(); // Prevent default link behavior
        toggleAuthForm('login'); // Show login form
    });

    /**
     * Function to toggle password visibility
     * @param {string} inputId - ID of the password input field
     * @param {HTMLElement} icon - The eye icon element
     */
    function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId); // Get password input element
        if (!input || !icon) return; // Exit if input or icon not found
        const isPassword = input.type === 'password'; // Check current input type
        // Toggle between 'text' and 'password' type
        input.type = isPassword ? 'text' : 'password';
        // Toggle eye icon classes to show appropriate state
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    }

    // Event listeners for password visibility toggles (if elements exist)
    toggleLoginPassword?.addEventListener('click', () =>
        togglePasswordVisibility('loginPassword', toggleLoginPassword)
    );
    toggleSignupPassword?.addEventListener('click', () =>
        togglePasswordVisibility('signupPassword', toggleSignupPassword)
    );
    toggleConfirmPassword?.addEventListener('click', () =>
        togglePasswordVisibility('confirmPassword', toggleConfirmPassword)
    );

    /**
     * Validate the login form inputs
     * @returns {boolean} - True if form is valid, false otherwise
     */
    function validateLoginForm() {
        clearErrors(); // Clear previous errors
        let valid = true; // Assume form is valid initially
        
        // Get form input elements (assuming they exist)
        const loginId = document.getElementById('loginId');
        const loginPassword = document.getElementById('loginPassword');
        
        // Validate login ID (student ID or email)
        if (!loginId.value.trim()) {
            showError('loginId', 'Student ID or Email is required');
            valid = false;
        }
        // Validate password
        if (!loginPassword.value.trim()) {
            showError('loginPassword', 'Password is required');
            valid = false;
        }
        return valid; // Return validation result
    }

    /**
     * Validate the signup form inputs
     * @returns {boolean} - True if form is valid, false otherwise
     */
    function validateSignupForm() {
        clearErrors(); // Clear previous errors
        let valid = true; // Assume form is valid initially
        
        // Get form input elements
        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        const signupEmail = document.getElementById('signupEmail');
        const signupId = document.getElementById('signupId');
        const signupPassword = document.getElementById('signupPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        
        // Validate first name
        if (!firstName.value.trim()) {
            showError('firstName', 'First name is required');
            valid = false;
        }
        // Validate last name
        if (!lastName.value.trim()) {
            showError('lastName', 'Last name is required');
            valid = false;
        }
        // Validate email format
        if (!isValidEmail(signupEmail.value)) {
            showError('signupEmail', 'Invalid email');
            valid = false;
        }
        // Validate student ID
        if (!signupId.value.trim()) {
            showError('signupId', 'Student ID required');
            valid = false;
        }
        // Validate password length
        if (signupPassword.value.length < 8) {
            showError('signupPassword', 'Min 8 characters');
            valid = false;
        }
        // Validate password confirmation
        if (signupPassword.value !== confirmPassword.value) {
            showError('confirmPassword', 'Passwords do not match');
            valid = false;
        }
        return valid; // Return validation result
    }

    /**
     * Validate email format using regular expression
     * @param {string} email - Email address to validate
     * @returns {boolean} - True if email is valid, false otherwise
     */
    function isValidEmail(email) {
        // Regular expression for basic email validation
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    /**
     * Display error message for a specific input field
     * @param {string} id - ID of the input element with error
     * @param {string} msg - Error message to display
     */
    function showError(id, msg) {
        const input = document.getElementById(id); // Get input element
        const group = input?.closest('.form-group'); // Find parent form-group
        if (!group) return; // Exit if no form-group found
        
        // Highlight input with red border
        input.style.borderColor = '#ff6b6b';
        
        // Create error message element
        const err = document.createElement('div');
        err.className = 'error-message'; // Apply CSS class
        err.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`; // Add icon and message
        group.appendChild(err); // Append error to form-group
    }

    /**
     * Clear all error messages and input highlights
     */
    function clearErrors() {
        // Remove all error message elements
        document.querySelectorAll('.error-message').forEach(e => e.remove());
        // Reset border color for all form inputs
        document.querySelectorAll('.form-group input').forEach(i => (i.style.borderColor = ''));
    }

    /**
     * Login form submission handler
     */
    loginFormElement?.addEventListener('submit', async e => {
        e.preventDefault(); // Prevent default form submission
        
        // Validate form before submission
        if (!validateLoginForm()) return;
        
        // Disable submit button to prevent multiple submissions
        const btn = loginFormElement.querySelector('.auth-btn');
        btn.disabled = true;
        
        try {
            // Send login request to server
            const res = await fetch('login.php', {
                method: 'POST',
                body: new FormData(loginFormElement) // Send form data
            });
            const data = await res.json(); // Parse JSON response
            
            if (data.success) {
                // Store login state in localStorage
                localStorage.setItem('isLoggedIn', 'true');
                // Redirect to main page on successful login
                window.location.href = 'index.html';
            } else {
                // Display error message from server
                showError('loginPassword', data.message || 'Login failed');
            }
        } catch (error) {
            // Handle network or server errors
            showError('loginPassword', 'Server error');
        } finally {
            // Re-enable submit button
            btn.disabled = false;
        }
    });

    /**
     * Signup form submission handler
     */
    signupFormElement?.addEventListener('submit', async e => {
        e.preventDefault(); // Prevent default form submission
        
        // Validate form before submission
        if (!validateSignupForm()) return;
        
        // Disable submit button to prevent multiple submissions
        const btn = signupFormElement.querySelector('.auth-btn');
        btn.disabled = true;
        
        try {
            // Send signup request to server
            const res = await fetch('signup.php', {
                method: 'POST',
                body: new FormData(signupFormElement) // Send form data
            });
            const data = await res.json(); // Parse JSON response
            
            if (data.success) {
                // Store login state in localStorage
                localStorage.setItem('isLoggedIn', 'true');
                // Show success modal
                successModal.style.display = 'flex';
                // Auto-redirect to main page after 3 seconds
                setTimeout(() => (window.location.href = 'index.html'), 3000);
            } else {
                // Display error message from server
                showError('signupPassword', data.message || 'Signup failed');
            }
        } catch (error) {
            // Handle network or server errors
            showError('signupPassword', 'Server error');
        } finally {
            // Re-enable submit button
            btn.disabled = false;
        }
    });

    /**
     * Event listener for "Go to Login" button in success modal
     */
    goToLogin?.addEventListener('click', () => {
        successModal.style.display = 'none'; // Hide modal
        toggleAuthForm('login'); // Switch to login form
    });

    // Initialize the page with login form visible
    toggleAuthForm('login');
});
