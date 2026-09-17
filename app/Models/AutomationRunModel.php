<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One row per automation run (or error) reported by n8n via
 * POST /api/v1/automation/runs; the latest is shown on the Incoming queue.
 */
class AutomationRunModel extends Model
{
    protected $table         = 'automation_runs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['workflow', 'status', 'created_count', 'duplicate_count', 'error_count', 'summary', 'message', 'execution_id'];

    public function latest(): ?array
    {
        $row = $this->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->first();
        if ($row && is_string($row['summary'])) {
            $row['summary'] = json_decode($row['summary'], true) ?: [];
        }

        return $row ?: null;
    }

    /**
     * @return list<array>
     */
    public function recent(int $limit = 20): array
    {
        $rows = $this->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->findAll($limit);
        foreach ($rows as &$row) {
            if (is_string($row['summary'])) {
                $row['summary'] = json_decode($row['summary'], true) ?: [];
            }
        }

        return $rows;
    }
}
