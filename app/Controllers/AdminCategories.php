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
 * Category management.
 */
class AdminCategories extends BaseAdminController
{
    public function categories()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        return view('admin/categories', ['categories' => $categories]);
    }

    public function addCategory()
    {
        $categoryModel = new CategoryModel();
        $name = $this->request->getPost('name');
        $slug = $this->request->getPost('slug');
        if ($name && $slug) {
            $categoryModel->insert(['name' => $name, 'slug' => $slug]);
        }
        return redirect()->to('/admin/categories');
    }

    public function deleteCategory()
    {
        $categoryModel = new CategoryModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $categoryModel->delete($id);
        }
        return redirect()->to('/admin/categories');
    }

    public function editCategory($id)
    {
        $categoryModel = new CategoryModel();
        $category = $categoryModel->find($id);
        return view('admin/category_edit', ['category' => $category]);
    }

    public function updateCategory($id)
    {
        $categoryModel = new CategoryModel();
        $name = $this->request->getPost('name');
        $slug = $this->request->getPost('slug');
        if ($name && $slug) {
            $categoryModel->update($id, ['name' => $name, 'slug' => $slug]);
        }
        return redirect()->to('/admin/categories');
    }
}
