<?php


namespace Villanovo\Cimalpes;

use Exception;
use PDO;

class CimalpesPersist {

    
    private $db;

    const DB_DSN = "mysql:host=localhost;port=3306;dbname=villanovo";
    const DB_USERNAME = "root";
    const DB_PASS = "";

     public function __construct(){
        try{
        
            $this->db = new \PDO(self::DB_DSN,self::DB_USERNAME,self::DB_PASS);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
           

        }catch(Exception $e){

           $e->getMessage();

        }
    }

    public function insererBiens($biens){

           $sql_query = "INSERT INTO villas (id,nom) values (?,?)";
           $query = $this->db->prepare($sql_query);
           
           foreach($biens as $bien){
            $query->bindParam(1,$bien["id"]);
            $query->bindParam(2,$bien["nom"]);
            $query->execute();
           }

    }
    
    
    public function insererDetail($detail){
              
              $sql_query = "INSERT INTO description (id_villa, description_bref,description_court, id_langue) values (:id_bien,:descriptif_court,:descriptif_bref,:id_langue)";
              $query = $this->db->prepare($sql_query);
              $fr = 1;
              $en = 2;

             //insertion de la description en langue francaise

             $query->bindParam(":id_bien",$detail['id_bien'],PDO::PARAM_INT);
             $query->bindParam(":descriptif_court",$detail['descriptif_court'], PDO::PARAM_STR);
             $query->bindParam(":descriptif_bref",$detail['descriptif_bref'],PDO::PARAM_STR);
             $query->bindValue(":id_langue",$fr,PDO::PARAM_INT);
             $query->execute();

             //insertion de la description en langue anglaise

              $query->bindParam(":id_bien",$detail['id_bien'],PDO::PARAM_INT);
              $query->bindParam(":descriptif_court",$detail['descriptif_court_en'],PDO::PARAM_STR);
              $query->bindParam(":descriptif_bref",$detail['descriptif_bref_en'],PDO::PARAM_STR);
              $query->bindValue(":id_langue",$en,PDO::PARAM_INT);
            //   var_dump($query);
              $query->execute();
             
    }
        
 


}
  



