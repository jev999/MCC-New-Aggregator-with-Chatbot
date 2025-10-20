<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>View Admin - Super Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .sidebar-header h3 i {
            color: #ffd700;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin: 0.5rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            gap: 0.75rem;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover i,
        .sidebar-menu a.active i {
            transform: scale(1.1);
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #ffffff;
            min-height: 100vh;
        }

        .header {
            background: #ffffff;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e5e7eb;
        }

        .header h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header h1 i {
            color: #ff6b6b;
            font-size: 2.2rem;
        }

        .detail-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
        }

        .admin-profile {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .admin-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .admin-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
            cursor: pointer;
        }

        .admin-avatar:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-overlay i {
            color: white;
            font-size: 1.5rem;
        }

        .profile-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .profile-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-btn-upload {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .profile-btn-remove {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        #profilePictureInput {
            display: none;
        }

        .admin-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .admin-role {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-super {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .role-admin {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .detail-grid {
            display: grid;
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-value {
            color: #666;
            font-weight: 500;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .current-user-badge {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-crown"></i> Super Admin Panel</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('superadmin.dashboard') }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a></li>
                <li><a href="{{ route('superadmin.admins.index') }}" class="active">
                    <i class="fas fa-users-cog"></i> Department Admin Management
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
                <li><a href="{{ route('superadmin.students.index') }}">
                    <i class="fas fa-user-graduate"></i> Students
                </a></li>
                <li>
                    <form method="POST" action="{{ route('superadmin.logout') }}" style="display: inline; width: 100%;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #cbd5e1; padding: 0.875rem 1.5rem; width: 100%; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; transition: all 0.2s ease;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-eye"></i> Admin Details</h1>
                <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="detail-container">
                <div class="admin-profile">
                    <div class="admin-avatar" onclick="document.getElementById('profilePictureInput').click()">
                        @if($admin->hasProfilePicture)
                            <img src="{{ $admin->profilePictureUrl }}" alt="{{ $admin->username }}" id="profileImage">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        @else
                            @if($admin->isSuperAdmin())
                                <i class="fas fa-crown"></i>
                            @else
                                <i class="fas fa-user-shield"></i>
                            @endif
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        @endif
                    </div>
                    <input type="file" id="profilePictureInput" accept="image/*" onchange="uploadProfilePicture(this)">
                    <div class="profile-actions">
                        <button class="profile-btn profile-btn-upload" onclick="document.getElementById('profilePictureInput').click()">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                        @if($admin->hasProfilePicture)
                            <button class="profile-btn profile-btn-remove" onclick="removeProfilePicture()">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        @endif
                    </div>
                    <div class="admin-name">
                        {{ $admin->username }}
                        @if($admin->id === auth('admin')->id())
                            <span class="current-user-badge">You</span>
                        @endif
                    </div>
                    <div class="admin-role {{ $admin->isSuperAdmin() ? 'role-super' : 'role-admin' }}">
                        @if($admin->isSuperAdmin())
                            <i class="fas fa-crown"></i> Super Administrator
                        @else
                            <i class="fas fa-user-shield"></i> Administrator
                        @endif
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-id-badge"></i> Admin ID
                        </div>
                        <div class="detail-value">#{{ $admin->id }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-user"></i> Username
                        </div>
                        <div class="detail-value">{{ $admin->username }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-shield-alt"></i> Role
                        </div>
                        <div class="detail-value">
                            {{ $admin->isSuperAdmin() ? 'Super Administrator' : 'Administrator' }}
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-plus"></i> Created
                        </div>
                        <div class="detail-value">
                            @if($admin->created_at)
                                {{ $admin->created_at->format('F d, Y \a\t g:i A') }}
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-clock"></i> Last Updated
                        </div>
                        <div class="detail-value">
                            @if($admin->updated_at)
                                {{ $admin->updated_at->format('F d, Y \a\t g:i A') }}
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-chart-bar"></i> Content Created
                        </div>
                        <div class="detail-value">
                            {{ $admin->announcements->count() + $admin->events->count() + $admin->news->count() }} items
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Admin
                    </a>
                    
                   
                </div>
            </div>
        </div>
    </div>

<style>
    .text-muted {
        color: #6b7280 !important;
        font-style: italic;
    }
</style>

<script>
    // Set up CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function uploadProfilePicture(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.match('image.*')) {
            Swal.fire({
                title: 'Invalid File!',
                text: 'Please select a valid image file.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                title: 'File Too Large!',
                text: 'Please select an image smaller than 5MB.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we upload the profile picture.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('profile_picture', file);

        fetch(`/superadmin/admins/{{ $admin->id }}/upload-picture`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload page to reflect changes
                    window.location.reload();
                });
            } else {
                let errorMessage = 'Error uploading profile picture.';
                if (data.errors && data.errors.profile_picture) {
                    errorMessage = data.errors.profile_picture[0];
                } else if (data.message) {
                    errorMessage = data.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error uploading profile picture:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Error uploading profile picture. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        });

        // Clear the input
        input.value = '';
    }

    function removeProfilePicture() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will remove the current profile picture.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Removing...',
                    text: 'Please wait while we remove the profile picture.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch(`/superadmin/admins/{{ $admin->id }}/remove-picture`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Removed!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload page to reflect changes
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Error removing profile picture.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error removing profile picture:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error removing profile picture. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }
</script>

</body>
</html>
