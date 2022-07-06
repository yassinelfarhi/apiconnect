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
    // function permettant d'inserer les villas dans la table villas
    /**
     * @param BienDto[] $biens
     * @param int $apiSource
     * @retrun void
     */
    public function insererORupdate($biens, $apiSource = 1)
    {




        foreach ($biens as $bien) {

            $local_bien = $this->checkApiVillas($bien->id, $apiSource);


            if ($local_bien !== false) {

                $this->updateBien($bien, $local_bien["villa_id"]);
            } else {
                $this->insererBien($bien, $apiSource);
            }
        }
    }

    /**
     * @param BienDto $bien
     * @param int $localId
     * @return void
     */
    public function updateBien($bien, $localId)
    {
        $descriptionQuery = "REPLACE INTO `description` (id_villa, description_bref, description_court, id_langue) values (:id_villa,:description_bref,:description_court,:id_langue)";
        
        foreach( $this->languges as $language):
            if(!array_key_exists($language['lng_code'],$bien->descriptionBerf) && !array_key_exists($language['lng_code'],$bien->descriptifCourt))
                continue;
            print_r( $language);
            $sqlStatement = $this->db->prepare($descriptionQuery);
            $descriptif_bref = array_key_exists($language['lng_code'],$bien->descriptionBerf)?$bien->descriptionBerf[$language['lng_code']]:'';
            $descriptif_court = array_key_exists($language['lng_code'],$bien->descriptifCourt)?$bien->descriptifCourt[$language['lng_code']]:'';
            $sqlStatement->bindParam(":id_villa", $localId, PDO::PARAM_INT);
            $sqlStatement->bindParam(":description_bref",$descriptif_bref, PDO::PARAM_STR);
            $sqlStatement->bindParam(":description_court",  $descriptif_court, PDO::PARAM_STR);
            $sqlStatement->bindParam(":id_langue", $language["lng_id"], PDO::PARAM_INT);
            $sqlStatement->execute();
        endforeach;
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
        $apiQuery = "INSERT INTO api_villas (villa_id,api_villa_id,api_source_id) values (:villa_id,:api_villa,:api_source_id)";
        $query = $this->db->prepare($apiQuery);
        $query->bindParam(":villa_id", $localId, PDO::PARAM_INT);
        $query->bindParam(":api_villa", $bien->id, PDO::PARAM_STR);
        $query->bindParam(":api_source_id", $apiSource, PDO::PARAM_INT);
        $query->execute();
    }

    public function insererBien($bien, $apiSource = 1)
    {
        try {
            $this->db->beginTransaction();
            $localId = $this->insertVilla($bien);
            $this->insertMatching($bien, $localId, $apiSource);
            $this->updateBien($bien, $localId);
            $this->db->commit();
        } catch (PDOException $ex) {
            echo "Stoped with exception ".$ex->getMessage()."\n";
            echo $ex->getTraceAsString()."\n";
            $this->db->rollBack();
        }
    }


/*
    public function insererDetail($detail)
    {

        $sql_query = "REPLACE INTO description (id_villa, description_bref,description_court, id_langue) values (:id_bien,:descriptif_court,:descriptif_bref,:id_langue)";
        $query = $this->db->prepare($sql_query);




        //insertion de la description en langue francaise

        $query->bindParam(":id_bien", $detail['id_bien'], PDO::PARAM_INT);
        $query->bindParam(":descriptif_court", $detail['descriptif_court'], PDO::PARAM_STR);
        $query->bindParam(":descriptif_bref", $detail['descriptif_bref'], PDO::PARAM_STR);
        $query->bindValue(":id_langue", self::REF_LANGUE_FR, PDO::PARAM_INT);
        $query->execute();

        //insertion de la description en langue anglaise

        $query->bindParam(":id_bien", $detail['id_bien'], PDO::PARAM_INT);
        $query->bindParam(":descriptif_court", $detail['descriptif_court_en'], PDO::PARAM_STR);
        $query->bindParam(":descriptif_bref", $detail['descriptif_bref_en'], PDO::PARAM_STR);
        $query->bindValue(":id_langue", self::REF_LANGUE_EN, PDO::PARAM_INT);

        $query->execute();
    }
*/
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
