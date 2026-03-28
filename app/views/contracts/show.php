<?php
$contractTitle  = trim((string)($contract['name'] ?? 'Contract'));
$contractNumber = trim((string)($contract['contract_number'] ?? ''));
$status         = trim((string)($contract['status'] ?? ''));
?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <div class="text-muted small mb-1">Contract Detail</div>
      <h1 class="h3 mb-1"><?= h($contractTitle) ?></h1>
      <div class="text-muted">
        <?php if ($contractNumber !== ''): ?>
          <span class="me-3"><strong>No.</strong> <?= h($contractNumber) ?></span>
        <?php endif; ?>
        <?php if ($status !== ''): ?>
          <span class="badge text-bg-secondary"><?= h($status) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex gap-2">
      <a href="/index.php?page=contracts" class="btn btn-outline-secondary btn-sm">Back</a>
      <a href="/index.php?page=contracts_edit&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-primary btn-sm">Edit Contract</a>
      <a href="/index.php?page=contracts_generate_html&contract_id=<?= (int)$contract['contract_id'] ?>" target="_blank" class="btn btn-outline-success btn-sm">Generate HTML</a>
      <a href="/index.php?page=contracts_generate_word&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-info btn-sm">Generate Word Doc</a>
    </div>
  </div>

  <div class="row g-4">

    <div class="col-lg-8">

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Summary</h2>
        </div>
        <div class="card-body">
          <div class="row g-3">
            
            <div class="col-md-6">
              <div class="small text-muted">Department</div>
              <div><?= h($contract['department_name'] ?? '') ?: '—' ?></div>
            </div>
            <div class="col-md-6">
              <div class="small text-muted">Counterparty Company</div>
              <div><?= h($contract['counterparty_company_name'] ?? '') ?: '—' ?></div>
            </div>
            

            <div class="col-md-6">
              <div class="small text-muted">Contract Type</div>
              <div><?= h($contract['contract_type_name'] ?? '') ?: '—' ?></div>
            </div>
             <div class="col-md-6">
            <div class="small text-muted">Counterparty Primary Contact</div>
            <div><?= h($contract['counterparty_primary_contact_name'] ?? '') ?: '—'  ?></div>
            <?php if (!empty($contract['counterparty_primary_contact_email'])): ?>
                ( <a href="mailto:<?= h($contract['counterparty_primary_contact_email']) ?>">
                    <?= h($contract['counterparty_primary_contact_email']) ?>
                </a> )
            <?php endif; ?>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Payment Terms</div>
              <div><?= h($contract['payment_terms_name'] ?? '') ?: '—' ?></div>
            </div>

           
           
        

            <div class="col-md-6">
              <div class="small text-muted">Start Date</div>
              <div><?= h($contract['start_date'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">End Date</div>
              <div><?= h($contract['end_date'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Auto Renew</div>
              <div><?= !empty($contract['auto_renew']) ? 'Yes' : 'No' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Renewal Term (Months)</div>
              <div><?= h($contract['renewal_term_months'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Created At</div>
              <div><?= h($contract['created_at'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Updated At</div>
              <div><?= h($contract['updated_at'] ?? '') ?: '—' ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Description</h2>
        </div>
        <div class="card-body">
          <?php $desc = trim((string)($contract['description'] ?? '')); ?>
          <?php if ($desc !== ''): ?>
            <div style="white-space: pre-wrap;"><?= h($desc) ?></div>
          <?php else: ?>
            <div class="text-muted">No description entered.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Financial / Terms</h2>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="small text-muted">Total Contract Value</div>
              <div><?= h($contract['total_contract_value'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Currency</div>
              <div><?= h($contract['currency'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Governing Law</div>
              <div><?= h($contract['governing_law'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-12">
              <div class="small text-muted">Payment Terms</div>
              <div><?= h($contract['payment_terms_name'] ?? '') ?: '—' ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">Exhibits</h2>
          <a href="/index.php?page=contract_exhibit_create&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-primary btn-sm">Add Exhibit</a>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($exhibits)): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Label</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Sort</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($exhibits as $exhibit): ?>
                    <tr>
                      <td><?= h($exhibit['exhibit_label'] ?? '') ?: '—' ?></td>
                      <td><?= h($exhibit['title'] ?? '') ?: '—' ?></td>
                      <td><?= h($exhibit['description'] ?? '') ?: '—' ?></td>
                      <td><?= h($exhibit['sort_order'] ?? '') ?: '—' ?></td>
                      <td class="text-end">
                        <a href="/index.php?page=contract_exhibit_edit&id=<?= (int)$exhibit['exhibit_id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="p-3 text-muted">No exhibits added.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">Documents</h2>
          <a href="/index.php?page=contract_documents_create&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-primary btn-sm">Add Document</a>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($documents)): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>File</th>
                    <th>Created</th>
                    <th>Doc ID (debug)</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($documents as $doc): ?>
                    <tr>
                      <td><?= !empty($doc['file_name']) ? h($doc['file_name']) : '—' ?></td>
                      <td><?= !empty($doc['doc_type']) ? h($doc['doc_type']) : '—' ?></td>
                      <td>
                        <?php
                          $webPath = '';
                          if (!empty($doc['file_path'])) {
                            $webPath = $doc['file_path'];
                            // Remove any absolute path prefix, keep only web path
                            if (strpos($webPath, '/storage/') === false && ($pos = strpos($webPath, 'storage/')) !== false) {
                              $webPath = '/' . substr($webPath, $pos);
                            } elseif (strpos($webPath, '/storage/') !== 0) {
                              $webPath = '/' . ltrim($webPath, '/');
                            }
                          }
                        ?>
                        <?php if ($webPath): ?>
                          <a href="<?= h($webPath) ?>" target="_blank">Open</a>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td><?= !empty($doc['created_at']) ? h($doc['created_at']) : '—' ?></td>
                      <td><?= isset($doc['contract_document_id']) ? h($doc['contract_document_id']) : '—' ?></td>
                      <td class="text-end">
                        <?php if (!empty($doc['contract_document_id']) && (int)$doc['contract_document_id'] > 0): ?>
                          <a href="/index.php?page=contract_document_email&id=<?= (int)$doc['contract_document_id'] ?>" class="btn btn-outline-primary btn-sm">Email Doc</a>
                          <form method="post" action="/index.php?page=contract_document_delete" style="display:inline;" onsubmit="return confirm('Delete this document?');">
                            <input type="hidden" name="document_id" value="<?= (int)$doc['contract_document_id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm ms-1">Delete</button>
                          </form>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="p-3 text-muted">No documents added.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div class="col-lg-4">

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Quick Info</h2>
        </div>
        <div class="card-body">
            
             <div class="col-md-6">
            <div class="small text-muted">Town Primary Contact</div>
            <div><?= h($contract['owner_primary_contact_name'] ?? '') ?: '—'  ?></div>
            <?php if (!empty($contract['owner_primary_contact_email'])): ?>
                ( <a href="mailto:<?= h($contract['owner_primary_contact_email']) ?>">
                    <?= h($contract['owner_primary_contact_email']) ?>
                </a> )
            <?php endif; ?>
            </div>

          <div class="mb-3">
            <div class="small text-muted">Department Code</div>
            <div><?= h($contract['department_code'] ?? '') ?: '—' ?></div>
          </div>

          <div class="mb-3">
            <div class="small text-muted">Documents Path</div>
            <div><?= h($contract['documents_path'] ?? '') ?: '—' ?></div>
          </div>

          <div class="mb-0">
            <div class="small text-muted">Contract Body HTML</div>
            <div><?= !empty($contract['contract_body_html']) ? 'Present' : '—' ?></div>
          </div>
        </div>
      </div>

    </div>

  </div>

  <div class="mt-4 d-flex gap-2">
    <a href="/index.php?page=contracts_edit&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-primary">Edit Contract</a>
    <a href="/index.php?page=contract_exhibit_create&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-secondary">Add Exhibit</a>
    <a href="/index.php?page=contract_document_create&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-secondary">Add Document</a>
  </div>

</div>