<?php


namespace Villanovo\ThirdParties;





use DateInterval;
use DatePeriod;
use DateTime;

use PDO;
use Exception;
use PDOException;
use Villanovo\ThirdParties\Dtos\BienDto;
use Villanovo\ThirdParties\Dtos\DetailDto;
use photos;
class BienPersist extends \db
{


    private $db;
    /**
     * @var array
     */
    public $languges;

    public $localTypes;
    public $bienParams;
    public $zones;
    public $cities;
    public $equipments;
    public $options;
    public $defaultOptions;
    public $apiDispos;
    public $toMatch;
    public $notifications;
    public $distances;

    const DB_DSN = "mysql:host=bqro.myd.infomaniak.com;port=3306;dbname=bqro_test";
    const DB_USERNAME = "bqro_yassine";
    const DB_PASS = "v6-kFGyEdL8";
    const REF_LANGUE_FR = 1;
    const REF_LANGUE_EN = 2;
    const DEFAULT_OPTIONS = [];


  
    public $api_source_id = 2;
    public $zone_default_id = 0;
    public $toUpdate = 0;

    public function __construct()
    {
        try {
            parent::__construct();
            $this->languges = $this->getLangugesCode();
            $this->localTypes = $this->getVillasTypes();
            $this->bienParams = $this->getBienParams();
            $this->zones = $this->getZones();
            $this->cities = $this->getCities();
            $this->equipments = $this->getEquipments();
            $this->options = $this->getOptions();
            $this->defaultOptions = $this->getDefaultOptions();
            $this->apiDispos = $this->getApiDispos();
            $this->distances = $this->getDistances();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    public function getLangugesCode(){
        $query = "SELECT lng_id,lng_code FROM vn_lngs";
        $this->exec($query);
        return  $this->fetchAll();
    }
  

    // @param BienDto[] $biens
    /**
     * @retrun void
     */
    public function insertOrUpdate($biens)
    {
        foreach ($biens as $bien) {
                
            if ($villaInfo = $this->checkApiVillas($bien["id"])) {
            
                if( ( !empty($bien->updatedAt) and ($bien->updatedAt > $villaInfo["api_updated_at"] or $villaInfo["api_to_update"] == 1) ) or empty($bien->updatedAt) ) {
                    
                    $bien["localId"] =$villaInfo["villa_id"];
                    $bien["villaSlug"] = $villaInfo["villa_slug"];                
                    $bien["zoneSlug"] = $villaInfo["zone_slug"];
                    $bien["zoneId"] = $villaInfo["zone_id"];
                    $bien["updated_at"] = "cimalpes api has no update date";
                    $bien["apiSlug"] = makeSlugs($bien["nom"]);
                    
                    
                    $this->updateBien($bien);
                    

                    if( $this->affectedRows() > 0 ){

                        if( $villaInfo["villa_slug"] != $bien["apiSlug"]){
                            $this->insertSlug($bien["localId"],$bien["apiSlug"]);

                        }
                    }
                }

            } else {
                $villaTypeId = $this->checkType($bien["type"]) ;
                $zoneIndex = $this->checkZone($bien["station"]);

                if ($zoneIndex >= 0) {
                    $zoneId = $this->zones[$zoneIndex]["zone_id"];
                }
                

                if ( $villaTypeId > 0 and $zoneId > 0 ) {

                    if ($idCity = $this->checkCity($bien["station"])) {
                      } else {
                         $idCity = $this->insertCity($bien["station"]);  
                    }
                    
                    
                    $bien["time"] = time();
                    $bien["apiSlug"] = makeSlugs($bien["nom"]);
                    $bien["typeId"] = $villaTypeId;
                    $bien["zoneId"] = $zoneId;
                    $bien["city"] = $this->zones[$zoneIndex]["city"];

                    $bien["zoneSlug"] = $this->zones[$zoneIndex]["cityslug"];

                    $bien["cityId"] = $idCity;
                    $bien["countryId"] = $this->zones[$zoneIndex]["country_id"];
                    $bien["calendarCode"] = generateCalendarCode(12); 

                    $bien["localId"] = $this->insertBien($bien);   
                  
                } elseif ($villaTypeId < 0 ) {
                    $this->toMatch["villaTypes"][] = '(' .$this->api_source_id. ',0,"'.$bien["type"]. '")';
                } elseif ($zoneIndex < 0 ) {
                    $this->toMatch["zones"][] = '(' .$this->api_source_id. ',0,"' .$bien["station"]. '")';
                }
                // $bien["localId"] = $this->insertBien($bien);  
            }

           if (!empty($bien["localId"])) {
                $this->insertEquipments($bien["localId"],$bien["equipments"]);               
                $this->insertOptions($bien["options"],$bien["localId"]);
                $this->insertSejours($bien["sejours"],$bien["localId"]);
                $this->syncPhotos($bien);
                $this->syncDistances($bien["distances"],$bien["localId"]);
                $this->apiToUpdate($bien["localId"]);
           }

   
        }

            $this->matchAll();
    }


    public function syncDistances($distances,$villaId){
        $toInsert = [];

        $this->unlinkDistances($villaId);   
        
        foreach ($distances as $distName => $distance) {
            

            $index = $this->checkDistance($distName);
                // var_dump($index);exit();
            if ($index !== false) {

                $distanceId = $this->distances[$index]["area_distance_id"];
                $disabled = !empty($this->distances[$index]["disabled_at"]) ;

                if($disabled == false) {
                    
                    if ($distanceId > 0 ) {

                            $distanceValue = !empty($distance["value"]) ? $distance["value"] : 0;
                            $unit = in_array($distance["unit"],[1,2]) ? $distance["unit"] : 0;
                            $time = !empty($distance["time"]) ? $distance["time"]: 0;
                            $time_unit = in_array($distance["time_unit"],[1,2]) ? $distance["time_unit"]: 0;
                            $time_per = in_array($distance["time_per"],[1,2,3,4,5]) ? $distance["time_per"]: 0;

                            if( ($distance > 0 && $unit > 0) || ($time > 0 && $time_unit > 0 && $time_per > 0 ) ) {
                                $toInsert["distances_ids"][] = '('.$villaId. ',' .$distanceId. ',"",' .$distanceValue. ','.$unit. ',' .$time. ',' .$time_unit. ',' .$time_per. ')';
                            }
                        
                        
                     } else {
                       $this->toUpdate = 1;
                     }
                }

            } else {
                    $this->toMatch["vn_api_areas"][] = '(2,0,"'.$distName. '",0)';
                    $this->toUpdate = 1;
            }



     
    

        }
        if (!empty($toInsert)) { $this->insertDistances($toInsert);}
   
    }

    public function insertDistances($toInsert){
        $sqlQuery = 'INSERT INTO vn_villas_distances_ids (villa_id,area_distance_id,villa_distance_desc,villa_distance,villa_distance_unite,
                     villa_distance_time,villa_distance_time_unite,villa_distance_time_per) values ' .implode(',',$toInsert["distances_ids"]);

                    //  var_dump($sqlQuery); exit();
        $this->exec($sqlQuery);
    }

    public function unlinkDistances($villaId) {
        $sqlQuery = 'DELETE FROM vn_villas_distances_ids WHERE villa_id =' .$villaId;
        $this->exec($sqlQuery);
    }

    public function checkDistance($distanceName) {
        return array_search($distanceName,array_column($this->distances,"area_distance_name"));
    }

    public function syncPhotos($bien) {

        $localPhotos = $this->getPhotos($bien["localId"]);

        $hasPhotos = count($localPhotos) > 0;
        $isUploadedPhoto = false;
        $uploadePhotos = [];

        foreach($bien["photos"] as $key => $photo) {
        $index = array_search($photo["id"],array_column($localPhotos,"photo_source_id"));

        if($index === false) { 
            $uploadedPhotos[$key]["name"] = $bien["zoneSlug"] . "-" . $bien["apiSlug"] . uniqid(rand(),true);
            $uploadedPhotos[$key]["ext"] = $photo["ext"];
            $uploadedPhotos[$key]["width"] = $photo["width"];
            $uploadedPhotos[$key]["height"] = $photo["height"];
            $uploadedPhotos[$key]["source_id"] = $photo["id"];
            $uploadedPhotos[$key]["url"] = $photo["url"];

        } else {
        unset($localPhotos[$key]);
        }
    }




    if(!empty($uploadedPhotos)) {  

        foreach( $uploadedPhotos as $uploadedPhoto ){

            $photo_obj = new photos();
            $photo_obj->setVillaId($bien["localId"]);
            $photo_obj->setName($uploadedPhoto['name']);
            $photo_obj->setExt($uploadedPhoto['ext']);
            $photo_obj->setSourceId($uploadedPhoto['source_id']);
            $photo_obj->setUploadMode('remote');



            if( $photo_obj->upload($uploadedPhoto['url']) ){

                if( $photo_obj->add() ){
                    $photo_obj->resize('resize');
                    $photo_obj->resize(1920);
                    $photo_obj->resize(1366);
                    $photo_obj->resize(960);
                    $photo_obj->resize(750);
                    $isUploadedPhoto = true;
                }

            }
    
        }

    }

    if(!empty($localPhotos)) {

    foreach( $localPhotos as $localPhoto ){
    $photo_obj = photos::build($localPhoto['villa_photo_id']);
    $photo_obj->delete();
    }

    }

    if( $hasPhotos && $isUploadedPhoto ){
    $this->notifications["photos"]["isNewPhotosNotification"] = true;
    } else {

    }


    }


   public function insertSejours($sejours,$localId) {
        $toInsert = [];
        $thisDay = new \DateTime("GMT");
        $thisDay->setTime(12,0,0,0);


       $dateMax = new DateTime("GMT");
       $dateMax->setTime(12,0,0);
       $dateMax = $dateMax->add(new DateInterval("P18M"));
      



       $this->unlinkSejours($localId); 

       foreach($sejours as $sejour){

         $status = $this->checkStatus($sejour["status"]);
         $dateFin = new \DateTime($sejour['fin']);
         $dateFin->setTime(12,0,0,0);

         if ($dateFin > $thisDay) {
            if ($status !== false ) { 
         
                if($status['is_disabled'] !== 1){
    
                    $dateDebut = new \DateTime($sejour['debut']); //ajouter date a midi
                    $dateDebut->setTime(12,0,0,0);
    
                    $interval = new DateInterval("P1D");
                    $daterange = new DatePeriod($dateDebut,$interval,$dateFin);
            
                    
                    foreach($daterange as $date){
                           if ($date > $thisDay and $date <= $dateMax) {
                            $toInsert[] = '(' .$localId.',' .$date->getTimestamp(). ',' .$status['is_dispo']. ',"",' .time().',' .$status['is_option'].',0,0,0)';
                           }
                    }
                }
         
             } elseif($status == false) {
                $this->toMatch['sejours'][] = '(' .$this->api_source_id. ',"' .$sejour["status"]. '",0,0,0,"")';
                $this->toUpdate = 1;
             }
         }

       }
    
       if(!empty($toInsert)) { $this->linkSejours($toInsert);} 
     
   }

    public function checkStatus($status){
        $statusArr = [];
        $statusIndex = array_search($status,array_column($this->apiDispos,"status_name"));

        if ($statusIndex !== false) {
            $statusArr["is_dispo"] = ($this->apiDispos[$statusIndex]["is_booked"] == 1) ? 0: 1;
            $statusArr["is_option"] = $this->apiDispos[$statusIndex]["is_option"];
            $statusArr["is_disabled"] = $this->apiDispos[$statusIndex]["is_disabled"];
            return $statusArr;
        } else {
            return false;
        }
    }

    

    public function unlinkSejours($localId){
        $thisDay = mktime(12,0,0,date("m"),date("d"),date("y"));
        $sqlQuery = 'DELETE FROM `vn_villas_dispos` WHERE resa_id = 0 and villa_id = '.$localId.' and villa_dispo_time >= '.$thisDay;
        $this->exec($sqlQuery);

    }


    public function linkSejours($toInsert){
          $sqlQuery = 'INSERT INTO `vn_villas_dispos` (villa_id, villa_dispo_time, villa_isdispo,villa_dispo_comment,villa_dispo_date,villa_dispo_option,
                       villa_dispo_presa,villa_dispo_api,resa_id) values' .implode(',', $toInsert);  
        $this->exec($sqlQuery);
    }



