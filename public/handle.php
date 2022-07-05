<?php 

use Villanovo\Cimalpes\CimalpesClient;
use Villanovo\Cimalpes\CimalpesPersist;

require_once __DIR__.'/../vendor/autoload.php';

 $flux = new CimalpesClient();
 $details = $flux->getDetails(2130);
 $biens = $flux->getBiens();
 print_r($details);


$persiste = new CimalpesPersist();
// $persiste->insererBiens($biens);
  $persiste->insererDetail($details);
