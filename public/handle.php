<?php 

use Villanovo\ThirdParties\CimalpesClient;
use Villanovo\ThirdParties\BienPersist;

require_once __DIR__.'/../vendor/autoload.php';

$flux = new CimalpesClient();
// // // //   $details = $flux->getDetail("1184");
  // $bien = $flux->getBien("436");
$biens = $flux->getBiens();

$biensJson = json_encode($biens);






var_dump($biens); exit();
//  print_r($biensJson);
//   file_put_contents(__DIR__."/biens.json",json_encode($biens));

//   // $biensJson = json_decode(file_get_contents(__DIR__."/biens.json"),true);

//   $persiste = new BienPersist();

 
// // // // // //  var_dump($persiste->languges);
//     $persiste->insertOrUpdate($biens);

//  print_r($biensJson);


