<?php

require_once __DIR__.'/../vendor/autoload.php';



define('api_bien', 'https://cimalpes.ski/fr/flux/?fonction=biens&login=xml@villanovo.com&pass=M4X876RV3N2D');

$conn = new db();
$conn->exec("SELECT id from vn_villas_temp");
$villasTemp = $conn->fetchAll();


var_dump($villasTemp);exit();

$inserts = [];

$fluxBien = new DOMDocument('1.0','UTF-8');
$fluxBien->load(api_bien);
$xpath = new DOMXPath($fluxBien);
$biens = $xpath->query('//bien');



foreach($biens as $bien) {
    // var_dump($bien);exit();
    $id = $bien->getElementsByTagName('id_bien')->item(0)->nodeValue;
    $nom = $bien->getElementsByTagName('nom_bien')->item(0)->nodeValue;
    $url = "https://cimalpes.ski/fr" . $bien->getElementsByTagName('url_rewriting')->item(0)->nodeValue;
    $station = $bien->getElementsByTagName('nom_station')->item(0)->nodeValue;
    $bedrooms = $bien->getElementsByTagName('nombre_chambres')->item(0)->nodeValue; 
    $tmp = array_search($id,array_column($villasTemp,'id'));


  if ($tmp === false) {
    $inserts[] = '(' .$id. ',"' .$nom. '","' .$url.  '","' .$station. '",' .$bedrooms.   ')';
    }
}

if (!empty($inserts)) {
    $insertsParts = array_chunk($inserts,100);

    foreach ($insertsParts as $insertsPart) {
        $query = 'INSERT INTO vn_villas_temp (id,nom,url,station,rooms) values' . implode(',',$insertsPart);
        $conn->exec($query);
    }
  
}