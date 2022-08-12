<?php 

use Villanovo\ThirdParties\CimalpesClient;
use Villanovo\ThirdParties\BienPersist;

require_once __DIR__.'/../vendor/autoload.php';

$flux = new CimalpesClient();
// // //   $details = $flux->getDetail("1184");
 $biens = $flux->getBiens();


 print_r($biens);
//  file_put_contents(__DIR__."/biens.json",json_encode($biens));

//   $biensJson = json_decode(file_get_contents(__DIR__."/biens.json"),true);

//   $persiste = new BienPersist();


// // // // //  var_dump($persiste->languges);
//   $persiste->insertOrUpdate($biensJson);

//  var_dump($biensJson);


