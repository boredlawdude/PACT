-- Add Planning Board date and Town Council Hearing date to development_agreements
ALTER TABLE development_agreements
  ADD COLUMN planning_board_date DATE DEFAULT NULL AFTER agreement_termination_date,
  ADD COLUMN town_council_hearing_date DATE DEFAULT NULL AFTER planning_board_date;
