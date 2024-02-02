DELIMITER $$

CREATE TRIGGER do_not_delete_measurements_trigger BEFORE DELETE ON measurements FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deletion blocked by trigger';
END $$

DELIMITER ;