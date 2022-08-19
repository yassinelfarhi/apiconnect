<?php

/**
 * CLASSE PERMETTANT DE SE CONNECTER A L'API cimalpes
 * FUNCTIONS PRRINCIPALES DE CETTE CLASSE :
 * 1- Extraire puis parser un flux de données XML
 * 2- Transformation des données en réspectant le schéma de la bd Mysql
 * 3- Insertion des données dans la db Mysql
 */


namespace Villanovo\ThirdParties;

use DateTime;
use DOMDocument;
use DOMXPath;
use Villanovo\ThirdParties\Dtos\BienDto;
use Villanovo\ThirdParties\Dtos\DetailDto;



class CimalpesClient
{
   
    public $fluxDetail;
	const API_BIENS = "https://cimalpes.ski/fr/flux/?fonction=biens&login=xml@villanovo.com&pass=M4X876RV3N2D";
	const API_DETAIL = "https://cimalpes.ski/fr/flux/?fonction=detail&login=xml@villanovo.com&pass=M4X876RV3N2D&id_bien=";
	const API_DISPO = "https://cimalpes.ski/fr/flux/?fonction=infos&login=rentals@cimalpes.com&pass=Cimalpes74120&id_bien=";
    


	public function getBiens()
	{
		$fluxBien = new \DOMDocument('1.0','UTF-8');
		$fluxBien->load(self::API_BIENS);
        $xpath = new DOMXPath($fluxBien);
        $biens = $xpath->query('//bien');
        
		$tableBiens = [];
		// foreach ($biens as $bien) {
		for($i=0; $i<3;$i++){
			$nodeBien = $biens[$i];
			$dtobien = $this->fromNodeListing($nodeBien);
			$dtobien = $this->getDetail($dtobien);
			array_push($tableBiens,$dtobien);
             break;
		}
		// }
		return $tableBiens;
	}

	/**
	 * @param BienDto $bien
	 */
	public function getDetail($bien)
	{
        
		$this->fluxDetail = new \DOMDocument('1.0','UTF-8');
        // $this->fluxDetail->preserveWhiteSpace = false;
        // $this->fluxDetail->formatOutput = true;
        // $file = file_get_contents(self::API_DETAIL .$bien->id);
        // $this->fluxDetail->loadXML($file);
		 $this->fluxDetail->load(self::API_DETAIL .$bien->id);

        $detail = $this->fluxDetail->getElementsByTagName('detail')->item(0);
        
		return $this->fromNodeDetail($detail, $bien);
       
	}
	

    
	private function fromNodeListing($node){
        $bien = new BienDto();
        $bien->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
        $bien->nom = $node->getElementsByTagName('nom_bien')->item(0)->nodeValue; 
        $bien->station = $node->getElementsByTagName('nom_station')->item(0)->nodeValue;
        $bien->quartier = $node->getElementsByTagName('nom_quartier')->item(0)->nodeValue;
        $bien->url = "https://cimalpes.ski/fr/" . $node->getElementsByTagName('url_rewriting')->item(0)->nodeValue;
 

        return $bien;
    }

