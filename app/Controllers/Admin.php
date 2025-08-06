<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use Exception;

class Admin extends BaseAdminController
{
    public function dashboard()
    {
        // Get view statistics
        $db = \Config\Database::connect();
        
        // Total views
        $totalViews = $db->table('news_views')->countAllResults();
        
        // Views today
        $todayViews = $db->table('news_views')
                         ->where('DATE(viewed_at)', date('Y-m-d'))
                         ->countAllResults();
        
        // Most viewed news
        $mostViewedNews = $db->table('news_views')
                            ->select('news_id, COUNT(*) as view_count')
                            ->groupBy('news_id')
                            ->orderBy('view_count', 'DESC')
                            ->limit(5)
                            ->get()
                            ->getResultArray();
        
        // Get news details for most viewed
        $newsModel = new NewsModel();
        $topNews = [];
        foreach ($mostViewedNews as $view) {
            $news = $newsModel->find($view['news_id']);
            if ($news) {
                $topNews[] = [
                    'title' => $news['title'],
                    'views' => $view['view_count']
                ];
            }
        }
        
        return view('admin/dashboard', [
            'totalViews' => $totalViews,
            'todayViews' => $todayViews,
            'topNews' => $topNews
        ]);
    }

    public function test()
    {
        return 'Test route is working!';
    }

