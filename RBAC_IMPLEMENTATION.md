# Role-Based Access Control (RBAC) Implementation

## Overview
This project now implements a comprehensive Role-Based Access Control (RBAC) system using the `spatie/laravel-permission` package. This provides fine-grained access control based on user roles and permissions.

## Implementation Details

### 1. Package Installation
- **Package**: `spatie/laravel-permission` v6.21.0
- **Configuration**: Published to `config/permission.php`
- **Migrations**: Custom migration created for permission tables

### 2. Database Structure
The following tables were created:
- `permissions` - Stores individual permissions
- `roles` - Stores user roles
- `model_has_permissions` - Links models to permissions
- `model_has_roles` - Links models to roles
- `role_has_permissions` - Links roles to permissions

### 3. Models Updated
Both `User` and `Admin` models now use the `HasRoles` trait from spatie/laravel-permission:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    // ...
}

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    // ...
}
```

### 4. Roles Defined
The system defines the following roles:

#### For Users (web guard):
- **student** - Regular students
- **faculty** - Faculty members

#### For Admins (admin guard):
- **department_admin** - Department administrators
- **office_admin** - Office administrators  
- **superadmin** - Super administrators

### 5. Permissions Structure
Permissions are organized by functionality:

#### User Permissions:
- `view-user-dashboard`
- `update-user-profile`
- `upload-user-profile-picture`
- `view-user-notifications`
- `mark-notifications-read`
- `create-comments`
- `update-own-comments`
- `delete-own-comments`
- `view-announcements`
- `view-events`
- `view-news`
- `view-public-content`

#### Admin Permissions:
- `view-admin-dashboard`
- `manage-announcements`
- `create-announcements`
- `edit-announcements`
- `delete-announcements`
- `publish-announcements`
- `manage-events`
- `create-events`
- `edit-events`
- `delete-events`
- `publish-events`
- `manage-news`
- `create-news`
- `edit-news`
- `delete-news`
- `publish-news`

#### Department Admin Permissions:
- `manage-department-content`
- `view-department-students`
- `edit-department-students`
- `delete-department-students`
- `view-department-faculty`
- `edit-department-faculty`
- `delete-department-faculty`

#### Office Admin Permissions:
- `manage-office-content`
- `view-office-students`
- `edit-office-students`

#### Super Admin Permissions:
- `view-superadmin-dashboard`
- `manage-all-content`
- `manage-admins`
- `create-admins`
- `edit-admins`
- `delete-admins`
- `manage-department-admins`
- `create-department-admins`
- `edit-department-admins`
- `delete-department-admins`
- `manage-office-admins`
- `create-office-admins`
- `edit-office-admins`
- `delete-office-admins`
- `manage-all-students`
- `create-students`
- `edit-students`
- `delete-students`
- `manage-all-faculty`
- `create-faculty`
- `edit-faculty`
- `delete-faculty`
- `view-system-logs`
- `manage-system-settings`

### 6. Middleware Implementation
The system uses Laravel's built-in `can` middleware which integrates seamlessly with spatie/laravel-permission:

#### Using Permission-based Middleware
```php
Route::middleware(['auth', 'can:view-user-dashboard'])->group(function () {
    // Routes accessible by users with specific permission
});

Route::middleware(['auth:admin', 'can:view-admin-dashboard'])->group(function () {
    // Admin routes accessible by admins with specific permission
});
```

#### Custom Middleware (Available but not used)
Two custom middleware classes were also created for more granular control:

#### RoleMiddleware
```php
Route::middleware(['auth', 'role:student,faculty'])->group(function () {
    // Routes accessible by students and faculty
});
```

#### PermissionMiddleware
```php
Route::middleware(['auth', 'permission:view-user-dashboard'])->group(function () {
    // Routes accessible by users with specific permission
});
```

### 7. Route Protection
All routes have been updated to use permission-based middleware:

#### User Routes
```php
Route::middleware(['auth', 'can:view-user-dashboard'])->group(function () {
    // User dashboard and functionality
});
```

#### Admin Routes
```php
Route::middleware(['auth:admin', 'can:view-admin-dashboard'])->group(function () {
    // Department admin functionality
});
```

#### Super Admin Routes
```php
Route::middleware(['auth:admin', 'can:view-superadmin-dashboard'])->group(function () {
    // Super admin functionality
});
```

### 8. Usage Examples

#### Checking Roles
```php
// Check if user has a specific role
if ($user->hasRole('student')) {
    // User is a student
}

// Check if user has any of multiple roles
if ($user->hasAnyRole(['student', 'faculty'])) {
    // User is either student or faculty
}
```

#### Checking Permissions
```php
// Check if user has a specific permission
if ($user->can('view-user-dashboard')) {
    // User can view dashboard
}

// Check if user has any of multiple permissions
if ($user->hasAnyPermission(['create-announcements', 'edit-announcements'])) {
    // User can manage announcements
}
```

#### Assigning Roles
```php
// Assign a role to a user
$user->assignRole('student');

// Assign multiple roles
$user->assignRole(['student', 'faculty']);
```

#### Assigning Permissions
```php
// Assign a permission to a user
$user->givePermissionTo('view-user-dashboard');

// Assign multiple permissions
$user->givePermissionTo(['view-user-dashboard', 'update-user-profile']);
```

### 9. Seeder
The `RolePermissionSeeder` creates all roles and permissions and assigns them to existing users based on their current role field.

### 10. Benefits
- **Fine-grained access control**: Permissions can be assigned at a granular level
- **Flexible role management**: Roles can be easily modified or new ones added
- **Scalable**: Easy to add new permissions and roles as the system grows
- **Secure**: Centralized permission checking prevents unauthorized access
- **Maintainable**: Clear separation of concerns between authentication and authorization

### 11. Migration from Old System
The implementation maintains backward compatibility by:
- Preserving existing role fields in the database
- Automatically assigning spatie roles based on existing role values
- Gradually migrating from custom middleware to role-based middleware

### 12. Testing
The system has been tested to ensure:
- Roles are properly assigned to users and admins
- Permissions are correctly associated with roles
- Middleware properly restricts access based on roles
- Both web and admin guards work correctly

## Conclusion
The RBAC implementation provides a robust, scalable, and maintainable access control system that enhances the security and flexibility of the application while maintaining backward compatibility with existing functionality.
