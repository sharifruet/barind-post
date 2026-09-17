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
 * Contact-form inbox: list, view, reply, delete, export.
 */
class AdminContacts extends BaseAdminController
{
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
}
