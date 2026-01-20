<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \DB::statement("ALTER TABLE purchase_request_items ADD COLUMN suggested_by INT UNSIGNED NULL AFTER alternate_description");
    echo "Added suggested_by\n";
} catch (\Exception $e) {
    echo "suggested_by: " . $e->getMessage() . "\n";
}

try {
    \DB::statement("ALTER TABLE purchase_request_items ADD COLUMN suggested_at TIMESTAMP NULL AFTER suggested_by");
    echo "Added suggested_at\n";
} catch (\Exception $e) {
    echo "suggested_at: " . $e->getMessage() . "\n";
}

try {
    \DB::statement("ALTER TABLE purchase_request_items ADD COLUMN original_description VARCHAR(500) NULL AFTER suggested_at");
    echo "Added original_description\n";
} catch (\Exception $e) {
    echo "original_description: " . $e->getMessage() . "\n";
}

try {
    \DB::statement("ALTER TABLE purchase_request_items ADD COLUMN removed_at TIMESTAMP NULL AFTER employee_wait_note");
    echo "Added removed_at\n";
} catch (\Exception $e) {
    echo "removed_at: " . $e->getMessage() . "\n";
}

try {
    \DB::statement("ALTER TABLE purchase_request_items ADD COLUMN removal_reason VARCHAR(255) NULL AFTER removed_at");
    echo "Added removal_reason\n";
} catch (\Exception $e) {
    echo "removal_reason: " . $e->getMessage() . "\n";
}

echo "Done!\n";
