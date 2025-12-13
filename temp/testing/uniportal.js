// UniPortal Authentication JavaScript
document.addEventListener('DOMContentLoaded', function () {
    console.log('UniPortal Authentication loaded');

    // DOM Elements
    const loginToggle = document.getElementById('loginToggle');
    const signupToggle = document.getElementById('signupToggle');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const switchToSignup = document.getElementById('switchToSignup');
    const switchToLogin = document.getElementById('switchToLogin');
    const loginFormElement = document.getElementById('loginFormElement');
    const signupFormElement = document.getElementById('signupFormElement');
    const successModal = document.getElementById('successModal');
    const goToLogin = document.getElementById('goToLogin');

    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const toggleSignupPassword = document.getElementById('toggleSignupPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    if (!loginToggle || !signupToggle || !loginForm || !signupForm) {
        console.error('Required DOM elements not found');
        return;
    }

    function toggleAuthForm(formToShow) {
        loginToggle.classList.toggle('active', formToShow === 'login');
        signupToggle.classList.toggle('active', formToShow === 'signup');
        loginForm.classList.toggle('active', formToShow === 'login');
        signupForm.classList.toggle('active', formToShow === 'signup');
        clearErrors();
        formToShow === 'login' ? loginFormElement.reset() : signupFormElement.reset();
    }

    loginToggle.addEventListener('click', e => {
        e.preventDefault();
        toggleAuthForm('login');
    });

    signupToggle.addEventListener('click', e => {
        e.preventDefault();
        toggleAuthForm('signup');
    });

    switchToSignup?.addEventListener('click', e => {
        e.preventDefault();
        toggleAuthForm('signup');
    });

    switchToLogin?.addEventListener('click', e => {
        e.preventDefault();
        toggleAuthForm('login');
    });

    function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId);
        if (!input || !icon) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    }

    toggleLoginPassword?.addEventListener('click', () =>
        togglePasswordVisibility('loginPassword', toggleLoginPassword)
    );
    toggleSignupPassword?.addEventListener('click', () =>
        togglePasswordVisibility('signupPassword', toggleSignupPassword)
    );
    toggleConfirmPassword?.addEventListener('click', () =>
        togglePasswordVisibility('confirmPassword', toggleConfirmPassword)
    );

    function validateLoginForm() {
        clearErrors();
        let valid = true;
        if (!loginId.value.trim()) {
            showError('loginId', 'Student ID or Email is required');
            valid = false;
        }
        if (!loginPassword.value.trim()) {
            showError('loginPassword', 'Password is required');
            valid = false;
        }
        return valid;
    }

    function validateSignupForm() {
        clearErrors();
        let valid = true;
        if (!firstName.value.trim()) showError('firstName', 'First name is required'), valid = false;
        if (!lastName.value.trim()) showError('lastName', 'Last name is required'), valid = false;
        if (!isValidEmail(signupEmail.value)) showError('signupEmail', 'Invalid email'), valid = false;
        if (!signupId.value.trim()) showError('signupId', 'Student ID required'), valid = false;
        if (signupPassword.value.length < 8) showError('signupPassword', 'Min 8 characters'), valid = false;
        if (signupPassword.value !== confirmPassword.value)
            showError('confirmPassword', 'Passwords do not match'), valid = false;
        return valid;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showError(id, msg) {
        const input = document.getElementById(id);
        const group = input?.closest('.form-group');
        if (!group) return;
        input.style.borderColor = '#ff6b6b';
        const err = document.createElement('div');
        err.className = 'error-message';
        err.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
        group.appendChild(err);
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(e => e.remove());
        document.querySelectorAll('.form-group input').forEach(i => (i.style.borderColor = ''));
    }

    loginFormElement?.addEventListener('submit', async e => {
        e.preventDefault();
        if (!validateLoginForm()) return;

        const btn = loginFormElement.querySelector('.auth-btn');
        btn.disabled = true;

        try {
            const res = await fetch('login.php', { method: 'POST', body: new FormData(loginFormElement) });
            const data = await res.json();

            if (data.success) {
                localStorage.setItem('isLoggedIn', 'true');   
                window.location.href = 'index.html';         
            } else {
                showError('loginPassword', data.message || 'Login failed');
            }
        } catch {
            showError('loginPassword', 'Server error');
        } finally {
            btn.disabled = false;
        }
    });

    signupFormElement?.addEventListener('submit', async e => {
        e.preventDefault();
        if (!validateSignupForm()) return;

        const btn = signupFormElement.querySelector('.auth-btn');
        btn.disabled = true;

        try {
            const res = await fetch('signup.php', { method: 'POST', body: new FormData(signupFormElement) });
            const data = await res.json();

            if (data.success) {
                localStorage.setItem('isLoggedIn', 'true');   
                successModal.style.display = 'flex';
                setTimeout(() => (window.location.href = 'index.html'), 3000); 
            } else {
                showError('signupPassword', data.message || 'Signup failed');
            }
        } catch {
            showError('signupPassword', 'Server error');
        } finally {
            btn.disabled = false;
        }
    });

    goToLogin?.addEventListener('click', () => {
        successModal.style.display = 'none';
        toggleAuthForm('login');
    });

    toggleAuthForm('login');
});
