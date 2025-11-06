// Simple counter animation for stats
function animateCounter(element, target) {
    let current = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 20);
}

// Initialize stats (you can replace these with actual data from your database)
document.addEventListener('DOMContentLoaded', function() {
    // Mock data - replace with actual API calls to your backend
    setTimeout(() => {
        animateCounter(document.getElementById('userCount'), 1247);
        animateCounter(document.getElementById('courseCount'), 89);
        animateCounter(document.getElementById('deptCount'), 15);
        animateCounter(document.getElementById('attendanceCount'), 8563);
    }, 1000);
    
    // Check user role and show admin panel if applicable
    // This is a placeholder - in a real application, you would check the user's role from your authentication system
    const userRole = 'student'; // Change to 'admin' to see the admin section
    if (userRole === 'admin') {
        document.getElementById('admin-section').style.display = 'block';
    }
    
    // Logout functionality for sidebar link only
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to log out?')) {
            alert('You have been logged out successfully.');
            // window.location.href = 'login.html';
        }
    });
});