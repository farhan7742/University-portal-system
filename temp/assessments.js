document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements  to match  HTML
    const createAssessmentBtn = document.getElementById('create-assessment');
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-assessments');
    const assessmentsContainer = document.getElementById('assessments-container');
    const noAssessments = document.getElementById('no-assessments');
    const modal = document.getElementById('assessment-modal');
    const closeBtn = document.querySelector('.close');
    
    let currentEditingId = null;
    let courses = [];
    let assessments = [];

    // Debug function to test API
    async function testAPI() {
        try {
            console.log('Testing API connection...');
            const response = await fetch('assessments.php');
            const result = await response.json();
            console.log('API Test Result:', result);
            return result.success;
        } catch (error) {
            console.error('API Test Failed:', error);
            return false;
        }
    }
    
    // Event Listeners - Updated for your HTML
    createAssessmentBtn.addEventListener('click', handleFormSubmit);
    searchBtn.addEventListener('click', searchAssessments);
    searchInput.addEventListener('input', searchAssessments);
    closeBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Initialize with API test
    testAPI().then(success => {
        if (success) {
            loadCourses();
            loadAssessments();
        } else {
            showNotification('Cannot connect to server. Please check if PHP server is running.', 'error');
        }
    });
    
    // Functions
    async function loadCourses() {
        try {
            console.log('Loading courses...');
            const response = await fetch('courses.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success === false) {
                throw new Error(result.error || 'Failed to load courses');
            }
            
            courses = Array.isArray(result) ? result : (result.data || []);
            console.log('Courses loaded:', courses);
            populateCourseDropdown();
        } catch (error) {
            console.error('Error loading courses:', error);
            showNotification('Error loading courses: ' + error.message, 'error');
            
            // Fallback to default courses if API fails
            courses = [
                { id: 1, course_code: 'CS101', course_name: 'Web Development' },
                { id: 2, course_code: 'CS102', course_name: 'Database Systems' },
                { id: 3, course_code: 'MATH101', course_name: 'Mathematics' }
            ];
            populateCourseDropdown();
            showNotification('Using fallback course data', 'warning');
        }
    }
    
    function populateCourseDropdown() {
        const courseSelect = document.getElementById('course-select');
        if (!courseSelect) {
            console.error('Course select element not found');
            return;
        }
        
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        
        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = `${course.course_code} - ${course.course_name}`;
            courseSelect.appendChild(option);
        });
        
        console.log('Course dropdown populated with', courses.length, 'courses');
    }
    
    async function loadAssessments() {
        try {
            console.log('Loading assessments...');
            const response = await fetch('assessments.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Assessments API response:', result);
            
            if (result.success === false) {
                throw new Error(result.error || 'Failed to load assessments');
            }
            
            // FIXED (ONLY CHANGE)
            assessments = Array.isArray(result) ? result : (result.data || []); // FIXED
            window.assessments = assessments; // FIXED

            renderAssessmentsCards(assessments);
        } catch (error) {
            console.error('Error loading assessments:', error);
            showNotification('Error loading assessments: ' + error.message, 'error');
            
            // Show empty state
            renderAssessmentsCards([]);
        }
    }
    
    async function handleFormSubmit(e) {
        if (e) e.preventDefault();
        console.log('Form submitted, currentEditingId:', currentEditingId);
        
        const formData = {
            assessment_name: document.getElementById('assessment-title').value,
            course_id: document.getElementById('course-select').value,
            assessment_type: document.getElementById('assessment-type').value,
            due_date: document.getElementById('due-date').value,
            total_marks: parseFloat(document.getElementById('total-marks').value) || 0,
            weight: parseFloat(document.getElementById('assessment-weight').value) || 0,
            description: document.getElementById('assessment-description').value,
            status: 'Not Started'
        };
        
        console.log('Form data:', formData);
        
        // Basic validation
        if (!formData.assessment_name.trim()) {
            showNotification('Assessment name is required', 'error');
            return;
        }
        
        if (!formData.course_id) {
            showNotification('Please select a course', 'error');
            return;
        }

        if (!formData.assessment_type) {
            showNotification('Please select assessment type', 'error');
            return;
        }
        
        try {
            let url = 'assessments.php';
            let method = 'POST';
            
            if (currentEditingId) {
                url = `assessments.php/${currentEditingId}`;
                method = 'PUT';
            }
            
            console.log('Making request to:', url, 'with method:', method);
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            console.log('Server response:', result);
            
            if (!response.ok || result.success === false) {
                throw new Error(result.error || result.message || 'Unknown error occurred');
            }
            
            showNotification(
                currentEditingId ? 'Assessment updated successfully!' : 'Assessment created successfully!', 
                'success'
            );
            
            resetForm();
            loadAssessments();
            
        } catch (error) {
            console.error('Error saving assessment:', error);
            showNotification('Error saving assessment: ' + error.message, 'error');
        }
    }
    
    function resetForm() {
        document.getElementById('assessment-title').value = '';
        document.getElementById('course-select').value = '';
        document.getElementById('assessment-type').value = '';
        document.getElementById('due-date').value = '';
        document.getElementById('total-marks').value = '';
        document.getElementById('assessment-weight').value = '';
        document.getElementById('assessment-description').value = '';
        currentEditingId = null;
        createAssessmentBtn.textContent = 'Create Assessment';
    }
    
    function openEditModal(assessment) {
        console.log('Opening edit modal for:', assessment);
        
        document.getElementById('assessment-title').value = assessment.assessment_name || '';
        document.getElementById('course-select').value = assessment.course_id || '';
        document.getElementById('assessment-type').value = assessment.assessment_type || '';
        document.getElementById('due-date').value = assessment.due_date || '';
        document.getElementById('total-marks').value = assessment.total_marks || '';
        document.getElementById('assessment-weight').value = assessment.weight || '';
        document.getElementById('assessment-description').value = assessment.description || '';
        
        currentEditingId = assessment.id;
        createAssessmentBtn.textContent = 'Update Assessment';

        document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
    }
    
    async function deleteAssessment(id) {
        if (!confirm('Are you sure you want to delete this assessment?')) {
            return;
        }
        
        try {
            console.log('Deleting assessment:', id);
            const response = await fetch(`assessments.php/${id}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            console.log('Delete response:', result);
            
            if (!response.ok || result.success === false) {
                throw new Error(result.error || 'Unknown error occurred');
            }
            
            showNotification('Assessment deleted successfully!', 'success');
            loadAssessments();
            
        } catch (error) {
            console.error('Error deleting assessment:', error);
            showNotification('Error deleting assessment: ' + error.message, 'error');
        }
    }
    
    function renderAssessmentsCards(assessments) {
        if (!assessmentsContainer) {
            console.error('Assessments container not found');
            return;
        }
        
        assessmentsContainer.innerHTML = '';
        
        if (!assessments || assessments.length === 0) {
            noAssessments.style.display = 'block';
            return;
        }
        
        noAssessments.style.display = 'none';
        
        console.log('Rendering', assessments.length, 'assessments as cards');
        
        assessments.forEach(assessment => {
            const card = document.createElement('div');
            card.className = `assessment-card ${assessment.assessment_type}`;
            
            const course = courses.find(c => c.id == assessment.course_id) || {};
            
            card.innerHTML = `
                <div class="assessment-header">
                    <div>
                        <div class="assessment-title">${assessment.assessment_name}</div>
                        <span class="assessment-type type-${assessment.assessment_type}">${assessment.assessment_type}</span>
                    </div>
                </div>
                <div class="assessment-details">
                    <div class="assessment-detail">
                        <span class="detail-label">Course:</span>
                        <span class="detail-value">${course.course_code || 'N/A'} - ${course.course_name || 'N/A'}</span>
                    </div>
                    <div class="assessment-detail">
                        <span class="detail-label">Due Date:</span>
                        <span class="detail-value">${formatDate(assessment.due_date)}</span>
                    </div>
                    <div class="assessment-detail">
                        <span class="detail-label">Total Marks:</span>
                        <span class="detail-value">${assessment.total_marks}</span>
                    </div>
                    <div class="assessment-detail">
                        <span class="detail-label">Weight:</span>
                        <span class="detail-value">${assessment.weight}%</span>
                    </div>
                    <div class="assessment-detail">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">${assessment.status}</span>
                    </div>
                </div>
                <div class="assessment-actions">
                    <button class="btn-action btn-view" onclick="viewAssessment(${assessment.id})">View</button>
                    <button class="btn-action btn-edit" onclick="openEditModalFromCard(${assessment.id})">Edit</button>
                    <button class="btn-action btn-delete" onclick="deleteAssessment(${assessment.id})">Delete</button>
                </div>
            `;
            
            assessmentsContainer.appendChild(card);
        });
    }
    
    async function searchAssessments() {
        const searchTerm = searchInput.value.toLowerCase();
        
        if (!searchTerm) {
            loadAssessments();
            return;
        }

        const filteredAssessments = assessments.filter(assessment => {
            const assessmentName = assessment.assessment_name.toLowerCase();
            const courseCode = (assessment.course_code || '').toLowerCase();
            const courseName = (courses.find(c => c.id == assessment.course_id)?.course_name || '').toLowerCase();
            
            return assessmentName.includes(searchTerm) || 
                   courseCode.includes(searchTerm) || 
                   courseName.includes(searchTerm);
        });
        
        renderAssessmentsCards(filteredAssessments);
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        } catch (error) {
            return dateString;
        }
    }
    
    function closeModal() {
        modal.style.display = 'none';
    }
    
    function showNotification(message, type) {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 400px;
        `;
        
        if (type === 'success') {
            notification.style.backgroundColor = '#00bfa6';
        } else if (type === 'error') {
            notification.style.backgroundColor = '#ef4444';
        } else if (type === 'warning') {
            notification.style.backgroundColor = '#f59e0b';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    window.assessments = assessments;

    window.openEditModalFromCard = async function(id) {
        try {
            const response = await fetch(`assessments.php/${id}`);
            const result = await response.json();
            
            if (result.success) {
                openEditModal(result.data);
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error loading assessment for edit:', error);
            showNotification('Error loading assessment for edit', 'error');
        }
    };
});

// Global functions for modal (need to be in global scope)
async function viewAssessment(id) {
    try {
        const response = await fetch(`assessments.php/${id}`);
        const result = await response.json();
        
        if (result.success) {
            const assessment = result.data;
            const modal = document.getElementById('assessment-modal');
            const modalBody = document.getElementById('modal-body');
            const modalTitle = document.getElementById('modal-title');
            
            modalBody.innerHTML = `
                <div class="assessment-info">
                    <div class="info-row">
                        <div class="info-label">Assessment Name:</div>
                        <div class="info-value">${assessment.assessment_name}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Course:</div>
                        <div class="info-value">${assessment.course_code} - ${assessment.course_name}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Type:</div>
                        <div class="info-value">${assessment.assessment_type}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Due Date:</div>
                        <div class="info-value">${new Date(assessment.due_date).toLocaleDateString()}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Total Marks:</div>
                        <div class="info-value">${assessment.total_marks}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Weight:</div>
                        <div class="info-value">${assessment.weight}%</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status:</div>
                        <div class="info-value">${assessment.status}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Description:</div>
                        <div class="info-value">${assessment.description || 'No description provided'}</div>
                    </div>
                </div>
            `;
            
            modalTitle.textContent = 'Assessment Details';
            modal.style.display = 'block';
        }
    } catch (error) {
        console.error('Error viewing assessment:', error);
        showNotification('Error viewing assessment details', 'error');
    }
}

async function deleteAssessment(id) {
    if (!confirm('Are you sure you want to delete this assessment?')) {
        return;
    }

    try {
        const response = await fetch(`assessments.php/${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Assessment deleted successfully!', 'success');
            location.reload();
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error deleting assessment:', error);
        showNotification('Error deleting assessment: ' + error.message, 'error');
    }
}

function showNotification(message, type) {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-width: 400px;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#00bfa6';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444';
    } else if (type === 'warning') {
        notification.style.backgroundColor = '#f59e0b';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 5000);

}
