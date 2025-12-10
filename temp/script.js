// Global variables
let currentEditingId = null;
let currentModule = '';

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    detectModule();
    initializeModule();
});

function detectModule() {
    const path = window.location.pathname;
    if (path.includes('userManagement')) {
        currentModule = 'user';
    } else if (path.includes('courseManagement')) {
        currentModule = 'course';
    } else if (path.includes('departmentManagement')) {
        currentModule = 'department';
    } else if (path.includes('attendanceManagement')) {
        currentModule = 'attendance';
    } else if (path.includes('studentProfileManagement')) {
        currentModule = 'studentProfile';
    }
}

function initializeModule() {
    switch(currentModule) {
        case 'user':
            loadUsers();
            setupUserForm();
            break;
        case 'course':
            loadCourses();
            setupCourseForm();
            break;
        case 'department':
            loadDepartments();
            setupDepartmentForm();
            break;
        case 'attendance':
            setupAttendanceForm();
            break;
        case 'studentProfile':
            setupStudentProfileForm();
            loadStudentProfiles();
            break;
    }
    
    setupSearchEnterKey();
}

// Setup enter key for search inputs
function setupSearchEnterKey() {
    // User search
    const userSearch = document.getElementById('searchTermUser');
    if (userSearch) {
        userSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchUsers();
        });
    }
    
    // Course search
    const courseSearch = document.getElementById('searchTermCourse');
    if (courseSearch) {
        courseSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchCourses();
        });
    }
    
    // Department search
    const deptSearch = document.getElementById('searchTermDepartment');
    if (deptSearch) {
        deptSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchDepartments();
        });
    }
    
    // Student Profile search
    const profileSearch = document.getElementById('searchTermStudentProfile');
    if (profileSearch) {
        profileSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchStudentProfiles();
        });
    }

}


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




// ==================== USER MANAGEMENT ====================
function setupUserForm() {
    const form = document.getElementById('addUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentEditingId) {
                updateUser(currentEditingId);
            } else {
                addUser();
            }
        });
    }

    // Search functionality - FIXED ID
    const searchInput = document.getElementById('searchTermUser');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchUsers, 300));
    }
}

function addUser() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone_number = document.getElementById('phone_number').value.trim();

    if (!name || !email || !phone_number) {
        showNotification('All fields are required!', 'error');
        return;
    }

    if (!validateEmail(email)) {
        showNotification('Please enter a valid email address!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone_number', phone_number);
    formData.append('action', 'add');

    fetch('addUser.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('successfully')) {
            showNotification('User added successfully!', 'success');
            resetUserForm();
            loadUsers();
        } else {
            showNotification(data, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding user: ' + error, 'error');
    });
}

function loadUsers() {
    fetch('getUsers.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('userList').innerHTML = data;
    })
    .catch(error => {
        console.error('Error loading users:', error);
        document.getElementById('userList').innerHTML = '<li class="no-data">Error loading users</li>';
    });
}

function searchUsers() {
    const searchTerm = document.getElementById('searchTermUser').value.trim(); // FIXED ID
    const resultsContainer = document.getElementById('userList'); // FIXED: using userList instead of userResults
    
    if (!searchTerm) {
        loadUsers(); // Load all users when search is empty
        return;
    }
    
    resultsContainer.innerHTML = '<li class="loading">Searching users...</li>';
    
    const formData = new FormData();
    formData.append('search', searchTerm);
    
    fetch('searchUser.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        resultsContainer.innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        resultsContainer.innerHTML = '<li class="no-data">Error searching users</li>';
    });
}

function editUser(id) {
    currentEditingId = id;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'get');

    fetch('manageUsers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('name').value = data.user.name;
            document.getElementById('email').value = data.user.email;
            document.getElementById('phone_number').value = data.user.phone_number;
            
            document.querySelector('#addUserForm button[type="submit"]').textContent = 'Update User';
            showNotification('Editing user: ' + data.user.name, 'info');
        }
    })
    .catch(error => {
        showNotification('Error loading user data', 'error');
    });
}

