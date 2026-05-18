<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            // Normalize action: allow either "STATUS UPDATE" or "STATUS_UPDATE" from UI
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->paginate(5)->withQueryString();

        if ($request->ajax()) {
            $html = view('custodian.audit_logs._table', [
                'logs' => $logs,
            ])->render();

            $pagination = $logs->hasPages() ? $logs->links()->toHtml() : '';

            return response()->json([
                'html' => $html,
                'total' => $logs->total(),
                'pagination' => $pagination,
            ]);
        }

        $availableActions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('custodian.audit_logs.index', compact('logs', 'availableActions'));
    }

    public function printPdf(Request $request): View
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->get();

        return view('custodian.audit_logs.print', [
            'logs' => $logs,
            'search' => $request->input('search'),
            'dateFrom' => $request->input('date_from'),
            'dateTo' => $request->input('date_to'),
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->get();

        $html = view('custodian.audit_logs.excel', ['logs' => $logs])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Audit-Logs-' . date('Y-m-d') . '.xls"');
    }

    public function downloadBackup(): BinaryFileResponse|RedirectResponse
    {
        $connection = config('database.connections.mysql');
        if (! $connection || empty($connection['database'])) {
            return back()->with('error', 'Backup failed: database configuration is missing.');
        }

        $mysqldumpPath = env('MYSQLDUMP_PATH');
        if (! $mysqldumpPath) {
            $defaultWindowsPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            $mysqldumpPath = file_exists($defaultWindowsPath) ? $defaultWindowsPath : 'mysqldump';
        } elseif (is_dir($mysqldumpPath)) {
            $mysqldumpPath = rtrim($mysqldumpPath, "\\/") . DIRECTORY_SEPARATOR . 'mysqldump.exe';
        }

        if (! file_exists($mysqldumpPath) && $mysqldumpPath !== 'mysqldump') {
            return back()->with('error', 'Backup failed: mysqldump was not found at the configured path.');
        }

        $database = $connection['database'];
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';
        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? 3306);

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup-' . $database . '-' . now()->format('Y-m-d_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $command = [
            $mysqldumpPath,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--databases',
            $database,
            '--routines',
            '--events',
            '--triggers',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--result-file=' . $filePath,
        ];

        $env = null;
        if ($password !== '') {
            $env = array_merge($_ENV, ['MYSQL_PWD' => $password]);
        }

        $process = new Process($command, null, $env);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! file_exists($filePath)) {
            return back()->with('error', 'Backup failed. Ensure mysqldump is available and try again.');
        }

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    public function recover(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_sql' => ['required', 'file', 'max:51200'],
        ]);

        $upload = $validated['backup_sql'];
        $originalName = strtolower((string) $upload->getClientOriginalName());
        if (! str_ends_with($originalName, '.sql')) {
            return back()->withErrors([
                'backup_sql' => 'Please upload a .sql file.',
            ]);
        }

        $connection = config('database.connections.mysql');
        if (! $connection || empty($connection['database'])) {
            return back()->with('error', 'Recovery failed: database configuration is missing.');
        }

        $mysqlPath = env('MYSQL_PATH');
        if (! $mysqlPath) {
            $defaultWindowsPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
            $mysqlPath = file_exists($defaultWindowsPath) ? $defaultWindowsPath : 'mysql';
        } elseif (is_dir($mysqlPath)) {
            $mysqlPath = rtrim($mysqlPath, "\\/") . DIRECTORY_SEPARATOR . 'mysql.exe';
        }

        if (! file_exists($mysqlPath) && $mysqlPath !== 'mysql') {
            return back()->with('error', 'Recovery failed: mysql client was not found at the configured path.');
        }

        $database = $connection['database'];
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';
        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? 3306);

        $importDir = storage_path('app/backups/imports');
        if (! is_dir($importDir)) {
            mkdir($importDir, 0755, true);
        }

        $filename = 'restore-' . $database . '-' . now()->format('Y-m-d_His') . '.sql';
        $filePath = $upload->move($importDir, $filename)->getPathname();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        foreach ($tables as $row) {
            $rowArray = (array) $row;
            $tableName = $rowArray[array_key_first($rowArray)] ?? null;
            if ($tableName) {
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }
        }
        $views = DB::select('SHOW FULL TABLES WHERE Table_type = "VIEW"');
        foreach ($views as $row) {
            $rowArray = (array) $row;
            $viewName = $rowArray[array_key_first($rowArray)] ?? null;
            if ($viewName) {
                DB::statement("DROP VIEW IF EXISTS `{$viewName}`");
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $command = [
            $mysqlPath,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--database=' . $database,
            '--default-character-set=utf8mb4',
            '--max_allowed_packet=256M',
            '--binary-mode=1',
        ];

        $env = null;
        if ($password !== '') {
            $env = array_merge($_ENV, ['MYSQL_PWD' => $password]);
        }

        $process = new Process($command, null, $env);
        $process->setTimeout(180);
        $stream = fopen($filePath, 'rb');
        if ($stream === false) {
            @unlink($filePath);
            return back()->with('error', 'Recovery failed: unable to read the uploaded SQL file.');
        }
        $process->setInput($stream);
        $process->run();

        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($filePath);

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $message = 'Recovery failed. Check the SQL file and mysql client configuration.';
            if ($errorOutput !== '') {
                $message .= ' Error: ' . $errorOutput;
            }
            return back()->with('error', $message);
        }

        return back()->with('success', 'Database recovery completed successfully.');
    }
}
