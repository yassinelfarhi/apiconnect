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
    protected $languges;

    const DB_DSN = "mysql:host=localhost;port=3306;dbname=villanovo";
    const DB_USERNAME = "root";
    const DB_PASS = "";
    const REF_LANGUE_FR = 1;
    const REF_LANGUE_EN = 2;

    public $api_source_id = 2;

    public function __construct()
    {
        try {

            $this->db = new \PDO(self::DB_DSN, self::DB_USERNAME, self::DB_PASS);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->languges = $this->getLangugesCode();
            $this->localTypes = $this->getVillasTypes();

        } catch (Exception $e) {

            $e->getMessage();
        }
    }


    public function getLangugesCode(){

        $query = "SELECT  lng_id,lng_code FROM vn_lngs";

        $sqlStatement = $this->db->prepare($query);

        $sqlStatement->execute();

        return  $sqlStatement->fetchAll();
    }
  
    /**
     * @param BienDto[] $biens
     * @param int $apiSource
     * @retrun void
     */
    public function insertOrUpdate($biens, $apiSource = 2)
    {
        // $checkType = $this->checkApiTypeVillas($biens->villaType);
        
        foreach ($biens as $bien) {
           
             // vérifier si le type villa existe 
             if ( array_search($bien->villaType,$this->localTypes) !== false ) {
                // RECUPERER ID TYPE VILLA
               // vérifier si un enregistrement api éxiste dans la table api_villas
                $local_bien = $this->checkApiVillas($bien->id, $apiSource);

                if ($local_bien !== false) {
                    $this->updateBien($bien, $local_bien["villa_id"],);
                } else {
                    $this->insertBien($bien, $apiSource);
                }
    
                  $this->insertDetail($bien,$local_bien);
            } else {

                $this->insertVillaType($apiSource = 2, $bien->villaType);
            }
        }
    }

    /**
     * @param int $apiSource
     * @param string $villaTypeApi
     * @return void
     */

    public function insertVillaType($apiSource = 2,$villaTypeApi) {
         $sqlQuery = "INSERT INTO `vn_api_villas_types` (api_source_id,villa_type_id, api_villas_type,disabled_at) values (:apiSource,:villaTypeApi) ";
         $sqlStatement = $this->db->prepare($sqlQuery);
         $sqlStatement->bindParam(":apiSource",$apiSource);
         $sqlStatement->bindParam(":villaTypeApi",$villaTypeApi);
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

     
    public function insertVilla($bien,$villaTypeId)
    {
        $villaQuery = "INSERT INTO `vn_villas` (nom) values (:nom_villa)";

        $paramters = [  "villa_time" => time(),
                        "manager_id" => "2 : test value", // table source
                        "villa_private_name" => $bien->nom,
                        "villa_public_name" => $bien->nom,
                        "villa_slug" => $bien->villaSlug,
                        "villa_type_id" => $villaTypeId,
                        "villa_state_id" => "test state id", // 3 par default
                        "currency_id" => "test currency id", // table source
                        "villa_occupancy" => $bien->occupancy,
                        "villa_occupancy_max" => $bien->occupancy,
                        "villa_bedrooms" => $bien->bedrooms,
                        "villa_baths" => $bien->baths,
                        "zone_id" => "test value",  // vérifier station dans table  vn_api_zones
                        "country_id" => "65",  
                        "city_id" => "test value", // vérifier si nom de statio éxiste dans table vn_cities (matching automatique)
                        "villa_zip" => $villa_zip, // VALEUr vide
                        "villa_address" => $bien->address,  // nom_quartier
                        "villa_latitude" => $bien->latitude, // bien->latitude
                        "villa_longitude" => $bien->longitude, // bien
                        "villa_calendar_code" => "test value", // generer un code alphanumerique unique de facon aleatoire 
                        "villa_contract" => "test value",  // a récuperer de la table source
                        "villa_tva" => "test value", // a récuperer de la table source
                        "villa_commission" => "test", // a récuperer de la table source
                        "villa_commission_tva" => "test", // a récuperer de la table source
                        "villa_acompte1_rate" => "test", // a récuperer de la table source
                        "villa_acompte2_days" => "", // a récuperer de la table source
                        "villa_acompte3_rate" => "", // a récuperer de la table source
                        "villa_acompte3_days" => "" // a récuperer de la table source
                     ];

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


    public function checkApiVillas($idApi, $idSource)
    {

        $sqlQuery = "SELECT * FROM api_villas WHERE api_villa_id = :idApi and api_source_id = :idSource";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":idApi", $idApi);
        $sqlStatement->bindParam(":idSource", $idSource);
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


    public function getVillasTypes(){
        $sqlQuery = "SELECT villa_type_id, api_villas_type FROM vn_api_villas_types where api_source_id = :source_id";
        $sqlStatement = $this->db->prepare($sqlQuery);
        $sqlStatement->bindParam(":source_id",$this->api_source_id);
        $sqlStatement->execute();
        $villasTypes = $sqlStatement->fetchAll();
        
        return $villasTypes;
    }


}
