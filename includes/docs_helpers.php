<?php
declare(strict_types=1);

/**
 * save_generated_doc
 *
 * Saves generated content (or copies an existing file) into:
 *   storage/contracts/{contractId}/{filename}
 * and inserts a row into contract_documents.
 *
 * Returns: array{contract_document_id:int, file_path:string, abs_path:string}
 */


function save_generated_doc(
  PDO $pdo,
  int $contractId,
  string $filename,
  string $docType,
  string $mimeType,
  ?int $createdByPersonId,
  array $opts = []
): array {
  // Options:
  // - 'content' => string (raw file contents to write)  <-- Use this for HTML generation
  // - 'source_path' => string (existing file path to copy from)  <-- Use this for DOCX generation (e.g., temp file)
  // - 'storage_base_abs' => string (absolute path to storage base; default __DIR__/../storage)
  // - 'storage_base_rel' => string (relative path recorded in DB; default 'storage')
  //
  // You must supply either 'content' or 'source_path'.

  $content     = $opts['content']     ?? null;
  $sourcePath  = $opts['source_path'] ?? null;

  if (($content === null && $sourcePath === null) || ($content !== null && $sourcePath !== null)) {
    throw new RuntimeException("save_generated_doc requires exactly one of: opts['content'] or opts['source_path'].");
  }

  if ($contractId <= 0) throw new RuntimeException("Invalid contractId.");
  if ($filename === '') throw new RuntimeException("Filename required.");

  // Prevent path traversal
  $filename = basename($filename);
  if ($filename === '' || $filename === '.' || $filename === '..') {
    throw new RuntimeException("Invalid filename.");
  }

  // Where "storage" lives (ABS) and what to store in DB (REL)
  // Default assumes this helper lives in /includes and storage is /storage next to it.
  $storageBaseAbs = get_storage_base_dir();
$storageBaseRel = get_generated_contracts_subdir();  // 'contracts' or whatever is in DB

$absDir = $storageBaseAbs . '/contracts/' . $contractId;   // still use 'contracts' literal here, or make configurable too
//$relDir = $storageBaseRel . '/contracts/' . $contractId;   // or just $storageBaseRel . '/' . $contractId if you want
$relDir = get_system_setting('contracts_generated_subdir', 'contracts') . '/' . $contractId;
// Final folder: {storage}/contracts/{id}

  if (!is_dir($absDir)) {
    if (!@mkdir($absDir, 0775, true) && !is_dir($absDir)) {
      throw new RuntimeException("Could not create directory: {$absDir}");
    }
  }
  if (!is_writable($absDir)) {
    throw new RuntimeException("Directory not writable: {$absDir}");
  }

  $absPath = $absDir . '/' . $filename;
  $relPath = $relDir . '/' . $filename;

  // Write/copy file
  if ($content !== null) {
    $bytes = file_put_contents($absPath, $content);
    if ($bytes === false) throw new RuntimeException("Failed writing file: {$absPath}");
  } else {
    $sourcePath = (string)$sourcePath;
    if (!is_file($sourcePath) || !is_readable($sourcePath)) {
      throw new RuntimeException("Source file missing/unreadable: {$sourcePath}");
    }
    if (!@copy($sourcePath, $absPath)) {
      throw new RuntimeException("Failed copying {$sourcePath} -> {$absPath}");
    }
  }

  // Insert row in contract_documents
  $ins = $pdo->prepare("
    INSERT INTO contract_documents
      (contract_id, doc_type, file_name, file_path, mime_type, created_by_person_id)
    VALUES
      (?, ?, ?, ?, ?, ?)
  ");
  $ins->execute([
    $contractId,
    $docType,
    $filename,
    $relPath,   // store RELATIVE path in DB
    $mimeType,
    $createdByPersonId
  ]);

  $docId = (int)$pdo->lastInsertId();

  return [
    'contract_document_id' => $docId,
    'file_path' => $relPath,
    'abs_path'  => $absPath,
  ];
}