function updateUser(id) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone_number = document.getElementById('phone_number').value.trim();

    if (!name || !email || !phone_number) {
        showNotification('All fields are required!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone_number', phone_number);
    formData.append('action', 'update');

    fetch('manageUsers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('User updated successfully!', 'success');
            resetUserForm();
            loadUsers();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating user', 'error');
    });
}

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'delete');

    fetch('manageUsers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('User deleted successfully!', 'success');
            loadUsers();
            if (currentEditingId === id) {
                resetUserForm();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error deleting user', 'error');
    });
}

function resetUserForm() {
    document.getElementById('addUserForm').reset();
    currentEditingId = null;
    const submitBtn = document.querySelector('#addUserForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.textContent = 'Add User';
    }
}

// ==================== COURSE MANAGEMENT ====================
function setupCourseForm() {
    const form = document.getElementById('addCourseForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentEditingId) {
                updateCourse(currentEditingId);
            } else {
                addCourse();
            }
        });
    }

    const searchInput = document.getElementById('searchTermCourse');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchCourses, 300));
    }
}

function addCourse() {
    const course_name = document.getElementById('course_name').value.trim();
    const course_code = document.getElementById('course_code').value.trim();
    const term_name = document.getElementById('term_name').value.trim();
    const term_code = document.getElementById('term_code').value.trim();

    if (!course_name || !course_code || !term_name || !term_code) {
        showNotification('All fields are required!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('course_name', course_name);
    formData.append('course_code', course_code);
    formData.append('term_name', term_name);
    formData.append('term_code', term_code);
    formData.append('action', 'add');

    fetch('addCourse.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('successfully')) {
            showNotification('Course added successfully!', 'success');
            resetCourseForm();
            loadCourses();
        } else {
            showNotification(data, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding course: ' + error, 'error');
    });
}

function loadCoursesForAttendance() {
    fetch('getCourses.php?type=dropdown')
    .then(response => response.text())
    .then(data => {
        document.getElementById('attendanceCourse').innerHTML = '<option value="">Select Course</option>' + data;
    })
    .catch(error => {
        console.error('Error loading courses:', error);
    });
}

function loadCoursesForView() {
    fetch('getCourses.php?type=dropdown')
    .then(response => response.text())
    .then(data => {
        document.getElementById('viewCourse').innerHTML = '<option value="">Select Course</option>' + data;
    })
    .catch(error => {
        console.error('Error loading courses:', error);
    });
}


function loadCourses() {
    fetch('getCourses.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('courseList').innerHTML = data;
    })
    .catch(error => {
        console.error('Error loading courses:', error);
        document.getElementById('courseList').innerHTML = '<li class="no-data">Error loading courses</li>';
    });
}

function searchCourses() {
    const searchTerm = document.getElementById('searchTermCourse').value.trim();
    const resultsContainer = document.getElementById('courseList');
    
    if (!searchTerm) {
        loadCourses(); // Load all courses when search is empty
        return;
    }
    
    resultsContainer.innerHTML = '<li class="loading">Searching courses...</li>';
    
    const formData = new FormData();
    formData.append('search', searchTerm);
    
    fetch('searchCourse.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        resultsContainer.innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        resultsContainer.innerHTML = '<li class="no-data">Error searching courses</li>';
    });
}

function editCourse(id) {
    currentEditingId = id;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'get');

    fetch('manageCourses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('course_name').value = data.course.course_name;
            document.getElementById('course_code').value = data.course.course_code;
            document.getElementById('term_name').value = data.course.term_name;
            document.getElementById('term_code').value = data.course.term_code;
            
            document.querySelector('#addCourseForm button[type="submit"]').textContent = 'Update Course';
            showNotification('Editing course: ' + data.course.course_name, 'info');
        }
    })
    .catch(error => {
        showNotification('Error loading course data', 'error');
    });
}

function updateCourse(id) {
    const course_name = document.getElementById('course_name').value.trim();
    const course_code = document.getElementById('course_code').value.trim();
    const term_name = document.getElementById('term_name').value.trim();
    const term_code = document.getElementById('term_code').value.trim();

    if (!course_name || !course_code || !term_name || !term_code) {
        showNotification('All fields are required!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('course_name', course_name);
    formData.append('course_code', course_code);
    formData.append('term_name', term_name);
    formData.append('term_code', term_code);
    formData.append('action', 'update');

    fetch('manageCourses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Course updated successfully!', 'success');
            resetCourseForm();
            loadCourses();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating course', 'error');
    });
}

