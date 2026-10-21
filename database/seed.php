<?php
/**
 * TRACE — Demonstration Database Seeder
 * Run via: php database/seed.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Repositories\AuditLogRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use App\Services\MatchingService;

echo "===============================================\n";
echo " TRACE: Database Seeder                        \n";
echo "===============================================\n";

try {
    $pdo = Database::getConnection();

    // Clear existing data in correct FK order
    echo "1. Truncating existing tables...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = [
        'user_preferences', 'audit_logs', 'report_flags', 'notifications',
        'claim_status_history', 'claims', 'match_suggestions',
        'report_private_details', 'report_images', 'reports', 'users', 'organizations'
    ];
    foreach ($tables as $tbl) {
        $pdo->exec("TRUNCATE TABLE {$tbl};");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Organizations
    echo "2. Seeding organizations...\n";
    $pdo->exec("INSERT INTO organizations (id, name, code) VALUES 
        (1, 'National Institute of Technology', 'NIT-CAMPUS'),
        (2, 'City Central Library & Public Spaces', 'LIB-METRO')");

    // 3. Users
    echo "3. Seeding users...\n";
    $defaultPass = password_hash('Password123!', PASSWORD_DEFAULT);

    $users = [
        ['name' => 'Director Marcus Vance', 'email' => 'admin@trace.internal', 'role' => 'admin', 'org' => 1],
        ['name' => 'Alex Morgan', 'email' => 'alex.morgan@campus.edu', 'role' => 'user', 'org' => 1],
        ['name' => 'Elena Rostova', 'email' => 'elena.rostova@campus.edu', 'role' => 'user', 'org' => 1],
        ['name' => 'David Chen', 'email' => 'david.chen@metro.org', 'role' => 'user', 'org' => 2],
        ['name' => 'Sarah Jenkins', 'email' => 'sarah.jenkins@campus.edu', 'role' => 'user', 'org' => 1],
    ];

    $userIds = [];
    foreach ($users as $u) {
        $id = UserRepository::create([
            'name' => $u['name'],
            'email' => $u['email'],
            'password_hash' => $defaultPass,
            'role' => $u['role'],
            'organization_id' => $u['org'],
            'status' => 'active'
        ]);
        $userIds[$u['email']] = $id;
    }

    echo " - Created " . count($users) . " demonstration user accounts.\n";

    // 4. Reports (Lost & Found)
    echo "4. Seeding lost and found incident reports...\n";

    $reportsData = [
        // 1. Lost by Alex
        [
            'creator_id' => $userIds['alex.morgan@campus.edu'],
            'report_type' => 'lost',
            'item_title' => 'Sony WH-1000XM4 Wireless Headphones',
            'category' => 'Electronics',
            'brand' => 'Sony',
            'color' => 'Black',
            'description' => "Lost my matte black Sony noise-cancelling headphones in their original hard travel case. Last used while studying at the library cubicles.",
            'general_location' => 'Central Library 2nd Floor Study Cubicle',
            'latitude' => 13.0827,
            'longitude' => 80.2707,
            'event_date' => date('Y-m-d', strtotime('-2 days')),
            'reward_amount' => 25.00,
            'expiration_date' => date('Y-m-d', strtotime('+58 days')),
            'status' => 'active',
        ],
        // 2. Found by Elena (Will match #1 strongly!)
        [
            'creator_id' => $userIds['elena.rostova@campus.edu'],
            'report_type' => 'found',
            'item_title' => 'Black Over-Ear Wireless Headphones in Carrying Case',
            'category' => 'Electronics',
            'brand' => 'Sony',
            'color' => 'Black',
            'description' => "Found a pair of black Sony wireless headphones left behind on study desk #14. The headphones are in a zippered travel case.",
            'general_location' => 'Library Study Carrel 14',
            'latitude' => 13.0830,
            'longitude' => 80.2710,
            'event_date' => date('Y-m-d', strtotime('-2 days')),
            'expiration_date' => date('Y-m-d', strtotime('+88 days')),
            'status' => 'active',
            'private' => [
                'custody_status' => 'Held by Finder',
                'safe_collection_instructions' => 'Can meet outside Central Library Entrance between 2pm-4pm',
                'secret_question' => 'What sticker or label is inside the headphone zippered case lid?',
                'secret_answer' => 'A small silver barcode label ending in -882',
                'secret_notes' => 'Checked with library desk staff, no one claimed it during closing',
            ]
        ],
        // 3. Lost by David
        [
            'creator_id' => $userIds['david.chen@metro.org'],
            'report_type' => 'lost',
            'item_title' => 'Hydro Flask 32oz Insulated Water Bottle',
            'category' => 'Water Bottles',
            'brand' => 'Hydro Flask',
            'color' => 'Cobalt Blue',
            'description' => "Stainless steel wide mouth bottle with flex cap. Has a minor scratch near the handle.",
            'general_location' => 'Campus Sports Complex Pavilion',
            'latitude' => 13.0850,
            'longitude' => 80.2740,
            'event_date' => date('Y-m-d', strtotime('-3 days')),
            'expiration_date' => date('Y-m-d', strtotime('+57 days')),
            'status' => 'active',
        ],
        // 4. Found by Sarah (Will match #3)
        [
            'creator_id' => $userIds['sarah.jenkins@campus.edu'],
            'report_type' => 'found',
            'item_title' => 'Blue Stainless Steel Water Bottle with Straw Lid',
            'category' => 'Water Bottles',
            'brand' => 'Hydro Flask',
            'color' => 'Blue',
            'description' => "Found blue metal water flask left on the sports bleachers after evening basketball.",
            'general_location' => 'Track and Field Bleachers',
            'latitude' => 13.0852,
            'longitude' => 80.2743,
            'event_date' => date('Y-m-d', strtotime('-3 days')),
            'expiration_date' => date('Y-m-d', strtotime('+87 days')),
            'status' => 'active',
            'private' => [
                'custody_status' => 'Handed to Security Desk',
                'safe_collection_instructions' => 'Left with Sports Complex Security Officer on duty',
                'secret_question' => 'What distinctive mark is on the bottom underside of the flask?',
                'secret_answer' => 'Minor circular dent near bottom rim',
            ]
        ],
        // 5. Found by Elena (ID Card)
        [
            'creator_id' => $userIds['elena.rostova@campus.edu'],
            'report_type' => 'found',
            'item_title' => 'Student ID Card for Computer Science Department',
            'category' => 'Identity Documents',
            'brand' => 'University ID',
            'color' => 'Red Lanyard',
            'description' => "Found student identity card on red lanyard near the cafeteria food court.",
            'general_location' => 'North Cafeteria Checkout Line',
            'latitude' => 13.0815,
            'longitude' => 80.2690,
            'event_date' => date('Y-m-d', strtotime('-1 days')),
            'expiration_date' => date('Y-m-d', strtotime('+89 days')),
            'status' => 'active',
            'private' => [
                'custody_status' => 'Left at Reception / Helpdesk',
                'safe_collection_instructions' => 'Deposited with Student Services Desk, Building A',
                'secret_question' => 'What is the full student roll number prefix?',
                'secret_answer' => 'CS-2024-8841',
            ]
        ],
        // 6. Lost by Sarah (Laptop Sleeve)
        [
            'creator_id' => $userIds['sarah.jenkins@campus.edu'],
            'report_type' => 'lost',
            'item_title' => 'Bellroy Grey Leather Laptop Sleeve 14-inch',
            'category' => 'Bags and Accessories',
            'brand' => 'Bellroy',
            'color' => 'Grey',
            'description' => "Charcoal grey magnetic laptop sleeve. Contains notes inside the front zippered compartment.",
            'general_location' => 'Main Seminar Auditorium Row G',
            'latitude' => 13.0840,
            'longitude' => 80.2725,
            'event_date' => date('Y-m-d', strtotime('-4 days')),
            'expiration_date' => date('Y-m-d', strtotime('+56 days')),
            'status' => 'active',
        ],
        // 7. Found by David (Keys)
        [
            'creator_id' => $userIds['david.chen@metro.org'],
            'report_type' => 'found',
            'item_title' => 'Set of 3 Brass House Keys with Toyota Fob',
            'category' => 'Keys',
            'brand' => 'Toyota',
            'color' => 'Brass',
            'description' => "Found keys with black car remote keyfob on a metallic carabiner clip.",
            'general_location' => 'Visitor Parking Lot B near Section 3',
            'latitude' => 13.0865,
            'longitude' => 80.2760,
            'event_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+85 days')),
            'status' => 'active',
            'private' => [
                'custody_status' => 'Handed to Security Desk',
                'safe_collection_instructions' => 'Parking Booth Security Station',
                'secret_question' => 'What specific color and shape is the carabiner clip?',
                'secret_answer' => 'Matte olive green D-shaped carabiner',
            ]
        ],
        // 8. Lost by Alex (Notebook)
        [
            'creator_id' => $userIds['alex.morgan@campus.edu'],
            'report_type' => 'lost',
            'item_title' => 'Moleskine Grid Notebook with Blue Pen',
            'category' => 'Books and Stationery',
            'brand' => 'Moleskine',
            'color' => 'Black',
            'description' => "Black hardcover grid notebook containing embedded systems project diagrams and equations.",
            'general_location' => 'Robotics Laboratory Lab 3',
            'latitude' => 13.0835,
            'longitude' => 80.2715,
            'event_date' => date('Y-m-d', strtotime('-6 days')),
            'expiration_date' => date('Y-m-d', strtotime('+54 days')),
            'status' => 'active',
        ],
    ];

    $reportIds = [];
    foreach ($reportsData as $idx => $rData) {
        $repId = ReportRepository::create($rData);
        $reportIds[$idx + 1] = $repId;

        if (!empty($rData['private'])) {
            ReportRepository::savePrivateDetails($repId, $rData['private']);
        }
    }

    echo " - Seeded " . count($reportsData) . " realistic reports.\n";

    // 5. Run Batch Smart Matching Engine
    echo "5. Running deterministic Smart Matching Engine...\n";
    $matchCount = MatchingService::runBatchMatching();
    echo " - Algorithmic correlations generated: {$matchCount} candidate pairs.\n";

    // 6. Seed Sample Claims & Handover
    echo "6. Seeding ownership claims and state transition history...\n";

    // Alex claims the Sony headphones found by Elena
    $claimId = ClaimRepository::create([
        'report_id' => $reportIds[2], // Found headphones
        'claimant_id' => $userIds['alex.morgan@campus.edu'],
        'claim_description' => "I was studying at the library cubicle #14 between 1pm and 3pm on Tuesday. Left the case on the desk when rushing to an exam.",
        'ownership_evidence' => "The case has a small silver barcode label inside ending in -882. I also have the original digital purchase receipt on Amazon if needed.",
    ]);

    // Reviewer (Elena) reviews and approves the claim
    ClaimRepository::updateStatus(
        $claimId,
        'approved',
        $userIds['elena.rostova@campus.edu'],
        'Evidence verified. Claimant accurately stated the barcode label ending -882.',
        'Please meet me at the Central Library Main Entrance today at 3:00 PM.'
    );

    // 7. Seed Sample Notifications
    echo "7. Seeding notifications dispatch log...\n";
    NotificationRepository::create(
        $userIds['alex.morgan@campus.edu'],
        'claim_approved',
        'Claim Approved &mdash; Safe Collection Instructions',
        'Your claim for Sony WH-1000XM4 Headphones has been approved by the finder. Safe collection details have been provided.',
        "/claims/{$claimId}"
    );

    NotificationRepository::create(
        $userIds['david.chen@metro.org'],
        'match_found',
        'High-Confidence Match Detected',
        'TRACE identified an 88% potential match for your lost Hydro Flask Water Bottle.',
        '/matches'
    );

    // 8. Seed Audit Logs
    echo "8. Seeding immutable audit log records...\n";
    AuditLogRepository::log($userIds['admin@trace.internal'], 'system_bootstrap', 'system', 1, 'TRACE database seeded with demonstration accounts');
    AuditLogRepository::log($userIds['alex.morgan@campus.edu'], 'claim_submitted', 'claims', $claimId, 'Claimant submitted evidence for report #2');
    AuditLogRepository::log($userIds['elena.rostova@campus.edu'], 'claim_approved', 'claims', $claimId, 'Finder verified secret answer and approved claim');

    echo "\n===============================================\n";
    echo " DATABASE SEEDING COMPLETED SUCCESSFULLY!       \n";
    echo "===============================================\n";
    echo "Demonstration Credentials:\n";
    echo " 1. Administrator:\n";
    echo "    Email:    admin@trace.internal\n";
    echo "    Password: Password123!\n\n";
    echo " 2. Standard User (Alex Morgan - Lost Headphones):\n";
    echo "    Email:    alex.morgan@campus.edu\n";
    echo "    Password: Password123!\n\n";
    echo " 3. Standard User (Elena Rostova - Found Headphones):\n";
    echo "    Email:    elena.rostova@campus.edu\n";
    echo "    Password: Password123!\n";
    echo "===============================================\n";

} catch (\Throwable $e) {
    echo "ERROR SEEDING DATABASE: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
