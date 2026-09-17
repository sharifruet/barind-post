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
 * News CRUD, featured/breaking toggles and the editor's story search. Reporters may only save drafts and only touch their own articles (enforced inline).
 */
class AdminNews extends BaseAdminController
{
    public function newsList()
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get current user's role and ID
        $userRole = session('user_role');
        $userId = session('user_id');
        
        // Get news with kicker info based on user role
        $news = $newsModel->getNewsListForAdmin($userRole, $userId);
        
        $categories = $categoryModel->findAll();
        
        // Get user data for author names
        $userModel = new UserModel();
        $users = $userModel->findAll();
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = $user['name'];
        }
        
        // Get view counts for each news article
        $newsModel = new \App\Models\NewsModel();
        $newsIds = array_column($news, 'id');
        $viewCounts = $newsModel->getViewCounts($newsIds);
        
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
        
        // Generate unique code for clean, shareable URLs only for new articles
        if (empty($data['slug'])) {
            // Use unique code for better sharing and professional look
            $data['slug'] = generate_unique_code(0, $data['published_at'] ?? null);
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Breaking news is now handled through kicker selection
        
        // Handle kicker and kicker color
        $data['kicker'] = $this->request->getPost('kicker') ?: null;
        $data['kicker_color'] = $this->request->getPost('kicker_color') ?: null;
        
        // The news row only stores kicker_id; the text/colour live in `kickers`.
        // Upsert the kicker, then link the article to it — without this the
        // kicker was saved to the kickers table but never attached to the story.
        $data['kicker_id'] = null;
        if (!empty($data['kicker'])) {
            $kickerModel = new KickerModel();
            $kickerModel->createOrUpdate($data['kicker'], $data['kicker_color']);
            $kickerModel->incrementUsage($data['kicker']);
            $data['kicker_id'] = $kickerModel->getByText($data['kicker'])['id'] ?? null;
        }
        
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
        
        // Get the news article with kicker info
        $news = $newsModel->findWithKicker($id);
        
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
        $news = $newsModel->findWithKicker($id);
        
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
        
        // Only generate new slug if it's truly empty and this is a new article
        // For existing articles, preserve the existing slug unless explicitly changed
        if (empty($data['slug']) && empty($news['slug'])) {
            // Use unique code for better sharing and professional look
            $data['slug'] = generate_unique_code($id, $data['published_at'] ?? null);
        } elseif (empty($data['slug']) && !empty($news['slug'])) {
            // Preserve existing slug if form doesn't send one
            $data['slug'] = $news['slug'];
        }
        
        // Handle featured checkbox - if not checked, set to false
        $data['featured'] = $this->request->getPost('featured') ? 1 : 0;
        
        // Breaking news is now handled through kicker selection
        
        // Handle kicker and kicker color
        $oldKicker = $news['kicker'] ?? null;
        $data['kicker'] = $this->request->getPost('kicker') ?: null;
        $data['kicker_color'] = $this->request->getPost('kicker_color') ?: null;
        
        // The news row only stores kicker_id; the text/colour live in `kickers`.
        // Keep usage counts honest: only move a count when the kicker actually
        // changes (a re-save with the same kicker used to bump it every time),
        // and always resolve kicker_id — clearing the field must clear the link.
        $kickerModel = new KickerModel();
        $newKicker   = $data['kicker'];

        if ($oldKicker !== $newKicker) {
            if (!empty($oldKicker)) {
                $kickerModel->decrementUsage($oldKicker);
            }
            if (!empty($newKicker)) {
                $kickerModel->createOrUpdate($newKicker, $data['kicker_color']);
                $kickerModel->incrementUsage($newKicker);
            }
        } elseif (!empty($newKicker)) {
            // Same kicker — still honour a colour change.
            $kickerModel->createOrUpdate($newKicker, $data['kicker_color']);
        }

        $data['kicker_id'] = !empty($newKicker)
            ? ($kickerModel->getByText($newKicker)['id'] ?? null)
            : null;
        
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

    public function toggleFeatured($id)
    {
        $newsModel = new NewsModel();
        
        // Get current user's role
        $userRole = session('user_role');
        
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
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

    public function toggleBreakingNews($id)
    {
        $newsModel = new NewsModel();
        
        // Get current user's role
        $userRole = session('user_role');
        
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        // Get the news article with kicker info
        $news = $newsModel->findWithKicker($id);
        
        if (!$news) {
            session()->setFlashdata('error', 'News article not found.');
            return redirect()->to('/admin/news');
        }
        
        // Toggle breaking news by setting/removing "ব্রেকিং" kicker
        $kickerModel = new KickerModel();
        $breakingKicker = $kickerModel->getByText('ব্রেকিং');
        
        if (!$breakingKicker) {
            // Create breaking kicker if it doesn't exist
            $kickerModel->insert([
                'text' => 'ব্রেকিং',
                'color' => '#dc3545',
                'usage_count' => 0
            ]);
            $breakingKicker = $kickerModel->getByText('ব্রেকিং');
        }
        
        $currentKickerId = $news['kicker_id'] ?? null;
        $isCurrentlyBreaking = ($currentKickerId == $breakingKicker['id']);
        
        if ($isCurrentlyBreaking) {
            // Remove breaking kicker
            $updateData = ['kicker_id' => null];
            $status = 'removed from breaking news';
            // Decrement usage count
            $kickerModel->decrementUsage('ব্রেকিং');
        } else {
            // Set breaking kicker
            $updateData = ['kicker_id' => $breakingKicker['id']];
            $status = 'marked as breaking news';
            // Increment usage count
            $kickerModel->incrementUsage('ব্রেকিং');
        }
        
        $newsModel->update($id, $updateData);
        session()->setFlashdata('success', "News article {$status} successfully.");
        
        return redirect()->to('/admin/news');
    }

    /**
     * JSON search over published stories, for the editor's "link a story" picker.
     * Published only: a link to a draft would 404 on the public site.
     */
    public function newsSearch()
    {
        $q       = trim((string) $this->request->getGet('q'));
        $exclude = (int) $this->request->getGet('exclude');

        $newsModel = new NewsModel();
        $rows      = $q === '' ? $newsModel->getLatestWithKicker(20) : $newsModel->searchNews($q, 20);

        $out = [];
        foreach ($rows as $row) {
            if ((int) $row['id'] === $exclude) {
                continue;
            }
            $out[] = [
                'id'           => (int) $row['id'],
                'title'        => $row['title'],
                'slug'         => $row['slug'],
                'published_at' => ! empty($row['published_at']) ? format_bangla_date($row['published_at']) : '',
            ];
        }

        return $this->response->setJSON($out);
    }
}
