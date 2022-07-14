<?php 

namespace Villanovo\Cimalpes\Dtos;

use Exception;

class BienDto{
    

    public $id;
    public $nom;
    public $villaSlug;
    public $villaType;

    /**
     * @var array
     */
    public $descriptionBerf;
      /**
     * @var array
     */
    public $descriptifCourt;
    

    static function fromNodeListing($node){
        $that = new self();
        $that->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
        $that->nom = $node->getElementsByTagName('nom_bien')->item(0)->nodeValue; 
        $that->type = $node->getElementsByTagName('type_bien')->item(0)->nodeValue;
        $that->address = $node->getElementsByTagName('nom_quartier')->item(0)->nodeValue; //
        $that->slug = BienDto::intoSlug($that->nom); 
        return $that;
    }

    static function fromNodeDetail($node){
        $that = new self();
      
        //TODO: check if multi langue

        try {
            $that->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
            $that->nom = $node->getElementsByTagName('bien')->item(0)->nodeValue; 
            $that->descriptionBerf['fr'] = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue; 
            $that->descriptifCourt['fr'] = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue; 
            $that->descriptionBerf['en'] = $node->getElementsByTagName('descriptif_bref_en')->item(0)->nodeValue; 
            $that->descriptifCourt['en'] = $node->getElementsByTagName('descriptif_court_en')->item(0)->nodeValue; 
            return $that;
        } catch (Exception $th) {
            throw $th->getMessage();
        }
     
    }

    public function intoSlug($text) {
       return str_replace(" ","-" ,trim(strtolower($text)));
    }
     

 
}