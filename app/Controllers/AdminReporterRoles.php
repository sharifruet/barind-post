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
 * Reporter roles and their assignment to users. Route-level `role:admin,editor,sub-editor`.
 */
class AdminReporterRoles extends BaseAdminController
{
    // Reporter Roles Management Methods
    public function reporterRoles()
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $roles = $reporterRoleModel->findAll();
        
        return view('admin/reporter_roles', ['roles' => $roles]);
    }

    public function addReporterRole()
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $name = $this->request->getPost('name');
        $description = $this->request->getPost('description');
        
        if ($name) {
            $reporterRoleModel->insert([
                'name' => $name,
                'description' => $description,
                'is_active' => 1
            ]);
            session()->setFlashdata('success', 'Reporter role added successfully.');
        }
        
        return redirect()->to('/admin/reporter-roles');
    }

    public function deleteReporterRole()
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $id = $this->request->getPost('id');
        
        if ($id) {
            $reporterRoleModel->delete($id);
            session()->setFlashdata('success', 'Reporter role deleted successfully.');
        }
        
        return redirect()->to('/admin/reporter-roles');
    }

    public function editReporterRole($id)
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $role = $reporterRoleModel->find($id);
        
        if (!$role) {
            session()->setFlashdata('error', 'Reporter role not found.');
            return redirect()->to('/admin/reporter-roles');
        }
        
        return view('admin/reporter_role_edit', ['role' => $role]);
    }

    public function updateReporterRole($id)
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $name = $this->request->getPost('name');
        $description = $this->request->getPost('description');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;
        
        if ($name) {
            $reporterRoleModel->update($id, [
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive
            ]);
            session()->setFlashdata('success', 'Reporter role updated successfully.');
        }
        
        return redirect()->to('/admin/reporter-roles');
    }

    public function assignReporterRoles($userId)
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $userModel = new UserModel();
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        
        $user = $userModel->find($userId);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/admin/users');
        }
        
        $allRoles = $reporterRoleModel->getActiveRoles();
        $userRoles = $reporterRoleModel->getUserRoles($userId);
        $userRoleIds = array_column($userRoles, 'id');
        
        return view('admin/assign_reporter_roles', [
            'user' => $user,
            'allRoles' => $allRoles,
            'userRoleIds' => $userRoleIds
        ]);
    }

    public function saveReporterRoleAssignment($userId)
    {
        // Check if user has permission to manage reporter roles
        $userRole = session('user_role');
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $reporterRoleModel = new \App\Models\ReporterRoleModel();
        $roleIds = $this->request->getPost('reporter_roles') ?? [];
        
        $reporterRoleModel->assignRolesToUser($userId, $roleIds);
        session()->setFlashdata('success', 'Reporter roles assigned successfully.');
        
        return redirect()->to('/admin/users');
    }
}
