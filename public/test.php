<?php 
use Villanovo\ThirdParties\CimalpesClient;
use Villanovo\ThirdParties\BienPersist;

require_once __DIR__.'/../vendor/autoload.php';

// $db = new \db();
//  $db->exec("SELECT * from vn_lngs");
//  var_dump($db->fetchAll());
// exit();

// $flux = new CimalpesClient();
// //   $details = $flux->getDetail("1184");
// $biens = $flux->getBiens();

// // print_r($details);

// print_r($biens);
$persiste = new BienPersist();
$photos = $persiste->getPhotos(1184);
 print_r($photos);