    public function insertEquipments($villaId,$equipments) {
        $toInsert = [] ;
        $this->unlinkEquipments($villaId);



        foreach($equipments as $equipment) {

            if ( $equipIndex = $this->checkEquipment($equipment)) {

              
                $equipmentId = $this->equipments[$equipIndex]["equipment_id"];

                $equipDisabled = !empty($this->equipments[$equipIndex]["disabled_at"]);
             

                if ($equipDisabled == false) {

                    if ($equipmentId > 0 ) {
                        $toInsert[] = '('.$villaId.','.$equipmentId.')';
                    } else {
                        $this->toUpdate = 1;
                    }

                }

            } else {
                $this->toMatch['equipments'][] = '(2,0,"' .$equipment. '")';
                $this->toUpdate = 1;
            }

            
    
        }
       if(!empty($toInsert)) { $this->linkEquipments($toInsert); }        
    }


    public function insertOptions($options,$villaId) {
      $toInsert = [];

      
      $this->unlinkOptions($villaId);

      foreach($options as $option){
          $optionIndex = $this->checkOption($option);
            
            if ($optionIndex !== false) {

                $optionId = $this->options[$optionIndex]["option_id"];
                $optionDisable = !empty($this->options[$optionIndex]["disabled_at"]);
                    
                if($optionDisable == false){

                    if ($optionId > 0) {
                        $toInsert[] = '(' .$villaId.',' .$optionId. ',1,0,0,0,0,0,0,0,0,0,0,0)';
                     } else {
                       $this->toUpdate = 1;
                     }
                }

            } else {
                 $this->toMatch['options'][] = '(2,0,"' .$option. '")';
                 $this->toUpdate = 1;
            }
      }

      foreach($this->defaultOptions as $defaultOption) {
        $toInsert[] = '(' .$villaId.',' .$defaultOption["villa_option_id"]. 
                                    ',' .$defaultOption["api_option_type"]. 
                                    ',' .$defaultOption["api_option_for"].
                                    ',' .$defaultOption["api_option_provider_id"].
                                    ',' .$defaultOption["api_option_term_number1"].
                                    ',' .$defaultOption["api_option_term_number2"].
                                    ',' .$defaultOption["api_option_term_same"].
                                    ',' .$defaultOption["api_option_currency_id"].
                                    ',' .$defaultOption["api_option_from"].
                                    ',' .$defaultOption["api_option_price"].
                                    ',' .$defaultOption["api_option_unit_id"].
                                    ',0,0)';
      }
    
      if(!empty($toInsert)) { $this->linkOptions($toInsert);}
    }

 

