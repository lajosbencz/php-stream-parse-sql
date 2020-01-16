
-- @todo tour tablarol levenni a tour_date es tour_division-be atrakott oszlopokat
-- @todo torolni a tour referenciakat es oszlopokat ahol tour_date_id vagy tour_division_id oszlop lett hozzaadva

-- ---------------------------------------------------------------------------------------------------------------------
-- create new tables
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE `tour_date`
(
    `id`                 bigint(20) UNSIGNED     NOT NULL,
    `tour_id`            bigint(20) UNSIGNED     NOT NULL,
    `date_from`          date                    NOT NULL,
    `date_until`         date                    NOT NULL,
    `date_deadline_book` date                    DEFAULT NULL,
    `date_deadline`      date                    DEFAULT NULL,
    `nights`             int(10) UNSIGNED        NOT NULL,
    `expenses`           decimal(12, 2) UNSIGNED NOT NULL,
    `persons`            smallint(5) UNSIGNED    DEFAULT NULL,
    `persons_min`        smallint(5) UNSIGNED    DEFAULT NULL,
    `modify_date`        datetime                NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_obsolete`        bit(1)                  NOT NULL DEFAULT b'0'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `tour_date`
    ADD PRIMARY KEY (`id`),
    ADD KEY `date_from` (`date_from`),
    ADD KEY `date_until` (`date_until`),
    ADD KEY `tour_id` (`tour_id`);

ALTER TABLE `tour_date`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tour_date`
    ADD FOREIGN KEY (`tour_id`) REFERENCES `tour` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



CREATE TABLE `tour_division`
(
    `id`           bigint(20) UNSIGNED NOT NULL,
    `tour_date_id` bigint(20) UNSIGNED NOT NULL,
    `user_id`      bigint(20) UNSIGNED NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `tour_division`
    ADD PRIMARY KEY (`id`),
    ADD KEY `tour_date_id` (`tour_date_id`),
    ADD KEY `user_id` (`user_id`);

ALTER TABLE `tour_division`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tour_division`
    ADD FOREIGN KEY (`tour_date_id`) REFERENCES `tour_date` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tour_division`
    ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


-- ---------------------------------------------------------------------------------------------------------------------
-- initialize data to new tables
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `tour_date` (`tour_id`, `date_from`, `date_until`, `date_deadline`, `date_deadline_book`, `nights`,
                         `modify_date`, `expenses`, `persons`, `persons_min`, `is_obsolete`)
SELECT `id`,
       `date_from`,
       `date_until`,
       `date_deadline`,
       `date_deadline_book`,
       `nights`,
       `modify_date`,
       `expenses`,
       `persons`,
       `persons_min`,
       `is_obsolete`
from `tour`;

INSERT INTO `tour_division` (`tour_date_id`, `user_id`)
SELECT td.id, t.user_id
FROM `tour_date` td
         INNER JOIN `tour` t ON t.id = td.tour_id;


-- ---------------------------------------------------------------------------------------------------------------------
-- tour_event changes
-- ---------------------------------------------------------------------------------------------------------------------

ALTER TABLE `tour_event`
    ADD `tour_date_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD `user_id` BIGINT UNSIGNED NOT NULL AFTER `tour_date_id`,
    ADD INDEX (`tour_date_id`),
    ADD INDEX (`user_id`);

UPDATE `tour_event` e
    INNER JOIN `tour_date` d ON d.tour_id = e.tour_id
    INNER JOIN `tour` t ON t.id = d.tour_id
SET e.tour_date_id = d.id, e.user_id = t.user_id;

ALTER TABLE `tour_event`
    ADD FOREIGN KEY (`tour_date_id`) REFERENCES `tour_date` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tour_event`
    ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;



-- ---------------------------------------------------------------------------------------------------------------------
-- tour_date relations
-- ---------------------------------------------------------------------------------------------------------------------

-- fix room_type key name before creating service one
ALTER TABLE `tour_service` DROP FOREIGN KEY `tour_service_ibfk_1`;
ALTER TABLE `tour_service` ADD CONSTRAINT `tour_room_type_id_ibfk_1` FOREIGN KEY (`tour_room_type_id`) REFERENCES `tour_room_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tour_service`
    ADD `tour_date_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_date_id`);

UPDATE `tour_service`
SET `tour_date_id` = (SELECT id FROM tour_date WHERE tour_id = tour_service.tour_id LIMIT 1);
ALTER TABLE `tour_service`
    ADD FOREIGN KEY (`tour_date_id`) REFERENCES `tour_date` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `tour_room_type`
    ADD `tour_date_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_date_id`);

