<?php

/**
 * ================================================================================
 * RBAC ROUTE PROTECTION EXAMPLES
 * ================================================================================
 * 
 * This file contains examples of how to protect routes using Role-Based Access Control.
 * Copy these examples to your routes/web.php file and adapt them to your needs.
 * 
 * Available Middleware:
 * - role: Check if user has specific role(s)
 * - permission: Check if user has specific permission(s)
 * - role_or_permission: Check if user has role OR permission
 */

// ================================================================================
// EXAMPLE 1: Protect Routes by Role
// ================================================================================

// Single role requirement
Route::middleware(['auth:admin', 'role:Super Administrator'])->group(function () {
    Route::get('/superadmin/settings', [SettingsController::class, 'index'])->name('superadmin.settings');
    Route::get('/superadmin/backups', [BackupController::class, 'index'])->name('superadmin.backups');
});

// Multiple roles (user must have ANY ONE of these roles)
Route::middleware(['auth:admin', 'role:Super Administrator,Department Administrator'])->group(function () {
    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students');
    Route::get('/admin/faculty', [AdminFacultyController::class, 'index'])->name('admin.faculty');
});

// ================================================================================
// EXAMPLE 2: Protect Routes by Permission
// ================================================================================

// Single permission requirement
Route::middleware(['auth:admin', 'permission:view-students'])->group(function () {
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
});

// Different permissions for different actions
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/students', [StudentController::class, 'index'])
        ->middleware('permission:view-students')
        ->name('students.index');
    
    Route::post('/students', [StudentController::class, 'store'])
        ->middleware('permission:create-students')
        ->name('students.store');
    
    Route::put('/students/{student}', [StudentController::class, 'update'])
        ->middleware('permission:edit-students')
        ->name('students.update');
    
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])
        ->middleware('permission:delete-students')
        ->name('students.destroy');
    
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDestroy'])
        ->middleware('permission:bulk-delete-students')
        ->name('students.bulk-delete');
});

// ================================================================================
// EXAMPLE 3: Protect Routes by Role OR Permission
// ================================================================================

// User needs Super Administrator role OR view-dashboard permission
Route::middleware(['auth:admin', 'role_or_permission:Super Administrator|view-dashboard'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ================================================================================
// EXAMPLE 4: Complex RBAC Structure - Content Management
// ================================================================================

Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Announcements - Role or Permission based
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])
            ->middleware('role_or_permission:Super Administrator,Content Manager|view-announcements')
            ->name('index');
        
        Route::get('/create', [AnnouncementController::class, 'create'])
            ->middleware('permission:create-announcements')
            ->name('create');
        
        Route::post('/', [AnnouncementController::class, 'store'])
            ->middleware('permission:create-announcements')
            ->name('store');
        
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])
            ->middleware('permission:edit-announcements')
            ->name('edit');
        
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])
            ->middleware('permission:edit-announcements')
            ->name('update');
        
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])
            ->middleware('permission:delete-announcements')
            ->name('destroy');
    });
    
    // Events - Similar structure
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])
            ->middleware('permission:view-events')
            ->name('index');
        
        Route::get('/create', [EventController::class, 'create'])
            ->middleware('permission:create-events')
            ->name('create');
        
        Route::post('/', [EventController::class, 'store'])
            ->middleware('permission:create-events')
            ->name('store');
        
        Route::get('/{event}/edit', [EventController::class, 'edit'])
            ->middleware('permission:edit-events')
            ->name('edit');
        
        Route::put('/{event}', [EventController::class, 'update'])
            ->middleware('permission:edit-events')
            ->name('update');
        
        Route::delete('/{event}', [EventController::class, 'destroy'])
            ->middleware('permission:delete-events')
            ->name('destroy');
    });
});

// ================================================================================
// EXAMPLE 5: Super Admin Only Routes
// ================================================================================

