// Section Grade Management System
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const courseSelect = document.getElementById('courseSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    const loadSectionBtn = document.getElementById('loadSectionBtn');
    const sectionInfo = document.getElementById('sectionInfo');
    const gradesSection = document.getElementById('gradesSection');
    const gradesList = document.getElementById('gradesList');
    const saveAllBtn = document.getElementById('saveAllBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    // Current section data
    let currentSection = null;
    let currentStudents = [];
    
    // Event Listeners
    courseSelect.addEventListener('change', function() {
        const course = this.value;
        
        if (course) {
            // Enable section select and populate options
            sectionSelect.disabled = false;
            sectionSelect.innerHTML = '<option value="">Select a section</option>';
            
            // Generate sections 1-5 for the selected course
            for (let i = 1; i <= 5; i++) {
                const option = document.createElement('option');
                option.value = `${course}-${i}`;
                option.textContent = `Section ${i}`;
                sectionSelect.appendChild(option);
            }
            
            // Enable load button if section is selected
            loadSectionBtn.disabled = sectionSelect.value === '';
        } else {
            // Reset section select and disable buttons
            sectionSelect.disabled = true;
            sectionSelect.innerHTML = '<option value="">Select a section</option>';
            loadSectionBtn.disabled = true;
        }
    });
    
    sectionSelect.addEventListener('change', function() {
        loadSectionBtn.disabled = !this.value;
    });
    
    loadSectionBtn.addEventListener('click', function() {
        const course = courseSelect.value;
        const sectionId = sectionSelect.value;
        
        if (course && sectionId) {
            loadSection(course, sectionId);
        }
    });
    
    saveAllBtn.addEventListener('click', function() {
        saveAllGrades();
    });
    
    exportBtn.addEventListener('click', function() {
        exportToCSV();
    });
    
    // Fetch courses from database
    function fetchCourses() {
        courseSelect.innerHTML = '<option value="">Loading courses...</option>';
        
        fetch('get_courses.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate course select
                    courseSelect.innerHTML = '<option value="">Select a course</option>';
                    data.data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.course_code;
                        option.textContent = `${course.course_code} - ${course.course_name}`;
                        courseSelect.appendChild(option);
                    });
                } else {
                    showMessage('Failed to load courses: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to load courses', 'error');
                // Fallback to sample courses if API fails
                loadSampleCourses();
            });
    }
    
    // Fallback sample courses
    function loadSampleCourses() {
        const courses = [
            { course_code: 'CS101', course_name: 'Web Development' },
            { course_code: 'CS102', course_name: 'Database Systems' },
            { course_code: 'MATH101', course_name: 'Mathematics' }
        ];
        
        courseSelect.innerHTML = '<option value="">Select a course</option>';
        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.course_code;
            option.textContent = `${course.course_code} - ${course.course_name}`;
            courseSelect.appendChild(option);
        });
    }
    
    // Load section data
    function loadSection(course, sectionId) {
        // Show loading
        gradesList.innerHTML = '<div class="loading">Loading students...</div>';
        
        // Extract section number
        const sectionNumber = sectionId.split('-')[1];
        
        // Fetch students for this course section
        fetch(`get_students.php?course_code=${course}&section=${sectionNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentSection = {
                        course: course,
                        section: sectionNumber,
                        sectionId: sectionId
                    };
                    currentStudents = data.data;
                    
                    // Update section info
                    updateSectionInfo();
                    
                    // Render student grades
                    renderStudentGrades();
                    
                    showMessage(`Loaded ${currentStudents.length} students for ${course} - Section ${sectionNumber}`, 'success');
                } else {
                    showMessage('Failed to load students: ' + data.error, 'error');
                    gradesList.innerHTML = '<div class="no-grades">Failed to load students</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to load students', 'error');
                gradesList.innerHTML = '<div class="no-grades">Failed to load students</div>';
            });
    }
    
    // Update section information
    function updateSectionInfo() {
        document.getElementById('currentSectionTitle').textContent = 
            `${currentSection.course} - Section ${currentSection.section}`;
        document.getElementById('detailCourse').textContent = currentSection.course;
        document.getElementById('detailSection').textContent = `Section ${currentSection.section}`;
        document.getElementById('detailStudents').textContent = currentStudents.length;
        
        // Show sections
        sectionInfo.style.display = 'block';
        gradesSection.style.display = 'block';
    }
    
    // Render student grades list
    function renderStudentGrades() {
        if (!currentStudents || currentStudents.length === 0) {
            gradesList.innerHTML = '<div class="no-grades">No students enrolled in this section.</div>';
            return;
        }
        
        gradesList.innerHTML = '';
        
        currentStudents.forEach((student, index) => {
            const gradeItem = document.createElement('div');
            gradeItem.className = `grade-item grade-${student.grade ? student.grade.charAt(0) : 'A'}`;
            gradeItem.innerHTML = `
                <div class="student-info">
                    <div class="student-name">${student.name}</div>
                    <div class="student-id">ID: ${student.student_id}</div>
                    <div class="student-email">${student.email}</div>
                </div>
                <div class="grade-controls">
                    <select class="grade-input" data-student-id="${student.student_id}">
                        <option value="A" ${student.grade === 'A' ? 'selected' : ''}>A</option>
                        <option value="A-" ${student.grade === 'A-' ? 'selected' : ''}>A-</option>
                        <option value="B+" ${student.grade === 'B+' ? 'selected' : ''}>B+</option>
                        <option value="B" ${student.grade === 'B' ? 'selected' : ''}>B</option>
                        <option value="B-" ${student.grade === 'B-' ? 'selected' : ''}>B-</option>
                        <option value="C+" ${student.grade === 'C+' ? 'selected' : ''}>C+</option>
                        <option value="C" ${student.grade === 'C' ? 'selected' : ''}>C</option>
                        <option value="C-" ${student.grade === 'C-' ? 'selected' : ''}>C-</option>
                        <option value="D" ${student.grade === 'D' ? 'selected' : ''}>D</option>
                        <option value="F" ${student.grade === 'F' ? 'selected' : ''}>F</option>
                    </select>
                    <input type="number" class="percentage-input" data-student-id="${student.student_id}" 
                           value="${student.percentage || 0}" min="0" max="100" step="0.1" placeholder="Percentage">
                </div>
            `;
            
            gradesList.appendChild(gradeItem);
        });
        
        // Add event listeners to grade and percentage inputs
        document.querySelectorAll('.grade-input').forEach(select => {
            select.addEventListener('change', function() {
                const studentId = this.getAttribute('data-student-id');
                updateStudentGrade(studentId, this.value);
            });
        });
        
        document.querySelectorAll('.percentage-input').forEach(input => {
            input.addEventListener('change', function() {
                const studentId = this.getAttribute('data-student-id');
                const percentage = parseFloat(this.value);
                
                if (isNaN(percentage) || percentage < 0 || percentage > 100) {
                    const student = currentStudents.find(s => s.student_id === studentId);
                    this.value = student.percentage || 0;
                    showMessage('Please enter a valid percentage between 0 and 100', 'error');
                    return;
                }
                
                updateStudentPercentage(studentId, percentage);
            });
        });
    }
    
    // Update student grade
    function updateStudentGrade(studentId, grade) {
        const student = currentStudents.find(s => s.student_id === studentId);
        if (student) {
            student.grade = grade;
            
            // Update the visual indicator
            const gradeItem = document.querySelector(`[data-student-id="${studentId}"]`).closest('.grade-item');
            gradeItem.className = `grade-item grade-${grade.charAt(0)}`;
            
            showMessage(`Updated grade for ${student.name}`, 'success');
        }
    }
    
    // Update student percentage
    function updateStudentPercentage(studentId, percentage) {
        const student = currentStudents.find(s => s.student_id === studentId);
        if (student) {
            student.percentage = percentage;
            showMessage(`Updated percentage for ${student.name}`, 'success');
        }
    }
    
    // Save all grades to database
    function saveAllGrades() {
        if (!currentSection || currentStudents.length === 0) {
            showMessage('No section loaded or no students to save', 'error');
            return;
        }
        
        const gradesData = {
            course_code: currentSection.course,
            section: currentSection.section,
            students: currentStudents.map(student => ({
                student_id: student.student_id,
                grade: student.grade || 'A',
                percentage: student.percentage || 0
            }))
        };
        
        // Show saving indicator
        saveAllBtn.disabled = true;
        saveAllBtn.textContent = 'Saving...';
        
        fetch('save_grades.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(gradesData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('All grades saved successfully!', 'success');
            } else {
                showMessage('Failed to save grades: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Failed to save grades', 'error');
        })
        .finally(() => {
            saveAllBtn.disabled = false;
            saveAllBtn.textContent = 'Save All Changes';
        });
    }
    
    // Export to CSV
    function exportToCSV() {
        if (!currentSection || currentStudents.length === 0) {
            showMessage('No data to export', 'error');
            return;
        }
        
        const course = courseSelect.options[courseSelect.selectedIndex].text;
        const section = sectionSelect.options[sectionSelect.selectedIndex].text;
        
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += `"${course} - ${section}"\n`;
        csvContent += "Student ID,Student Name,Email,Grade,Percentage\n";
        
        currentStudents.forEach(student => {
            csvContent += `"${student.student_id}","${student.name}","${student.email}","${student.grade || 'A'}","${student.percentage || 0}"\n`;
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `${currentSection.course}-section-${currentSection.section}-grades.csv`);
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
        document.body.removeChild(link);
        
        showMessage('Grades exported to CSV', 'success');
    }
    
    // Show message
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create message element
        const messageEl = document.createElement('div');
        messageEl.textContent = message;
        messageEl.className = `message`;
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        
        // Set background color based on type
        if (type === 'success') {
            messageEl.style.backgroundColor = '#28a745';
        } else if (type === 'error') {
            messageEl.style.backgroundColor = '#dc3545';
        } else {
            messageEl.style.backgroundColor = '#007BFF';
        }
        
        // Add to page
        document.body.appendChild(messageEl);
        
        // Remove after 3 seconds
        setTimeout(() => {
            messageEl.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (document.body.contains(messageEl)) {
                    document.body.removeChild(messageEl);
                }
            }, 300);
        }, 3000);
    }
    
    // Initialize the application
    fetchCourses();
});