<?php
/**
 * TRACE — Smart Matching Engine Unit & Functional Tests
 * Run via: php tests/MatchingServiceTest.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Services\MatchingService;

class MatchingServiceTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "===============================================\n";
        echo " RUNNING MATCHING ENGINE TESTS                 \n";
        echo "===============================================\n";

        $this->testTokenization();
        $this->testJaccardSimilarity();
        $this->testHaversineDistance();
        $this->testExactMatchScoring();
        $this->testIncompatibleCategoryRejection();
        $this->testDateProximityDecay();
        $this->testSelfReportExclusion();

        echo "\n-----------------------------------------------\n";
        echo "Tests Passed: {$this->passed} | Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            echo "FAILED: Some matching tests failed.\n";
            exit(1);
        } else {
            echo "SUCCESS: All matching engine tests passed 100%!\n";
        }
    }

    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            echo "  [PASS] {$message}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$message}\n";
            $this->failed++;
        }
    }

    private function testTokenization(): void
    {
        $tokens = MatchingService::tokenize("Sony WH-1000XM4 Noise-Cancelling Headphones in black case!");
        $this->assert(in_array('sony', $tokens, true), "Tokenize extracts 'sony'");
        $this->assert(in_array('headphones', $tokens, true), "Tokenize extracts 'headphones'");
        $this->assert(!in_array('in', $tokens, true), "Tokenize removes stop word 'in'");
    }

    private function testJaccardSimilarity(): void
    {
        $tokens1 = ['apple', 'macbook', 'air', 'm2'];
        $tokens2 = ['apple', 'macbook', 'air', 'm2'];
        $simIdentical = MatchingService::jaccardSimilarity($tokens1, $tokens2);
        $this->assert(abs($simIdentical - 1.0) < 0.001, "Identical token sets return Jaccard index 1.0");

        $tokens3 = ['dell', 'xps', 'laptop'];
        $simDisjoint = MatchingService::jaccardSimilarity($tokens1, $tokens3);
        $this->assert($simDisjoint === 0.0, "Disjoint token sets return Jaccard index 0.0");
    }

    private function testHaversineDistance(): void
    {
        // 2 points ~330 meters apart on campus
        $dist = MatchingService::haversineDistance(13.0827, 80.2707, 13.0830, 80.2710);
        $this->assert($dist > 0.02 && $dist < 0.1, "Haversine accurately measures nearby coordinates: " . round($dist * 1000) . "m");
    }

    private function testExactMatchScoring(): void
    {
        $lost = [
            'id' => 101,
            'creator_id' => 1,
            'report_type' => 'lost',
            'item_title' => 'Sony WH-1000XM4 Headphones',
            'category' => 'Electronics',
            'brand' => 'Sony',
            'color' => 'Black',
            'description' => 'Black wireless noise cancelling headphones in case',
            'general_location' => 'Library 2nd Floor',
            'latitude' => 13.0827,
            'longitude' => 80.2707,
            'event_date' => '2026-10-20',
            'status' => 'active'
        ];

        $found = [
            'id' => 202,
            'creator_id' => 2,
            'report_type' => 'found',
            'item_title' => 'Sony Over-Ear Headphones',
            'category' => 'Electronics',
            'brand' => 'Sony',
            'color' => 'Black',
            'description' => 'Found black Sony wireless headphones on study desk',
            'general_location' => 'Library Study Carrel',
            'latitude' => 13.0830,
            'longitude' => 80.2710,
            'event_date' => '2026-10-20',
            'status' => 'active'
        ];

        $result = MatchingService::evaluatePair($lost, $found);
        $this->assert($result !== null, "Correlated pair produces evaluation result");
        $this->assert($result['score'] >= 80, "Exact match scores in Strong band (Score: {$result['score']}%)");
        $this->assert($result['confidence_band'] === 'strong', "Confidence band is classified as 'strong'");
        $this->assert(str_contains($result['explanation'], 'Electronics'), "Explanation details category match");
    }

    private function testIncompatibleCategoryRejection(): void
    {
        $lost = [
            'id' => 101, 'creator_id' => 1, 'report_type' => 'lost',
            'item_title' => 'Car Keys', 'category' => 'Keys', 'brand' => '', 'color' => '',
            'description' => 'Brass keys', 'general_location' => 'Cafeteria',
            'latitude' => null, 'longitude' => null, 'event_date' => '2026-10-20', 'status' => 'active'
        ];
        $found = [
            'id' => 202, 'creator_id' => 2, 'report_type' => 'found',
            'item_title' => 'Blue Water Flask', 'category' => 'Water Bottles', 'brand' => '', 'color' => '',
            'description' => 'Flask bottle', 'general_location' => 'Cafeteria',
            'latitude' => null, 'longitude' => null, 'event_date' => '2026-10-20', 'status' => 'active'
        ];

        $result = MatchingService::evaluatePair($lost, $found);
        $this->assert($result === null, "Incompatible categories (Keys vs Water Bottles) are rejected");
    }

    private function testDateProximityDecay(): void
    {
        $lostSameDate = [
            'id' => 101, 'creator_id' => 1, 'report_type' => 'lost',
            'item_title' => 'Black Umbrella', 'category' => 'Other', 'brand' => '', 'color' => 'Black',
            'description' => 'Foldable umbrella', 'general_location' => 'Main Entrance',
            'latitude' => null, 'longitude' => null, 'event_date' => '2026-10-20', 'status' => 'active'
        ];
        $foundSameDate = [
            'id' => 202, 'creator_id' => 2, 'report_type' => 'found',
            'item_title' => 'Black Umbrella', 'category' => 'Other', 'brand' => '', 'color' => 'Black',
            'description' => 'Foldable umbrella', 'general_location' => 'Main Entrance',
            'latitude' => null, 'longitude' => null, 'event_date' => '2026-10-20', 'status' => 'active'
        ];
        $foundOldDate = $foundSameDate;
        $foundOldDate['event_date'] = '2026-07-01'; // 110 days apart

        $evalSame = MatchingService::evaluatePair($lostSameDate, $foundSameDate);
        $evalOld = MatchingService::evaluatePair($lostSameDate, $foundOldDate);

        $this->assert($evalSame['score'] > $evalOld['score'], "Same day score ({$evalSame['score']}%) is higher than 110 days apart ({$evalOld['score']}%)");
        $this->assert(($evalSame['score'] - $evalOld['score']) === 10, "Date decay delta is exactly 10 points");
    }

    private function testSelfReportExclusion(): void
    {
        $lost = [
            'id' => 101, 'creator_id' => 5, 'report_type' => 'lost',
            'item_title' => 'Watch', 'category' => 'Jewelry', 'status' => 'active'
        ];
        $found = [
            'id' => 102, 'creator_id' => 5, 'report_type' => 'found', // Same user
            'item_title' => 'Watch', 'category' => 'Jewelry', 'status' => 'active'
        ];

        $result = MatchingService::evaluatePair($lost, $found);
        $this->assert($result === null, "Reports from same user are excluded from matching against each other");
    }
}

$test = new MatchingServiceTest();
$test->run();