    private function fromNodeDetail($node, $bien){
        // $xpath =new DOMXPath($this->fluxDetail);
        //  $multimedias = $xpath->query('equipement_multimedia//libelle');
        //  $electromenagers = $xpath->query('equipement_electromenager//libelle');
        //  $generales = $xpath->query('equipement_general//libelle');

        // $multimedias = $node->getElementsByTagName('equipement_multimedia');
        // $electromenagers = $node->getElementsByTagName('equipement_electromenager');
        // $generales = $node->getElementsByTagName('equipement_general');

        $xpath = new DOMXPath($this->fluxDetail);
        $bien->baths = 0;
        $resumeEN = $xpath->query("//descriptif_bref_en");
        $descriptionEN = $xpath->query("//descriptif_court_en");
        $resumeFR = $xpath->query("//descriptif_bref");
        $descriptionFR = $xpath->query("//descriptif_court");
        $typeBien = $xpath->query("//type_bien");
        $nbChambre = $xpath->query("//nombre_chambres");
        $latitude = $xpath->query("//latitude");
        $longitude = $xpath->query("//longitude");
        $nbAdultes = $xpath->query("//nb_adultes");
        $nbEnfants = $xpath->query("//nb_enfants");
        $occupancy = intval($nbAdultes->item(0)->nodeValue) + intval($nbEnfants->item(0)->nodeValue);


        // var_dump($resumeEN->item(0)->nodeValue);exit();

        $bien->descriptions = [ 
                                     [
                                    'lang_id' => 1,
                                    "resume" => $resumeFR->item(0)->nodeValue,
                                    "description" => $descriptionFR->item(0)->nodeValue,
                                    ],
                                     [
                                    'lang_id' => 2,
                                    "resume" => $resumeEN->item(0)->nodeValue,
                                    "description" => $descriptionEN->item(0)->nodeValue,
                                    ] 
                                 ];
        
        $bien->type = $typeBien->item(0)->nodeValue;
        $bien->bedrooms = $nbChambre->item(0)->nodeValue;
        $bien->latitude = $latitude->item(0)->nodeValue;
        $bien->longitude = $longitude->item(0)->nodeValue;
        $bien->occupancy = $occupancy;



        // if ($node->getElementsByTagName('descriptif_bref')->item(0) !== null) {
        //     $bien->descriptions['fr'] = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue;
        // } else {
        //     $bien->descriptionBref['fr'] = "";
        // }
        
        // if ($node->getElementsByTagName('descriptif_court')->item(0) !== null) {
        //     $bien->descriptionCourt['fr'] = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue;
        // } else {
        //     $bien->descriptionCourt['fr'] = "";
        // }

        // if ($node->getElementsByTagName('descriptif_bref_en')->item(0) !== null) {
        //     $bien->descriptionBref['en'] = $node->getElementsByTagName('descriptif_bref_en')->item(0)->nodeValue;
        // } else {
        //     $bien->descriptionBref['en'] = "" ;
        // }

        // if ( $node->getElementsByTagName('descriptif_court_en')->item(0) !== null ) {
        //     $bien->descriptionCourt['en'] = $node->getElementsByTagName('descriptif_court_en')->item(0)->nodeValue;
        // } else {
        //     $bien->descriptionCourt['en'] = "";
        // }

        // if ( $node->getElementsByTagName('type_bien')->item(0) !== null ) {
        //     $bien->type = $node->getElementsByTagName('type_bien')->item(0)->nodeValue;
        // } else {
        //     $bien->type = "";
        // }

        // if ( $node->getElementsByTagName('nombre_chambres')->item(0) !== null ) {
        //     $bien->bedrooms = $node->getElementsByTagName('nombre_chambres')->item(0)->nodeValue;
        // } else {
        //     $bien->bedrooms = "";
        // }

        // if ( $node->getElementsByTagName('latitude')->item(0) !== null ) {
        //     $bien->latitude = $node->getElementsByTagName('latitude')->item(0)->nodeValue;
        // } else {
        //     $bien->latitude = "";
        // }

        // if ( $node->getElementsByTagName('longitude')->item(0) !== null ) {
        //     $bien->longitude = $node->getElementsByTagName('longitude')->item(0)->nodeValue;
        // } else {
        //     $bien->longitude = "";
        // }

        // if ( $node->getElementsByTagName('longitude')->item(0) !== null ) {
        //     $bien->longitude = $node->getElementsByTagName('longitude')->item(0)->nodeValue;
        // } else {
        //     $bien->longitude = "";
        // }

        // if( $node->getElementsByTagName('nb_adultes')->item(0) !== null ) {
        //     $bien->occupancy = $node->getElementsByTagName('nb_adultes')->item(0)->nodeValue; //occupancy max rest a saisir
        //   }
            
            
         $bien->equipments = $this->getEquipments($bien->id);
         $bien->options = $this->getOptions();
         $bien->calendars = $this->getSejours($bien->id,$bien->bedrooms);
         $bien->photos = $this->getPhotos($bien->id);
         $bien->distances = $this->getDistances();
         $bien->chambres = $this->getRooms($bien->id);
         $bien->periods = $this->getSejours($bien->id,$bien->bedrooms,2);

            return $bien;
    
   
      
    }

