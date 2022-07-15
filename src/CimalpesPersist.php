<?php


namespace Villanovo\Cimalpes;

use PDO;
use Exception;
use PDOException;
use Villanovo\Cimalpes\Dtos\BienDto;

class CimalpesPersist
{


    private $db;
    /**
     * @var array
     */
    public $languges;
    public $localTypes;
    public $bienParams;
    public $zones;

    const DB_DSN = "mysql:host=bqro.myd.infomaniak.com;port=3306;dbname=bqro_test";
    const DB_USERNAME = "bqro_yassine";
    const DB_PASS = "v6-kFGyEdL8";
    const REF_LANGUE_FR = 1;
    const REF_LANGUE_EN = 2;


  
    public $api_source_id = 2;
    public $zone_default_id = 0;

    public function __construct()
    {
        try {

            $this->db = new \PDO(self::DB_DSN, self::DB_USERNAME, self::DB_PASS);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $this->languges = $this->getLangugesCode();
            $this->localTypes = $this->getVillasTypes();
            $this->bienParams = $this->getBienParams();
            $this->zones = $this->getZones();

        } catch (Exception $e) {

            $e->getMessage();
        }
    }


    public function getLangugesCode(){

        $query = "SELECT lng_id,lng_code FROM vn_lngs";

        $sqlStatement = $this->db->prepare($query);

        $sqlStatement->execute();

        return  $sqlStatement->fetchAll();
    }
  
    /**
     * @param BienDto[] $biens
     * @retrun void
     */
    public function insertOrUpdate($biens)
    {
        foreach ($biens as $bien) {


     // vérifier si le type et la zone existent 
            $villaTypeId = $this->checkType($this->localTypes,$bien->type) ;
            $zoneId = $this->checkZone($this->zones,$bien->station);

             if ( $villaTypeId > 0 && $zoneId > 0 ) {

                       //vérifier si la ville éxiste
                       if ($this->checkCity($bien->station) !== false) {
                           $idCity = $this->checkCity($bien->station)["city_id"];
                         } else {
                            $idCity = $this->insertCity($bien->station);
                       }
                     
                    $bien->time = time();
                    $bien->typeId = $villaTypeId;
                    $bien->baths = 0;
                    $bien->zoneId = $zoneId;
                    $bien->cityId = $idCity;
                    $bien->calendarCode = bin2hex(random_bytes(10));   
                    
                     // vérifier si un enregistrement api éxiste sur la table api_villas
                     $bien->localId = $this->checkApiVillas($bien->id);
                     
                     if ($bien->localId !== false) {
                         $this->updateBien($bien,$this->bienParams);
                     } else {
                         $this->insertBien($bien,$this->bienParams);
                     }
    
                    $this->insertDetail($bien,$this->bienParams);

                
            } elseif ($villaTypeId < 0 ) {
                $this->insertVillaType($bien->type);
            } elseif ($zoneId < 0) {
                $this->insertZone($bien->station);
            }
        }
    }

   

    public function insertVillaType($bienType) {

         $sqlQuery = "INSERT INTO `vn_api_villas_types` (api_source_id,villa_type_id, api_villas_type,disabled_at) values (:apiSource,:villaTypeApi) ";
         $sqlStatement = $this->db->prepare($sqlQuery);
         $sqlStatement->bindParam(":apiSource",$this->api_source_id);
         $sqlStatement->bindParam(":villaTypeApi",$bienType);
         $sqlStatement->execute();
    }

    /**
     * @param BienDto $bien
     * @param int $localId
     * @return void
     */
    public function updateBien($bien, $localId)
    {

        //update table villa
    
        $villaQuery = "UPDATE `vn_villas` SET nom = :nom_villa WHERE id = :local_id";
        $sqlStatement = $this->db->prepare($villaQuery);
        $sqlStatement->bindParam(":nom_villa",$bien->nom);
        $sqlStatement->bindParam(":local_id",$localId);
        $sqlStatement->execute();

        
       //inserer description : appeler function insertDetail
       
         $this->insertDetail($bien,$localId);
       


    }