    /**
     * @param BienDto $bien
     * @param int $localId
     * @return void
     */
    public function updateBien($bien)
    {
        $sqlQuery = 'UPDATE vn_villas SET villa_private_name = "'.$bien["nom"].'", villa_public_name = "'.$bien["nom"].'", villa_slug = "'.$bien["apiSlug"].'",
                      villa_occupancy = '.$bien["occupancy"].', villa_occupancy_max = '.$bien["occupancy"].', villa_bedrooms = '.$bien["bedrooms"].', villa_baths = '.$bien["baths"].'
                      , villa_latitude = '.$bien["latitude"].', villa_longitude = '.$bien["longitude"].',api_updated_at ="' .$bien["updated_at"].'" WHERE villa_id = '.$bien["localId"];
        $this->exec($sqlQuery);
    }

    /**
     * @param BienDto $bien
     * @param int $villaTypeId
     * @return int
     */

     
    public function insertVilla($bien)
    {

        $sqlQuery = 'INSERT INTO vn_villas (villa_time,api_source_id,api_villa_id,manager_id,villa_private_name,villa_public_name,villa_slug,villa_type_id,villa_state_id,currency_id,villa_occupancy,
                       villa_occupancy_max,villa_bedrooms,villa_baths,zone_id,country_id,city_id,villa_zip,villa_address,villa_latitude,villa_longitude,villa_calendar_code,
                       villa_contract,villa_tva,villa_commission,villa_commission_tva,villa_acompte1_rate,villa_acompte2_rate,villa_acompte2_days,villa_acompte3_rate,villa_acompte3_days)
                        values ('.$bien["time"].','.$this->api_source_id.','.$bien["id"].','.$this->bienParams['manager_id'].',"'.$bien["nom"].'","'.$bien["nom"].'","'.$bien["apiSlug"].'",'.$bien["typeId"].
                        ','.$this->bienParams['villa_state_id'].','.$this->bienParams['currency_id'].','.$bien["occupancy"].','.$bien["occupancy"].','.$bien["bedrooms"].','.$bien["baths"].','.$bien["zoneId"].
                        ','.$this->bienParams['country_id'].','.$bien["cityId"].','.$this->bienParams['villa_zip'].',"'.$bien["station"].'",
                        '.$bien["latitude"].','.$bien["longitude"].',"'.$bien["calendarCode"].'",'.$this->bienParams['villa_contract'].','.$this->bienParams['villa_tva'].','.$this->bienParams['villa_commission'].
                        ','.$this->bienParams['villa_commission_tva'].','.$this->bienParams['villa_acompte1_rate'].','.$this->bienParams['villa_acompte2_rate'].','.$this->bienParams['villa_acompte2_days'].
                        ','.$this->bienParams['villa_acompte3_rate'].
                        ','.$this->bienParams['villa_acompte3_days'].')';
 
        $this->exec($sqlQuery);
     
        return $this->lastOID();
    } 


