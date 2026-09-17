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
 * Tag management.
 */
class AdminTags extends BaseAdminController
{
    public function tags()
    {
        $tagModel = new TagModel();
        $tags = $tagModel->findAll();
        return view('admin/tags', ['tags' => $tags]);
    }

    public function addTag()
    {
        $tagModel = new TagModel();
        $name = $this->request->getPost('name');
        if ($name) {
            $tagModel->insert(['name' => $name]);
        }
        return redirect()->to('/admin/tags');
    }

    public function deleteTag()
    {
        $tagModel = new TagModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $tagModel->delete($id);
        }
        return redirect()->to('/admin/tags');
    }

    public function editTag($id)
    {
        $tagModel = new TagModel();
        $tag = $tagModel->find($id);
        return view('admin/tag_edit', ['tag' => $tag]);
    }

    public function updateTag($id)
    {
        $tagModel = new TagModel();
        $name = $this->request->getPost('name');
        if ($name) {
            $tagModel->update($id, ['name' => $name]);
        }
        return redirect()->to('/admin/tags');
    }
}
