

// UniPortal Authentication JavaScript
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Password toggle elements
    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const toggleSignupPassword = document.getElementById('toggleSignupPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    
    // Check if elements exist
    if (!loginToggle || !signupToggle || !loginForm || !signupForm) {
        console.error('Required DOM elements not found');
        return;
    }
    
    console.log('All DOM elements found');
    
    // Toggle between login and signup forms
    function toggleAuthForm(formToShow) {
        console.log('Switching to form:', formToShow);
        
        // Update toggle buttons
        loginToggle.classList.toggle('active', formToShow === 'login');
        signupToggle.classList.toggle('active', formToShow === 'signup');
        
        // Update forms visibility
        loginForm.classList.toggle('active', formToShow === 'login');
        signupForm.classList.toggle('active', formToShow === 'signup');
        
        // Clear any existing errors
        clearErrors();
        
        // Reset forms
        if (formToShow === 'login') {
            loginFormElement.reset();
        } else {
            signupFormElement.reset();
        }
    }
    
    // Event listeners for toggle buttons
    loginToggle.addEventListener('click', (e) => {
        e.preventDefault();
        toggleAuthForm('login');
    });
    
    signupToggle.addEventListener('click', (e) => {
        e.preventDefault();
        toggleAuthForm('signup');
    });
    
    if (switchToSignup) {
        switchToSignup.addEventListener('click', (e) => {
            e.preventDefault();
            toggleAuthForm('signup');
        });
    }
    
    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            toggleAuthForm('login');
        });
    }

    // ===============================
