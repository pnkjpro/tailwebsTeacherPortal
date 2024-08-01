<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <style>
        .container {
            margin-top: -6px; 
    } 
    .editable-cell {
    background-color: #f9f9f9;
    border: 1px dashed #007bff;
    cursor: pointer;
    transition: background-color 0.3s;
}

.editable-cell:hover {
    background-color: #e9ecef;
}

.editable-cell:focus {
    outline: none;
    border-color: #0056b3;
    background-color: #fff;
}
   
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
      <a class="navbar-brand px-3" href="#">{{ config('app.name', 'TailWebs Teacher Portal') }}</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between" id="navbarNavDropdown">
        <div class="ml-auto px-3">
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              {{ Auth::user()->name }}
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>    
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

    <div class="container">
        <h1>Teacher Portal</h1>
        <button class="btn mb-2" onclick="showAddStudentModal()">Add New Student</button>

        <div class="row w-50">
            <div class="col-sm">
            <input type="text" class="form-control" id="search-name" placeholder="Search by name" oninput="
                setTimeout(() => {
                    refreshStudentTable()
                }, 500)">
            </div>
            <div class="col-sm">
            <select class="form-control" id="filter-subject" onchange="refreshStudentTable()">
                <option value="">Select Subject</option>
                <!-- Options will be populated by JavaScript -->
            </select>
            </div>
            
        </div>

        <table class="mb-2">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="student-table-body">
                @foreach ($students as $student)
                <tr data-id="{{ $student->id }}">  
                    <td contenteditable="true" onblur="updateStudentField(this, 'name', {{ $student->id }})">{{ $student->name }}</td>
                    <td contenteditable="true" onblur="updateStudentField(this, 'subject', {{ $student->id }})">{{ $student->subject }}</td>
                    <td contenteditable="true" onblur="updateStudentField(this, 'marks', {{ $student->id }})">{{ $student->marks }}</td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteStudent({{ $student->id }})">Delete</button>
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>
        
        <!-- Modal for adding new student -->
        <div id="add-student-modal" class="modal" style="z-index: 1000;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Student</h2>
                    <span class="close" onclick="closeAddStudentModal()">&times;</span>
                </div>
                <div class="modal-body">

                 <div id="form-errors"></div>
                    <form id="add-student-form" onsubmit="addStudent(event)">
                        <div class="form-group">
                            <label for="student-name">Name:</label>
                            <input type="text" id="student-name" required>
                        </div>
                        <div class="form-group">
                            <label for="student-subject">Subject:</label>
                            <input type="text" id="student-subject" required>
                        </div>
                        <div class="form-group">
                            <label for="student-marks">Marks:</label>
                            <input type="number" id="student-marks" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn">Add</button>
                            <button type="button" class="btn btn-secondary" onclick="closeAddStudentModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchSubjects();
        });

        function fetchSubjects() {
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

 async function validateForm() {
    const name = document.getElementById('student-name').value.trim();
    const subject = document.getElementById('student-subject').value.trim();
    const marks = document.getElementById('student-marks').value.trim();
    const errorDiv = document.getElementById('form-errors');

    errorDiv.innerHTML = ''; // Clear previous errors

    let isValid = true;

    // Validate name
    if (!name) {
        displayError('Name is required.');
        isValid= false;
    }

    // Validate subject
    if (!subject) {
        displayError('Subject is required.');
        isValid= false;
    }

    // Validate marks
    if (!marks || isNaN(marks)) {
        displayError('Marks must be a number.');
        isValid= false;
    } else {
        const marksValue = parseInt(marks, 10);
        if (marksValue < 0 || marksValue > 100) {
            displayError('Marks must be between 0 and 100.');
            isValid= false;
        }
    }

    // Checking if the total marks exceed 100
    if (isValid) {
        const currentMarks =await getCurrentMarks(name, subject);
        console.log(currentMarks)
        if (currentMarks != null) {
            const totalMarks = currentMarks + parseInt(marks, 10);
            console.log(totalMarks)
            if (totalMarks > 100) {
                displayError('Total marks (current + new) cannot exceed 100.');
                isValid= false;
            }
        }
    }

    return isValid
}

function displayError(message) {
    const errorDiv = document.getElementById('form-errors');
    const errorItem = document.createElement('p');
    errorItem.textContent = message;
    errorItem.style.color = 'red';
    errorDiv.appendChild(errorItem);
}

async function getCurrentMarks(name, subject) {
    try {
        const response = await fetch('{{ route('currentMarks') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, subject })
            })
        const data = await response.json();
        return data.marks || 0;
    } catch (error) {
        console.error('Error fetching current marks:', error);
        return null;
    }
}


     async function addStudent(event) {
            event.preventDefault();
            let check=await validateForm()
            console.log(check)
                if (!check) {
                return; 
            }

            const name = document.getElementById('student-name').value;
            const subject = document.getElementById('student-subject').value;
            const marks = document.getElementById('student-marks').value;

          await  fetch('{{ route('student.store') }}', {
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
                    closeAddStudentModal();  
                    document.getElementById('add-student-form').reset();  
                    refreshStudentTable();  
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

            fetch('{{ route('student.index') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, subject })
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('student-table-body');
                    tbody.innerHTML = '';
                    data.forEach(student => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', student.id);
                        row.innerHTML = `
                            <td contenteditable="true" onblur="updateStudentField(this, 'name', ${student.id})">${student.name}</td>
                            <td contenteditable="true" onblur="updateStudentField(this, 'subject', ${student.id})">${student.subject}</td>
                            <td contenteditable="true" onblur="updateStudentField(this, 'marks', ${student.id})">${student.marks}</td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteStudent(${student.id})">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function updateStudentField(element, field, studentId) {
            const newValue = element.innerText.trim();
           
            const data = {
                [field]: newValue
            };

            if(data.marks > 100){
                window.alert('Marks cannot be greater than 100');
                return refreshStudentTable();
            }
            
            fetch(`/student/${studentId}`, {
                method: 'PATCH', 
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    window.alert(`${field} updated successfully.`);
                    fetchSubjects();
                    
                } else {
                    console.error(`Failed to update ${field}:`, data.message);
                    fetchSubjects();
                }
            })
            .catch(error => {
                console.error('Error updating student:', error);
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
                fetchSubjects();
                alert(data.message);
            });
        }
    </script>
    
</body>
</html>
