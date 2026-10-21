<?php
namespace App\Services;

use App\Core\Database;
use App\Repositories\MatchRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ReportRepository;

class MatchingService
{
    private static array $stopWords = [
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are',
        'as', 'at', 'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but',
        'by', 'could', 'did', 'do', 'does', 'doing', 'down', 'during', 'each', 'few', 'for', 'from',
        'further', 'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him',
        'himself', 'his', 'how', 'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself', 'just', 'me',
        'more', 'most', 'my', 'myself', 'no', 'nor', 'not', 'of', 'off', 'on', 'once', 'only', 'or',
        'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'she', 'should',
        'so', 'some', 'such', 'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then',
        'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up',
        'very', 'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why',
        'with', 'would', 'you', 'your', 'yours', 'yourself', 'yourselves', 'item', 'lost', 'found', 'please'
    ];

    /**
     * Compare a lost report and a found report, returning score, band, and detailed explanation.
     */
    public static function evaluatePair(array $lostReport, array $foundReport): ?array
    {
        // Must be opposite types
        if ($lostReport['report_type'] === $foundReport['report_type']) {
            return null;
        }

        // Must not be self-report
        if ($lostReport['id'] === $foundReport['id'] || $lostReport['creator_id'] === $foundReport['creator_id']) {
            return null;
        }

        // Must both be active
        if ($lostReport['status'] !== 'active' || $foundReport['status'] !== 'active') {
            return null;
        }

        $explanations = [];
        $totalScore = 0;

        // 1. Category Similarity (Weight: 20)
        $categoryScore = 0;
        if (strcasecmp($lostReport['category'], $foundReport['category']) === 0) {
            $categoryScore = 20;
            $explanations[] = "Identical category '{$lostReport['category']}' (+20)";
        } else {
            // Check compatible categories
            $compatible = self::areCategoriesCompatible($lostReport['category'], $foundReport['category']);
            if ($compatible) {
                $categoryScore = 8;
                $explanations[] = "Compatible categories (+8)";
            } else {
                // Incompatible categories get 0 points
                return null;
            }
        }
        $totalScore += $categoryScore;

        // 2. Item Title Token Similarity (Weight: 20)
        $titleTokens1 = self::tokenize($lostReport['item_title']);
        $titleTokens2 = self::tokenize($foundReport['item_title']);
        $titleJaccard = self::jaccardSimilarity($titleTokens1, $titleTokens2);
        $titleOverlap = self::overlapCoefficient($titleTokens1, $titleTokens2);
        $titleSim = max($titleJaccard, $titleOverlap * 0.9);
        $titleScore = (int)round($titleSim * 20);
        $totalScore += $titleScore;
        if ($titleScore > 0) {
            $commonTitleWords = array_intersect($titleTokens1, $titleTokens2);
            $wordsStr = implode(', ', array_slice($commonTitleWords, 0, 3));
            $explanations[] = "Title keyword match: '{$wordsStr}' (+{$titleScore})";
        }

        // 3. Description Keyword Similarity (Weight: 15)
        $descTokens1 = self::tokenize($lostReport['description']);
        $descTokens2 = self::tokenize($foundReport['description']);
        $descJaccard = self::jaccardSimilarity($descTokens1, $descTokens2);
        $descOverlap = self::overlapCoefficient($descTokens1, $descTokens2);
        $descSim = max($descJaccard, $descOverlap * 0.85);
        $descScore = (int)round($descSim * 15);
        $totalScore += $descScore;
        if ($descScore > 0) {
            $explanations[] = "Description details overlap (+{$descScore})";
        }

        // 4. Brand Similarity (Weight: 10)
        $brandScore = 0;
        $brand1 = trim(strtolower($lostReport['brand'] ?? ''));
        $brand2 = trim(strtolower($foundReport['brand'] ?? ''));
        if (!empty($brand1) && !empty($brand2)) {
            if ($brand1 === $brand2 || str_contains($brand1, $brand2) || str_contains($brand2, $brand1)) {
                $brandScore = 10;
                $explanations[] = "Matching brand '{$lostReport['brand']}' (+10)";
            }
        } elseif (empty($brand1) || empty($brand2)) {
            // Neutral partial score when brand wasn't specified by one of the parties
            $brandScore = 4;
        }
        $totalScore += $brandScore;

        // 5. Color Similarity (Weight: 10)
        $colorScore = 0;
        $color1 = trim(strtolower($lostReport['color'] ?? ''));
        $color2 = trim(strtolower($foundReport['color'] ?? ''));
        if (!empty($color1) && !empty($color2)) {
            if ($color1 === $color2 || str_contains($color1, $color2) || str_contains($color2, $color1)) {
                $colorScore = 10;
                $explanations[] = "Matching color '{$lostReport['color']}' (+10)";
            }
        } elseif (empty($color1) || empty($color2)) {
            // Neutral partial score when color not specified
            $colorScore = 4;
        }
        $totalScore += $colorScore;

        // 6. Date Proximity (Weight: 10)
        $dateScore = 0;
        $d1 = strtotime($lostReport['event_date']);
        $d2 = strtotime($foundReport['event_date']);
        if ($d1 && $d2) {
            $dayDiff = abs($d1 - $d2) / 86400;
            if ($dayDiff <= 1) {
                $dateScore = 10;
                $explanations[] = "Event dates within 24 hours (+10)";
            } elseif ($dayDiff <= 3) {
                $dateScore = 9;
                $explanations[] = "Event dates within 3 days (+9)";
            } elseif ($dayDiff <= 7) {
                $dateScore = 7;
                $explanations[] = "Event dates within 1 week (+7)";
            } elseif ($dayDiff <= 14) {
                $dateScore = 5;
                $explanations[] = "Event dates within 2 weeks (+5)";
            } elseif ($dayDiff <= 30) {
                $dateScore = 3;
            }
        }
        $totalScore += $dateScore;

        // 7. Geographic Proximity (Weight: 15)
        $geoScore = 0;
        $hasCoords = !empty($lostReport['latitude']) && !empty($lostReport['longitude']) &&
                     !empty($foundReport['latitude']) && !empty($foundReport['longitude']);

        if ($hasCoords) {
            $distKm = self::haversineDistance(
                (float)$lostReport['latitude'],
                (float)$lostReport['longitude'],
                (float)$foundReport['latitude'],
                (float)$foundReport['longitude']
            );
            if ($distKm <= 0.5) {
                $geoScore = 15;
                $explanations[] = sprintf("Near coordinates (%.1f m apart) (+15)", $distKm * 1000);
            } elseif ($distKm <= 2.0) {
                $geoScore = 12;
                $explanations[] = sprintf("Proximity within %.1f km (+12)", $distKm);
            } elseif ($distKm <= 5.0) {
                $geoScore = 9;
                $explanations[] = sprintf("Proximity within %.1f km (+9)", $distKm);
            } elseif ($distKm <= 10.0) {
                $geoScore = 5;
            }
        } else {
            // Textual location similarity
            $locTokens1 = self::tokenize($lostReport['general_location']);
            $locTokens2 = self::tokenize($foundReport['general_location']);
            $locJaccard = self::jaccardSimilarity($locTokens1, $locTokens2);
            $geoScore = (int)round($locJaccard * 15);
            if ($geoScore > 0) {
                $explanations[] = "Matching location description (+{$geoScore})";
            }
        }
        $totalScore += $geoScore;

        // Clamp between 0 and 100
        $totalScore = max(0, min(100, $totalScore));

        // Evaluate confidence band
        if ($totalScore >= 80) {
            $band = 'strong';
        } elseif ($totalScore >= 60) {
            $band = 'possible';
        } elseif ($totalScore >= 40) {
            $band = 'weak';
        } else {
            return null; // Score < 40 is not considered a viable match candidate
        }

        $explanationText = implode('; ', $explanations);
        if (empty($explanationText)) {
            $explanationText = "Potential match based on shared attributes.";
        }

        return [
            'score' => $totalScore,
            'confidence_band' => $band,
            'explanation' => $explanationText,
            'lost_report_id' => $lostReport['id'],
            'found_report_id' => $foundReport['id'],
        ];
    }