// GOOGLE SCRIPT READY CHECK
// ===============================
const googleReady = setInterval(() => {
    if (window.google && google.accounts) {
        clearInterval(googleReady);
        initGoogleAuth();
    }
}, 300);

    
    // Password visibility toggle
    function togglePasswordVisibility(inputId, iconElement) {
        const passwordInput = document.getElementById(inputId);
        if (!passwordInput || !iconElement) return;
        
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        iconElement.classList.toggle('fa-eye', !isPassword);
        iconElement.classList.toggle('fa-eye-slash', isPassword);
    }
    
    if (toggleLoginPassword) {
        toggleLoginPassword.addEventListener('click', () => {
            togglePasswordVisibility('loginPassword', toggleLoginPassword);
        });
    }
    
    if (toggleSignupPassword) {
        toggleSignupPassword.addEventListener('click', () => {
            togglePasswordVisibility('signupPassword', toggleSignupPassword);
        });
    }
    
    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', () => {
            togglePasswordVisibility('confirmPassword', toggleConfirmPassword);
        });
    }
    
    // Form validation
    function validateLoginForm() {
        const login = document.getElementById('loginId').value.trim();
        const password = document.getElementById('loginPassword').value.trim();
        let isValid = true;
        
        clearErrors();
        
        if (!login) {
            showError('loginId', 'Student ID or Email is required');
            isValid = false;
        }
        
        if (!password) {
            showError('loginPassword', 'Password is required');
            isValid = false;
        }
        
        return isValid;
    }
    
    function validateSignupForm() {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const email = document.getElementById('signupEmail').value.trim();
        const studentId = document.getElementById('signupId').value.trim();
        const password = document.getElementById('signupPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();
        
        let isValid = true;
        
        clearErrors();
        
        if (!firstName) {
            showError('firstName', 'First name is required');
            isValid = false;
        }
        
        if (!lastName) {
            showError('lastName', 'Last name is required');
            isValid = false;
        }
        
        if (!email) {
            showError('signupEmail', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('signupEmail', 'Please enter a valid email address');
            isValid = false;
        }
        
        if (!studentId) {
            showError('signupId', 'Student ID is required');
            isValid = false;
        }
        
        if (!password) {
            showError('signupPassword', 'Password is required');
            isValid = false;
        } else if (password.length < 8) {
            showError('signupPassword', 'Password must be at least 8 characters long');
            isValid = false;
        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
            showError('signupPassword', 'Password must contain uppercase, lowercase, and numbers');
            isValid = false;
        }
        
        if (!confirmPassword) {
            showError('confirmPassword', 'Please confirm your password');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('confirmPassword', 'Passwords do not match');
            isValid = false;
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showError(inputId, message) {
        const inputElement = document.getElementById(inputId);
        if (!inputElement) return;
        
        const formGroup = inputElement.closest('.form-group');
        if (!formGroup) return;
        
        // Remove any existing error
        const existingError = formGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error styling to input
        inputElement.style.borderColor = '#ff6b6b';
        
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        formGroup.appendChild(errorElement);
    }
    
    function clearErrors() {
        // Remove all error messages
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        // Reset input borders
        document.querySelectorAll('.form-group input').forEach(input => {
            input.style.borderColor = 'rgba(255, 255, 255, 0.2)';
        });
    }
    
    // Form submission handlers
    if (loginFormElement) {
        loginFormElement.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Login form submitted');
            
            if (!validateLoginForm()) return;
            
            // Show loading state
            const submitBtn = loginFormElement.querySelector('.auth-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
            
            // Get form data
            const formData = new FormData(loginFormElement);
            
            // For debugging - log what data is being sent
            console.log('Login form data:', Object.fromEntries(formData));
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Log response status
                console.log('Response status:', response.status, response.statusText);
                
                // Try to get response text first for debugging
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Failed to parse JSON:', parseError);
                    throw new Error('Server returned invalid JSON');
                }
                
                if (result.success) {
                    // Redirect to dashboard
                    console.log('Login successful, redirecting to:', result.data?.redirect || 'dashboard.php');
                    window.location.href = result.data?.redirect || 'dashboard.php';
                } else {
                    console.log('Login failed:', result.message);
                    showError('loginPassword', result.message || 'Login failed');
                }
            } catch (error) {
                console.error('Login fetch error:', error);
                console.error('Error details:', error.message);
                
                // Show more specific error message
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    showError('loginPassword', 'Cannot connect to server. Make sure PHP is running.');
                } else if (error.message.includes('JSON')) {
                    showError('loginPassword', 'Server returned invalid response. Check PHP errors.');
                } else {
                    showError('loginPassword', 'Network error. Please check console for details.');
                }
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    if (signupFormElement) {
        signupFormElement.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Signup form submitted');
            
            if (!validateSignupForm()) return;
            
            // Check terms agreement
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                alert('Please agree to the Terms of Service and Privacy Policy');
                return;
            }
            
            // Show loading state
            const submitBtn = signupFormElement.querySelector('.auth-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            submitBtn.disabled = true;
            
            // Get form data
            const formData = new FormData(signupFormElement);
            
            // For debugging - log what data is being sent
            console.log('Signup form data:', Object.fromEntries(formData));
            
            try {
                const response = await fetch('signup.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Log response status
                console.log('Response status:', response.status, response.statusText);
                
                // Try to get response text first for debugging
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Failed to parse JSON:', parseError);
                    throw new Error('Server returned invalid JSON');
                }
                
                if (result.success) {
                    // Show success modal
                    if (successModal) {
                        successModal.style.display = 'flex';
                    }
                    
                    // Auto-redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = result.data?.redirect || 'dashboard.php';
                    }, 3000);
                } else {
                    console.log('Signup failed:', result.message);
                    showError('signupPassword', result.message || 'Signup failed');
                }
            } catch (error) {
                console.error('Signup fetch error:', error);
                console.error('Error details:', error.message);
                
                // Show more specific error message
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    showError('signupPassword', 'Cannot connect to server. Make sure PHP is running.');
                } else if (error.message.includes('JSON')) {
                    showError('signupPassword', 'Server returned invalid response. Check PHP errors.');
                } else {
                    showError('signupPassword', 'Network error. Please check console for details.');
                }
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    // Modal functionality
    if (goToLogin) {
        goToLogin.addEventListener('click', function() {
            if (successModal) {
                successModal.style.display = 'none';
            }
            toggleAuthForm('login');
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === successModal) {
            successModal.style.display = 'none';
        }
    });
    
    // Facebook authentication button
    const facebookBtn = document.querySelector('.social-btn.facebook');
    if (facebookBtn) {
        facebookBtn.addEventListener('click', function() {
            console.log('Facebook authentication clicked');
            alert('In a real implementation, this would redirect to Facebook OAuth login');
        });
    }
    
    // Forgot password link
    const forgotPassword = document.querySelector('.forgot-password');
    if (forgotPassword) {
        forgotPassword.addEventListener('click', function(e) {
            e.preventDefault();
            const email = prompt('Please enter your registered email address:');
            if (email && isValidEmail(email)) {
                // Send password reset request
                alert(`Password reset link would be sent to ${email}`);
            }
        });
    }
    
    // Initialize with login form active
    toggleAuthForm('login');
});