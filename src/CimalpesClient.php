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
		for($i=0; $i<2;$i++){
			$nodeBien = $biens[$i];
			$dtobien = $this->fromNodeListing($nodeBien);
			$dtobien = $this->getDetail($dtobien);
			array_push($tableBiens,$dtobien);
            
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
        $bien->baths = 0;

        if ($node->getElementsByTagName('descriptif_bref')->item(0) !== null) {
            $bien->descriptionBref['fr'] = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue;
        } else {
            $bien->descriptionBref['fr'] = "";
        }
        
        if ($node->getElementsByTagName('descriptif_court')->item(0) !== null) {
            $bien->descriptionCourt['fr'] = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue;
        } else {
            $bien->descriptionCourt['fr'] = "";
        }

        if ($node->getElementsByTagName('descriptif_bref_en')->item(0) !== null) {
            $bien->descriptionBref['en'] = $node->getElementsByTagName('descriptif_bref_en')->item(0)->nodeValue;
        } else {
            $bien->descriptionBref['en'] = "" ;
        }

        if ( $node->getElementsByTagName('descriptif_court_en')->item(0) !== null ) {
            $bien->descriptionCourt['en'] = $node->getElementsByTagName('descriptif_court_en')->item(0)->nodeValue;
        } else {
            $bien->descriptionCourt['en'] = "";
        }

        if ( $node->getElementsByTagName('type_bien')->item(0) !== null ) {
            $bien->type = $node->getElementsByTagName('type_bien')->item(0)->nodeValue;
        } else {
            $bien->type = "";
        }

        if ( $node->getElementsByTagName('nombre_chambres')->item(0) !== null ) {
            $bien->bedrooms = $node->getElementsByTagName('nombre_chambres')->item(0)->nodeValue;
        } else {
            $bien->bedrooms = "";
        }

        if ( $node->getElementsByTagName('latitude')->item(0) !== null ) {
            $bien->latitude = $node->getElementsByTagName('latitude')->item(0)->nodeValue;
        } else {
            $bien->latitude = "";
        }

        if ( $node->getElementsByTagName('longitude')->item(0) !== null ) {
            $bien->longitude = $node->getElementsByTagName('longitude')->item(0)->nodeValue;
        } else {
            $bien->longitude = "";
        }

        if ( $node->getElementsByTagName('longitude')->item(0) !== null ) {
            $bien->longitude = $node->getElementsByTagName('longitude')->item(0)->nodeValue;
        } else {
            $bien->longitude = "";
        }

        if( $node->getElementsByTagName('nb_adultes')->item(0) !== null ) {
            $bien->occupancy = $node->getElementsByTagName('nb_adultes')->item(0)->nodeValue; //occupancy max rest a saisir
          }
            
            
    //     // parsing des equipements
    //    $equipments_arr = [];
    //     foreach($multimedias as $multimedia){
    //         array_push($equipments_arr, $multimedia->getElementsByTagName('libelle')->item(0)->nodeValue);
    //       }
  
    //       foreach($electromenagers as $electromenager){
    //           array_push($equipments_arr ,$electromenager->getElementsByTagName('libelle')->item(0)->nodeValue);
    //         }
  
    //         foreach($generales as $generale){
    //           array_push($equipments_arr ,$generale->getElementsByTagName('libelle')->item(0)->nodeValue);
    //         }

         $bien->equipments = $this->getEquipments($bien->id);
         $bien->options = $this->getOptions();
         $bien->sejours = $this->getSejours($bien->id);
         $bien->photos = $this->getPhotos($bien->id);
         $bien->distances = $this->getDistances();
         $bien->chambres = $this->getRooms($bien->id);

            return $bien;
    
   
      
    }

    private function getSejours($bienId){
        $flux = new DOMDocument();
        $flux->load(self::API_DISPO .$bienId);
        $xpath = new DOMXPath($flux);
       
        $sejours = $xpath->query("//sejour");

        $sejoursArray = [];
        foreach($sejours as $key => $sejour){
            $sejoursArray[$key]["debut"] = $sejour->getElementsByTagName("date_debut")->item(0)->nodeValue;
            $sejoursArray[$key]["fin"] = $sejour->getElementsByTagName("date_fin")->item(0)->nodeValue;
            $sejoursArray[$key]["status"] = $sejour->getElementsByTagName("etat_reservation")->item(0)->nodeValue;
        }

       return $sejoursArray;
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
        // $base = __DIR__ . "/photos/villa" . $bienId . "/" . $key . "-" .$bienId . ".jpg";
        // var_dump($nodePhoto->nodeValue,$base);exit();
        // $file  = file_get_contents($nodePhoto->nodeValue);
        // file_put_contents($base,$file);
        // mkdir(__DIR__ . "/photos/" . $bienId);
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
              

                // var_dump($numEtage,$idbien,$chambres);exit();
                foreach ($chambres as $chambre) {

                    // $rooms[$key]["nom"] = $chambre->getElementsByTagName("libelle")->item(0)->nodeValue;
                    $rooms[$key]["nom"] = $chambre->getElementsByTagName("libelle")->item(0)->nodeValue;
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
                        $litsArr[$num]["charac_2"] = $types[1];;
                    }

                    $rooms[$key]["equipments"] = array_unique($equipsArr);
                    $rooms[$key]["lits"] = $litsArr;
                    $key++;
                }
            }

    return $rooms;
    }


    // public function getLits(){
        
    //     $xpath = new DOMXPath($this->fluxDetail);

    //     $lits = $xpath->query("")
    // }

	
}