Route::middleware(['auth:admin', 'role:Super Administrator'])->prefix('superadmin')->name('superadmin.')->group(function () {
    
    // Admin Management
    Route::resource('admins', SuperAdminController::class);
    Route::resource('department-admins', DepartmentAdminController::class);
    Route::resource('office-admins', OfficeAdminController::class);
    
    // Access Logs
    Route::get('admin-access', [AdminAccessController::class, 'index'])->name('admin-access');
    Route::delete('admin-access/{id}', [AdminAccessController::class, 'destroy'])->name('admin-access.delete');
    Route::post('admin-access/bulk-delete', [AdminAccessController::class, 'bulkDestroy'])->name('admin-access.bulk-delete');
    
    // Backups
    Route::get('backup', [BackupController::class, 'index'])->name('backup');
    Route::post('backup/create', [BackupController::class, 'create'])->name('backup.create');
    Route::get('backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{filename}', [BackupController::class, 'delete'])->name('backup.delete');
    
    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

// ================================================================================
// EXAMPLE 6: Department-Specific Routes
// ================================================================================

Route::middleware(['auth:admin', 'role:Department Administrator'])->prefix('department')->name('department.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DepartmentDashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');
    
    // Department Students (own department only)
    Route::get('/students', [DepartmentStudentController::class, 'index'])
        ->middleware('permission:view-own-department-students')
        ->name('students.index');
    
    Route::get('/students/{student}/edit', [DepartmentStudentController::class, 'edit'])
        ->middleware('permission:edit-own-department-students')
        ->name('students.edit');
    
    // Department Content
    Route::resource('announcements', DepartmentAnnouncementController::class)
        ->middleware('permission:manage-own-department');
});

// ================================================================================
// EXAMPLE 7: Office-Specific Routes
// ================================================================================

Route::middleware(['auth:admin', 'role:Office Administrator'])->prefix('office')->name('office.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [OfficeDashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');
    
    // Office Content
    Route::get('/content', [OfficeContentController::class, 'index'])
        ->middleware('permission:view-own-office-content')
        ->name('content.index');
    
    Route::resource('announcements', OfficeAnnouncementController::class)
        ->middleware('permission:manage-own-office');
});

// ================================================================================
// EXAMPLE 8: Mixed Access Routes
// ================================================================================

// Routes accessible by multiple types of admins
Route::middleware(['auth:admin'])->group(function () {
    
    // Profile routes - accessible by all authenticated admins
    Route::get('/profile', [ProfileController::class, 'show'])
        ->middleware('permission:view-own-profile')
        ->name('profile.show');
    
    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:edit-own-profile')
        ->name('profile.update');
    
    // Students - different permissions for different users
    Route::get('/students', [StudentController::class, 'index'])
        ->middleware('role_or_permission:Super Administrator,Student Affairs Manager|view-students')
        ->name('students.index');
});

// ================================================================================
// EXAMPLE 9: API Routes with RBAC
// ================================================================================

// In routes/api.php
Route::middleware(['auth:admin-api', 'throttle:60,1'])->prefix('admin')->group(function () {
    
    // API endpoints with permission checks
    Route::get('/students', [Api\StudentController::class, 'index'])
        ->middleware('permission:view-students');
    
    Route::post('/students', [Api\StudentController::class, 'store'])
        ->middleware('permission:create-students');
    
    Route::delete('/students/bulk', [Api\StudentController::class, 'bulkDestroy'])
        ->middleware('permission:bulk-delete-students');
});

// ================================================================================
// EXAMPLE 10: Dynamic Role Assignment Routes
// ================================================================================

Route::middleware(['auth:admin', 'role:Super Administrator'])->prefix('roles')->name('roles.')->group(function () {
    
    // Role Management
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    
    // Assign Roles to Users
    Route::post('/assign', [RoleController::class, 'assignRole'])
        ->middleware('permission:assign-roles')
        ->name('assign');
    
    // Permission Management
    Route::post('/permissions/assign', [PermissionController::class, 'assignPermission'])
        ->middleware('permission:assign-permissions')
        ->name('permissions.assign');
});

// ================================================================================
// NOTES:
// ================================================================================
// 
// 1. Always place more specific routes before general ones
// 2. Use middleware groups for better organization
// 3. Combine 'auth:admin' with RBAC middleware
// 4. Use descriptive route names for clarity
// 5. Log unauthorized access attempts (done automatically by middleware)
// 6. Test all protected routes after implementation
// 7. Document any custom permissions you create
// 8. Regular security audits of route permissions
// 9. Use resource controllers where appropriate
// 10. Keep related routes grouped together
