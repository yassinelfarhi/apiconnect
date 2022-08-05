<?php

/**
 * CLASSE PERMETTANT DE SE CONNECTER A L'API cimalpes
 * FUNCTIONS PRRINCIPALES DE CETTE CLASSE :
 * 1- Extraire puis parser un flux de données XML
 * 2- Transformation des données en réspectant le schéma de la bd Mysql
 * 3- Insertion des données dans la db Mysql
 */


namespace Villanovo\ThirdParties;

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
            // break;
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
        $this->fluxDetail->preserveWhiteSpace = false;
        $this->fluxDetail->formatOutput = true;
        $file = file_get_contents(self::API_DETAIL .$bien->id);
        $this->fluxDetail->loadXML($file);
		// $this->fluxDetail->load(self::API_DETAIL .$bien->id);

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

        $multimedias = $node->getElementsByTagName('equipement_multimedia');
        $electromenagers = $node->getElementsByTagName('equipement_electromenager');
        $generales = $node->getElementsByTagName('equipement_general');
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
            
            
        // parsing des equipements
       $equipments_arr = [];
        foreach($multimedias as $multimedia){
            array_push($equipments_arr, $multimedia->getElementsByTagName('libelle')->item(0)->nodeValue);
          }
  
          foreach($electromenagers as $electromenager){
              array_push($equipments_arr ,$electromenager->getElementsByTagName('libelle')->item(0)->nodeValue);
            }
  
            foreach($generales as $generale){
              array_push($equipments_arr ,$generale->getElementsByTagName('libelle')->item(0)->nodeValue);
            }

         $bien->equipments = $equipments_arr; //  cette partie doit etre élaboré sous forme de fonction
         $bien->options = $this->getOptions();
         $bien->sejours = $this->getSejours($bien->id);
         $bien->photos = $this->getPhotos($bien->id);

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




    private function getOptions() {
             $services = [];
             $xpath = new DOMXPath($this->fluxDetail);
             $servicesPersonnel = iterator_to_array($xpath->query("//bien_service_personnel//libelle[@lang='fr']")); // QUERY ONLY NOT EMPTY  VALUES
             $servicesInclus = iterator_to_array($xpath->query("//services_inclus/libelle[@lang='fr']"));
             $servicesBiensInclus = iterator_to_array($xpath->query("//bien_service_inclus/libelle[@lang='fr']"));
             $servicesList = array_merge($servicesPersonnel,$servicesInclus,$servicesBiensInclus);
             
             foreach($servicesList as $service){
                if(!empty($service->nodeValue)) {  
                    $services[] = $service->nodeValue;
                };
             }
          
          return $services;

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
    




	
}
