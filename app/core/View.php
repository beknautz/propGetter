<?php
/**
 * PropIntel CRM - Simple view renderer
 *
 * Renders PHP view files with shared data.
 * Supports layouts and partials.
 */

class View
{
    private static array $shared = [];

    /** Share data with every view */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /** Render a view file with the given data */
    public static function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $data = array_merge(self::$shared, $data);

        // Capture inner content
        $content = self::capture($view, $data);

        if ($layout) {
            $data['content'] = $content;
            echo self::capture("layouts/{$layout}", $data);
        } else {
            echo $content;
        }
    }

    /** Render a partial (no layout) */
    public static function partial(string $view, array $data = []): string
    {
        return self::capture("partials/{$view}", array_merge(self::$shared, $data));
    }

    /** Render a partial and echo it */
    public static function include(string $view, array $data = []): void
    {
        echo self::partial($view, $data);
    }

    /** Capture view output to a string */
    private static function capture(string $view, array $data): string
    {
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            throw new RuntimeException("View not found: {$file}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /** Send a JSON response */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Redirect to a URL, optionally setting a flash message */
    public static function redirect(string $url, string $message = '', string $type = 'success', int $status = 302): void
    {
        if ($message !== '') {
            $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
        }
        header("Location: {$url}", true, $status);
        exit;
    }

    /** Return Bootstrap alert HTML for the current flash message, then clear it */
    public static function flashHtml(): string
    {
        if (empty($_SESSION['_flash'])) return '';
        $f = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        $msg  = self::e($f['message']);
        $type = self::e($f['type']);
        return "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">"
             . $msg
             . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
             . '</div>';
    }

    /** Escape output for HTML */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Format currency */
    public static function money(?float $amount, string $symbol = '$'): string
    {
        if ($amount === null) return '—';
        return $symbol . number_format($amount, 0);
    }

    /** Format a date */
    public static function date(?string $date, string $format = 'M j, Y'): string
    {
        if (!$date) return '—';
        return date($format, strtotime($date));
    }

    /** Return Bootstrap badge HTML for lead status */
    public static function statusBadge(string $status): string
    {
        $map = [
            'New'             => 'primary',
            'Researching'     => 'info',
            'Skip Trace Needed' => 'warning',
            'Contacted'       => 'secondary',
            'Follow-Up'       => 'warning',
            'Appointment Set' => 'success',
            'Offer Made'      => 'success',
            'Under Contract'  => 'dark',
            'Closed'          => 'success',
            'Dead'            => 'danger',
        ];
        $color = $map[$status] ?? 'secondary';
        $safe  = self::e($status);
        return "<span class=\"badge bg-{$color}\">{$safe}</span>";
    }

    /** Return Bootstrap badge HTML for lead score */
    public static function scoreBadge(int $score): string
    {
        if ($score >= 80)      $color = 'danger';
        elseif ($score >= 60)  $color = 'warning text-dark';
        elseif ($score >= 40)  $color = 'info';
        else                   $color = 'secondary';

        return "<span class=\"badge bg-{$color} score-badge\">{$score}</span>";
    }

    /** Boolean flag icon */
    public static function flagIcon(bool|int $value, string $label = ''): string
    {
        if ($value) {
            return '<span class="text-danger" title="' . self::e($label) . '"><i class="bi bi-check-circle-fill"></i></span>';
        }
        return '<span class="text-muted" title="' . self::e($label) . '"><i class="bi bi-dash-circle"></i></span>';
    }
}
