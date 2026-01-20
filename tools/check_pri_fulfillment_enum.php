<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SHOW COLUMNS FROM purchase_request_items WHERE Field = 'fulfillment_status'");
if (!$cols) {
    echo "fulfillment_status column not found\n";
    exit(1);
}
print_r($cols[0]);
