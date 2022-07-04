<?php
/**
 * CLASSE PERMETTANT DE SE CONNECTER A L'API cimalpes
 * FUNCTIONS PRRINCIPALES DE CETTE CLASSE :
 * 1- Extraire puis parser un flux de données XML
 * 2- Transformation des données en réspectant le schéma de la bd Mysql
 * 3- Insertion des données dans la db Mysql
 */

// namespace Api\Connect\CimalpesConnect;
 


class CimalpesConnect {

    
    public $flux_xml;
    public $doc_xml;
    public $biens;
    protected $login;
    protected $pass;
    
    const API_BIENS = "https://cimalpes.ski/fr/flux/?fonction=biens&login=xml@villanovo.com&pass=M4X876RV3N2D";
    const API_DETAIL = "https://cimalpes.ski/fr/flux/?fonction=detail&login=xml@villanovo.com&pass=M4X876RV3N2D&id_bien=%20ID_BIEN";
    const API_DISPO = "https://cimalpes.ski/fr/flux/?fonction=infos&login=rentals@cimalpes.com&pass=Cimalpes74120&id_bien=17";

    public function parseBiens($url){

    
        $this->flux_xml = new DOMDocument();
        $this->flux_xml->load($url);
        $biens = $this->flux_xml->getElementsByTagName("bien");
    
        return $biens;
    
    
     }


     public function parseDetails($id_bien) {

          $this->flux_xml = new DOMDocument();
          $this->flux_xml->load($url);

          $detailBiens = 
     }



     public function persistData() {

     }
   

}

$flux = new CimalpesConnect();
$biens = $flux->parseBiens('');


foreach($biens as $bien) {
    print_r($bien->attributes);
}


  

