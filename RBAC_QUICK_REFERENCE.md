# RBAC Quick Reference Guide

## Route Protection Patterns

### Basic Authentication
```php
Route::middleware('auth')->group(function () {
    // Only authenticated users
});
```

### Role-Based Protection
```php
Route::middleware([SuperAdminAuth::class])->group(function () {
    // Only SuperAdmins
});

Route::middleware([DepartmentAdminAuth::class])->group(function () {
    // Only Department Admins
});

Route::middleware([OfficeAdminAuth::class])->group(function () {
    // Only Office Admins
});
```

### Permission-Based Protection
```php
Route::middleware('can:create-announcements')->post('announcements', ...);
Route::middleware('can:edit-announcements')->put('announcements/{id}', ...);
Route::middleware('can:delete-announcements')->delete('announcements/{id}', ...);
```

### Combined Protection
```php
Route::middleware(['auth:admin', 'session.security', 'can:view-admin-dashboard'])->group(function () {
    // Authenticated admin with specific permission
});
```

## Permission Checklist

### SuperAdmin Permissions
- ✅ `view-superadmin-dashboard`
- ✅ `manage-admins`
- ✅ `manage-department-admins`
- ✅ `manage-office-admins`
- ✅ `view-admin-access-logs`
- ✅ `delete-admin-access-logs`
- ✅ `manage-admin-profiles`
- ✅ `create-announcements`, `edit-announcements`, `delete-announcements`
- ✅ `create-events`, `edit-events`, `delete-events`
- ✅ `create-news`, `edit-news`, `delete-news`
- ✅ `manage-faculty`, `manage-students`

### Department Admin Permissions
- ✅ `view-admin-dashboard`
- ✅ `create-announcements`, `edit-announcements`, `delete-announcements`
- ✅ `create-events`, `edit-events`, `delete-events`
- ✅ `create-news`, `edit-news`, `delete-news`
- ✅ `manage-faculty`, `manage-students`

### Office Admin Permissions
- ✅ `view-office-admin-dashboard`
- ✅ `create-announcements`, `edit-announcements`, `delete-announcements`
- ✅ `create-events`, `edit-events`, `delete-events`
- ✅ `create-news`, `edit-news`, `delete-news`

### Student/Faculty Permissions
- ✅ `view-user-dashboard`
- ✅ `update-user-profile`
- ✅ `upload-user-profile-picture`
- ✅ `view-user-notifications`
- ✅ `mark-notifications-read`
- ✅ `create-comments`
- ✅ `update-own-comments`
- ✅ `delete-own-comments`
- ✅ `view-announcements`, `view-events`, `view-news`
- ✅ `view-public-content`

## Controller Authorization

### Using authorize() Method
```php
public function store(Request $request)
{
    $this->authorize('create-announcements');
    // Create logic here
}

public function update(Request $request, Announcement $announcement)
{
    $this->authorize('edit-announcements');
    // Update logic here
}

public function destroy(Announcement $announcement)
{
    $this->authorize('delete-announcements');
    // Delete logic here
}
```

### Using can() Method
```php
if ($user->can('create-announcements')) {
    // Allow creation
}

if (!$user->can('delete-announcements')) {
    abort(403, 'Unauthorized');
}
```

## Blade Template Authorization

### Using @can Directive
```blade
@can('create-announcements')
    <button>Create Announcement</button>
@endcan

@cannot('delete-announcements')
    <p>You cannot delete announcements</p>
@endcannot

@canany(['edit-announcements', 'delete-announcements'])
    <div>Admin Actions</div>
@endcanany
```

## Role Checking

### Check Single Role
```php
if ($user->hasRole('superadmin')) {
    // SuperAdmin logic
}

if ($admin->isSuperAdmin()) {
    // SuperAdmin logic
}

if ($admin->isDepartmentAdmin()) {
    // Department Admin logic
}

if ($admin->isOfficeAdmin()) {
    // Office Admin logic
}
```

### Check Multiple Roles
```php
if ($user->hasAnyRole(['superadmin', 'department_admin'])) {
    // Admin logic
}

if ($user->hasAllRoles(['superadmin', 'admin'])) {
    // Has both roles
}
```

## Common RBAC Patterns

### Protecting a Resource
```php
Route::middleware('can:view-announcements')->get('announcements', ...);
Route::middleware('can:create-announcements')->post('announcements', ...);
Route::middleware('can:edit-announcements')->put('announcements/{id}', ...);
Route::middleware('can:delete-announcements')->delete('announcements/{id}', ...);
```

### Protecting Admin Routes
```php
Route::prefix('admin')->middleware([
    'auth:admin',
    'session.security',
    'can:view-admin-dashboard'
])->group(function () {
    // Admin routes
});
```

### Protecting User Routes
```php
Route::prefix('user')->middleware([
    'auth',
    'password.expiration',
    'session.security',
    'can:view-user-dashboard'
])->group(function () {
    // User routes
});
```

## Debugging RBAC

### Check User Permissions
```php
$user = auth()->user();
$permissions = $user->getAllPermissions();
$roles = $user->getRoleNames();

dd([
    'permissions' => $permissions,
    'roles' => $roles,
    'can_create_announcements' => $user->can('create-announcements')
]);
```

### Check Admin Permissions
```php
$admin = auth('admin')->user();
$permissions = $admin->getAllPermissions();
$roles = $admin->getRoleNames();

dd([
    'permissions' => $permissions,
    'roles' => $roles,
    'is_superadmin' => $admin->isSuperAdmin(),
    'can_manage_admins' => $admin->can('manage-admins')
]);
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| 403 Forbidden | Check user has required permission |
| Permission not working | Run `php artisan db:seed --class=RolePermissionSeeder` |
| Role not assigned | Check user model has `HasRoles` trait |
| Cache issues | Run `php artisan cache:clear` |
| Middleware not applied | Verify middleware is registered in `Kernel.php` |

## Testing RBAC

```php
// Test permission check
$this->actingAs($user)
    ->post('/announcements', $data)
    ->assertStatus(403);

// Test with permission
$user->givePermissionTo('create-announcements');
$this->actingAs($user)
    ->post('/announcements', $data)
    ->assertStatus(201);
```

## Audit Logging

Always log sensitive operations:

```php
\Log::warning('Sensitive action performed', [
    'user_id' => auth()->id(),
    'action' => 'delete-announcement',
    'resource_id' => $announcement->id,
    'timestamp' => now()
]);
```

