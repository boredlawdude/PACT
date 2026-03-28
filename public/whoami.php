<?php
declare(strict_types=1);
ini_set('display_errors','1'); error_reporting(E_ALL);

echo "<pre>";

$out=[]; $rc=0;
exec('whoami 2>&1', $out, $rc);
echo "whoami rc=$rc => " . implode("\n",$out) . "\n";

$out=[]; $rc=0;
exec('id 2>&1', $out, $rc);
echo "id rc=$rc => " . implode("\n",$out) . "\n";

$testDir = __DIR__ . '/storage';
echo "\nTest dir: $testDir\n";
echo "is_dir: " . (is_dir($testDir) ? "YES":"NO") . "\n";
echo "is_writable: " . (is_writable($testDir) ? "YES":"NO") . "\n";

$probe = $testDir . '/__probe_' . date('Ymd_His') . '.txt';
$ok = @file_put_contents($probe, "probe\n");
echo "write probe: " . ($ok !== false ? "OK ($probe)" : "FAILED") . "\n";

echo "</pre>";
