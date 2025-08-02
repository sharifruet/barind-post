<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;

class Admin extends BaseAdminController
{
    public function dashboard()
    {
        return view('admin/dashboard');
    }

    public function test()
    {
        return 'Test route is working!';
    }

    public function roles()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();
        return view('admin/roles', ['roles' => $roles]);
    }

    public function addRole()
    {
        $roleModel = new RoleModel();
        $name = $this->request->getPost('name');
        if ($name) {
            $roleModel->insert(['name' => $name]);
        }
        return redirect()->to('/admin/roles');
    }

    public function deleteRole()
    {
        $roleModel = new RoleModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $roleModel->delete($id);
        }
        return redirect()->to('/admin/roles');
    }

    public function users()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();
        return view('admin/users', ['users' => $users]);
    }

    public function addUser()
    {
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
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $userModel->delete($id);
        }
        return redirect()->to('/admin/users');
    }

    public function newsList()
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $news = $newsModel->orderBy('created_at', 'DESC')->findAll();
        $categories = $categoryModel->findAll();
        return view('admin/news_list', ['news' => $news, 'categories' => $categories]);
    }

    public function newsCreate()
    {
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $categories = $categoryModel->findAll();
        $tags = $tagModel->findAll();
        return view('admin/news_form', ['categories' => $categories, 'tags' => $tags, 'news' => null]);
    }

    public function newsStore()
    {
        $newsModel = new NewsModel();
        $tagModel = new TagModel();
        $data = $this->request->getPost();
        $data['author_id'] = session('user_id');
        
        // Generate English slug only if not provided (frontend handles most cases)
        if (empty($data['slug'])) {
            // Simple fallback slug generation
            $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s-]+/', '-', $data['title']), '-'));
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Handle image upload or image_url
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move('public/uploads/news', $newName);
            $data['image_url'] = '/public/uploads/news/' . $newName;
        } else if (!empty($data['image_url'])) {
            // Use provided image_url from the form
            $data['image_url'] = $data['image_url'];
        } else {
            $data['image_url'] = null;
        }
        $newsId = $newsModel->insert($data, true);
        // Handle tags
        $tags = $this->request->getPost('tags') ?? [];
        if ($newsId && !empty($tags)) {
            foreach ($tags as $tagId) {
                $db = \Config\Database::connect();
                $db->table('news_tags')->insert(['news_id' => $newsId, 'tag_id' => $tagId]);
            }
        }
        return redirect()->to('/admin/news');
    }

    public function newsEdit($id)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $news = $newsModel->find($id);
        $categories = $categoryModel->findAll();
        $tags = $tagModel->findAll();
        // Get selected tags
        $db = \Config\Database::connect();
        $selectedTags = $db->table('news_tags')->where('news_id', $id)->get()->getResultArray();
        $selectedTagIds = array_column($selectedTags, 'tag_id');
        return view('admin/news_form', [
            'news' => $news,
            'categories' => $categories,
            'tags' => $tags,
            'selectedTagIds' => $selectedTagIds
        ]);
    }

    public function newsUpdate($id)
    {
        $newsModel = new NewsModel();
        $data = $this->request->getPost();
        
        // Generate English slug only if not provided (frontend handles most cases)
        if (empty($data['slug'])) {
            // Simple fallback slug generation
            $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s-]+/', '-', $data['title']), '-'));
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Handle image upload or image_url
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move('public/uploads/news', $newName);
            $data['image_url'] = '/public/uploads/news/' . $newName;
        } else if (!empty($data['image_url'])) {
            $data['image_url'] = $data['image_url'];
        } else {
            // Keep old image if not uploading new one and no new URL
            $old = $newsModel->find($id);
            if ($old && isset($old['image_url'])) {
                $data['image_url'] = $old['image_url'];
            } else {
                $data['image_url'] = null;
            }
        }
        $newsModel->update($id, $data);
        // Update tags
        $tags = $this->request->getPost('tags') ?? [];
        $db = \Config\Database::connect();
        $db->table('news_tags')->where('news_id', $id)->delete();
        foreach ($tags as $tagId) {
            $db->table('news_tags')->insert(['news_id' => $id, 'tag_id' => $tagId]);
        }
        return redirect()->to('/admin/news');
    }

    public function newsDelete($id)
    {
        $newsModel = new NewsModel();
        $newsModel->delete($id);
        $db = \Config\Database::connect();
        $db->table('news_tags')->where('news_id', $id)->delete();
        return redirect()->to('/admin/news');
    }

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

    // List images in public/uploads/news for selection in news form
    public function newsImagesList()
    {
        $dir = FCPATH . 'public/uploads/news/';
        $images = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (in_array($file, ['.', '..', 'index.html'])) continue;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $images[] = [
                        'name' => $file,
                        'url' => '/public/uploads/news/' . $file
                    ];
                }
            }
        }
        return $this->response->setJSON($images);
    }

    public function toggleFeatured($id)
    {
        $newsModel = new NewsModel();
        $news = $newsModel->find($id);
        
        if (!$news) {
            return redirect()->to('/admin/news')->with('error', 'News article not found.');
        }
        
        // Toggle the featured status
        $newFeaturedStatus = !$news['featured'];
        $newsModel->update($id, ['featured' => $newFeaturedStatus]);
        
        $status = $newFeaturedStatus ? 'featured' : 'unfeatured';
        return redirect()->to('/admin/news')->with('success', "News article {$status} successfully.");
    }
} 