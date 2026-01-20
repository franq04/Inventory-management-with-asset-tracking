<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE purchase_request_items MODIFY COLUMN fulfillment_status ENUM('pending','ordered','unavailable','alternative','waiting','fulfilled','expired') NOT NULL DEFAULT 'pending'");
    echo "SUCCESS: Updated fulfillment_status enum\n";
    $col = DB::select("SHOW COLUMNS FROM purchase_request_items WHERE Field = 'fulfillment_status'");
    echo "New type: {$col[0]->Type}\n";
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
}
