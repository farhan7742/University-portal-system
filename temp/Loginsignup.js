// Tab switching functionality
const loginTab = document.getElementById('login-tab');
const signupTab = document.getElementById('signup-tab');
const loginForm = document.getElementById('login-form');
const signupForm = document.getElementById('signup-form');
const switchToSignup = document.getElementById('switch-to-signup');
const switchToLogin = document.getElementById('switch-to-login');
const loginSuccess = document.getElementById('login-success');

// Show login form
function showLoginForm() {
    loginTab.classList.add('active');
    signupTab.classList.remove('active');
    loginForm.classList.add('active');
    signupForm.classList.remove('active');
}

// Show signup form
function showSignupForm() {
    signupTab.classList.add('active');
    loginTab.classList.remove('active');
    signupForm.classList.add('active');
    loginForm.classList.remove('active');
}

// Event listeners for tab switching
loginTab.addEventListener('click', showLoginForm);
signupTab.addEventListener('click', showSignupForm);
switchToSignup.addEventListener('click', (e) => {
    e.preventDefault();
    showSignupForm();
});
switchToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginForm();
});

// Role selection functionality
const roleOptions = document.querySelectorAll('.role-option');
const userRoleInput = document.getElementById('user-role');

roleOptions.forEach(option => {
    option.addEventListener('click', () => {
        roleOptions.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        userRoleInput.value = option.getAttribute('data-role');
    });
});

// Password toggle functionality
function setupPasswordToggle(passwordInputId, toggleButtonId) {
    const passwordInput = document.getElementById(passwordInputId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    toggleButton.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleButton.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
}

// Initialize password toggles
setupPasswordToggle('login-password', 'login-password-toggle');
setupPasswordToggle('signup-password', 'signup-password-toggle');
setupPasswordToggle('signup-confirm-password', 'signup-confirm-password-toggle');

// Form validation and submission
const loginFormElement = document.getElementById('loginForm');
const signupFormElement = document.getElementById('signupForm');

// Login form submission
loginFormElement.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    // Simple validation
    let isValid = true;
    
    if (!validateEmail(email)) {
        document.getElementById('login-email').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('login-email').classList.remove('error');
    }
    
    if (password.length < 6) {
        document.getElementById('login-password').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('login-password').classList.remove('error');
    }
    
    if (isValid) {
        // In a real application, you would send this data to a server
        console.log('Login attempt:', { email, password });
        
        // Redirect to dashboard (simulate successful login)
        window.location.href = 'index.html';
    }
});

// Signup form submission
signupFormElement.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const name = document.getElementById('signup-name').value;
    const email = document.getElementById('signup-email').value;
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-confirm-password').value;
    const role = userRoleInput.value;
    const agreeTerms = document.getElementById('agree-terms').checked;
    
    // Simple validation
    let isValid = true;
    
    if (name.trim() === '') {
        document.getElementById('signup-name').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('signup-name').classList.remove('error');
    }
    
    if (!validateEmail(email)) {
        document.getElementById('signup-email').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('signup-email').classList.remove('error');
    }
    
    if (password.length < 6) {
        document.getElementById('signup-password').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('signup-password').classList.remove('error');
    }
    
    if (password !== confirmPassword) {
        document.getElementById('signup-confirm-password').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('signup-confirm-password').classList.remove('error');
    }
    
    if (!agreeTerms) {
        document.getElementById('agree-terms').parentElement.classList.add('error');
        isValid = false;
    } else {
        document.getElementById('agree-terms').parentElement.classList.remove('error');
    }
    
    if (isValid) {
        // In a real application, you would send this data to a server
        console.log('Signup attempt:', { name, email, password, role });
        
        // Show success message and switch to login form
        loginSuccess.style.display = 'block';
        showLoginForm();
        
        // Clear form
        signupFormElement.reset();
        userRoleInput.value = 'student';
        roleOptions.forEach(opt => {
            if (opt.getAttribute('data-role') === 'student') {
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });
    }
});

// Email validation function
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Check if we're coming from a successful registration
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('registered') === 'true') {
    loginSuccess.style.display = 'block';
}

// Forgot password functionality
document.querySelector('.forgot-password').addEventListener('click', (e) => {
    e.preventDefault();
    const email = prompt('Please enter your email address to reset your password:');
    if (email && validateEmail(email)) {
        alert(`Password reset instructions have been sent to ${email}`);
    } else if (email) {
        alert('Please enter a valid email address');
    }
});

// Social login handlers
document.querySelector('.social-btn.google').addEventListener('click', () => {
    alert('Google authentication would be implemented here');
});

document.querySelector('.social-btn.facebook').addEventListener('click', () => {
    alert('Facebook authentication would be implemented here');
});