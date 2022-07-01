<?php


class ApiConnect {

    
    public $flux_xml;
    public $doc_xml;
    private $login;
    private $pass;
    

    public function parse_xml($url){

    
        $this->flux_xml = new DOMDocument();
        $this->doc_xml = $this->flux_xml->load($url);
        
    
        $biens = $xml_doc->getElementsByTagName("bien")->item(0);
    
        return print_r($biens);
    
    
     }


     public function persistData() {

     }
   

}


  

