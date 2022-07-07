<?php 

use Villanovo\Cimalpes\CimalpesClient;
use Villanovo\Cimalpes\CimalpesPersist;

require_once __DIR__.'/../vendor/autoload.php';

$flux = new CimalpesClient();
 //$details = $flux->getDetails(2130);
$biens = array_slice($flux->getBiens(),0,10);

$biens = array_map(function($bien) use($flux){
  return  $flux->getDetails($bien->id);
},$biens);

print_r($biens);


$persiste = new CimalpesPersist();
$persiste->insererORupdate($biens);


// $persiste->insererBiens($biens);
