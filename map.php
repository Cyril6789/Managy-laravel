<?php
require('./classes/GoogleMapAPI.class.php');
$gmap = new GoogleMapAPI();
$gmap->setDivId('test1');
$gmap->setDirectionDivId('route');
$gmap->setCenter('Nantes France');
$gmap->setEnableWindowZoom(true);
$gmap->setEnableAutomaticCenterZoom(true);
$gmap->setDisplayDirectionFields(true);
// $gmap->setClusterer(true);
$gmap->setSize('600px','600px');
$gmap->setZoom(11);
$gmap->setLang('fr');
$gmap->setDefaultHideMarker(false);
// $gmap->addDirection('nantes','paris');
// cat1
$coordtab = array();
$coordtab []= array('nantes france','Nantes','<strong>html content</strong>');
$coordtab []= array('carquefou france','Carquefou','<strong>html content</strong>');
$coordtab []= array('vertou france','Vertou','<strong>html content</strong>');
$coordtab []= array('rezé france','Rezé','<strong>html content</strong>');
// $gmap->setIconSize(20,34);
$gmap->addArrayMarkerByAddress($coordtab,'cat1','http://maps.gstatic.com/intl/fr_fr/mapfiles/ms/micons/red-pushpin.png');
// cat2
$coordtab = array();
$coordtab []= array('saint-herblain france','Saint-herblain','<strong>html content</strong>');
$coordtab []= array('bouguenais france','Bouguenais','<strong>html content</strong>');
$coordtab []= array('orvault france','Orvault','<strong>html content</strong>');
$gmap->addArrayMarkerByAddress($coordtab,'cat2');
// cat3
$coordtab = array();
$coordtab []= array('48.8792','2.34778','test','<strong>test paris</strong>');
$gmap->addArrayMarkerByCoords($coordtab,'cat3');
$gmap->generate();
echo $gmap->getGoogleMap();
?>