    /**
     * @param BienDto $bien
     * @param int $villaTypeId
     * @return int
     */

     
    public function insertVilla($bien)
    {
        // $villaQuery = "INSERT INTO `vn_villas` (nom) values (:nom_villa)";
         
     

        $VillaQuery = 'insert into vn_villas(villa_time,manager_id,villa_private_name,villa_public_name,villa_slug,villa_type_id,villa_state_id,currency_id,villa_occupancy,
                       villa_occupancy_max,villa_bedrooms,villa_baths,zone_id,country_id,city_id,villa_zip,villa_address,villa_latitude,villa_longitude,villa_calendar_code,
                       villa_contract,villa_tva,villa_commission,villa_commission_tva,villa_acompte1_rate,villa_acompte2_rate,villa_acompte2_days,villa_acompte3_rate,villa_acompte3_days)
                       values(:villa_time,:manager_id,:villa_private_name,:villa_public_name,:villa_slug,:villa_type,:villa_state,:currency_id,:villa_occupancy_max,
                       :villa_occupancy_max,:villa_bedrooms,:villa_baths,:zone_id,:country_id,:city_id,:villa_zip,:villa_address,:villa_latitude,:villa_longitude,:villa_calendar_code,:villa_contract,:villa_tva,:villa_commission,:villa_commission_tva,:villa_acompte1_rate,:villa_acompte2_rate,:villa_acompte2_days,:villa_acompte3_rate,:villa_acompte3_days)';


        $query = $this->db->prepare($villaQuery);

        foreach($paramters as $key => $param) {
            $query->bindParam($key, $param);
        }
        $query->execute();

