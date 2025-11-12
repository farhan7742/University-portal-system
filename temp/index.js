// Scroll to top functionality
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.style.display = 'block';
    } else {
        scrollToTopBtn.style.display = 'none';
    }
});

scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Mobile sidebar toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    }
});

// Quick navigation functionality
const quickNavLinks = document.querySelectorAll('.quick-nav a');

quickNavLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = link.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Animated counter for stats
function animateCounter(elementId, targetValue, duration = 2000) {
    const element = document.getElementById(elementId);
    let startValue = 0;
    const increment = targetValue / (duration / 16);
    
    const updateCounter = () => {
        startValue += increment;
        if (startValue < targetValue) {
            element.textContent = Math.floor(startValue);
            setTimeout(updateCounter, 16);
        } else {
            element.textContent = targetValue;
        }
    };
    
    updateCounter();
}

// Initialize counters when page loads
window.addEventListener('load', () => {
    animateCounter('userCount', 1247);
    animateCounter('courseCount', 86);
    animateCounter('deptCount', 12);
    animateCounter('attendanceCount', 5489);
});

// Check if user is admin and show admin section
// This is a placeholder - in a real app, you would check user role from backend
const isAdmin = false; // Change to true to see admin section
if (isAdmin) {
    document.getElementById('admin-section').style.display = 'block';
}

// Add active class to current page in sidebar
const currentPage = window.location.pathname.split('/').pop();
const sidebarLinks = document.querySelectorAll('.sidebar a');

sidebarLinks.forEach(link => {
    if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
    }
});
