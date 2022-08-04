<?php 

use Villanovo\Cimalpes\CimalpesClient;
use Villanovo\Cimalpes\CimalpesPersist;

require_once __DIR__.'/../vendor/autoload.php';

$flux = new CimalpesClient();
//  $details = $flux->getDetails(2130);
 $biens = $flux->getBiens();

// $biens = array_map(function($bien) use($flux){
//   return  $flux->getDetails($bien->id);
// },$biens);

// $biens = array_map(function($bien) use($flux){
  
//          $bienArray = (array) $bien;
//          $bienDetail = (array) $flux->getDetails($bien->id);

//          return  (object) array_merge($bienArray,$bienDetail);
// },$biens);








// $biens = $flux->getBiensTest();
 print_r($biens);
  // print_r($flux->getDetails(2130));



// $persiste = new CimalpesPersist();
// // $persiste->zones;
//   // print_r($persiste->localTypes);
//   // print_r($persiste->zones);
//  $persiste->insertOrUpdate($biens);
