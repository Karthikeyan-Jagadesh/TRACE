<?php
namespace App\Services;

use App\Core\Database;
use App\Repositories\AuditLogRepository;
use App\Repositories\ReportRepository;
use App\Validators\Validator;

class ReportService
{
    public static function createReport(int $creatorId, array $data, ?array $uploadedFile = null): array
    {
        $rules = [
            'report_type' => 'required|in:lost,found',
            'item_title' => 'required|min:3|max:150',
            'category' => 'required|max:50',
            'description' => 'required|min:10|max:3000',
            'general_location' => 'required|min:3|max:200',
            'event_date' => 'required|date',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Expiration date
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $days = ($data['report_type'] === 'lost') 
            ? ($config['expiration']['lost_days'] ?? 60) 
            : ($config['expiration']['found_days'] ?? 90);
        $expirationDate = date('Y-m-d', strtotime("+{$days} days"));

        $reportData = [
            'creator_id' => $creatorId,
            'report_type' => $data['report_type'],
            'item_title' => trim($data['item_title']),
            'category' => trim($data['category']),
            'brand' => !empty($data['brand']) ? trim($data['brand']) : null,
            'color' => !empty($data['color']) ? trim($data['color']) : null,
            'description' => trim($data['description']),
            'general_location' => trim($data['general_location']),
            'latitude' => !empty($data['latitude']) ? (float)$data['latitude'] : null,
            'longitude' => !empty($data['longitude']) ? (float)$data['longitude'] : null,
            'event_date' => $data['event_date'],
            'event_time' => !empty($data['event_time']) ? $data['event_time'] : null,
            'contact_preference' => $data['contact_preference'] ?? 'in_app',
            'reward_amount' => !empty($data['reward_amount']) ? (float)$data['reward_amount'] : null,
            'status' => 'active',
            'expiration_date' => $expirationDate
        ];

        $reportId = ReportRepository::create($reportData);

        // If found report, save private custody and verification questions
        if ($data['report_type'] === 'found') {
            ReportRepository::savePrivateDetails($reportId, [
                'custody_status' => $data['custody_status'] ?? 'Held by finder',
                'safe_collection_instructions' => $data['safe_collection_instructions'] ?? null,
                'secret_question' => !empty($data['secret_question']) ? trim($data['secret_question']) : null,
                'secret_answer' => !empty($data['secret_answer']) ? trim($data['secret_answer']) : null,
                'secret_notes' => !empty($data['secret_notes']) ? trim($data['secret_notes']) : null,
            ]);
        }

        // Process image upload if provided
        if ($uploadedFile && !empty($uploadedFile['tmp_name']) && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            $imagePath = self::handleImageUpload($uploadedFile);
            if ($imagePath) {
                ReportRepository::addImage($reportId, $imagePath, true);
            }
        }

        AuditLogRepository::log(
            $creatorId,
            'report_created',
            'reports',
            $reportId,
            "Created {$data['report_type']} report: {$data['item_title']}"
        );

        // Trigger immediate matching evaluation
        $matches = MatchingService::runMatchingForReport($reportId);

        $report = ReportRepository::findById($reportId);
        return [
            'success' => true,
            'report' => $report,
            'matches' => $matches
        ];
    }

    public static function handleImageUpload(array $file): ?string
    {
        // Max 5 MB
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // Allowed MIME types
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes, true)) {
            return null;
        }

        // Verify valid image binary
        $info = @getimagesize($file['tmp_name']);
        if (!$info) {
            return null;
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg'
        };

        $safeName = 'trc_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $safeName;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $safeName;
        }

        return null;
    }
}
