<?php

namespace App\Controllers\Api;

use App\Models\AutomationRunModel;
use CodeIgniter\Controller;

/**
 * Run reporting for the n8n pipelines (Bearer-authenticated like the rest of api/v1).
 *
 *   POST /api/v1/automation/runs
 *   {
 *     "workflow": "Collect All Sources (10 each)",
 *     "status": "success" | "error",
 *     "execution_id": "123",                       optional
 *     "summary": [ {"source": "Daily Star", "created": 10, "duplicate": 0, "error": 1}, ... ],  optional
 *     "message": "free text — the error, or notes"  optional
 *   }
 *
 * The newsroom sees the latest row at the top of /admin/incoming.
 */
class AutomationController extends Controller
{
    public function createRun()
    {
        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'workflow'     => 'required|string|max_length[120]',
            'status'       => 'permit_empty|in_list[success,error]',
            'execution_id' => 'permit_empty|string|max_length[64]',
            'message'      => 'permit_empty|string',
            'summary'      => 'permit_empty|is_array',
        ]);
        if (! $validation->run($payload)) {
            return $this->response->setJSON(['error' => 'Validation failed', 'fields' => $validation->getErrors()])->setStatusCode(422);
        }

        $summary = [];
        $created = $duplicate = $error = 0;
        foreach ((array) ($payload['summary'] ?? []) as $row) {
            if (! is_array($row) || empty($row['source']) || $row['source'] === 'TOTAL') {
                continue;
            }
            $r = [
                'source'    => (string) $row['source'],
                'created'   => (int) ($row['created'] ?? 0),
                'duplicate' => (int) ($row['duplicate'] ?? 0),
                'error'     => (int) ($row['error'] ?? 0),
            ];
            if (! empty($row['note'])) {
                $r['note'] = mb_substr((string) $row['note'], 0, 300);
            }
            $summary[] = $r;
            $created   += $r['created'];
            $duplicate += $r['duplicate'];
            $error     += $r['error'];
        }

        $status = $payload['status'] ?? 'success';
        if ($status === 'success' && $error > 0 && $created === 0 && $duplicate === 0) {
            $status = 'error'; // nothing worked at all
        }

        $id = (new AutomationRunModel())->insert([
            'workflow'        => (string) $payload['workflow'],
            'status'          => $status,
            'created_count'   => $created,
            'duplicate_count' => $duplicate,
            'error_count'     => $error,
            'summary'         => json_encode($summary, JSON_UNESCAPED_UNICODE),
            'message'         => isset($payload['message']) ? mb_substr((string) $payload['message'], 0, 4000) : null,
            'execution_id'    => $payload['execution_id'] ?? null,
        ], true);

        return $this->response->setJSON(['id' => (int) $id, 'status' => $status, 'created' => $created, 'duplicate' => $duplicate, 'error' => $error])->setStatusCode(201);
    }
}