    public function insertBien($bien)
    {
        try {
            $localId = $this->insertVilla($bien);
            $this->insertSlug($localId,$bien["apiSlug"]);
            $this->showInDomain($localId,1);
      

        } catch (PDOException $ex) {
            echo "Stoped with exception ".$ex->getMessage()."\n";
            echo $ex->getTraceAsString()."\n";
        }
 
     return $localId;
    }

    public function insertCity($city){
        $sqlQuery = 'INSERT INTO `vn_cities` (city_name) values ("'.$city.'")';
        $this->exec($sqlQuery);
       
        return $this->lastOID();
    }

    public function insertSlug($villaId,$slug) {
        $query = 'insert into vn_villas_slugs(villa_id,villa_slug,villa_slug_time) values('.$villaId.',"'.$slug.'","'.time().'")';
        $this->exec($query);
    }

    public function showInDomain($villaId,$domainId) {
        $sqlQuery = 'INSERT INTO `vn_villas_domains` (villa_id,domain_id) values ('.$villaId.','.$domainId.')';
        $this->exec($sqlQuery);
        }

    public function insertDetail($bien)
    {

        foreach( $this->languges as $language):
            
            $desc = $this->checkDescription($bien["localId"], $language['lng_id']);
             // utiliser isset
            if ( $desc !== false) {
              
                if(!array_key_exists($language['lng_code'],$bien["descriptionBerf"]) && !array_key_exists($language['lng_code'],$bien['descriptifCourt']))
                continue;

                $descriptif_bref = array_key_exists($language['lng_code'],$bien["descriptionBerf"])?$bien["descriptionBerf"][$language['lng_code']]:'';
                $descriptif_court = array_key_exists($language['lng_code'],$bien['descriptifCourt'])?$bien['descriptifCourt'][$language['lng_code']]:'';

                $updateQuery = 'UPDATE `description` SET description_bref = '.$descriptif_bref.',description_court = '.$descriptif_court.' WHERE id_desc = '.$desc;

                $this->exec($updateQuery);

            } else {

                if(!array_key_exists($language['lng_code'],$bien["descriptionBerf"]) && !array_key_exists($language['lng_code'],$bien['descriptifCourt']))
                continue;

                $descriptif_bref = array_key_exists($language['lng_code'],$bien["descriptionBerf"])?$bien["descriptionBerf"][$language['lng_code']]:'';
                $descriptif_court = array_key_exists($language['lng_code'],$bien['descriptifCourt'])?$bien['descriptifCourt'][$language['lng_code']]:'';

                $insertQuery = 'INSERT INTO `description` (id_villa, description_bref, description_court, id_langue) values ('.$bien["localId"].', '.$descriptif_bref.', '.$descriptif_court.', '.$language['lng_id'].')';

                $this->exec($insertQuery);

            }


               


        endforeach;

    }


    

