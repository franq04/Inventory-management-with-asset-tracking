<?php
$path = __DIR__ . '/../resources/views/custodian/dashboard.blade.php';
$s = file_get_contents($path);
$out = '';
for ($i=0;$i<strlen($s);$i++){
    $ord = ord($s[$i]);
    if ($ord > 127) {
        $out .= ' ';
    } else {
        $out .= $s[$i];
    }
}
file_put_contents($path, $out);
echo "sanitized\n";
