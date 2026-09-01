<?php
declare(strict_types=1);

/**
 * One-time backfill: development_agreements.property_pin/property_address/
 * property_realestateid/property_acerage (legacy single-parcel columns that
 * feed the da_property_* merge fields) were left blank for any DA saved via
 * the current tracts-based edit form. This backfills them from each DA's
 * primary (first, by sort order) tract. Idempotent/safe to re-run.
 */

require __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/app/models/DevelopmentAgreement.php';
require_once APP_ROOT . '/app/models/DevelopmentAgreementTract.php';

$db      = db();
$model   = new DevelopmentAgreement($db);
$tracts  = new DevelopmentAgreementTract($db);

$daIds = $db->query('SELECT dev_agreement_id FROM development_agreements')->fetchAll(PDO::FETCH_COLUMN);

$updated = 0;
foreach ($daIds as $id) {
    $id = (int)$id;
    $primaryTract = $tracts->allForAgreement($id)[0] ?? null;
    if (!$primaryTract) continue;
    $model->syncPrimaryTractFields($id, $primaryTract);
    $updated++;
}

echo "Processed " . count($daIds) . " development agreements, synced legacy fields for $updated.\n";
