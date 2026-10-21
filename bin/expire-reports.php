<?php
/**
 * TRACE — Automated Report Expiration CLI Command
 * Run via: php bin/expire-reports.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Services\ExpirationService;

echo "===============================================\n";
echo " TRACE: Automated Report Lifecycle Expiration \n";
echo "===============================================\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Scanning for reports past expiration date...\n";

try {
    $result = ExpirationService::processExpirations();
    echo "Summary:\n";
    echo " - Total Expired: {$result['processed_count']}\n";
    echo " - Lost Reports: {$result['lost_count']}\n";
    echo " - Found Reports: {$result['found_count']}\n";
    echo "Process completed successfully.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