    public function roles()
    {
        // Check if user has permission to manage roles
        $userRole = session('user_role');
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage roles.');
            return redirect()->to('/admin/dashboard');
        }
        
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();
        return view('admin/roles', ['roles' => $roles]);
    }

    public function addRole()
    {
        // Check if user has permission to manage roles
        $userRole = session('user_role');
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage roles.');
            return redirect()->to('/admin/dashboard');
        }
        
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
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage roles.');
            return redirect()->to('/admin/dashboard');
        }
        
        $roleModel = new RoleModel();
        $id = $this->request->getPost('id');
        if ($id) {
            $roleModel->delete($id);
        }
        return redirect()->to('/admin/roles');
    }

    public function users()
    {
        // Check if user has permission to manage users
        $userRole = session('user_role');
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage users.');
            return redirect()->to('/admin/dashboard');
        }
        
        $userModel = new UserModel();
        $users = $userModel->findAll();
        return view('admin/users', ['users' => $users]);
    }

    public function addUser()
    {
        // Check if user has permission to manage users
        $userRole = session('user_role');
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage users.');
            return redirect()->to('/admin/dashboard');
        }
        
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
        if ($userRole === 'reporter') {
            session()->setFlashdata('error', 'You do not have permission to manage users.');
            return redirect()->to('/admin/dashboard');
        }
        
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
        
        // Get current user's role and ID
        $userRole = session('user_role');
        $userId = session('user_id');
        
        // Role-based news filtering
        if ($userRole === 'reporter') {
            // Reporters can only see their own news
            $news = $newsModel->where('author_id', $userId)
                             ->orderBy('created_at', 'DESC')
                             ->findAll();
        } else {
            // Editors, sub-editors, and admins can see all news
            $news = $newsModel->orderBy('created_at', 'DESC')->findAll();
        }
        
        $categories = $categoryModel->findAll();
        
        // Get user data for author names
        $userModel = new UserModel();
        $users = $userModel->findAll();
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = $user['name'];
        }
        
        // Get view counts for each news article
        $db = \Config\Database::connect();
        $viewCounts = [];
        foreach ($news as $article) {
            $count = $db->table('news_views')
                        ->where('news_id', $article['id'])
                        ->countAllResults();
            $viewCounts[$article['id']] = $count;
        }
        
        return view('admin/news_list', [
            'news' => $news, 
            'categories' => $categories,
            'userRole' => $userRole,
            'userMap' => $userMap,
            'viewCounts' => $viewCounts
        ]);
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
        
        // Get current user's role
        $userRole = session('user_role');
        
        // Generate unique code for clean, shareable URLs
        if (empty($data['slug'])) {
            // Use unique code for better sharing and professional look
            $data['slug'] = generate_unique_code(0, $data['published_at'] ?? null);
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Role-based status handling
        if ($userRole === 'reporter') {
            // Reporters can only create drafts
            $data['status'] = 'draft';
            $data['published_at'] = null;
        } else {
            // Editors, sub-editors, and admins can publish
            if (empty($data['status'])) {
                $data['status'] = 'published';
            }
            
            // Set published_at to current time if status is published
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        // Handle image data (from upload, existing selection, or external URL)
        $data['image_url'] = $this->request->getPost('image_url') ?: null;
        $data['image_caption'] = $this->request->getPost('image_caption') ?: null;
        $data['image_alt_text'] = $this->request->getPost('image_alt_text') ?: null;
        
        // Set default language to Bangla if not specified
        if (empty($data['language'])) {
            $data['language'] = 'bn';
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
        
        // Set success message based on role
        if ($userRole === 'reporter') {
            session()->setFlashdata('success', 'News article created as draft. An editor will review and publish it.');
        } else {
            session()->setFlashdata('success', 'News article created successfully.');
        }
        
        return redirect()->to('/admin/news');
    }

    public function newsEdit($id)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        
        // Get current user's role and ID
        $userRole = session('user_role');
        $userId = session('user_id');
        
        // Get the news article
        $news = $newsModel->find($id);
        
        // Check if news exists
        if (!$news) {
            session()->setFlashdata('error', 'News article not found.');
            return redirect()->to('/admin/news');
        }
        
        // Role-based access control for editing
        if ($userRole === 'reporter' && $news['author_id'] != $userId) {
            session()->setFlashdata('error', 'You can only edit your own news articles.');
            return redirect()->to('/admin/news');
        }
        
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
        
        // Get current user's role and ID
        $userRole = session('user_role');
        $userId = session('user_id');
        
        // Get the news article to check ownership
        $news = $newsModel->find($id);
        
        // Check if news exists
        if (!$news) {
            session()->setFlashdata('error', 'News article not found.');
            return redirect()->to('/admin/news');
        }
        
        // Role-based access control for updating
        if ($userRole === 'reporter' && $news['author_id'] != $userId) {
            session()->setFlashdata('error', 'You can only update your own news articles.');
            return redirect()->to('/admin/news');
        }
        
        // Generate unique code for clean, shareable URLs
        if (empty($data['slug'])) {
            // Use unique code for better sharing and professional look
            $data['slug'] = generate_unique_code($id, $data['published_at'] ?? null);
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Role-based status handling for updates
        if ($userRole === 'reporter') {
            // Reporters can only save as drafts
            $data['status'] = 'draft';
            $data['published_at'] = null;
        } else {
            // Editors, sub-editors, and admins can publish
            // Set published_at to current time if status is being changed to published
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        // Handle image data (from upload, existing selection, or external URL)
        $data['image_url'] = $this->request->getPost('image_url') ?: null;
        $data['image_caption'] = $this->request->getPost('image_caption') ?: null;
        $data['image_alt_text'] = $this->request->getPost('image_alt_text') ?: null;
        $newsModel->update($id, $data);
        
        // Update tags
        $tags = $this->request->getPost('tags') ?? [];
        $db = \Config\Database::connect();
        $db->table('news_tags')->where('news_id', $id)->delete();
        foreach ($tags as $tagId) {
            $db->table('news_tags')->insert(['news_id' => $id, 'tag_id' => $tagId]);
        }
        
        // Set success message based on role
        if ($userRole === 'reporter') {
            session()->setFlashdata('success', 'News article updated as draft. An editor will review and publish it.');
        } else {
            session()->setFlashdata('success', 'News article updated successfully.');
        }
        
        return redirect()->to('/admin/news');
    }

    public function newsDelete($id)
    {
        $newsModel = new NewsModel();
        
        // Get current user's role and ID
        $userRole = session('user_role');
        $userId = session('user_id');
        
        // Get the news article to check ownership
        $news = $newsModel->find($id);
        
        // Check if news exists
        if (!$news) {
            session()->setFlashdata('error', 'News article not found.');
            return redirect()->to('/admin/news');
        }
        
        // Role-based access control for deleting
        if ($userRole === 'reporter' && $news['author_id'] != $userId) {
            session()->setFlashdata('error', 'You can only delete your own news articles.');
            return redirect()->to('/admin/news');
        }
        
        $newsModel->delete($id);
        $db = \Config\Database::connect();
        $db->table('news_tags')->where('news_id', $id)->delete();
        
        session()->setFlashdata('success', 'News article deleted successfully.');
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

    public function contacts()
    {
        return view('admin/contacts');
    }

    public function getContacts()
    {
        $page = $this->request->getGet('page') ?? 1;
        $status = $this->request->getGet('status') ?? '';
        $subject = $this->request->getGet('subject') ?? '';
        $date = $this->request->getGet('date') ?? '';
        $search = $this->request->getGet('search') ?? '';
        $perPage = 20;

        $db = \Config\Database::connect();
        $builder = $db->table('contacts');

        // Apply filters
        if ($status) {
            $builder->where('status', $status);
        }
        if ($subject) {
            $builder->where('subject', $subject);
        }
        if ($date) {
            $builder->where('DATE(created_at)', $date);
        }
        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('message', $search)
                ->groupEnd();
        }

        // Get total count
        $total = $builder->countAllResults(false);

        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $contacts = $builder->orderBy('created_at', 'DESC')
                           ->limit($perPage, $offset)
                           ->get()
                           ->getResultArray();

        // Update status to 'read' for viewed contacts
        if ($page == 1) {
            $db->table('contacts')->where('status', 'unread')->update(['status' => 'read']);
        }

        $totalPages = ceil($total / $perPage);

        return $this->response->setJSON([
            'success' => true,
            'contacts' => $contacts,
            'total' => $total,
            'pagination' => [
                'current_page' => (int)$page,
                'total_pages' => $totalPages,
                'per_page' => $perPage
            ]
        ]);
    }

    public function getContact($id)
    {
        $db = \Config\Database::connect();
        $contact = $db->table('contacts')->where('id', $id)->get()->getRowArray();

        if (!$contact) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contact not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'contact' => $contact
        ]);
    }

    public function replyToContact($id)
    {
        $subject = $this->request->getJSON()->subject;
        $message = $this->request->getJSON()->message;

        if (!$subject || !$message) {
            return $this->response->setJSON(['success' => false, 'message' => 'Subject and message are required']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Get contact details
            $contact = $db->table('contacts')->where('id', $id)->get()->getRowArray();
            if (!$contact) {
                return $this->response->setJSON(['success' => false, 'message' => 'Contact not found']);
            }

            // Update contact status
            $db->table('contacts')->where('id', $id)->update(['status' => 'replied']);

            // Send email (you can implement email sending here)
            // $this->sendReplyEmail($contact['email'], $subject, $message);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Reply sent successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error sending reply'
            ]);
        }
    }

    public function deleteContact($id)
    {
        try {
            $db = \Config\Database::connect();
            $db->table('contacts')->where('id', $id)->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Contact deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting contact'
            ]);
        }
    }

    public function exportContacts()
    {
        $status = $this->request->getGet('status') ?? '';
        $subject = $this->request->getGet('subject') ?? '';
        $date = $this->request->getGet('date') ?? '';
        $search = $this->request->getGet('search') ?? '';

        $db = \Config\Database::connect();
        $builder = $db->table('contacts');

        // Apply filters
        if ($status) {
            $builder->where('status', $status);
        }
        if ($subject) {
            $builder->where('subject', $subject);
        }
        if ($date) {
            $builder->where('DATE(created_at)', $date);
        }
        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('message', $search)
                ->groupEnd();
        }

        $contacts = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        // Generate CSV
        $filename = 'contacts_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, ['নাম', 'ইমেইল', 'ফোন', 'বিষয়', 'বার্তা', 'স্ট্যাটাস', 'তারিখ']);
        
        foreach ($contacts as $contact) {
            fputcsv($output, [
                $contact['name'],
                $contact['email'],
                $contact['phone'] ?? '',
                $contact['subject'],
                $contact['message'],
                $contact['status'],
                $contact['created_at']
            ]);
        }
        
        fclose($output);
        exit;
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



    public function toggleFeatured($id)
    {
        $newsModel = new NewsModel();
        
        // Get current user's role
        $userRole = session('user_role');
        
        // Only admins can toggle featured status
        if ($userRole !== 'admin') {
            session()->setFlashdata('error', 'Only administrators can toggle featured status.');
            return redirect()->to('/admin/news');
        }
        
        // Get the news article
        $news = $newsModel->find($id);
        
        if (!$news) {
            session()->setFlashdata('error', 'News article not found.');
            return redirect()->to('/admin/news');
        }
        
        // Toggle the featured status
        $newFeaturedStatus = !$news['featured'];
        $newsModel->update($id, ['featured' => $newFeaturedStatus]);
        
        $status = $newFeaturedStatus ? 'featured' : 'unfeatured';
        session()->setFlashdata('success', "News article {$status} successfully.");
        
        return redirect()->to('/admin/news');
    }

    public function photoCardGenerator()
    {
        // Check if user is admin
        if (session('user_role') !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/admin/dashboard');
        }
        
        $newsModel = new NewsModel();
        $news = $newsModel->where('status', 'published')
                          ->orderBy('published_at', 'DESC')
                          ->findAll(50); // Get last 50 published news
        
        return view('admin/photo_card_generator', [
            'news' => $news
        ]);
    }

    public function generatePhotoCard()
    {
        // Check if user is admin
        if (session('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }
        
        $newsId = $this->request->getPost('news_id');
        $template = $this->request->getPost('template') ?: 'default';
        
        if (!$newsId) {
            return $this->response->setJSON(['success' => false, 'message' => 'News ID is required']);
        }
        
        $newsModel = new NewsModel();
        $news = $newsModel->find($newsId);
        
        if (!$news) {
            return $this->response->setJSON(['success' => false, 'message' => 'News not found']);
        }
        
        try {
            // Check GD library first
            if (!extension_loaded('gd')) {
                throw new Exception('GD library is not available');
            }
            
            // Generate the photo card
            $photoCardPath = $this->createPhotoCard($news, $template);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Photo card generated successfully',
                'image_url' => $photoCardPath
            ]);
        } catch (Exception $e) {
            log_message('error', 'Photo card generation error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error generating photo card: ' . $e->getMessage()
            ]);
        }
    }

    public function photoCardTest()
    {
        // Check if user is admin
        if (session('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }
        
        try {
            // Test GD library
            if (!extension_loaded('gd')) {
                throw new Exception('GD library is not available');
            }
            
            // Test basic image creation
            $image = imagecreatetruecolor(100, 100);
            if ($image === false) {
                throw new Exception('Failed to create test image');
            }
            
            // Test colors
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            // Fill background
            imagefill($image, 0, 0, $white);
            
            // Add some text
            $fontPath = '/System/Library/Fonts/Arial.ttf';
            if (file_exists($fontPath)) {
                imagettftext($image, 12, 0, 10, 50, $black, $fontPath, 'Test');
            }
            
            // Save test image
            $outputDir = FCPATH . 'public/uploads/photo_cards/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            $testPath = $outputDir . 'test_' . time() . '.png';
            if (!imagepng($image, $testPath)) {
                throw new Exception('Failed to save test image');
            }
            
            imagedestroy($image);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Photo card test successful',
                'test_image' => 'public/uploads/photo_cards/' . basename($testPath)
            ]);
            
        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Photo card test failed: ' . $e->getMessage()
            ]);
        }
    }

    private function createPhotoCard($news, $template = 'default')
    {
        // Check if GD library is available
        if (!extension_loaded('gd')) {
            throw new Exception('GD library is not available');
        }
        
        // Set up image dimensions
        $width = 1200;
        $height = 630;
        
        // Create image
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new Exception('Failed to create image resource');
        }
        
        // Set colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 220, 53, 69);
        $darkGray = imagecolorallocate($image, 52, 58, 64);
        $lightGray = imagecolorallocate($image, 108, 117, 125);
        
        // Fill background based on template
        switch ($template) {
            case 'red':
                imagefill($image, 0, 0, $red);
                break;
            case 'gradient':
                // Create gradient background
                for ($i = 0; $i < $height; $i++) {
                    $color = imagecolorallocate($image, 
                        220 - ($i / $height) * 50, 
                        53 - ($i / $height) * 20, 
                        69 - ($i / $height) * 20
                    );
                    imageline($image, 0, $i, $width, $i, $color);
                }
                break;
            default:
                imagefill($image, 0, 0, $white);
                break;
        }
        
        // Load and add logo
        $logoPath = FCPATH . 'public/logo.png';
        if (file_exists($logoPath)) {
            $logo = imagecreatefrompng($logoPath);
            if ($logo === false) {
                // Skip logo if it can't be loaded
                $logo = null;
            } else {
                $logoWidth = imagesx($logo);
                $logoHeight = imagesy($logo);
                
                // Scale logo to fit (smaller size)
                $logoNewWidth = 100;
                $logoNewHeight = ($logoHeight / $logoWidth) * $logoNewWidth;
                
                $scaledLogo = imagecreatetruecolor($logoNewWidth, $logoNewHeight);
                imagealphablending($scaledLogo, false);
                imagesavealpha($scaledLogo, true);
                imagecopyresampled($scaledLogo, $logo, 0, 0, 0, 0, $logoNewWidth, $logoNewHeight, $logoWidth, $logoHeight);
                
                // Position logo at top-right corner
                imagecopy($image, $scaledLogo, $width - $logoNewWidth - 50, 30, 0, 0, $logoNewWidth, $logoNewHeight);
                
                imagedestroy($logo);
                imagedestroy($scaledLogo);
            }
        }
        
        // Load and add news image if exists (positioned at top with larger size)
        if (!empty($news['image_url'])) {
            $imageUrl = $news['image_url'];
            
            // Handle both local files and external URLs
            if (strpos($imageUrl, 'http') === 0) {
                // External URL - try to download
                $imageData = @file_get_contents($imageUrl);
                if ($imageData === false) {
                    // Skip if external image can't be loaded
                    $imageData = null;
                }
            } else {
                // Local file
                $newsImagePath = FCPATH . 'public/uploads/news/' . basename($imageUrl);
                if (file_exists($newsImagePath)) {
                    $imageData = file_get_contents($newsImagePath);
                } else {
                    $imageData = null;
                }
            }
            
            if ($imageData !== null && $imageData !== false) {
                $newsImage = imagecreatefromstring($imageData);
                if ($newsImage === false) {
                    // Skip if image can't be created
                } else {
                    $newsImageWidth = imagesx($newsImage);
                    $newsImageHeight = imagesy($newsImage);
                    
                    // Scale news image to fit top portion with larger size
                    $newsImageNewWidth = $width - 100; // Full width minus margins
                    $newsImageNewHeight = ($newsImageHeight / $newsImageWidth) * $newsImageNewWidth;
                    
                    // Limit height to 60% of total height to leave space for text
                    $maxImageHeight = $height * 0.6;
                    if ($newsImageNewHeight > $maxImageHeight) {
                        $newsImageNewHeight = $maxImageHeight;
                        $newsImageNewWidth = ($newsImageWidth / $newsImageHeight) * $newsImageNewHeight;
                    }
                    
                    $scaledNewsImage = imagecreatetruecolor($newsImageNewWidth, $newsImageNewHeight);
                    imagecopyresampled($scaledNewsImage, $newsImage, 0, 0, 0, 0, $newsImageNewWidth, $newsImageNewHeight, $newsImageWidth, $newsImageHeight);
                    
                    // Position news image at top center
                    $newsImageX = ($width - $newsImageNewWidth) / 2;
                    $newsImageY = 50; // Top margin
                    imagecopy($image, $scaledNewsImage, $newsImageX, $newsImageY, 0, 0, $newsImageNewWidth, $newsImageNewHeight);
                    
                    imagedestroy($newsImage);
                    imagedestroy($scaledNewsImage);
                }
            }
        }
        
        // Add text overlay - Use fonts that support Bengali text rendering
        $fontPath = '/usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf'; // Use Bold version which might work better
        if (!file_exists($fontPath)) {
            // Try alternative Bengali fonts
            $alternativeFonts = [
                '/usr/share/fonts/truetype/noto/NotoSansBengali-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengali-SemiBold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSerifBengali-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSerifBengali-Bold.ttf',
                '/usr/share/fonts/truetype/noto/HindSiliguri-Regular.ttf',
                '/usr/share/fonts/truetype/noto/HindSiliguri-Bold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengaliUI-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengaliUI-Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                '/System/Library/Fonts/KohinoorBangla.ttc',
                '/System/Library/Fonts/Arial.ttf'
            ];
            
            $fontPath = null;
            foreach ($alternativeFonts as $altFont) {
                if (file_exists($altFont)) {
                    $fontPath = $altFont;
                    break;
                }
            }
            
            if (!$fontPath) {
                throw new Exception('No suitable font found for Bengali text rendering');
            }
        }
        
        // Add title text (positioned at bottom)
        $title = $news['title'];
        
        // Debug: Log the title being processed
        error_log("Photo card title: " . $title);
        error_log("Title length: " . strlen($title));
        error_log("Title mb_strlen: " . mb_strlen($title, 'UTF-8'));
        
        // Ensure proper UTF-8 encoding for Bengali text
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        
        $titleFontSize = 36;
        $titleColor = $template === 'default' ? $black : $white;
        
        // Calculate text position at bottom
        $titleX = 50;
        $titleY = $height - 200; // Position at bottom
        $maxTitleWidth = $width - 100; // Full width minus margins
        
        // For Bengali text, use simpler approach without complex wrapping
        $lines = [$title]; // Just use the title as one line for now
        $lineHeight = 45;
        
        // Simple direct rendering approach (like the working example)
        foreach ($lines as $index => $line) {
            $y = $titleY + ($index * $lineHeight);
            if ($y < $height - 100) { // Don't go below bottom
                // Ensure proper UTF-8 encoding
                $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                
                // Try with a test title first to see if the issue is with the data
                $testTitle = 'আমরা বিশ্বাস করি';
                imagettftext($image, $titleFontSize, 0, $titleX, $y, $titleColor, $fontPath, $testTitle);
                
                // Then try with the actual title
                imagettftext($image, $titleFontSize, 0, $titleX, $y + 50, $titleColor, $fontPath, $line);
            }
        }
        
        // Additional fallback rendering if needed
        // (Removed complex fallback system to simplify and avoid issues)
        
        // Add subtitle/date (positioned at bottom)
        $dateText = date('d M, Y', strtotime($news['published_at']));
        $dateFontSize = 18;
        $dateColor = $template === 'default' ? $lightGray : $white;
        $dateY = $titleY + (count($lines) * $lineHeight) + 20;
        
        // Simple direct rendering for date (like the working example)
        imagettftext($image, $dateFontSize, 0, $titleX, $dateY, $dateColor, $fontPath, $dateText);
        
        // Add website URL
        $urlText = 'barindpost.com';
        $urlFontSize = 16;
        $urlColor = $template === 'default' ? $red : $white;
        $urlY = $height - 50;
        
        // Simple direct rendering for URL (like the working example)
        imagettftext($image, $urlFontSize, 0, $titleX, $urlY, $urlColor, $fontPath, $urlText);
        
        // Save the image
        $outputDir = FCPATH . 'public/uploads/photo_cards/';
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new Exception('Failed to create output directory');
            }
        }
        
        $filename = 'photo_card_' . $news['id'] . '_' . time() . '.png';
        $outputPath = $outputDir . $filename;
        
        if (!imagepng($image, $outputPath)) {
            throw new Exception('Failed to save image to ' . $outputPath);
        }
        
        imagedestroy($image);
        
        return 'public/uploads/photo_cards/' . $filename;
    }

    private function wrapText($text, $fontPath, $fontSize, $maxWidth)
    {
        // For Bengali text, we need to handle it differently
        // Bengali text doesn't use spaces the same way as English
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            
            // Ensure proper UTF-8 encoding
            $testLine = mb_convert_encoding($testLine, 'UTF-8', 'UTF-8');
            
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            if ($bbox === false) {
                // If bbox fails, try with a simpler approach
                $lineWidth = strlen($testLine) * ($fontSize * 0.6); // Approximate width
            } else {
                $lineWidth = $bbox[2] - $bbox[0];
            }
            
            if ($lineWidth > $maxWidth && $currentLine !== '') {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        
        if (!empty($currentLine)) {
            $lines[] = trim($currentLine);
        }
        
        return implode("\n", $lines);
    }
} 