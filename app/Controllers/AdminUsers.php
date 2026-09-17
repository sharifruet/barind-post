<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\PrayerTimesModel;
use App\Models\CityModel;
use App\Models\KickerModel;
use Exception;

/**
 * Users and roles. Route-level `role:admin,editor,sub-editor` keeps reporters out.
 */
class AdminUsers extends BaseAdminController
{
    public function users()
    {
        // Check if user has permission to manage users
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $userModel = new UserModel();
        $users = $userModel->findAll();
        return view('admin/users', ['users' => $users]);
    }

    public function addUser()
    {
        // Check if user has permission to manage users
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $userModel = new UserModel();
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $role = $this->request->getPost('role');
        if ($name && $email && $password && $role) {
            $userModel->insert([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $role,
            ]);
        }
        return redirect()->to('/admin/users');
    }

    public function deleteUser()
    {
        // Check if user has permission to manage users
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $userModel->delete($id);
        }
        return redirect()->to('/admin/users');
    }

    public function roles()
    {
        // Check if user has permission to manage roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();
        return view('admin/roles', ['roles' => $roles]);
    }

    public function addRole()
    {
        // Check if user has permission to manage roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $roleModel = new RoleModel();
        $name = $this->request->getPost('name');
        if ($name) {
            $roleModel->insert(['name' => $name]);
        }
        return redirect()->to('/admin/roles');
    }

    public function deleteRole()
    {
        // Check if user has permission to manage roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $roleModel = new RoleModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $roleModel->delete($id);
        }
        return redirect()->to('/admin/roles');
    }
}
