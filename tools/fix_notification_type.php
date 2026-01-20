<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Add 'action_required' to the type ENUM
try {
    DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('info','success','warning','error','task','action_required') NOT NULL DEFAULT 'info'");
    echo "SUCCESS: Added 'action_required' to notifications.type ENUM\n";
    
    // Verify the change
    $cols = DB::select("SHOW COLUMNS FROM notifications WHERE Field = 'type'");
    echo "New type column: {$cols[0]->Type}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