        return $this->db->lastInsertId();
    } 


     /**
     * @param BienDto $bien
     * @param int $localId
     * @param int $apiSource
     * @return void
     */
    public function insertMatching($bien, $localId, $apiSource)
    {
        $apiQuery = "INSERT INTO api_villas (villa_id,api_villa_id,api_source_id) values (:api_villa,:villa_id,:api_source_id)";
        $query = $this->db->prepare($apiQuery);
        $query->bindParam(":api_villa", $bien->id, PDO::PARAM_STR);
        $query->bindParam(":villa_id", $localId, PDO::PARAM_INT);
        $query->bindParam(":api_source_id", $apiSource, PDO::PARAM_INT);
        $query->execute();
    }

    public function insertBien($bien, $apiSource = 2)
    {
        try {

            $this->db->beginTransaction();
            $localId = $this->insertVilla($bien);
            $this->insertMatching($bien, $localId, $apiSource);
            $this->db->commit();

           
        } catch (PDOException $ex) {
            echo "Stoped with exception ".$ex->getMessage()."\n";
            echo $ex->getTraceAsString()."\n";
            $this->db->rollBack();
        }
 
     return $localId;
    }

    public function insertCity($city){
          $sqlQuery = "INSERT INTO `vn_cities` (city_name) values (:city)";
          $sqlStatement = $this->db->prepare($sqlQuery);
          $sqlStatement->bindParam(":city",$city);
          $sqlStatement->execute();

          return $this->db->lastInsertId();
    }



    public function insertDetail($bien,$localId)
    {

        foreach( $this->languges as $language):
            
            $desc = $this->checkDescription($localId, $language['lng_id']);
             // utiliser isset
            if ( $desc !== false) {
              
                if(!array_key_exists($language['lng_code'],$bien->descriptionBerf) && !array_key_exists($language['lng_code'],$bien->descriptifCourt))
                continue;

                $updateQuery = "UPDATE `description` SET description_bref = :descriptif_bref, description_court = :descriptif_court WHERE id_desc = :desc_id";
                $sqlStatement = $this->db->prepare($updateQuery);
                $descriptif_bref = array_key_exists($language['lng_code'],$bien->descriptionBerf)?$bien->descriptionBerf[$language['lng_code']]:'';
                $descriptif_court = array_key_exists($language['lng_code'],$bien->descriptifCourt)?$bien->descriptifCourt[$language['lng_code']]:'';
                $sqlStatement->bindParam(":desc_id", $desc, PDO::PARAM_STR);
                $sqlStatement->bindParam(":description_bref",$descriptif_bref, PDO::PARAM_STR);
                $sqlStatement->bindParam(":description_court",$descriptif_court, PDO::PARAM_STR);
                $sqlStatement->bindParam(":id_langue", $language["lng_id"], PDO::PARAM_STR);
                $sqlStatement->execute();

            } else {

                if(!array_key_exists($language['lng_code'],$bien->descriptionBerf) && !array_key_exists($language['lng_code'],$bien->descriptifCourt))
                continue;

                $insertQuery = "INSERT INTO `description` (id_villa, description_bref, description_court, id_langue) values (:id_villa, :description_bref, :description_court, :id_langue)";
                $sqlStatement = $this->db->prepare($insertQuery);
                $descriptif_bref = array_key_exists($language['lng_code'],$bien->descriptionBerf)?$bien->descriptionBerf[$language['lng_code']]:'';
                $descriptif_court = array_key_exists($language['lng_code'],$bien->descriptifCourt)?$bien->descriptifCourt[$language['lng_code']]:'';
                $sqlStatement->bindParam(":id_villa", $localId, PDO::PARAM_STR);
                $sqlStatement->bindParam(":description_bref",$descriptif_bref, PDO::PARAM_STR);
                $sqlStatement->bindParam(":description_court",$descriptif_court, PDO::PARAM_STR);
                $sqlStatement->bindParam(":id_langue", $language["lng_id"], PDO::PARAM_STR);
                $sqlStatement->execute();

            }


               


        endforeach;

    }

    public function insertZone($stationName) {

        $sqlQuery = "INSERT INTO `vn_api_zones` (api_source_id,zone_id,zone_name,disabled_at) values (:apiSourceId,:zoneId,:zoneName,'')";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":apiSourceId",$this->api_source_id);
        $sqlStatement->bindParam(":zoneId",$this->zone_default_id);
        $sqlStatement->bindParam(":zoneName",$stationName);
        $sqlStatement->execute();
    }

    
    public function checkZone($localTypes,$bienType) {
        $villaTypeId = -1 ;
        array_walk($localTypes , function($localType,$index,$bienType){
            if (in_array($bienType, $localType)) {
              $villaTypeId = $localType["villa_type_id"];
            }           
        },$bienType);

        return $villaTypeId ;
     
    }



    public function checkType($localZones, $bienZone) {
        $zoneId = -1 ;
        array_walk($localZones , function($zone,$index,$bienZone){
            if (in_array($bienZone, $zone)) {
              $zoneId = $zone["zone_id"];
            }         
           },$bienZone);

           return $zoneId;
          }

    public function checkDescription($localId, $idLangue) {
         
        $id_villa = intval($localId);
        $query = "SELECT * FROM `description` WHERE id_villa = :local_id and id_langue = :id_langue";
        $sqlStatement = $this->db->prepare($query);  
        $sqlStatement->bindParam(":local_id", $id_villa);
        $sqlStatement->bindParam(":id_langue", $idLangue);
        $sqlStatement->execute();
        
        $result = $sqlStatement->fetch();

        return $result !== false ? $result["id_desc"] : false ;

    }


    public function checkApiVillas($idApi)
    {
        $sqlQuery = "SELECT * FROM api_villas WHERE api_villa_id = :idApi and api_source_id = :idSource";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":idApi", $idApi);
        $sqlStatement->bindParam(":idSource",$this->api_source_id);
        $sqlStatement->execute();
        return  $sqlStatement->fetch();
    }

    public function checkApiTypeVillas($apiVillaType) {
        $sqlQuery = "SELECT * FROM `vn_api_villas_type` WHERE api_villas_type = :api_villa_type"; 
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":api_villa_type",$apiVillaType);
        $sqlStatement->execute();
        return $sqlStatement->fetch();
    }
    
    public function checkCity($city) {
        $sqlQuery = "SELECT * FROM `vn_cities` WHERE city_name = :city";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":city",$city);
        $sqlStatement->execute();
        return $sqlStatement->fetch();
    }
   

 
    public function getZones() {

        $sqlQuery = "SELECT zone_id, zone_name FROM `vn_api_zones` WHERE api_source_id = :source_id";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":source_id",$this->api_source_id);
        $sqlStatement->execute();
        $zones = $sqlStatement->fetchAll();
        return $zones;
    }




    public function getVillasTypes(){

        $sqlQuery = "SELECT villa_type_id, api_villas_type FROM vn_api_villas_types where api_source_id = :source_id";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":source_id",$this->api_source_id);
        $sqlStatement->execute();
        $types = $sqlStatement->fetchAll();

        return $types;
    }

    public function getBienParams() {
  
        // $sqlQuery = "SELECT manager_id,currency_id,villa_contract,villa_tva,villa_commission,villa_commission_tva, villa_acompte1_rate, villa_acompte2_days,villa_acompte3_rate, villa_acompte3_days
        //              FROM `vn_api_sources` WHERE api_source_id = 2";

        $sqlQuery = "SELECT * FROM `vn_api_sources` WHERE api_source_id = 2";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->execute();
        $apiSources = (object) $sqlStatement->fetch(); 
        
        $bien_params = [];

        $bien_params["villa_zip"] = "";
        $bien_params["country_id"] = 65;
        $bien_params["villa_state_id"] = 3;;
        $bien_params["manager_id"] = $apiSources->manager_id;
        $bien_params["currency_id"] = $apiSources->currency_id;
        $bien_params["villa_contract"] = $apiSources->api_contract;
        $bien_params["villa_tva"] = $apiSources->api_contract;
        $bien_params["villa_commission"] = $apiSources->api_commission;
        $bien_params["villa_commision_tva"] = $apiSources->api_commission_tva;
        $bien_params["villa_acompte1_rate"] = $apiSources->api_acompte1_rate;
        $bien_params["villa_acompte2_days"] = $apiSources->api_acompte2_days;
        $bien_params["villa_acompte3_rate"] = $apiSources->api_acompte3_rate;
        $bien_params["villa_acompte3_days"] = $apiSources->api_acompte3_days;

        return $bien_params;
    }


}
