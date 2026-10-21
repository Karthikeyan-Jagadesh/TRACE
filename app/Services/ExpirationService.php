<?php
namespace App\Services;

use App\Core\Database;
use App\Repositories\AuditLogRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ReportRepository;

class ExpirationService
{
    public static function processExpirations(): array
    {
        $today = date('Y-m-d');
        
        $expiredLost = ReportRepository::getExpiringReports($today, 'lost');
        $expiredFound = ReportRepository::getExpiringReports($today, 'found');

        $allExpiring = array_merge($expiredLost, $expiredFound);
        $count = 0;

        foreach ($allExpiring as $report) {
            ReportRepository::updateStatus($report['id'], 'expired');
            $count++;

            NotificationRepository::create(
                $report['creator_id'],
                'report_expired',
                'Report Expired',
                "Your {$report['report_type']} report '{$report['item_title']}' has reached its expiration date and has been archived.",
                "/reports/{$report['id']}"
            );

            AuditLogRepository::log(
                null,
                'report_auto_expired',
                'reports',
                $report['id'],
                "Automated lifecycle expiration for report #{$report['identifier']}"
            );
        }

        return [
            'processed_count' => $count,
            'lost_count' => count($expiredLost),
            'found_count' => count($expiredFound),
        ];
    }
}
