<?php
$d = parse_ini_file(__DIR__ . '/.env');
$p = new PDO('mysql:host='.$d['DB_HOST'].';dbname='.$d['DB_NAME'].';charset=utf8mb4', $d['DB_USER'], $d['DB_PASS']);
$rows = $p->query('SELECT contract_document_id, file_name, file_path FROM contract_documents ORDER BY contract_document_id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $resolved = str_starts_with($r['file_path'], '/') ? $r['file_path'] : __DIR__ . '/' . ltrim($r['file_path'], '/');
    echo $r['contract_document_id'] . ' | ' . $r['file_name'] . "\n";
    echo '  DB path:       ' . $r['file_path'] . "\n";
    echo '  Resolved path: ' . $resolved . "\n";
    echo '  File exists:   ' . (is_file($resolved) ? 'YES' : 'NO') . "\n\n";
}
echo "APP_ROOT would be: " . __DIR__ . "\n";
