<?php

namespace App\Services;

/**
 * Parses storage/logs/laravel.log for ERROR-level+ entries. There's no
 * external error monitoring service wired up (would need a third-party
 * account/API key), so this is the zero-dependency substitute: read what
 * Laravel already logs on every unhandled exception, and surface it in the
 * admin UI instead of requiring someone to SSH in and tail a file.
 */
class ErrorLogReader
{
    private const LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /**
     * @return array<int, array{timestamp: string, level: string, message: string, full: string}>
     */
    public function recent(int $limit = 100, int $tailBytes = 3_000_000): array
    {
        $path = storage_path('logs/laravel.log');

        if (!is_file($path)) {
            return [];
        }

        $raw = $this->tail($path, $tailBytes);

        preg_match_all(
            '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\z)/ms',
            $raw,
            $matches,
            PREG_SET_ORDER
        );

        $entries = [];
        foreach ($matches as $m) {
            if (!in_array(strtoupper($m[3]), self::LEVELS, true)) {
                continue;
            }

            $entries[] = [
                'timestamp' => $m[1],
                'level' => strtoupper($m[3]),
                'message' => trim(explode("\n", $m[4])[0]),
                'full' => trim($m[4]),
            ];
        }

        return array_slice(array_reverse($entries), 0, $limit);
    }

    public function countSince(\DateTimeInterface $since): int
    {
        $cutoff = $since->format('Y-m-d H:i:s');

        return count(array_filter(
            $this->recent(500),
            fn (array $entry) => $entry['timestamp'] >= $cutoff
        ));
    }

    private function tail(string $path, int $bytes): string
    {
        $size = filesize($path);
        $handle = fopen($path, 'r');

        if ($size > $bytes) {
            fseek($handle, -$bytes, SEEK_END);
        }

        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
