<?php
$path = __DIR__ . '/../resources/views/custodian/dashboard.blade.php';
$s = file_get_contents($path);
$chars = [];
for ($i=0;$i<strlen($s);$i++){
    $ord = ord($s[$i]);
    if ($ord > 127) {
        $chars[$ord] = $s[$i];
    }
}
if (empty($chars)) {
    echo "NoNonAscii\n";
} else {
    foreach ($chars as $ord => $ch) {
        echo $ord . " - " . $ch . "\n";
    }
}