    private function getSejours($bienId ,$nbChambre,$output = 1){
        $flux = new DOMDocument();
        $flux->load(self::API_DISPO .$bienId);
        $xpath = new DOMXPath($flux);
       
        $calendarsNodes = $xpath->query("//sejour");

        $calendars = [];
        $periods = [];
        foreach($calendarsNodes as $calendarNode){

            $calendar = [];
            $calendar['debut'] = $calendarNode->getElementsByTagName("date_debut")->item(0)->nodeValue;
            $calendar['fin'] = $calendarNode->getElementsByTagName("date_fin")->item(0)->nodeValue;
            $calendar['status'] = $calendarNode->getElementsByTagName("etat_reservation")->item(0)->nodeValue;

            $calendars[] = $calendar;

            $period = [];
            $period['debut'] = $calendarNode->getElementsByTagName("date_debut")->item(0)->nodeValue;
            $period['fin'] = $calendarNode->getElementsByTagName("date_fin")->item(0)->nodeValue;
            $period["min_stay"] = $calendarNode->getElementsByTagName("duree")->item(0)->nodeValue;  
            $period["semaine"] = 0;
            $period["prices"][] = [ "price" => ceil(intval($calendarNode->getElementsByTagName("montant")->item(0)->nodeValue) / intval($period["min_stay"])),
                                      "nb_chambre" => $nbChambre,
                                      "nb_nuit" => 1
                                    ] ;

                                // var_dump($period["prices"]);exit();
            $period["saison"] = "Saison price " . max(array_column($period["prices"],"price"));

            $periods[] = $period;

        }
            return ($output == 1) ?  $calendars: $periods;
      
    }



    private function getEquipments($bienId){
        $equipments = [];
        $xpath = new DOMXPath($this->fluxDetail);

       
        $espaceLoisirs = iterator_to_array($xpath->query("//espace_loisir//libelle[@lang='fr']"));
        $salleGym = iterator_to_array($xpath->query("//node_salle_gym//libelle[@lang='fr']"));
        $piecesVie = iterator_to_array($xpath->query("//node_piece_vie//libelle[@lang='fr']"));
        $multimedias = iterator_to_array($xpath->query("//equipement_multimedia//libelle[@lang='fr']"));
        $electromenagers = iterator_to_array($xpath->query("//equipement_electromenager//libelle[@lang='fr']"));
        $generales = iterator_to_array($xpath->query("//equipement_general//libelle[@lang='fr']"));

        $equipmentsArr = array_merge($espaceLoisirs,$salleGym,$piecesVie,$multimedias,$electromenagers,$generales);
        
        foreach($equipmentsArr as $equip){
            if(!empty($equip->nodeValue)) {  
                $equipments[] = trim($equip->nodeValue);
            };
         }
     

        //  var_dump($equipments,$bienId);exit();

        return array_unique($equipments);
    }





    private function getOptions() {
             $services = [];
             $xpath = new DOMXPath($this->fluxDetail);
             $servicesPersonnel = iterator_to_array($xpath->query("//bien_service_personnel//libelle[@lang='fr']")); // QUERY ONLY NOT EMPTY  VALUES
             $servicesInclus = iterator_to_array($xpath->query("//services_inclus/libelle[@lang='fr']"));
             $servicesBiensInclus = iterator_to_array($xpath->query("//bien_service_inclus/libelle[@lang='fr']"));
             $servicesList = array_merge($servicesPersonnel,$servicesInclus,$servicesBiensInclus);
             
             

             foreach($servicesList as $service){
                if(!empty($service->nodeValue)) {  
                    $services[] = trim($service->nodeValue);
                };
             }
          
          return array_unique($services);

    }


