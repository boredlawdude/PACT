-- Developer intake submissions: public form → admin review → import as real dev agreement
CREATE TABLE IF NOT EXISTS development_agreement_submissions (
    submission_id              INT           NOT NULL AUTO_INCREMENT,

    -- Who submitted (external developer / applicant)
    submitter_name             VARCHAR(200)  DEFAULT NULL,
    submitter_email            VARCHAR(200)  DEFAULT NULL,
    submitter_phone            VARCHAR(50)   DEFAULT NULL,
    submitter_company          VARCHAR(200)  DEFAULT NULL,

    -- Mirror of dev agreement fields
    project_name               VARCHAR(255)  NOT NULL DEFAULT '',
    project_description        TEXT          DEFAULT NULL,
    proposed_improvements      TEXT          DEFAULT NULL,
    current_zoning             VARCHAR(100)  DEFAULT NULL,
    proposed_zoning            VARCHAR(100)  DEFAULT NULL,
    comp_plan_designation      VARCHAR(200)  DEFAULT NULL,
    anticipated_start_date     DATE          DEFAULT NULL,
    anticipated_end_date       DATE          DEFAULT NULL,
    agreement_termination_date DATE          DEFAULT NULL,
    planning_board_date        DATE          DEFAULT NULL,
    town_council_hearing_date  DATE          DEFAULT NULL,

    -- Property tracts stored as JSON array (no FK constraints on unreviewed data)
    tracts_json                TEXT          DEFAULT NULL,

    -- Review metadata
    status                     ENUM('pending','imported','rejected') NOT NULL DEFAULT 'pending',
    imported_dev_agreement_id  INT           DEFAULT NULL,
    review_notes               TEXT          DEFAULT NULL,
    reviewed_by                INT           DEFAULT NULL,
    reviewed_at                DATETIME      DEFAULT NULL,
    submitted_at               DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (submission_id),
    KEY idx_sub_status (status),
    KEY idx_sub_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
