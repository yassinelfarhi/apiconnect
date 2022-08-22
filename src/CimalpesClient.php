<?php




namespace Villanovo\ThirdParties;

use DateTime;
use DOMDocument;
use DOMXPath;
use Villanovo\ThirdParties\Dtos\BienDto;
use Villanovo\ThirdParties\Dtos\DetailDto;



class CimalpesClient
{
   /**
    *  @var DOMXPath 
    */
    public $fluxDetail;


     /**
    *  @var DOMXPath 
    */
    public $fluxDispos;

	const API_BIENS = "https://cimalpes.ski/fr/flux/?fonction=biens&login=xml@villanovo.com&pass=M4X876RV3N2D";
	const API_DETAIL = "https://cimalpes.ski/fr/flux/?fonction=detail&login=xml@villanovo.com&pass=M4X876RV3N2D&id_bien=";
	const API_DISPO = "https://cimalpes.ski/fr/flux/?fonction=infos&login=rentals@cimalpes.com&pass=Cimalpes74120&id_bien=";
    
    /**
     * @var BienDto
     */
    protected $dto;



    public function getBien($idBien){
        $this->dto = new BienDto();
        $this->dto->id = $idBien;
        $this->getDetail();
        return $this->dto;
    }



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
			$this->dto = $this->fromNodeListing($nodeBien);
			$this->getDetail();
			array_push($tableBiens,$this->dto);
             break;
		}
		// }
		return $tableBiens;
	}

	/**
	 * @param BienDto $bien
	 */
	public function getDetail()
	{
		$fluxDetail = new \DOMDocument('1.0','UTF-8');
		$fluxDetail->load(self::API_DETAIL .$this->dto->id);
        $this->fluxDetail =  new DOMXPath($fluxDetail);
		$this->fromNodeDetail();
       
	}
	

    
	private function fromNodeListing($node){
        $bien = new BienDto();
        $bien->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
        // $bien->nom = $node->getElementsByTagName('nom_bien')->item(0)->nodeValue; 
        // $bien->station = $node->getElementsByTagName('nom_station')->item(0)->nodeValue;
        // $bien->quartier = $node->getElementsByTagName('nom_quartier')->item(0)->nodeValue;
        $bien->url = "https://cimalpes.ski/fr/" . $node->getElementsByTagName('url_rewriting')->item(0)->nodeValue;
        return $bien;
    }

    private function fromNodeDetail(){


        
        $resumeEN = $this->fluxDetail->query("//descriptif_bref_en");
        $descriptionEN = $this->fluxDetail->query("//descriptif_court_en");
        $resumeFR = $this->fluxDetail->query("//descriptif_bref");
        $descriptionFR = $this->fluxDetail->query("//descriptif_court");
        $typeBien = $this->fluxDetail->query("//type_bien");
        $nbChambre = $this->fluxDetail->query("//nombre_chambres");
        $latitude = $this->fluxDetail->query("//latitude");
        $longitude = $this->fluxDetail->query("//longitude");
        $nbAdultes = $this->fluxDetail->query("//nb_adultes");
        $nbEnfants = $this->fluxDetail->query("//nb_enfants");
        $occupancy = intval($nbAdultes->item(0)->nodeValue) + intval($nbEnfants->item(0)->nodeValue);



        $this->dto->nom = $this->fluxDetail->query("//nom_bien")->item(0)->nodeValue;
        $this->dto->station = $this->fluxDetail->query("//station")->item(0)->nodeValue;
        $this->dto->descriptions = [ 
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
        
        $this->dto->type = $typeBien->item(0)->nodeValue;
        $this->dto->bedrooms = $nbChambre->item(0)->nodeValue;
        $this->dto->latitude = $latitude->item(0)->nodeValue;
        $this->dto->longitude = $longitude->item(0)->nodeValue;
        $this->dto->occupancy = $occupancy;
        $this->dto->equipments = $this->getEquipments($this->dto->id);
        $this->dto->options = $this->getOptions();
        $this->dto->calendars = $this->getSejours($this->dto->id,$this->dto->bedrooms);
        $this->dto->photos = $this->getPhotos($this->dto->id);
        $this->dto->distances = $this->getDistances();
        $this->dto->chambres = $this->getRooms($this->dto->id);
        $this->dto->periods = $this->getSejours($this->dto->id,$this->dto->bedrooms,2);

            // return $bien;
    
   
      
    }

    private function getSejours($bienId ,$nbChambre,$output = 1){
        $flux = new DOMDocument();
        $flux->load(self::API_DISPO .$bienId);
        $this->fluxDispos = new DOMXPath($flux);
        $calendarsNodes = $this->fluxDispos->query("//sejour");

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
        $espaceLoisirs = iterator_to_array($this->fluxDetail->query("//espace_loisir//libelle[@lang='fr']"));
        $salleGym = iterator_to_array($this->fluxDetail->query("//node_salle_gym//libelle[@lang='fr']"));
        $piecesVie = iterator_to_array($this->fluxDetail->query("//node_piece_vie//libelle[@lang='fr']"));
        $multimedias = iterator_to_array($this->fluxDetail->query("//equipement_multimedia//libelle[@lang='fr']"));
        $electromenagers = iterator_to_array($this->fluxDetail->query("//equipement_electromenager//libelle[@lang='fr']"));
        $generales = iterator_to_array($this->fluxDetail->query("//equipement_general//libelle[@lang='fr']"));

        $equipmentsArr = array_merge($espaceLoisirs,$salleGym,$piecesVie,$multimedias,$electromenagers,$generales);
        
        foreach($equipmentsArr as $equip){
            if(!empty($equip->nodeValue)) {  
                $equipments[] = trim($equip->nodeValue);
            };
         }
     
        return array_unique($equipments);
    }





    private function getOptions() {
             $services = [];
             $servicesPersonnel = iterator_to_array($this->fluxDetail->query("//bien_service_personnel//libelle[@lang='fr']")); 
             $servicesInclus = iterator_to_array($this->fluxDetail->query("//services_inclus/libelle[@lang='fr']"));
             $servicesBiensInclus = iterator_to_array($this->fluxDetail->query("//bien_service_inclus/libelle[@lang='fr']"));
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

        $nodePhotos = iterator_to_array($this->fluxDetail->query("//node_photo//photo"));

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
        $distancesList = $this->fluxDetail->query("//*[contains(local-name(), 'distance_m')]");
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
    
    private function getRooms(){
        $rooms = [];
        $equipsArr = [];
        $litsArr = [];
        $etages = $this->fluxDetail->query("//node_etage//etage");
        $key = 0;
        $this->dto->baths = 0;
      

            foreach($etages as $numEtage => $etage) {
                $chambres = $etage->getElementsBytagName("chambre");
                $this->dto->baths += $etage->getElementsByTagName("sdb")->length;        
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



	
}