    public function unlinkOptions($villaId) {
        $sqlQuery = 'DELETE FROM `vn_villas_options_ids` WHERE villa_id = '.$villaId;
        $this->exec($sqlQuery);

    }

    public function linkOptions($options){
        $linkQuery = 'INSERT INTO `vn_villas_options_ids` (villa_id,villa_option_id,villa_option_inclus,villa_option_villa,villa_option_provider_id
                      ,villa_option_term_number1,villa_option_term_number2,villa_option_term_same,villa_option_currency_id,villa_option_from,villa_option_price,
                      villa_option_unit_id,villa_option_per,villa_option_high) values ' . implode(',',$options);
  
        $this->exec($linkQuery);
    }



    public function unlinkEquipments($villaId){
        $sqlQuery = 'DELETE FROM `vn_villas_equipments_ids` WHERE villa_id = '.$villaId;
        $this->exec($sqlQuery);
    
    }

    public function linkEquipments($equipments) {     
        $linkQuery = 'INSERT INTO `vn_villas_equipments_ids` (villa_id,villa_equipment_id) values ' . implode(',',$equipments);
        $this->exec($linkQuery);
    }


    public function checkType($bienType) {
            $typeIndex = array_search($bienType,array_column($this->localTypes,"api_villa_type"));
            return ($typeIndex !== false) ?  $this->localTypes[$typeIndex]["villa_type_id"] : -1;
    }
    
     public function checkEquipment($equipmentName) {
        $equipmentIndex = array_search($equipmentName,array_column($this->equipments,"equipment_name"));
        return ($equipmentIndex !== false ) ? $equipmentIndex : false;
     }

     
    public function checkZone($bienZone) {
            $zoneIndex = array_search($bienZone,array_column($this->zones,"quartier"));
            return ($zoneIndex !== false) ? $zoneIndex  : -1;
    }

    public function checkCity($city) {

        $cityIndex = array_search($city,array_column($this->cities,"city_name"));
        return ($cityIndex !== false) ? $this->cities[$cityIndex]['city_id'] : false;
    }
   


    public function checkDescription($localId, $idLangue) {
        $id_villa = intval($localId);
        $sqlQuery = 'SELECT * FROM `description` WHERE id_villa = '.$id_villa.' and id_langue = '.$idLangue.'';
        $this->exec($sqlQuery);
        $row = $this->getAssoc();
        return $row !== false ? $row["id_desc"] : false ;
    }
    

    public function checkOption($apiOptionName) {
        return array_search($apiOptionName,array_column($this->options,"option_name"));
        // return ($optionIndex !== false ) ? $this->options[$optionIndex]["option_id"] : -1;
    }

    public function checkApiVillas($idApi)
    {
        $sqlQuery = 'SELECT 
                    v.villa_id, v.villa_slug, v.api_updated_at, v.api_to_update, v.zone_id,z2t.zone_slug
                    from vn_villas as v 
                    join vn_zones as z1
                    on v.zone_id = z1.zone_id
                    join vn_zones as z2
                    on z1.zone_parent_id = z2.zone_id
                    join vn_zones_trad as z2t
                    on z2.zone_id = z2t.zone_id and z2t.langue_id = 2
                    where v.api_villa_id = '.$idApi.' and v.api_source_id = 2';

        $this->exec($sqlQuery);
        $row = $this->getAssoc();
        return  (!empty($row)) ? $row : false;

    }


    public function checkApiTypeVillas($apiVillaType) {
        $sqlQuery = 'SELECT * FROM `vn_api_villas_type` WHERE api_villas_type = '.$apiVillaType.''; 
        $this->exec($sqlQuery);
        return  $this->fetchAll();

    }
    

    public function getEquipments() {
        $sqlQuery = "SELECT equipment_name, equipment_id,disabled_at FROM vn_api_equipments";
        $this->exec($sqlQuery);
        return  $this->fetchAll();
    }

    public function getCities() {
        $sqlQuery = "SELECT city_id, city_name FROM `vn_cities`";
        $this->exec($sqlQuery);
        return  $this->fetchAll();
    }
 
    public function getZones() {


        $sqlQuery = "SELECT az.zone_id, az.zone_name as quartier, zt2.zone_name as city,zt2.zone_slug as cityslug, z3.country_id
                     FROM vn_api_zones as az
                    LEFT JOIN vn_zones as z1 ON az.zone_id = z1.zone_id
                    LEFT JOIN vn_zones_trad as zt ON z1.zone_id = zt.zone_id and zt.langue_id = 2
                    LEFT JOIN vn_zones as z2 ON z1.zone_parent_id = z2.zone_id
                    LEFT JOIN vn_zones_trad as zt2 ON z2.zone_id = zt2.zone_id and zt2.langue_id = 2
                    LEFT JOIN vn_zones as z3 on z2.zone_parent_id = z3.zone_id 
                    WHERE api_source_id = 2";

        $this->exec($sqlQuery);
        return  $this->fetchAll();
    }




    public function getVillasTypes(){
        $sqlQuery = "SELECT villa_type_id, api_villa_type FROM vn_api_villas_types where api_source_id = 2";
        $this->exec($sqlQuery);
        return $this->fetchAll() ;
    }

    public function getBienParams() {

        $sqlQuery = "SELECT * FROM `vn_api_sources` WHERE api_source_id = 2";
        $this->exec($sqlQuery);
        $res = $this->getAssoc(); 
 
        $bien_params = [];
        $bien_params["villa_zip"] = "000";
        $bien_params["country_id"] = 65;
        $bien_params["villa_state_id"] = 3;
        $bien_params["manager_id"] = $res["manager_id"];
        $bien_params["currency_id"] = $res["currency_id"];
        $bien_params["villa_contract"] = $res["api_contract"];
        $bien_params["villa_tva"] = $res['api_contract'];
        $bien_params["villa_commission"] = $res['api_commission'];
        $bien_params["villa_commission_tva"] = $res['api_commission_tva'];
        $bien_params["villa_acompte1_rate"] = $res['api_acompte1_rate'];
        $bien_params["villa_acompte2_rate"] = $res['api_acompte2_rate'];
        $bien_params["villa_acompte2_days"] = $res['api_acompte2_days'];
        $bien_params["villa_acompte3_rate"] = $res['api_acompte3_rate'];
        $bien_params["villa_acompte3_days"] = $res['api_acompte3_days'];
        $bien_params["lng_id"] = $res["lng_id"];
        return $bien_params;
    }
   
    public function getDistances(){
        $sqlQuery = 'SELECT * FROM vn_api_areas WHERE api_source_id = 2';
        $this->exec($sqlQuery);
        return $this->fetchAll();
    }
    public function getDefaultOptions() {
        $sqlQuery = "SELECT * FROM `vn_api_options_ids` WHERE api_source_id = 2";
        $this->exec($sqlQuery);
        return  $this->fetchAll();
    }
    public function getOptions() {
        $sqlQuery = "SELECT * FROM `vn_api_options` WHERE api_source_id = 2";
        $this->exec($sqlQuery);
     
        return  $this->fetchAll();
    }

    public function getApiDispos(){
        $sqlQuery = "SELECT * FROM `vn_api_dispos` WHERE api_source_id = 2";
        $this->exec($sqlQuery);
        return $this->fetchAll(); 
    }

    public function getPhotos($villaId) {
        $sqlQuery = 'SELECT villa_photo_id, photo_source_id,photo_name FROM `vn_villas_photos` WHERE villa_id = '.$villaId.' and photo_source_id is not null';
        $this->exec($sqlQuery);
  
        return $this->fetchAll();
    }

    public function matchAll() {
        if (!empty($this->toMatch['villaTypes'])) {
            $villaTypes = array_unique($this->toMatch['villaTypes']);
            $typesQuery = "INSERT INTO `vn_api_villas_types` (api_source_id,villa_type_id, api_villa_type) values " . implode(',',$villaTypes);
           
            $this->exec($typesQuery);
        }

       if (!empty($this->toMatch['zones'])) {
         $zones = array_unique($this->toMatch['zones']);
         $zonesQuery = "INSERT INTO `vn_api_zones` (api_source_id,zone_id,zone_name) values" .implode(',',$zones);
        //  var_dump($zonesQuery);exit();
         $this->exec($zonesQuery);
       }

        if (!empty($this->toMatch['equipments'])) {
            $equipments = array_unique($this->toMatch['equipments']);
            $equipmentsQuery = "INSERT INTO `vn_api_equipments` (api_source_id,equipment_id,equipment_name) values " . implode(',',$equipments);
            $this->exec($equipmentsQuery);
          }

        if (!empty($this->toMatch['options'])) {
            $options = array_unique($this->toMatch['options']);
            $optionsQuery = "INSERT INTO `vn_api_options` (api_source_id,option_id,option_name) values " . implode(',',$options);
            $this->exec($optionsQuery);
          }

        if (!empty($this->toMatch['sejours'])) {
            $status = array_unique($this->toMatch['sejours']);      
            $sejoursQuery = 'INSERT INTO `vn_api_dispos` (api_source_id,status_name,is_booked,is_option,is_disabled) values ' . implode(',',$status);
           $this->exec($sejoursQuery);
          }

          //matching des distances
          if (!empty($this->toMatch["vn_api_areas"])) { 
            $distances = array_unique($this->toMatch["vn_api_areas"]);
            $sqlQuery = 'INSERT INTO vn_api_areas (api_source_id,area_distance_id,area_distance_name,disabled_at,edited_at) values ' .implode(',',$distances);
            $this->exec($sqlQuery);
        }

       

      

    }

     //mise à jour du champs apitoUpdate

    public function apiToUpdate($villaId) {
    
            $sqlQuery = 'UPDATE vn_villas set  api_to_update =' .$this->toUpdate. ' WHERE villa_id = ' .$villaId;
            $this->exec($sqlQuery);
        
    }


}
