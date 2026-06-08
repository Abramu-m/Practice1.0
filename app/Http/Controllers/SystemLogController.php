<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemLogController extends Controller
{
    /**
     * Display a paginated list of system logs.
     */
    public function index(Request $request)
    {
        $logsDir = storage_path('logs');
        if (!is_dir($logsDir)) {
            abort(404, 'Logs directory not found');
        }

        // Collect available .log files sorted by most recently modified first
        $files = collect(File::files($logsDir))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.log'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        if ($files->isEmpty()) {
            return view('system_logs.index', [
                'entries' => new LengthAwarePaginator([], 0, 50, 1),
                'files' => [],
                'currentFile' => null,
            ]);
        }

        $requestedFile = $request->query('file');
        $defaultFile = $files->first()->getFilename();
        $currentFile = $requestedFile && $files->contains(fn ($f) => $f->getFilename() === $requestedFile)
            ? $requestedFile
            : (file_exists($logsDir . DIRECTORY_SEPARATOR . 'laravel.log') ? 'laravel.log' : $defaultFile);

        $path = $logsDir . DIRECTORY_SEPARATOR . $currentFile;

        // Read and parse log entries
        $entries = $this->parseLogFile($path);

        // Optional search filter
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $entries = array_values(array_filter($entries, function ($e) use ($q) {
                $msg = (string) ($e['message'] ?? '');
                $raw = (string) ($e['raw'] ?? '');
                return (stripos($msg, $q) !== false) || (stripos($raw, $q) !== false);
            }));
        }

        // Pagination
        $perPage = (int) $request->query('per_page', 50);
        if ($perPage < 10) { $perPage = 10; }
        if ($perPage > 200) { $perPage = 200; }
        $page = (int) $request->query('page', 1);

        $total = count($entries);
        $offset = max(0, ($page - 1) * $perPage);
        $pageItems = array_slice($entries, $offset, $perPage);

        $paginator = new LengthAwarePaginator($pageItems, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('system_logs.index', [
            'entries' => $paginator,
            'files' => $files->map(fn ($f) => $f->getFilename())->all(),
            'currentFile' => $currentFile,
        ]);
    }

    /**
     * Parse a Laravel log file into discrete entries.
     * Each entry begins with a line like: [YYYY-MM-DD HH:MM:SS] env.LEVEL: message
     * Returns an array of associative arrays with keys: date, env, level, message, context, stack, raw
     */
    protected function parseLogFile(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            return [];
        }

        $entries = [];
        $buffer = [];
        $headerPattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([\w-]+)\.([A-Z]+):\s(.*)$/';

        while (($line = fgets($handle)) !== false) {
            if (preg_match($headerPattern, $line)) {
                // Flush previous
                if (!empty($buffer)) {
                    $entries[] = $this->buildEntry($buffer, $headerPattern);
                    $buffer = [];
                }
            }
            $buffer[] = rtrim($line, "\r\n");
        }
        // Flush last buffer
        if (!empty($buffer)) {
            $entries[] = $this->buildEntry($buffer, $headerPattern);
        }
        fclose($handle);

        // Newest first (Laravel appends, so last lines are newest). Reverse for descending order.
        $entries = array_reverse($entries);

        return $entries;
    }

    /**
     * Build a structured entry from buffered lines.
     */
    protected function buildEntry(array $lines, string $headerPattern): array
    {
        $first = $lines[0] ?? '';
        $date = $env = $level = $message = '';
        $context = null;
        if (preg_match($headerPattern, $first, $m)) {
            $date = $m[1] ?? '';
            $env = $m[2] ?? '';
            $level = $m[3] ?? '';
            $message = $m[4] ?? '';

            // Try to split message and JSON context if present
            // Example: "Something happened {"user_id":1} []"
            $msg = $message;
            $ctx = null;
            // Heuristic: context JSON often follows a trailing " { ... } [ ... ]"
            if (preg_match('/^(.*?)(\{.*\})\s*\[.*\]\s*$/', $message, $mm)) {
                $msg = trim($mm[1]);
                $json = $mm[2];
                $decoded = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $ctx = $decoded;
                }
            }
            $message = $msg;
            $context = $ctx;
        }

        $stack = [];
        if (count($lines) > 1) {
            $stack = array_slice($lines, 1);
        }

        return [
            'date' => $date,
            'env' => $env,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'stack' => $stack,
            'raw' => implode("\n", $lines),
        ];
    }

    /**
     * Clear log files.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'file' => 'nullable|string',
        ]);

        $logsDir = storage_path('logs');
        $file = $request->input('file');

        if ($file) {
            // Delete specific file
            $path = $logsDir . DIRECTORY_SEPARATOR . $file;
            if (file_exists($path) && is_file($path)) {
                unlink($path);
                return redirect()->route('system.logs.index')
                    ->with('success', "Log file '{$file}' has been deleted.");
            }
        } else {
            // Delete all log files
            $files = File::files($logsDir);
            $cleared = 0;
            foreach ($files as $f) {
                if (Str::endsWith($f->getFilename(), '.log')) {
                    unlink($f->getPathname());
                    $cleared++;
                }
            }
            return redirect()->route('system.logs.index')
                ->with('success', "{$cleared} log file(s) have been deleted.");
        }

        return redirect()->route('system.logs.index')
            ->with('error', 'Log file not found.');
    }
}
