$ErrorActionPreference = 'Stop'

$root = "C:\xampp\htdocs\Inventory-management-with-asset-tracking"
$sqlPath = Join-Path $root "inventory_tracking (1).sql"
$migrationsDir = Join-Path $root "database\migrations"

$sql = Get-Content -Raw -Path $sqlPath

$createMatches = [regex]::Matches(
    $sql,
    'CREATE TABLE `(?<table>[^`]+)`\s*\((?<body>[\s\S]*?)\) ENGINE=InnoDB[^;]*;',
    [System.Text.RegularExpressions.RegexOptions]::IgnoreCase
)

$alterMatches = [regex]::Matches(
    $sql,
    'ALTER TABLE `(?<table>[^`]+)`(?<body>[\s\S]*?);',
    [System.Text.RegularExpressions.RegexOptions]::IgnoreCase
)

$alterByTable = @{}
foreach ($m in $alterMatches) {
    $table = $m.Groups['table'].Value
    $stmt = $m.Value.Trim()

    if (-not $alterByTable.ContainsKey($table)) {
        $alterByTable[$table] = New-Object System.Collections.Generic.List[string]
    }

    $null = $alterByTable[$table].Add($stmt)
}

$start = Get-Date "2026-03-17 12:00:00"
$index = 0

foreach ($m in $createMatches) {
    $table = $m.Groups['table'].Value
    $createSql = $m.Value.Trim()

    if ($table -eq 'employees' -and $createSql -notmatch '`signature`') {
        $createSql = $createSql -replace [regex]::Escape('`profile_img` varchar(255) NOT NULL,'), "`profile_img` varchar(255) NOT NULL,`r`n  `signature` longblob DEFAULT NULL,"
    }

    $ts = $start.AddSeconds($index).ToString('yyyy_MM_dd_HHmmss')
    $index++

    $fileName = "${ts}_create_${table}_table.php"
    $filePath = Join-Path $migrationsDir $fileName

    $createSqlEscaped = $createSql.Replace("'", "\'")

    $alterBlocks = ""
    if ($alterByTable.ContainsKey($table)) {
        foreach ($stmt in $alterByTable[$table]) {
            $stmtEscaped = $stmt.Replace("'", "\'")
            $alterBlocks += "        DB::statement('$stmtEscaped');`r`n"
        }
    }

    $php = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('$table')) {
            return;
        }

        DB::statement('$createSqlEscaped');
$alterBlocks    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('$table');
    }
};
"@

    Set-Content -Path $filePath -Value $php -Encoding UTF8
}

Write-Host "Generated $index migration files from SQL schema."
