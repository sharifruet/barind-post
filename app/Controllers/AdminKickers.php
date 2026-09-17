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
 * Kicker (label) management and the editor's kicker JSON.
 */
class AdminKickers extends BaseAdminController
{
    /**
     * Kicker Management Methods
     */
    public function kickers()
    {
        $kickerModel = new KickerModel();
        $kickers = $kickerModel->getAllKickers();
        
        return view('admin/kickers', [
            'kickers' => $kickers,
            'title' => 'Manage Kickers'
        ]);
    }

    public function createKicker()
    {
        if ($this->request->getMethod() === 'post') {
            $kickerModel = new KickerModel();
            
            $data = [
                'text' => $this->request->getPost('text'),
                'color' => $this->request->getPost('color')
            ];
            
            if ($kickerModel->insert($data)) {
                return redirect()->to('/admin/kickers')->with('success', 'Kicker created successfully');
            } else {
                return redirect()->back()->withInput()->with('errors', $kickerModel->errors());
            }
        }
        
        return view('admin/kicker_form', [
            'title' => 'Create New Kicker'
        ]);
    }

    public function editKicker($id)
    {
        $kickerModel = new KickerModel();
        $kicker = $kickerModel->find($id);
        
        if (!$kicker) {
            return redirect()->to('/admin/kickers')->with('error', 'Kicker not found');
        }
        
        if ($this->request->getMethod() === 'post') {
            $data = [
                'text' => $this->request->getPost('text'),
                'color' => $this->request->getPost('color')
            ];
            
            if ($kickerModel->update($id, $data)) {
                return redirect()->to('/admin/kickers')->with('success', 'Kicker updated successfully');
            } else {
                return redirect()->back()->withInput()->with('errors', $kickerModel->errors());
            }
        }
        
        return view('admin/kicker_form', [
            'kicker' => $kicker,
            'title' => 'Edit Kicker'
        ]);
    }

    public function deleteKicker($id)
    {
        $kickerModel = new KickerModel();
        $kicker = $kickerModel->find($id);
        
        if (!$kicker) {
            return redirect()->to('/admin/kickers')->with('error', 'Kicker not found');
        }
        
        // Check if kicker is being used
        if ($kicker['usage_count'] > 0) {
            return redirect()->to('/admin/kickers')->with('error', 'Cannot delete kicker that is currently being used');
        }
        
        if ($kickerModel->delete($id)) {
            return redirect()->to('/admin/kickers')->with('success', 'Kicker deleted successfully');
        } else {
            return redirect()->to('/admin/kickers')->with('error', 'Failed to delete kicker');
        }
    }

    public function getKickers()
    {
        $kickerModel = new KickerModel();
        $kickers = $kickerModel->getAllKickers();
        
        return $this->response->setJSON($kickers);
    }
}
