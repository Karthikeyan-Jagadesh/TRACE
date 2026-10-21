<?php
namespace App\Helpers;

use App\Core\Session;

class Helper
{
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function csrfField(): string
    {
        $token = Session::csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::escape($token) . '">';
    }

    public static function csrfToken(): string
    {
        return Session::csrfToken();
    }

    public static function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');
        return $path;
    }

    public static function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    public static function uploadUrl(?string $path): string
    {
        if (empty($path)) {
            return '/assets/images/placeholder-item.svg';
        }
        return '/uploads/' . ltrim($path, '/');
    }

    public static function formatDate(?string $date, string $format = 'M d, Y'): string
    {
        if (!$date) return 'Unknown date';
        $time = strtotime($date);
        return $time ? date($format, $time) : $date;
    }

    public static function timeAgo(?string $datetime): string
    {
        if (!$datetime) return 'Recently';
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('M d, Y', $time);
        }
    }

    public static function statusBadge(string $status): string
    {
        $status = strtolower(trim($status));
        $classes = match ($status) {
            'active' => 'badge-active',
            'recovered', 'completed', 'approved' => 'badge-recovered',
            'claimed', 'under_review' => 'badge-claimed',
            'expired', 'closed', 'rejected' => 'badge-expired',
            default => 'badge-neutral'
        };

        $label = match ($status) {
            'active' => 'Active Signal',
            'under_review' => 'Under Review',
            'claimed' => 'Claim Pending',
            'recovered' => 'Recovered',
            'completed' => 'Completed',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            'closed' => 'Closed',
            default => ucfirst(str_replace('_', ' ', $status))
        };

        return '<span class="status-badge ' . $classes . '">' . self::escape($label) . '</span>';
    }

    public static function typeBadge(string $type): string
    {
        $type = strtolower($type);
        if ($type === 'lost') {
            return '<span class="type-badge badge-lost"><span class="badge-dot"></span>LOST</span>';
        }
        return '<span class="type-badge badge-found"><span class="badge-dot"></span>FOUND</span>';
    }

    public static function scoreBadge(int $score): string
    {
        if ($score >= 80) {
            $band = 'Strong Match';
            $cls = 'score-strong';
        } elseif ($score >= 60) {
            $band = 'Possible Match';
            $cls = 'score-possible';
        } else {
            $band = 'Weak Match';
            $cls = 'score-weak';
        }
        return '<span class="score-badge ' . $cls . ' font-mono">' . $score . '% &mdash; ' . $band . '</span>';
    }
}
