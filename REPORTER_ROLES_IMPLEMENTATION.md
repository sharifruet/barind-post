# Reporter Roles Implementation

## Overview
This implementation adds a comprehensive reporter role management system to the Barind Post news website. Users can now be assigned multiple reporter roles, and when drafting news articles, they can choose from their assigned roles.

## Database Changes

### 1. News Table Update
- Added `reporterRole` field (VARCHAR(100)) to replace the old `reporter` field
- Field is positioned after `lead_text` for better organization

### 2. New Tables Created

#### reporter_roles Table
```sql
CREATE TABLE reporter_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### user_reporter_roles Table (Many-to-Many Relationship)
```sql
CREATE TABLE user_reporter_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reporter_role_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_role_id) REFERENCES reporter_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, reporter_role_id)
);
```

### 3. Default Reporter Roles
The system comes with 19 pre-configured reporter roles covering different regions and specialties:
- নিজস্ব প্রতিবেদক (Main staff reporter)
- আন্তর্জাতিক ডেস্ক (International desk reporter)
- নাটোর প্রতিনিধি (Natore correspondent)
- রাজশাহী প্রতিনিধি (Rajshahi correspondent)
- And 15 more regional correspondents

## Code Changes

### 1. New Model: ReporterRoleModel
- `getActiveRoles()` - Get all active reporter roles
- `getUserRoles($userId)` - Get roles assigned to a specific user
- `assignRolesToUser($userId, $roleIds)` - Assign roles to a user
- `userHasRole($userId, $roleId)` - Check if user has a specific role
- `getRoleByName($name)` - Get role by name

### 2. Updated NewsModel
- Changed `allowedFields` to use `reporterRole` instead of `reporter`

### 3. Admin Controller Updates
Added new methods for reporter role management:
- `reporterRoles()` - List all reporter roles
- `addReporterRole()` - Add new reporter role
- `deleteReporterRole()` - Delete reporter role
- `editReporterRole($id)` - Edit reporter role
- `updateReporterRole($id)` - Update reporter role
- `assignReporterRoles($userId)` - Assign roles to user
- `saveReporterRoleAssignment($userId)` - Save role assignments

### 4. New Views
- `admin/reporter_roles.php` - Main reporter roles management page
- `admin/reporter_role_edit.php` - Edit reporter role form
- `admin/assign_reporter_roles.php` - Assign roles to users

### 5. Updated Views
- `admin/sidebar.php` - Added "Reporter Roles" menu item
- `admin/users.php` - Added "Assign Roles" button for each user
- `admin/news_form.php` - Updated to use reporterRole field with user-specific role options
- `public/news.php` - Updated to display reporterRole instead of reporter

### 6. Routes Added
```php
// Reporter Roles Routes
$routes->get('/admin/reporter-roles', 'Admin::reporterRoles');
$routes->post('/admin/reporter-roles/add', 'Admin::addReporterRole');
$routes->post('/admin/reporter-roles/delete', 'Admin::deleteReporterRole');
$routes->get('/admin/reporter-roles/edit/(:num)', 'Admin::editReporterRole/$1');
$routes->post('/admin/reporter-roles/edit/(:num)', 'Admin::updateReporterRole/$1');
$routes->get('/admin/reporter-roles/assign/(:num)', 'Admin::assignReporterRoles/$1');
$routes->post('/admin/reporter-roles/assign/(:num)', 'Admin::saveReporterRoleAssignment/$1');
```

## Features

### 1. Role Management
- Admins can create, edit, and delete reporter roles
- Roles can be activated/deactivated
- Each role has a name and description

### 2. User Role Assignment
- Admins can assign multiple reporter roles to users
- Users can have different roles for different types of reporting
- Many-to-many relationship allows flexible role assignment

### 3. News Creation with Roles
- When creating news, users see only their assigned reporter roles
- Admins and editors see all active roles
- The selected role is saved with the news article

### 4. Role-Based Access Control
- Reporters can only see and select their assigned roles
- Admins and editors have access to all roles
- Proper permission checks throughout the system

## Usage

### For Admins
1. Go to Admin → Reporter Roles to manage roles
2. Go to Admin → Users → Assign Roles to assign roles to users
3. Create/edit news articles with any reporter role

### For Reporters
1. Contact admin to get reporter roles assigned
2. When creating news, select from your assigned roles
3. Only your assigned roles will appear in the dropdown

### For Editors
1. Can see all active reporter roles when creating news
2. Can assign roles to users (if they have admin permissions)

## Benefits

1. **Flexibility**: Users can have multiple reporter roles
2. **Organization**: Clear separation of different reporting responsibilities
3. **Scalability**: Easy to add new roles and regions
4. **SEO Friendly**: Bengali role names are more SEO-friendly
5. **User Experience**: Users only see relevant roles
6. **Maintainability**: Centralized role management

## Database Migration
The changes are applied via the updated `dbscript.sql` file, which includes:
- ALTER TABLE statement to add reporterRole column
- CREATE TABLE statements for new tables
- INSERT statements for default roles
- Sample user-role assignments

## Testing
All functionality has been tested and verified:
- Database structure is correct
- Reporter roles are properly created
- User-role assignments work
- News creation with reporterRole works
- Admin interface functions correctly