    /**
     * Run matching for a newly created or updated report against opposite active reports.
     */
    public static function runMatchingForReport(int $reportId): array
    {
        $report = ReportRepository::findById($reportId);
        if (!$report || $report['status'] !== 'active') {
            return [];
        }

        $candidates = ReportRepository::getEligibleOpposites($report['report_type'], $report['category']);
        $newMatches = [];

        foreach ($candidates as $candidate) {
            if ($report['report_type'] === 'lost') {
                $lost = $report;
                $found = $candidate;
            } else {
                $lost = $candidate;
                $found = $report;
            }

            $evaluation = self::evaluatePair($lost, $found);
            if ($evaluation && $evaluation['score'] >= 40) {
                MatchRepository::saveMatch(
                    $evaluation['lost_report_id'],
                    $evaluation['found_report_id'],
                    $evaluation['score'],
                    $evaluation['confidence_band'],
                    $evaluation['explanation']
                );

                $newMatches[] = $evaluation;

                // Send notification to both owners if strong or possible match
                if ($evaluation['score'] >= 60) {
                    $otherUser = ($report['report_type'] === 'lost') ? $candidate['creator_id'] : $candidate['creator_id'];
                    NotificationRepository::create(
                        $report['creator_id'],
                        'match_found',
                        'Potential Match Detected',
                        "TRACE found a {$evaluation['score']}% potential match for your report '{$report['item_title']}'.",
                        "/matches"
                    );
                    NotificationRepository::create(
                        $candidate['creator_id'],
                        'match_found',
                        'Potential Match Detected',
                        "TRACE found a {$evaluation['score']}% potential match for your report '{$candidate['item_title']}'.",
                        "/matches"
                    );
                }
            }
        }

        return $newMatches;
    }