UPDATE `tour_room_type`
SET `tour_date_id` = (SELECT id FROM tour_date where tour_id = tour_room_type.tour_id LIMIT 1);
ALTER TABLE `tour_room_type`
    ADD FOREIGN KEY (`tour_date_id`) REFERENCES `tour_date` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- ---------------------------------------------------------------------------------------------------------------------
-- test for ; character inside text data
-- ---------------------------------------------------------------------------------------------------------------------
INSERT INTO tour_room_type (name) VALUES ('foo; bar!');



-- ---------------------------------------------------------------------------------------------------------------------
-- tour_division relations
-- ---------------------------------------------------------------------------------------------------------------------

ALTER TABLE `tour_room`
    ADD `tour_division_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_division_id`);

UPDATE `tour_room`
SET `tour_division_id` = (
    SELECT v.id
    FROM tour_division v
             INNER JOIN tour_date t ON v.tour_date_id = t.id
    WHERE t.tour_id = tour_room.tour_id
    LIMIT 1
);
ALTER TABLE `tour_room`
    ADD FOREIGN KEY (`tour_division_id`) REFERENCES `tour_division` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE `tour_traveler`
    ADD `tour_division_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_division_id`);

UPDATE `tour_traveler`
SET `tour_division_id` = (
    SELECT v.id
    FROM tour_division v
             INNER JOIN tour_date t ON v.tour_date_id = t.id
    WHERE t.tour_id = tour_traveler.tour_id
    LIMIT 1
);
ALTER TABLE `tour_traveler`
    ADD FOREIGN KEY (`tour_division_id`) REFERENCES `tour_division` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `tour_join_leave`
    ADD `tour_division_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_division_id`);

UPDATE `tour_join_leave`
SET `tour_division_id` = (
    SELECT v.id
    FROM tour_division v
             INNER JOIN tour_date t ON v.tour_date_id = t.id
    WHERE t.tour_id = tour_join_leave.tour_id
    LIMIT 1
);
ALTER TABLE `tour_join_leave`
    ADD FOREIGN KEY (`tour_division_id`) REFERENCES `tour_division` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE `tour_vehicle`
    ADD `tour_division_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_division_id`);

UPDATE `tour_vehicle`
SET `tour_division_id` = (
    SELECT v.id
    FROM tour_division v
             INNER JOIN tour_date t ON v.tour_date_id = t.id
    WHERE t.tour_id = tour_vehicle.tour_id
    LIMIT 1
);
ALTER TABLE `tour_vehicle`
    ADD FOREIGN KEY (`tour_division_id`) REFERENCES `tour_division` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE `tour_vehicle_seat`
    ADD `tour_division_id` BIGINT UNSIGNED NOT NULL AFTER `tour_id`,
    ADD INDEX (`tour_division_id`);

UPDATE `tour_vehicle_seat`
SET `tour_division_id` = (
    SELECT v.id
    FROM tour_division v
             INNER JOIN tour_date t ON v.tour_date_id = t.id
    WHERE t.tour_id = tour_vehicle_seat.tour_id
    LIMIT 1
);
ALTER TABLE `tour_vehicle_seat`
    ADD FOREIGN KEY (`tour_division_id`) REFERENCES `tour_division` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
