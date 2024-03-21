

#saleoffer
INSERT INTO `our`.`sale` (`canceled`,`createStamp`,`customer`,`date`,`deliveryAddress`,`deliveryDate`,`deliverySlip`,`deposit`,`disabled`,`discount`,`editStamp`,`finished`,`id`,`invoiceAddress`,`invoiceTitle`,`mainAddress`,`no`,`notice`,`notification`,`payment`,`paymentOpen`,`paymentTerm`,`priceGross`,`priceNet`,`priceProduct`,`priceProductGross`,`signed`)

SELECT
`auftraege`.`Storniert` AS `canceled`.`canceled`,
`auftraege`.`Erstellt_am` AS `createStamp`.`createStamp`,
`auftraege`.`Adressen_Kunden_ID` AS `customer`.`customer`,
`auftraege`.`Auftragsdatum` AS `date`.`date`,
`auftraege`.`Adressen_Lieferanschrift_ID` AS `deliveryAddress`.`deliveryAddress`,
`auftraege`.`Liefertermin` AS `deliveryDate`.`deliveryDate`,
`auftraege`.`Lieferhinweis` AS `deliverySlip`.`deliverySlip`,
`auftraege`.`Anzahlungsbetrag2` AS `deposit`.`deposit`,
`auftraege`.`Geloescht` AS `disabled`.`disabled`,
`auftraege`.`Endrabatt` AS `discount`.`discount`,
`auftraege`.`Geaendert_am` AS `editStamp`.`editStamp`,
`auftraege`.`Erledigt` AS `finished`.`finished`,
auftraege.ID + 100000 AS `id`.`id`,
`auftraege`.`Adressen_Rechnungsanschrift_ID` AS `invoiceAddress`.`invoiceAddress`,
`auftraege`.`Nachlauftext` AS `invoiceTitle`.`invoiceTitle`,
`auftraege`.`Adressen_Kunden_ID` AS `mainAddress`.`mainAddress`,
`auftraege`.`Auftragsnummer` AS `no`.`no`,
`auftraege`.`Notizen` AS `notice`.`notice`,
`auftraege`.`Hinweis` AS `notification`.`notification`,
`auftraege`.`Anzahlungsbetrag` AS `payment`.`payment`,
`auftraege`.`OffenePosten` AS `paymentOpen`.`paymentOpen`,
`auftraege`.`Zahlungshinweis` AS `paymentTerm`.`paymentTerm`,
`auftraege`.`VKBrutto` AS `priceGross`.`priceGross`,
`auftraege`.`VKNetto` AS `priceNet`.`priceNet`,
`auftraege`.`VKBrutto_Waehrung` AS `priceProduct`.`priceProduct`,
`auftraege`.`VKBrutto_Fest` AS `priceProductGross`.`priceProductGross`,
0 AS `signed`.`signed`
FROM `db_guh`.`angebote`

GROUP BY `angebote`.`ID`;


