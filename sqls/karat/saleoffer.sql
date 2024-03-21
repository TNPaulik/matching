

#saleoffer
INSERT INTO `ruder`.`saleoffer` (``)

SELECT
`auftraege`.`Storniert` AS `canceled`,
`auftraege`.`Erstellt_am` AS `createStamp`,
`auftraege`.`Adressen_Kunden_ID` AS `customer`,
`auftraege`.`Auftragsdatum` AS `date`,
`auftraege`.`Adressen_Lieferanschrift_ID` AS `deliveryAddress`,
`auftraege`.`Liefertermin` AS `deliveryDate`,
`auftraege`.`Lieferhinweis` AS `deliverySlip`,
`auftraege`.`Anzahlungsbetrag2` AS `deposit`,
`auftraege`.`Geloescht` AS `disabled`,
`auftraege`.`Endrabatt` AS `discount`,
`auftraege`.`Geaendert_am` AS `editStamp`,
`auftraege`.`Erledigt` AS `finished`,
auftraege.ID + 100000 AS `id`,
`auftraege`.`Adressen_Rechnungsanschrift_ID` AS `invoiceAddress`,
`auftraege`.`Nachlauftext` AS `invoiceTitle`,
`auftraege`.`Adressen_Kunden_ID` AS `mainAddress`,
`auftraege`.`Auftragsnummer` AS `no`,
`auftraege`.`Notizen` AS `notice`,
`auftraege`.`Hinweis` AS `notification`,
`auftraege`.`Anzahlungsbetrag` AS `payment`,
`auftraege`.`OffenePosten` AS `paymentOpen`,
`auftraege`.`Zahlungshinweis` AS `paymentTerm`,
`auftraege`.`VKBrutto` AS `priceGross`,
`auftraege`.`VKNetto` AS `priceNet`,
`auftraege`.`VKBrutto_Waehrung` AS `priceProduct`,
`auftraege`.`VKBrutto_Fest` AS `priceProductGross`,
0 AS `signed`
FROM `db_guh`.`auftraege`

GROUP BY `auftraege`.`ID`;


