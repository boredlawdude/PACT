-- Tracks each successful login for per-user activity reporting
CREATE TABLE IF NOT EXISTS person_login_events (
  login_event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  person_id INT NOT NULL,
  logged_in_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (login_event_id),
  KEY idx_ple_person_logged_in_at (person_id, logged_in_at),
  CONSTRAINT fk_ple_person
    FOREIGN KEY (person_id) REFERENCES people (person_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
