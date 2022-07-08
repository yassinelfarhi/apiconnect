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

    public function __construct()
    {
        try {

            $this->db = new \PDO(self::DB_DSN, self::DB_USERNAME, self::DB_PASS);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->languges = $this->getLangugesCode();
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




        foreach ($biens as $bien) {

            $local_bien = $this->checkApiVillas($bien->id, $apiSource);


            if ($local_bien !== false) {
                $this->updateBien($bien, $local_bien["villa_id"]);
            } else {
                $this->insertBien($bien, $apiSource);
            }

              $this->insertDetail();

        }
    }

    /**
     * @param BienDto $bien
     * @param int $localId
     * @return void
     */
    public function updateBien($bien, $localId)
    {

        //update table villa
    
        $villaQuery = "UPDATE villas SET nom = :nom_villa WHERE id = :local_id";
        $sqlStatement = $this->db->prepare($villaQuery);
        $sqlStatement->bindParam(":nom_villa",$bien->nom);
        $sqlStatement->bindParam(":local_id",$localId);
        $sqlStatement->execute();

        
       //inserer description : appeler function insertDetail
       
         $this->insertDetail($bien,$localId);
       


    }

    /**
     * @param BienDto $bien
     * @return int
     */
    public function insertVilla($bien)
    {
        $villaQuery = "INSERT INTO villas (nom) values (:nom_villa)";
        $query = $this->db->prepare($villaQuery);
        $query->bindParam(":nom_villa", $bien->nom, PDO::PARAM_STR);
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
}
