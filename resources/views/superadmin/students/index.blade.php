@extends('layouts.app')

@section('title', 'Student Management - Super Admin')

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-crown"></i> Super Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('superadmin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('superadmin.admins.index') }}">
                <i class="fas fa-users-cog"></i>Department Admin Management
            </a></li>
             <li><a href="{{ route('superadmin.office-admins.index') }}">
                    <i class="fas fa-briefcase"></i> Officer Management
                </a></li>
            <li><a href="{{ route('superadmin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('superadmin.events.index') }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('superadmin.news.index') }}">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li><a href="{{ route('superadmin.faculty.index') }}">
                <i class="fas fa-chalkboard-teacher"></i> Faculty
            </a></li>
            <li><a href="{{ route('superadmin.students.index') }}" class="active">
                <i class="fas fa-user-graduate"></i> Students
            </a></li>
            @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
            <li><a href="{{ route('superadmin.admin-access') }}">
                <i class="fas fa-clipboard-list"></i> Admin Access Logs
            </a></li>
            <li><a href="{{ route('superadmin.backup') }}">
                <i class="fas fa-database"></i> Database Backup
            </a></li>
            @endif
            
        </ul>
    </div>
    
    <div class="main-content">
        <div class="enhanced-header">
            <div class="header-content">
                <div class="header-text">
                    <div class="header-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h1>Student Management</h1>
                        <p>Manage and monitor students across all departments</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-modern btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                   
                </div>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert-modern alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Success!</h4>
                    <p>{{ session('success') }}</p>
                </div>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $students->count() }}</h3>
                    <p>Total Students</p>
                </div>
               
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $students->where('created_at', '>=', now()->subMonth())->count() }}</h3>
                    <p>New This Month</p>
                </div>
                
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $students->groupBy('department')->count() }}</h3>
                    <p>Departments</p>
                </div>
                
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $students->count() }}</h3>
                    <p>Registered Students</p>
                </div>
                
            </div>
        </div>
        
        <div class="enhanced-table-container">
            <div class="table-header-modern">
                <div class="table-title">
                    <h2><i class="fas fa-list"></i> Student Directory</h2>
                    <p>Complete list of registered students</p>
                </div>
                <div class="table-controls">
                    <div class="search-container-modern">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search students..." class="search-input-modern">
                        <button class="search-clear" onclick="clearSearch()" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="filter-controls">
                        <select class="filter-select" id="departmentFilter">
                            <option value="">All Departments</option>
                            <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                            <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                            <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                            <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                            <option value="Bachelor of Secondary Education">Bachelor of Secondary Education</option>
                        </select>
                        <select class="filter-select" id="sortFilter">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">Name A-Z</option>
                            <option value="name-desc">Name Z-A</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Actions Bar -->
            <div id="bulkActionsBar" class="bulk-actions bulk-actions-hidden">
                <div class="selected-count">
                    <i class="fas fa-check-square"></i>
                    <span class="count" id="selectedCount">0</span> selected
                </div>
                <button onclick="bulkDeleteStudents()" class="bulk-delete-btn">
                    <i class="fas fa-trash-alt"></i> Delete Selected
                </button>
                <button onclick="clearSelection()" class="clear-selection-btn">
                    <i class="fas fa-times"></i> Clear Selection
                </button>
            </div>

            <!-- Desktop Table View -->
            <div class="table-responsive desktop-view">
                <table class="enhanced-table" id="dataTable">
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" title="Select All">
                            </th>
                            <th class="sortable" data-sort="id">
                                <i class="fas fa-hashtag"></i> ID
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-sort="name">
                                <i class="fas fa-user"></i> Student
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-sort="department">
                                <i class="fas fa-building"></i> Department
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-sort="year_level">
                                <i class="fas fa-graduation-cap"></i> Year Level
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-sort="email">
                                <i class="fas fa-envelope"></i> Contact
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-sort="date">
                                <i class="fas fa-calendar"></i> Joined
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="no-sort">
                                <i class="fas fa-cogs"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr class="table-row-enhanced student-row" id="student-row-{{ $student->id }}" data-department="{{ $student->department }}">
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="student-checkbox" value="{{ $student->id }}" onchange="toggleRowSelection(this)">
                                </td>
                                <td>
                                    <span class="id-badge">#{{ str_pad($student->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            @if($student->hasProfilePicture)
                                                <img src="{{ $student->profilePictureUrl }}" 
                                                     alt="{{ $student->first_name }} {{ $student->surname }}" 
                                                     class="profile-image">
                                            @else
                                                <span class="initials">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->surname, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</div>
                                            <div class="user-role">Student</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="department-badge department-{{ strtolower($student->department) }}">
                                        {{ $student->department }}
                                    </span>
                                </td>
                                <td>
                                    <span class="year-level-badge">
                                        {{ $student->year_level ?? 'Not Set' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        @if($student->gmail_account)
                                            <a href="mailto:{{ $student->gmail_account }}" class="email-link">
                                                <i class="fab fa-google"></i>
                                                {{ $student->gmail_account }}
                                            </a>
                                        @elseif($student->ms365_account)
                                            <a href="mailto:{{ $student->ms365_account }}" class="email-link">
                                                <i class="fas fa-envelope"></i>
                                                {{ $student->ms365_account }}
                                            </a>
                                        @else
                                            <span class="text-muted">No email</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <div class="date-primary">{{ $student->created_at->format('M d, Y') }}</div>
                                        <div class="date-secondary">{{ $student->created_at->diffForHumans() }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons-modern">
                                        <a href="{{ route('superadmin.students.edit', $student->id) }}" class="btn-action btn-edit" title="Edit Student">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-action btn-delete" onclick="deleteStudent({{ $student->id }})" title="Delete Student">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state-modern">
                                        <div class="empty-icon">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h3>No Students Found</h3>
                                        <p>Students will appear here once they register through the user portal.</p>
                                        <button class="btn-modern btn-primary" onclick="refreshData()">
                                            <i class="fas fa-refresh"></i>
                                            Refresh Data
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-view">
                @forelse($students as $student)
                    <div class="student-card" data-department="{{ $student->department }}">
                        <div class="card-header">
                            <div class="user-avatar-large">
                                @if($student->hasProfilePicture)
                                    <img src="{{ $student->profilePictureUrl }}" 
                                         alt="{{ $student->first_name }} {{ $student->surname }}" 
                                         class="profile-image-large">
                                @else
                                    <span class="initials-large">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->surname, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="card-title">
                                <h3>{{ $student->first_name }} {{ $student->surname }}</h3>
                                <p>Student #{{ str_pad($student->id, 4, '0', STR_PAD_LEFT) }}</p>
                                <span class="department-badge department-{{ strtolower($student->department) }}">
                                    {{ $student->department }}
                                </span>
                            </div>
                            <div class="card-menu">
                                <button class="btn-menu" onclick="toggleCardMenu(this)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="card-menu-dropdown">
                                    <a href="{{ route('superadmin.students.edit', $student->id) }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="deleteStudent({{ $student->id }})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="info-row">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $student->ms365_account }}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-graduation-cap"></i>
                                <span>{{ $student->year_level ?? 'Year Level Not Set' }}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-calendar"></i>
                                <span>Joined {{ $student->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-card btn-primary" onclick="viewStudent({{ $student->id }})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <a href="{{ route('superadmin.students.edit', $student->id) }}" class="btn-card btn-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-mobile">
                        <i class="fas fa-user-graduate"></i>
                        <h3>No Students</h3>
                        <p>Students will appear here once they register.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentDeleteId = null;

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#dataTable tbody tr');
    const mobileCards = document.querySelectorAll('.student-card');
    const clearBtn = document.querySelector('.search-clear');

    clearBtn.style.display = searchTerm ? 'block' : 'none';

    // Filter table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Department filter
document.getElementById('departmentFilter').addEventListener('change', function() {
    const selectedDept = this.value;
    const tableRows = document.querySelectorAll('#dataTable tbody tr[data-department]');
    const mobileCards = document.querySelectorAll('.student-card[data-department]');

    // Filter table rows
    tableRows.forEach(row => {
        const dept = row.getAttribute('data-department');
        row.style.display = (!selectedDept || dept === selectedDept) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const dept = card.getAttribute('data-department');
        card.style.display = (!selectedDept || dept === selectedDept) ? '' : 'none';
    });
});

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.querySelector('.search-clear').style.display = 'none';
    document.querySelectorAll('#dataTable tbody tr, .student-card').forEach(el => {
        el.style.display = '';
    });
}

async function deleteStudent(id) {
    const result = await Swal.fire({
        title: 'Delete Student?',
        text: 'Are you sure you want to delete this student? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        // Show loading state
        Swal.fire({
            title: 'Deleting...',
            text: 'Please wait while we delete the student.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/superadmin/students/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewStudent(id) {
    // Implement view functionality
    alert('View student details for ID: ' + id);
}

function refreshData() {
    location.reload();
}

function toggleCardMenu(button) {
    const dropdown = button.nextElementSibling;
    dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.card-menu')) {
        document.querySelectorAll('.card-menu-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Mobile responsiveness
function handleResize() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const desktopView = document.querySelector('.desktop-view');
    const mobileView = document.querySelector('.mobile-view');

    if (window.innerWidth <= 1024) {
        mobileMenuBtn.style.display = 'block';
    } else {
        mobileMenuBtn.style.display = 'none';
        document.querySelector('.sidebar').classList.remove('open');
    }

    if (window.innerWidth <= 768) {
        desktopView.style.display = 'none';
        mobileView.style.display = 'block';
    } else {
        desktopView.style.display = 'block';
        mobileView.style.display = 'none';
    }
}

window.addEventListener('resize', handleResize);
handleResize();

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

// Checkbox and Bulk Delete Functions
let selectedStudents = new Set();

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedStudents.add(cb.value);
            cb.closest('tr').classList.add('selected');
        } else {
            selectedStudents.delete(cb.value);
            cb.closest('tr').classList.remove('selected');
        }
    });
    updateBulkActionsBar();
}

function toggleRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        selectedStudents.add(checkbox.value);
        row.classList.add('selected');
    } else {
        selectedStudents.delete(checkbox.value);
        row.classList.remove('selected');
        document.getElementById('selectAll').checked = false;
    }
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const count = selectedStudents.size;
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountEl = document.getElementById('selectedCount');
    
    selectedCountEl.textContent = count;
    
    if (count > 0) {
        bulkActionsBar.classList.remove('bulk-actions-hidden');
    } else {
        bulkActionsBar.classList.add('bulk-actions-hidden');
    }
}

function clearSelection() {
    selectedStudents.clear();
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.querySelectorAll('.student-row').forEach(row => {
        row.classList.remove('selected');
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActionsBar();
}

async function bulkDeleteStudents() {
    const count = selectedStudents.size;
    if (count === 0) {
        Swal.fire({
            title: 'No Selection',
            text: 'Please select at least one student to delete.',
            icon: 'info'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Delete Multiple Students',
        html: `Are you sure you want to delete <strong>${count}</strong> student(s)?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, Delete ${count} Student(s)`,
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        // Show loading
        Swal.fire({
            title: 'Deleting...',
            text: `Please wait while we delete ${count} student(s).`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Convert Set to Array
        const studentIds = Array.from(selectedStudents);

        try {
            // Make bulk delete request
            const response = await fetch('{{ route("superadmin.students.bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ student_ids: studentIds })
            });

            const data = await response.json();

            if (data.success) {
                // Remove the rows from the table
                studentIds.forEach(studentId => {
                    const row = document.getElementById(`student-row-${studentId}`);
                    if (row) {
                        row.remove();
                    }
                });
                
                clearSelection();
                
                await Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload to update statistics
                location.reload();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete students.',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while deleting the students.',
                icon: 'error'
            });
        }
    }
}
</script>

<style>
/* Enhanced Header */
.enhanced-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-text {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
}

.header-text h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Modern Buttons */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-modern:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.btn-secondary {
    background: linear-gradient(135deg, #a8edea, #fed6e3);
    color: #333;
    box-shadow: 0 4px 15px rgba(168, 237, 234, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--accent-color);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.stat-primary { --accent-color: #667eea; }
.stat-info { --accent-color: #4facfe; }
.stat-warning { --accent-color: #f093fb; }
.stat-success { --accent-color: #4facfe; }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    background: var(--accent-color);
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.875rem;
}

.stat-trend {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #22c55e;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Enhanced Table */
.enhanced-table-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.table-header-modern {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.table-title h2 {
    margin: 0;
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
}

.table-title p {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}

.table-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.search-container-modern {
    position: relative;
    flex: 1;
    min-width: 300px;
}

.search-container-modern i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.search-input-modern {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.search-input-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-controls {
    display: flex;
    gap: 0.5rem;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
}

/* Department Badges */
.department-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.department-bsit {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.department-bsba {
    background: linear-gradient(135deg, #10b981, #047857);
    color: white;
}

.department-beed {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.department-bshm {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

/* Year Level Badge */
.year-level-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    text-transform: uppercase;
}

/* Enhanced Table Styles */
.enhanced-table {
    width: 100%;
    border-collapse: collapse;
}

.enhanced-table th {
    background: #f8fafc;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.enhanced-table td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.table-row-enhanced:hover {
    background: #f8fafc;
}

/* Checkbox Styles */
.checkbox-cell {
    width: 50px;
    text-align: center;
}

.checkbox-cell input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
}

.enhanced-table tbody tr.selected {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%) !important;
    border-left: 4px solid #667eea;
}

/* Bulk Actions */
.bulk-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    border-bottom: 1px solid #e5e7eb;
}

.bulk-actions-hidden {
    display: none;
}

.selected-count {
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.selected-count .count {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 700;
}

.bulk-delete-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.bulk-delete-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6);
}

.clear-selection-btn {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(107, 114, 128, 0.4);
}

.clear-selection-btn:hover {
    background: linear-gradient(135deg, #4b5563, #374151);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(107, 114, 128, 0.6);
}

.id-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    overflow: hidden;
    position: relative;
}

.profile-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.initials {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
}

.user-role {
    font-size: 0.75rem;
    color: #64748b;
}

.email-link {
    color: #667eea;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.3s ease;
}

.email-link:hover {
    color: #4f46e5;
}

.date-primary {
    font-weight: 600;
    color: #1e293b;
}

.date-secondary {
    font-size: 0.75rem;
    color: #64748b;
}

.action-buttons-modern {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-edit {
    background: #fff3e0;
    color: #f57c00;
}

.btn-edit:hover {
    background: #ffe0b2;
    transform: scale(1.1);
}

.btn-delete {
    background: #ffebee;
    color: #d32f2f;
}

.btn-delete:hover {
    background: #ffcdd2;
    transform: scale(1.1);
}

/* Mobile View */
.mobile-view {
    display: none;
    padding: 1rem;
}

.student-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.student-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    position: relative;
}

.user-avatar-large {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    overflow: hidden;
    position: relative;
}

.profile-image-large {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.initials-large {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.card-title h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.125rem;
    font-weight: 600;
}

.card-title p {
    margin: 0.25rem 0;
    color: #64748b;
    font-size: 0.875rem;
}

.card-menu {
    margin-left: auto;
    position: relative;
}

.btn-menu {
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.card-menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 120px;
    z-index: 10;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.card-menu-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.card-menu-dropdown a,
.card-menu-dropdown button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    color: #374151;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

.card-menu-dropdown a:hover,
.card-menu-dropdown button:hover {
    background: #f3f4f6;
}

.card-content {
    padding: 1rem 1.5rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #64748b;
    font-size: 0.875rem;
}

.info-row i {
    width: 16px;
    color: #9ca3af;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: #f8fafc;
}

.btn-card {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-card.btn-primary {
    background: #667eea;
    color: white;
}

.btn-card.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

/* Alert Modern */
.alert-modern {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border: 1px solid #86efac;
}

.alert-icon {
    font-size: 1.25rem;
    color: #059669;
}

.alert-content h4 {
    margin: 0;
    color: #065f46;
    font-weight: 600;
}

.alert-content p {
    margin: 0;
    color: #047857;
}

.alert-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: none;
    border: none;
    color: #059669;
    cursor: pointer;
    font-size: 1rem;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 90%;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-bottom: 1px solid #f87171;
}

.modal-header h3 {
    margin: 0;
    color: #dc2626;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8fafc;
}

/* Empty State */
.empty-state-modern {
    text-align: center;
    padding: 3rem 2rem;
    color: #64748b;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-modern h3 {
    margin: 0 0 0.5rem 0;
    color: #374151;
}

.empty-state-mobile {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}

.empty-state-mobile i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .mobile-menu-btn {
        display: block !important;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .desktop-view {
        display: none !important;
    }

    .mobile-view {
        display: block !important;
    }

    .enhanced-header {
        padding: 1.5rem;
    }

    .header-text h1 {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .table-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .search-container-modern {
        min-width: auto;
    }

    .filter-controls {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .enhanced-header {
        padding: 1rem;
    }

    .header-text {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }

    .card-actions {
        flex-direction: column;
    }
}
</style>
@endsection
