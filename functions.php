<?php
require_once __DIR__.'/config.php';

function esc(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(string $path = ''): string {
    return rtrim(APP_ORIGIN, '/').rtrim(BASE_PATH, '/').'/'.ltrim($path, '/');
}

function send_mail(string $to, string $subject, string $html): void {
    $headers = [];
    $headers[] = 'From: '.MAIL_FROM_NAME.' <'.MAIL_FROM.'>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    @mail($to, $subject, $html, implode("\r\n", $headers));
}

function calculate_next_run(string $freq, int $interval, string $time, ?string $from = null): string {
    $tz = new DateTimeZone(date_default_timezone_get());
    $now = new DateTime('now', $tz);
    $start = $from ? new DateTime($from, $tz) : clone $now;
    list($h,$m) = explode(':', $time);
    $start->setTime((int)$h, (int)$m, 0);
    while ($start <= $now) {
        switch ($freq) {
            case 'DAILY':
                $start->modify('+'.$interval.' day');
                break;
            case 'WEEKLY':
                $start->modify('+'.$interval.' week');
                break;
            case 'MONTHLY':
                $start->modify('+'.$interval.' month');
                break;
            case 'YEARLY':
                $start->modify('+'.$interval.' year');
                break;
        }
        $start->setTime((int)$h, (int)$m, 0);
    }
    return $start->format('Y-m-d H:i:s');
}

function schema_labels(array $schema): array {
    $labels = [];
    $walk = function($nodes) use (&$labels, &$walk) {
        foreach ($nodes as $node) {
            if (isset($node['config']['label'])) {
                $key = $node['name'] ?? $node['id'] ?? null;
                if ($key) $labels[$key] = $node['config']['label'];
            }
            if (!empty($node['columns'])) {
                foreach ($node['columns'] as $col) {
                    if (!empty($col['fields'])) {
                        $walk($col['fields']);
                    }
                }
            }
            if (!empty($node['fields'])) {
                $walk($node['fields']);
            }
        }
    };
    if (!empty($schema['fields'])) {
        $walk($schema['fields']);
    }
    return $labels;
}
