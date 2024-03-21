

#product
INSERT INTO `our`.`product` (`amount`,`amountOrdered`,`amountReserved`,`catalog`,`color`,`contractorPool`,`createStamp`,`depth`,`descr`,`ean`,`editStamp`,`group`,`height`,`id`,`identification`,`margin`,`model`,`modelNo`,`no`,`owner`,`price`,`priceGross`,`priceGrossTotal`,`priceOffer`,`pricebase`,`reserved`,`short`,`size`,`stock`,`stockMax`,`stockMin`,`stockValue`,`storageItem`,`store`,`tax`,`title`,`volume`,`weight`,`width`)

SELECT
`lagerbestaende`.`Bestand` AS `amount`.`amount`,
`lagerbestaende`.`Bestellt` AS `amountOrdered`.`amountOrdered`,
`lagerbestaende`.`Reserviert` AS `amountReserved`.`amountReserved`,
`artikel`.`KatalogStatus` AS `catalog`.`catalog`,
`artikel`.`Farbe` AS `color`.`color`,
`artikel`.`Hauptlieferant` AS `contractorPool`.`contractorPool`,
`artikel`.`Anlagedatum` AS `createStamp`.`createStamp`,
`artikel`.`Tiefe` AS `depth`.`depth`,
`artikel`.`aufwand_beschreibung` AS `descr`.`descr`,
`artikel`.`EAN` AS `ean`.`ean`,
`artikel`.`Geaendertdatum` AS `editStamp`.`editStamp`,
`artikel`.`KalkGruppe` AS `group`.`group`,
`artikel`.`Hoehe` AS `height`.`height`,
`artikel`.`ID` AS `id`.`id`,
`artikel`.`Artikeltyp` AS `identification`.`identification`,
artikel_vkpreise.Abholpreisnetto - artikel_ekpreise.EKNetto AS `margin`.`margin`,
`artikel`.`Artikeltyp` AS `model`.`model`,
`artikel`.`Modell` AS `modelNo`.`modelNo`,
`artikel`.`Artikelnummer` AS `no`.`no`,
`artikel`.`Anlagewer` AS `owner`.`owner`,
`artikel_vkpreise`.`VKNetto` AS `price`.`price`,
`artikel_vkpreise`.`VKBrutto` AS `priceGross`.`priceGross`,
`artikel_vkpreise`.`VKBrutto` AS `priceGrossTotal`.`priceGrossTotal`,
`artikel_vkpreise`.`VKAktionspreisnetto` AS `priceOffer`.`priceOffer`,
`lagerbestaende`.`EK` AS `pricebase`.`pricebase`,
`lagerbestaende`.`Reserviert` AS `reserved`.`reserved`,
`artikel`.`Artikelinfo` AS `short`.`short`,
`artikel`.`Hoehe` AS `size`.`size`,
`artikel`.`bestand_verfuegbar` AS `stock`.`stock`,
`artikel`.`Hoechstbestand` AS `stockMax`.`stockMax`,
`artikel`.`Mindestbestand` AS `stockMin`.`stockMin`,
`lagerbestaende`.`Bestand` AS `stockValue`.`stockValue`,
`artikel`.`Lagerhaltung` AS `storageItem`.`storageItem`,
'was soll hier rein?' AS `store`.`store`,
`konfig_mwst`.`Steuersatz` AS `tax`.`tax`,
`artikel`.`Bezeichnung` AS `title`.`title`,
`artikel`.`Volumen` AS `volume`.`volume`,
`artikel`.`Gewicht` AS `weight`.`weight`,
`artikel`.`Breite` AS `width`.`width`
FROM `db_guh`.`artikel`
LEFT JOIN `db_guh`.`artikel_ekpreise` ON `artikel`.`ID` = `artikel_ekpreise`.`Artikel_ID`
LEFT JOIN `db_guh`.`artikel_vkpreise` ON `artikel`.`ID` = `artikel_vkpreise`.`Artikel_ID`
LEFT JOIN `db_guh`.`lagerbestaende` ON `artikel`.`ID` = `lagerbestaende`.`Artikel_ID`
LEFT JOIN `db_guh`.`konfig_mwst` ON `artikel`.`Konfig_MwSt_ID` = `konfig_mwst`.`Id`

GROUP BY `artikel`.`ID`;