function deleteCourse(id) {
    if (!confirm('Are you sure you want to delete this course?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'delete');

    fetch('manageCourses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Course deleted successfully!', 'success');
            loadCourses();
            if (currentEditingId === id) {
                resetCourseForm();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error deleting course', 'error');
    });
}

function resetCourseForm() {
    document.getElementById('addCourseForm').reset();
    currentEditingId = null;
    const submitBtn = document.querySelector('#addCourseForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.textContent = 'Add Course';
    }
}

// ==================== DEPARTMENT MANAGEMENT ====================
function setupDepartmentForm() {
    const form = document.getElementById('addDepartmentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentEditingId) {
                updateDepartment(currentEditingId);
            } else {
                addDepartment();
            }
        });
    }

    const searchInput = document.getElementById('searchTermDepartment');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchDepartments, 300));
    }
}

function addDepartment() {
    const dept_name = document.getElementById('dept_name').value.trim();
    const dept_code = document.getElementById('dept_code').value.trim();
    const program_name = document.getElementById('program_name').value.trim();
    const faculty = document.getElementById('faculty').value.trim();
    const year = document.getElementById('year').value.trim();

    if (!dept_name || !dept_code) {
        showNotification('Department Name and Code are required!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('dept_name', dept_name);
    formData.append('dept_code', dept_code);
    formData.append('program_name', program_name);
    formData.append('faculty', faculty);
    formData.append('year', year);
    formData.append('action', 'add');

    fetch('addDepartment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('successfully')) {
            showNotification('Department added successfully!', 'success');
            resetDepartmentForm();
            loadDepartments();
        } else {
            showNotification(data, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding department: ' + error, 'error');
    });
}

function loadDepartments() {
    fetch('getDepartments.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('departmentList').innerHTML = data;
    })
    .catch(error => {
        console.error('Error loading departments:', error);
        document.getElementById('departmentList').innerHTML = '<li class="no-data">Error loading departments</li>';
    });
}

function searchDepartments() {
    const searchTerm = document.getElementById('searchTermDepartment').value.trim();
    const resultsContainer = document.getElementById('departmentList');
    
    if (!searchTerm) {
        loadDepartments(); // Load all departments when search is empty
        return;
    }
    
    resultsContainer.innerHTML = '<li class="loading">Searching departments...</li>';
    
    const formData = new FormData();
    formData.append('search', searchTerm);
    
    fetch('searchDepartment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        resultsContainer.innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        resultsContainer.innerHTML = '<li class="no-data">Error searching departments</li>';
    });
}

function editDepartment(id) {
    currentEditingId = id;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'get');

    fetch('manageDepartments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('dept_name').value = data.department.department_name || '';
            document.getElementById('dept_code').value = data.department.department_code || '';
            document.getElementById('program_name').value = data.department.program_name || '';
            document.getElementById('faculty').value = data.department.faculty_name || '';
            document.getElementById('year').value = data.department.academic_year || '';
            
            document.querySelector('#addDepartmentForm button[type="submit"]').textContent = 'Update Department';
            showNotification('Editing department: ' + data.department.department_name, 'info');
        }
    })
    .catch(error => {
        showNotification('Error loading department data', 'error');
    });
}

function updateDepartment(id) {
    const dept_name = document.getElementById('dept_name').value.trim();
    const dept_code = document.getElementById('dept_code').value.trim();
    const program_name = document.getElementById('program_name').value.trim();
    const faculty = document.getElementById('faculty').value.trim();
    const year = document.getElementById('year').value.trim();

    if (!dept_name || !dept_code) {
        showNotification('Department Name and Code are required!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('dept_name', dept_name);
    formData.append('dept_code', dept_code);
    formData.append('program_name', program_name);
    formData.append('faculty', faculty);
    formData.append('year', year);
    formData.append('action', 'update');

    fetch('manageDepartments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Department updated successfully!', 'success');
            resetDepartmentForm();
            loadDepartments();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating department', 'error');
    });
}

