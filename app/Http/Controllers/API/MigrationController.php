<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class MigrationController extends Controller
{
    /**
     * Handle artisan database & system commands via HTTP request.
     */
    public function run(Request $request)
    {
        $providedKey = $request->query('key') 
            ?? $request->input('key') 
            ?? $request->header('X-Migrate-Key');

        $secretKey = env('MIGRATE_KEY', 'mkemet_secret_migrate_2026');

        if (!$providedKey || $providedKey !== $secretKey) {
            $unauthorizedMsg = 'Unauthorized access. Please provide a valid secret key via ?key=YOUR_KEY or X-Migrate-Key header.';
            
            if ($request->expectsJson() || $request->query('format') === 'json') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $unauthorizedMsg,
                    'tip'     => 'Default secret key is: mkemet_secret_migrate_2026 (or configure MIGRATE_KEY in .env)'
                ], 403);
            }

            return response($this->renderHtml('🔑 Access Denied / غير مصرح', $unauthorizedMsg, false, $providedKey), 403);
        }

        $action = strtolower($request->query('action', $request->input('action', 'migrate')));
        $output = '';
        $success = true;
        $commandName = '';

        try {
            switch ($action) {
                case 'migrate':
                    $commandName = 'php artisan migrate --force';
                    Artisan::call('migrate', ['--force' => true]);
                    $output = Artisan::output();
                    break;

                case 'rollback':
                    $commandName = 'php artisan migrate:rollback --force';
                    Artisan::call('migrate:rollback', ['--force' => true]);
                    $output = Artisan::output();
                    break;

                case 'seed':
                    $commandName = 'php artisan db:seed --force';
                    Artisan::call('db:seed', ['--force' => true]);
                    $output = Artisan::output();
                    break;

                case 'status':
                    $commandName = 'php artisan migrate:status';
                    Artisan::call('migrate:status');
                    $output = Artisan::output();
                    break;

                case 'clear-cache':
                    $commandName = 'php artisan optimize:clear';
                    Artisan::call('optimize:clear');
                    $output = Artisan::output();
                    break;

                case 'storage-link':
                    $commandName = 'php artisan storage:link';
                    Artisan::call('storage:link');
                    $output = Artisan::output();
                    break;

                default:
                    $success = false;
                    $output = "Invalid action: {$action}. Available actions: migrate, rollback, seed, status, clear-cache, storage-link.";
                    break;
            }
        } catch (Throwable $e) {
            $success = false;
            $output = "Error executing command:\n" . $e->getMessage() . "\n\nStack Trace:\n" . $e->getTraceAsString();
        }

        if (empty(trim($output)) && $success) {
            $output = "Command completed with no output (Nothing to migrate or already up to date).";
        }

        if ($request->expectsJson() || $request->query('format') === 'json') {
            return response()->json([
                'status'    => $success ? 'success' : 'error',
                'action'    => $action,
                'command'   => $commandName,
                'output'    => explode("\n", trim($output)),
                'raw_output' => $output
            ], $success ? 200 : 500);
        }

        return response($this->renderHtml("M-Kemet Web Artisan: {$commandName}", $output, $success, $providedKey, $action));
    }

    /**
     * Render terminal-style HTML page for browser access.
     */
    private function renderHtml(string $title, string $output, bool $success, ?string $currentKey = '', string $currentAction = 'migrate'): string
    {
        $keyQuery = $currentKey ? '&key=' . urlencode($currentKey) : '';
        $bgBadge = $success ? '#16a34a' : '#dc2626';
        $statusText = $success ? 'SUCCESS' : 'ERROR / DENIED';
        $escapedOutput = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-color: #f8fafc;
            --accent-color: #38bdf8;
            --border-color: #334155;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 24px;
            direction: ltr;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--card-bg);
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        .title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
            margin: 0;
        }
        .badge {
            background-color: {$bgBadge};
            color: #fff;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .btn {
            background: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn:hover, .btn.active {
            background: var(--accent-color);
            color: #0f172a;
            border-color: var(--accent-color);
            font-weight: 700;
        }
        .terminal {
            background-color: #020617;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow-x: auto;
        }
        .terminal-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .red { background: #ef4444; }
        .yellow { background: #f59e0b; }
        .green { background: #10b981; }
        pre {
            margin: 0;
            font-family: 'Fira Code', 'Courier New', Courier, monospace;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #e2e8f0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .info-box {
            margin-top: 20px;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 0.88rem;
            color: #93c5fd;
            direction: rtl;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">⚡ M-Kemet Web Artisan CLI</h1>
            <span class="badge">{$statusText}</span>
        </div>

        <div class="actions">
            <a href="?action=migrate{$keyQuery}" class="btn " . ($currentAction === 'migrate' ? 'active' : '') . "">▶ Run Migrate</a>
            <a href="?action=status{$keyQuery}" class="btn " . ($currentAction === 'status' ? 'active' : '') . "">📊 Migration Status</a>
            <a href="?action=seed{$keyQuery}" class="btn " . ($currentAction === 'seed' ? 'active' : '') . "">🌱 Run Seed</a>
            <a href="?action=clear-cache{$keyQuery}" class="btn " . ($currentAction === 'clear-cache' ? 'active' : '') . "">🧹 Clear Cache</a>
            <a href="?action=storage-link{$keyQuery}" class="btn " . ($currentAction === 'storage-link' ? 'active' : '') . "">🔗 Storage Link</a>
            <a href="?action=rollback{$keyQuery}" class="btn " . ($currentAction === 'rollback' ? 'active' : '') . "" onclick="return confirm('هل أنت تأكد من إلغاء آخر الهجرات (Rollback)؟')">⚠️ Rollback</a>
        </div>

        <div class="terminal">
            <div class="terminal-header">
                <span class="dot red"></span>
                <span class="dot yellow"></span>
                <span class="dot green"></span>
                <span style="margin-left: 10px; color: #64748b; font-size: 0.85rem; font-family: monospace;">Output Terminal</span>
            </div>
            <pre>{$escapedOutput}</pre>
        </div>

        <div class="info-box">
            📌 <strong>تنبيه أمان:</strong> هذا الرابط مخصص للاستخدام المؤقت عبر المتصفح أثناء عدم القدرة على الدخول لـ Hostinger SSH.
            يمكنك تغيير المفتاح السري عبر إضافة <code>MIGRATE_KEY=your_custom_key</code> في ملف <code>.env</code>.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
