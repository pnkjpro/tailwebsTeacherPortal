
        document.addEventListener('DOMContentLoaded', function() {
            fetchSubjects();
        });

        function fetchSubjects() {
            // Clear all options except the first one
            const subjectSelect = document.getElementById('filter-subject');
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            fetch('/subjects')
                .then(response => response.json())
                .then(subjects => {
                    const subjectSelect = document.getElementById('filter-subject');
                    subjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject;
                        option.textContent = subject;
                        subjectSelect.appendChild(option);
                    });
                });
        }


        function showAddStudentModal() {
            document.getElementById('add-student-modal').style.display = 'block';
        }

        function closeAddStudentModal() {
            document.getElementById('add-student-modal').style.display = 'none';
        }

        function validateForm() {
        const name = document.getElementById('student-name').value.trim();
        const subject = document.getElementById('student-subject').value.trim();
        const marks = document.getElementById('student-marks').value.trim();
        const errorDiv = document.getElementById('form-errors');

        errorDiv.innerHTML = ''; // Clear previous errors

        let isValid = true;

        // Validate name
        if (!name) {
            displayError('Name is required.');
            isValid = false;
        }

        // Validate subject
        if (!subject) {
            displayError('Subject is required.');
            isValid = false;
        }

        // Validate marks
        if (!marks || isNaN(marks)) {
            displayError('Marks must be a number.');
            isValid = false;
        } else {
            const marksValue = parseInt(marks, 10);
            if (marksValue < 0 || marksValue > 100) {
                displayError('Marks must be between 0 and 100.');
                isValid = false;
            }
        }

        return isValid;
    }

    function displayError(message) {
        const errorDiv = document.getElementById('form-errors');
        const errorElement = document.createElement('div');
        errorElement.classList.add('alert', 'alert-danger');
        errorElement.innerText = message;
        errorDiv.appendChild(errorElement);
    }

        function addStudent(event) {
            event.preventDefault();

            if (!validateForm()) {
            return; // Stop form submission if validation fails
        }

            const name = document.getElementById('student-name').value;
            const subject = document.getElementById('student-subject').value;
            const marks = document.getElementById('student-marks').value;

            fetch('{{ route('student.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, subject, marks })
            })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    closeAddStudentModal();  // Close the modal
                    document.getElementById('add-student-form').reset();  // Reset form fields
                    refreshStudentTable();  // Refresh the table data
                    fetchSubjects();
                }
            })
            .catch(errors => {
                console.log("first");
                console.log(errors.errors);
                displayValidationErrors(errors.errors);
            });
        }

        function displayValidationErrors(errors){
            const errorDiv = document.getElementById('form-errors');
            errorDiv.innerHTML = '';

            for(let field in errors){
                const fieldErrors = errors[field];
                fieldErrors.forEach(error => {
                    const errorElement = document.createElement('div');
                    errorElement.classList.add('alert', 'alert-danger');
                    errorElement.innerText = error;
                    errorDiv.appendChild(errorElement);
                });
            }
        }

        function refreshStudentTable() {
            const name = document.getElementById('search-name').value;
            const subject = document.getElementById('filter-subject').value;

            fetch(`{{ route('student.index') }}?name=${encodeURIComponent(name)}&subject=${encodeURIComponent(subject)}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('student-table-body');
                    tbody.innerHTML = '';
                    data.forEach(student => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', student.id);
                        row.innerHTML = `
                            <td>${student.name}</td>
                            <td>${student.subject}</td>
                            <td contenteditable="true" onblur="updateStudentMarks(this, ${student.id})">${student.marks}</td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteStudent(${student.id})">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function updateStudentMarks(element, studentId) {
            const marks = element.innerText;

            fetch(`/student/${studentId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ marks })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        }

        function deleteStudent(studentId) {
            fetch(`/student/${studentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.querySelector(`tr[data-id='${studentId}']`).remove();
                alert(data.message);
            });
        }
    