    private function getPhotos($bienId) {
        $photos = [];
        $xpath = new DOMXPath($this->fluxDetail);

        $nodePhotos = iterator_to_array($xpath->query("//node_photo//photo"));

        foreach($nodePhotos as $key => $nodePhoto) {
           
            list($width,$height,$type) = getimagesize(trim($nodePhoto->nodeValue));

            if(!empty($nodePhoto->nodeValue) and $width >= 1200) {
                $last = explode('/',trim($nodePhoto->nodeValue));
                $lastArr = explode(".",array_pop($last));
                $photos[$key]["url"] = trim($nodePhoto->nodeValue);
                $photos[$key]["ext"] = $lastArr[1];
                $photos[$key]["id"] = str_replace('/','_',$lastArr[0]);
                $photos[$key]["width"] = $width;
                $photos[$key]["height"] = $height;
            }
        }
          
          return $photos;
    }

    private function getDistances(){
        $distances = [];
        $xpath = new DOMXPath($this->fluxDetail);

        $distancesList = $xpath->query("//*[contains(local-name(), 'distance_m')]");


        foreach ($distancesList as $distance) {
            $type = str_replace("_distance_m","",$distance->nodeName);
            $distances[$type] = [
                'value' => $distance->nodeValue / 1000,
                'unit' => 1,
                'time' => "",
                'time_unit' => "",
                'time_per' => "",
               ];
        }


        return $distances;
    }
    
    private function getRooms($idbien){
        $rooms = [];
        $equipsArr = [];
        $litsArr = [];
        $xpath = new DOMXPath($this->fluxDetail);
        $etages = $xpath->query("//node_etage//etage");
        $key = 0;


            foreach($etages as $numEtage => $etage) {
                $chambres = $etage->getElementsBytagName("chambre");
              

                
                foreach ($chambres as $chambre) {

                   
                    $rooms[$key]["nom"] = "";
                    $rooms[$key]["id"] = $chambre->getAttribute('id');
                 
                    $rooms[$key]["type"] = 1;  
                    $rooms[$key]["etage"] = $numEtage;  

                    $chambreEquips = $chambre->getElementsByTagName("equipement_chambre");

                    foreach ($chambreEquips as $chambreEquip) {
                        $equipsArr[] = trim($chambreEquip->getElementsByTagName('libelle')->item(0)->nodeValue);
                    }

                    $sdbEquips = $chambre->getElementsByTagName("equipement_sdb");

                    foreach ($sdbEquips as $sdbEquip) {
                        $equipsArr[] = trim($sdbEquip->getElementsByTagName('libelle')->item(0)->nodeValue);
                    }
                    

                    $lits = $chambre->getElementsByTagName("lit");

                    foreach($lits as $num => $lit){
                        $litsArr[$num]["quantite"] = $lit->getElementsByTagName('quantite_lit')->item(0)->nodeValue;
                        $litsArr[$num]["largeur"] = $lit->getElementsByTagName('largeur')->item(0)->nodeValue;
                        $types = explode("(",$lit->getElementsByTagName('libelle')->item(0)->nodeValue);
                        $litsArr[$num]["charac_1"] = $types[0];
                        $litsArr[$num]["charac_2"] = !empty($types[1]) ? str_replace(")","",$types[1]) : "";
                    }

                    $rooms[$key]["equipments"] = array_unique($equipsArr);
                    $rooms[$key]["lits"] = $litsArr;
                    $key++;
                }
            }

    return $rooms;
    }


    public function getPrices() {
        $prices = [];
        $xpath = new DOMXPath($this->fluxDetail);

        $pricesList = $xpath->query("//*[contains(local-name(), '_prix')]");


        foreach ($pricesList as $price) {
            
            $prices[$price->nodeName] = [
                'value' => $price->nodeValue,
               ];
        }


        return $prices;
    }

	
}
