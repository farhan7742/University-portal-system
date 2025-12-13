// ===============================
// DASHBOARD AUTH PROTECTION
// ===============================
if (localStorage.getItem('isLoggedIn') !== 'true') {
    window.location.href = 'uniportal.html';
}
// Dashboard protection
if (localStorage.getItem('isLoggedIn') !== 'true') {
    window.location.href = 'uniportal.html';
}

// Logout
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        localStorage.removeItem('isLoggedIn');
        window.location.href = 'uniportal.html';
    });
}

// ===============================
// Scroll to top functionality
// ===============================
const scrollToTopBtn = document.getElementById('scrollToTop');

if (scrollToTopBtn) {
    window.addEventListener('scroll', () => {
        scrollToTopBtn.style.display = window.pageYOffset > 300 ? 'block' : 'none';
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// ===============================
// Mobile sidebar toggle
// ===============================
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener('click', e => {
        if (
            window.innerWidth <= 768 &&
            !sidebar.contains(e.target) &&
            !sidebarToggle.contains(e.target) &&
            sidebar.classList.contains('active')
        ) {
            sidebar.classList.remove('active');
        }
    });
}

// ===============================
// Quick navigation
// ===============================
document.querySelectorAll('.quick-nav a').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const target = document.getElementById(link.getAttribute('href').substring(1));
        target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// ===============================
// Animated counters
// ===============================
function animateCounter(elementId, targetValue, duration = 2000) {
    const element = document.getElementById(elementId);
    if (!element) return;

    let startValue = 0;
    const increment = targetValue / (duration / 16);

    const update = () => {
        startValue += increment;
        if (startValue < targetValue) {
            element.textContent = Math.floor(startValue);
            requestAnimationFrame(update);
        } else {
            element.textContent = targetValue;
        }
    };
    update();
}

window.addEventListener('load', () => {
    animateCounter('userCount', 1247);
    animateCounter('courseCount', 86);
    animateCounter('deptCount', 12);
    animateCounter('attendanceCount', 5489);
});

// ===============================
// Admin section visibility
// ===============================
const isAdmin = false; // replace with backend value later
const adminSection = document.getElementById('admin-section');
if (isAdmin && adminSection) {
    adminSection.style.display = 'block';
}

// ===============================
// Sidebar active link
// ===============================
const currentPage = window.location.pathname.split('/').pop();
document.querySelectorAll('.sidebar a').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
    }
});

