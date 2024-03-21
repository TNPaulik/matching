

#customer
INSERT INTO `our`.`customer` (`birthday`,`birthday2`,`id`,`info`,`location`,`mail`,`no`,`order`,`phone`,`prename`,`sale`,`source`,`status`,`street`,`surname`,`title`,`type`,`zip`)

SELECT
concat(adressen.Geburtstag, ' gerboren in ', angebote.Angebotsdatum) AS `birthday`.`birthday`,
`adressen`.`Geburtstag2` AS `birthday2`.`birthday2`,
`adressen`.`ID` AS `id`.`id`,
`adressen`.`Info` AS `info`.`info`,
`adressen`.`Ort` AS `location`.`location`,
`adressen`.`EMail` AS `mail`.`mail`,
`adressen`.`Nummer` AS `no`.`no`,
`order`.`order` AS `order`.`order`,
concat('[{"value":"', adressen.Telefon, '"}]') AS `phone`.`phone`,
`adressen`.`Name1` AS `prename`.`prename`,
`sale`.`sale` AS `sale`.`sale`,
'CARAT' AS `source`.`source`,
'client' AS `status`.`status`,
`adressen`.`Strasse` AS `street`.`street`,
`adressen`.`Name2` AS `surname`.`surname`,
`konfig_anreden`.`Anrede` AS `title`.`title`,
CASE WHEN `adressen`.`Kategorie` = '1' THEN 'Kunde' WHEN `adressen`.`Kategorie` = '2' THEN 'Lieferant' WHEN `adressen`.`Kategorie` = '3' THEN 'Mitarbeiter' WHEN `adressen`.`Kategorie` = '4' THEN 'Fremdfirma' WHEN `adressen`.`Kategorie` = '5' THEN 'Diverser' WHEN `adressen`.`Kategorie` = '6' THEN 'Anschrift' WHEN `adressen`.`Kategorie` = '7' THEN 'Ansprechpartner' WHEN `adressen`.`Kategorie` = '8' THEN 'Lageradresse' END AS `type`.`type`,
`adressen`.`PLZ` AS `zip`.`zip`
FROM `db_guh`.`adressen`
LEFT JOIN `db_guh`.`konfig_anreden` ON `adressen`.`Konfig_Anreden_ID` = `konfig_anreden`.`ID`
LEFT JOIN (SELECT `Kundennummer`, GROUP_CONCAT(`ID` SEPARATOR ',') `order` FROM `bestellungen` GROUP BY `Kundennummer`) `bestellungen` ON `adressen`.`ID` = `bestellungen`.`Kundennummer`
LEFT JOIN (SELECT count(*) `sale`,  `Adressen_Kunden_ID` FROM `angebote` GROUP BY `Adressen_Kunden_ID`) `angebote` ON `adressen`.`ID` = `angebote`.`Adressen_Kunden_ID`
GROUP BY `adressen`.`ID`;


