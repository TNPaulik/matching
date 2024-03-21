

#ordering
INSERT INTO `our`.`ordering` (`createStamp`,`customer`,`deliveryCity`,`deliveryDate`,`deliveryLocation`,`deliveryNote`,`deliveryStreet`,`deliveryZip`,`disabled`,`editStamp`,`id`,`no`,`orderDate`,`orderDescr`,`price`)

SELECT
`bestellungen`.`Erstellt_am` AS `createStamp`.`createStamp`,
`bestellungen`.`Kundennummer` AS `customer`.`customer`,
`adressen`.`Ort` AS `deliveryCity`.`deliveryCity`,
`bestellungen`.`AB_Liefertermin` AS `deliveryDate`.`deliveryDate`,
concat('Lieferscheinnummer: ', bestellungen.Lieferscheinnummer) AS `deliveryLocation`.`deliveryLocation`,
`bestellungen`.`Lieferavis_Notiz` AS `deliveryNote`.`deliveryNote`,
`adressen`.`Strasse` AS `deliveryStreet`.`deliveryStreet`,
`adressen`.`PLZ` AS `deliveryZip`.`deliveryZip`,
`bestellungen_positionen`.`Geloescht` AS `disabled`.`disabled`,
`bestellungen_positionen`.`Geaendert` AS `editStamp`.`editStamp`,
`bestellungen`.`ID` AS `id`.`id`,
`bestellungen`.`Bestellnummer` AS `no`.`no`,
`bestellungen`.`Erstellt_am` AS `orderDate`.`orderDate`,
`bestellungen`.`Bestellung_Notiz` AS `orderDescr`.`orderDescr`,
`bestellungen`.`RG_EK` AS `price`.`price`
FROM `db_guh`.`bestellungen`
LEFT JOIN `db_guh`.`adressen` ON `bestellungen`.`Lieferanschrift_ID` = `adressen`.`ID`
LEFT JOIN `db_guh`.`bestellungen_positionen` ON `bestellungen`.`ID` = `bestellungen_positionen`.`Bestellungen_ID`

GROUP BY `bestellungen`.`ID`;