    /**
     * Batch run matching across all active reports.
     */
    public static function runBatchMatching(): int
    {
        $lostReports = Database::query("SELECT * FROM reports WHERE report_type = 'lost' AND status = 'active'")->fetchAll();
        $foundReports = Database::query("SELECT * FROM reports WHERE report_type = 'found' AND status = 'active'")->fetchAll();

        $savedCount = 0;
        foreach ($lostReports as $lost) {
            foreach ($foundReports as $found) {
                $eval = self::evaluatePair($lost, $found);
                if ($eval && $eval['score'] >= 40) {
                    MatchRepository::saveMatch(
                        $eval['lost_report_id'],
                        $eval['found_report_id'],
                        $eval['score'],
                        $eval['confidence_band'],
                        $eval['explanation']
                    );
                    $savedCount++;
                }
            }
        }
        return $savedCount;
    }

    public static function tokenize(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        // Replace punctuation with spaces
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $filtered = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if (mb_strlen($token) >= 2 && !in_array($token, self::$stopWords, true)) {
                $filtered[] = $token;
            }
        }
        return array_unique($filtered);
    }

    public static function jaccardSimilarity(array $tokens1, array $tokens2): float
    {
        if (empty($tokens1) && empty($tokens2)) {
            return 0.0;
        }
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));

        if (empty($union)) {
            return 0.0;
        }
        return count($intersection) / count($union);
    }

    public static function overlapCoefficient(array $tokens1, array $tokens2): float
    {
        if (empty($tokens1) || empty($tokens2)) {
            return 0.0;
        }
        $intersection = array_intersect($tokens1, $tokens2);
        $minCount = min(count($tokens1), count($tokens2));
        if ($minCount === 0) {
            return 0.0;
        }
        return count($intersection) / $minCount;
    }

    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private static function areCategoriesCompatible(string $cat1, string $cat2): bool
    {
        $groups = [
            ['Electronics', 'Bags and Accessories'],
            ['Books and Stationery', 'Identity Documents'],
            ['Clothing', 'Jewelry'],
        ];
        foreach ($groups as $group) {
            if (in_array($cat1, $group, true) && in_array($cat2, $group, true)) {
                return true;
            }
        }
        return false;
    }
}