function deleteDepartment(id) {
    if (!confirm('Are you sure you want to delete this department?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'delete');

    fetch('manageDepartments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Department deleted successfully!', 'success');
            loadDepartments();
            if (currentEditingId === id) {
                resetDepartmentForm();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error deleting department', 'error');
    });
}

function resetDepartmentForm() {
    document.getElementById('addDepartmentForm').reset();
    currentEditingId = null;
    const submitBtn = document.querySelector('#addDepartmentForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.textContent = 'Add Department';
    }
}

// ==================== ATTENDANCE MANAGEMENT ====================
function viewAttendanceRecords() {
    const courseId = document.getElementById('viewCourse').value;
    const date = document.getElementById('viewDate').value;

    if (!courseId) {
        showNotification('Please select a course', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('course_id', courseId);
    if (date) formData.append('date', date);

    document.getElementById('attendanceRecords').innerHTML = '<div class="loading">Loading records...</div>';

    fetch('getAttendanceRecords.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log("Attendance records response:", data);
        
        // Check if the response contains HTML table or error message
        if (data.trim() === '' || data.includes('No attendance records found')) {
            document.getElementById('attendanceRecords').innerHTML = '<div class="no-data">No attendance records found for the selected criteria</div>';
        } else {
            // Remove any "no-data" class and replace with table
            document.getElementById('attendanceRecords').innerHTML = data;
            document.getElementById('attendanceRecords').classList.remove('no-data');
        }
        
        // Always try to calculate stats after loading records
        calculateAttendanceStats(courseId);
    })
    .catch(error => {
        console.error('Error loading attendance records:', error);
        document.getElementById('attendanceRecords').innerHTML = '<div class="no-data">Error loading attendance records</div>';
        showNotification('Error loading attendance records', 'error');
    });
}

function calculateAttendanceStats(courseId) {
    const formData = new FormData();
    formData.append('course_id', courseId);

    fetch('getAttendanceStats.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log("Attendance stats response:", data);
        
        // Check if we got valid stats HTML
        if (data.trim() === '' || data.includes('Select a course')) {
            document.getElementById('attendanceStats').innerHTML = 
                '<div class="no-data">No statistics available for this course</div>';
        } else {
            document.getElementById('attendanceStats').innerHTML = data;
        }
    })
    .catch(error => {
        console.error('Error loading attendance stats:', error);
        document.getElementById('attendanceStats').innerHTML = 
            '<div class="no-data">Error loading attendance statistics</div>';
    });
}

function setupAttendanceForm() {
    console.log("Setting up attendance form...");
    loadCoursesForAttendance();
    loadCoursesForView();
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('attendanceDate').value = today;
    document.getElementById('viewDate').value = today;
}

function loadCoursesForAttendance() {
    console.log("Loading courses for attendance...");
    document.getElementById('attendanceCourse').innerHTML = '<option value="">Loading courses...</option>';
    
    fetch('getCourses.php?type=dropdown')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log("Courses loaded:", data);
        document.getElementById('attendanceCourse').innerHTML = '<option value="">Select Course</option>' + data;
    })
    .catch(error => {
        console.error('Error loading courses:', error);
        document.getElementById('attendanceCourse').innerHTML = '<option value="">Error loading courses</option>';
        showNotification('Error loading courses: ' + error.message, 'error');
    });
}

function loadStudentsForAttendance() {
    const courseId = document.getElementById('attendanceCourse').value;
    const date = document.getElementById('attendanceDate').value;

    if (!courseId || !date) {
        showNotification('Please select course and date', 'error');
        return;
    }

    console.log("Loading students for course:", courseId, "date:", date);
    
    document.getElementById('studentsList').innerHTML = '<div class="loading">Loading students...</div>';
    document.getElementById('studentsAttendanceSection').style.display = 'block';

    const formData = new FormData();
    formData.append('course_id', courseId);
    formData.append('date', date);

    fetch('getStudentsForAttendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log("Students data received:", data);
        document.getElementById('studentsList').innerHTML = data;
        
        // Check if we got any student data
        const studentItems = document.querySelectorAll('.student-attendance-item');
        if (studentItems.length > 0) {
            showNotification('Students loaded successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error loading students:', error);
        document.getElementById('studentsList').innerHTML = '<div class="no-data">Error loading students: ' + error.message + '</div>';
        showNotification('Error loading students', 'error');
    });
}




// ==================== STUDENT PROFILE MANAGEMENT ====================
function setupStudentProfileForm() {
    const form = document.getElementById('addStudentProfileForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentEditingId) {
                updateStudentProfile(currentEditingId);
            } else {
                addStudentProfile();
            }
        });
    }

    // Search functionality
    const searchInput = document.getElementById('searchTermStudentProfile');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchStudentProfiles, 300));
    }

    // Load departments for dropdown
    loadDepartmentsForStudentProfile();
}

function addStudentProfile() {
    const user_id = document.getElementById('user_id').value.trim();
    const student_id = document.getElementById('student_id').value.trim();
    const parent_name = document.getElementById('parent_name').value.trim();
    const parent_contact = document.getElementById('parent_contact').value.trim();
    const major_department_id = document.getElementById('major_department_id').value;
    const status = document.getElementById('status').value;
    const entry_session = document.getElementById('entry_session').value.trim();
    const current_cgpa = document.getElementById('current_cgpa').value;
    const credits_passed = document.getElementById('credits_passed').value;
    const total_credits = document.getElementById('total_credits').value;
    const current_semester = document.getElementById('current_semester').value.trim();

    if (!user_id || !student_id || !parent_name || !parent_contact || !major_department_id || !status || !entry_session || !current_semester) {
        showNotification('All required fields must be filled!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('user_id', user_id);
    formData.append('student_id', student_id);
    formData.append('parent_name', parent_name);
    formData.append('parent_contact', parent_contact);
    formData.append('major_department_id', major_department_id);
    formData.append('status', status);
    formData.append('entry_session', entry_session);
    formData.append('current_cgpa', current_cgpa || '0.00');
    formData.append('credits_passed', credits_passed || '0');
    formData.append('total_credits', total_credits || '0');
    formData.append('current_semester', current_semester);

    fetch('setup_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Student profile added successfully!', 'success');
            resetStudentProfileForm();
            loadStudentProfiles();
        } else {
            showNotification(data.message || 'Error adding student profile', 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding student profile: ' + error, 'error');
    });
}

function loadStudentProfiles() {
    fetch('get_profile.php')
    .then(response => response.json())
    .then(data => {
        if (data.records) {
            displayStudentProfiles(data.records);
        } else {
            document.getElementById('studentProfileList').innerHTML = '<div class="no-data">No student profiles found</div>';
        }
    })
    .catch(error => {
        console.error('Error loading student profiles:', error);
        document.getElementById('studentProfileList').innerHTML = '<div class="no-data">Error loading student profiles</div>';
    });
}

function displayStudentProfiles(profiles) {
    const container = document.getElementById('studentProfileList');
    if (!container) return;

    if (!profiles || profiles.length === 0) {
        container.innerHTML = '<div class="no-data">No student profiles found</div>';
        return;
    }

    let html = '';
    profiles.forEach(profile => {
        html += `
            <div class="profile-item" data-id="${profile.id}">
                <div class="profile-header">
                    <h4>${profile.student_id} - ${profile.parent_name}</h4>
                    <div class="profile-actions">
                        <button class="btn-edit" onclick="editStudentProfile(${profile.id})">Edit</button>
                        <button class="btn-delete" onclick="deleteStudentProfile(${profile.id})">Delete</button>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="detail-row">
                        <span class="label">Department:</span>
                        <span class="value">${profile.department_name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Status:</span>
                        <span class="value status-${profile.status ? profile.status.toLowerCase() : 'unknown'}">${profile.status || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">CGPA:</span>
                        <span class="value">${profile.current_cgpa || '0.00'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Credits:</span>
                        <span class="value">${profile.credits_passed || 0}/${profile.total_credits || 0}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Semester:</span>
                        <span class="value">${profile.current_semester || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Entry Session:</span>
                        <span class="value">${profile.entry_session || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Parent Contact:</span>
                        <span class="value">${profile.parent_contact || 'N/A'}</span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function searchStudentProfiles() {
    const searchTerm = document.getElementById('searchTermStudentProfile').value.trim();
    const resultsContainer = document.getElementById('studentProfileList');
    
    if (!searchTerm) {
        loadStudentProfiles();
        return;
    }
    
    resultsContainer.innerHTML = '<div class="loading">Searching student profiles...</div>';
    
    // For now, we'll filter client-side. You can create a search_student_profile.php for server-side search
    fetch('get_profile.php')
    .then(response => response.json())
    .then(data => {
        if (data.records) {
            const filteredProfiles = data.records.filter(profile => 
                profile.student_id.toLowerCase().includes(searchTerm.toLowerCase()) ||
                profile.parent_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (profile.department_name && profile.department_name.toLowerCase().includes(searchTerm.toLowerCase()))
            );
            displayStudentProfiles(filteredProfiles);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultsContainer.innerHTML = '<div class="no-data">Error searching student profiles</div>';
    });
}

function editStudentProfile(id) {
    currentEditingId = id;
    
    fetch('get_profile.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.id) {
            document.getElementById('user_id').value = data.user_id || '';
            document.getElementById('student_id').value = data.student_id || '';
            document.getElementById('parent_name').value = data.parent_name || '';
            document.getElementById('parent_contact').value = data.parent_contact || '';
            document.getElementById('major_department_id').value = data.major_department_id || '';
            document.getElementById('status').value = data.status || '';
            document.getElementById('entry_session').value = data.entry_session || '';
            document.getElementById('current_cgpa').value = data.current_cgpa || '0.00';
            document.getElementById('credits_passed').value = data.credits_passed || '0';
            document.getElementById('total_credits').value = data.total_credits || '0';
            document.getElementById('current_semester').value = data.current_semester || '';
            
            document.querySelector('#addStudentProfileForm button[type="submit"]').textContent = 'Update Profile';
            showNotification('Editing student profile: ' + data.student_id, 'info');
        }
    })
    .catch(error => {
        showNotification('Error loading student profile data', 'error');
    });
}

function updateStudentProfile(id) {
    const user_id = document.getElementById('user_id').value.trim();
    const student_id = document.getElementById('student_id').value.trim();
    const parent_name = document.getElementById('parent_name').value.trim();
    const parent_contact = document.getElementById('parent_contact').value.trim();
    const major_department_id = document.getElementById('major_department_id').value;
    const status = document.getElementById('status').value;
    const entry_session = document.getElementById('entry_session').value.trim();
    const current_cgpa = document.getElementById('current_cgpa').value;
    const credits_passed = document.getElementById('credits_passed').value;
    const total_credits = document.getElementById('total_credits').value;
    const current_semester = document.getElementById('current_semester').value.trim();

    if (!user_id || !student_id || !parent_name || !parent_contact || !major_department_id || !status || !entry_session || !current_semester) {
        showNotification('All required fields must be filled!', 'error');
        return;
    }

    const profileData = {
        id: id,
        user_id: user_id,
        student_id: student_id,
        parent_name: parent_name,
        parent_contact: parent_contact,
        major_department_id: major_department_id,
        status: status,
        entry_session: entry_session,
        current_cgpa: current_cgpa || '0.00',
        credits_passed: credits_passed || '0',
        total_credits: total_credits || '0',
        current_semester: current_semester
    };

    fetch('update_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(profileData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Student profile updated successfully!', 'success');
            resetStudentProfileForm();
            loadStudentProfiles();
        } else {
            showNotification(data.message || 'Error updating student profile', 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating student profile', 'error');
    });
}

function deleteStudentProfile(id) {
    if (!confirm('Are you sure you want to delete this student profile?')) {
        return;
    }

    const profileData = {
        id: id
    };

    fetch('delete_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(profileData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Student profile deleted successfully!', 'success');
            loadStudentProfiles();
            if (currentEditingId === id) {
                resetStudentProfileForm();
            }
        } else {
            showNotification(data.message || 'Error deleting student profile', 'error');
        }
    })
    .catch(error => {
        showNotification('Error deleting student profile', 'error');
    });
}

function resetStudentProfileForm() {
    const form = document.getElementById('addStudentProfileForm');
    if (form) {
        form.reset();
    }
    currentEditingId = null;
    const submitBtn = document.querySelector('#addStudentProfileForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.textContent = 'Add Profile';
    }
}

function loadDepartmentsForStudentProfile() {
    fetch('get_departments_dropdown.php')
    .then(response => response.text())
    .then(data => {
        const dropdown = document.getElementById('major_department_id');
        if (dropdown) {
            dropdown.innerHTML = '<option value="">Select Department</option>' + data;
        }
    })
    .catch(error => {
        console.error('Error loading departments:', error);
    });
}




// ==================== UTILITY FUNCTIONS ====================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

    const colors = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// REMOVED CSS INJECTION - Using your existing style.css instead