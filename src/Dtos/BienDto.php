<?php 

namespace Villanovo\Cimalpes\Dtos;

use Exception;

class BienDto{
    

    public $id;
    public $nom;
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

    // static function fromNodeDisponibilities($node){
    //     $that = new self();
        
    //     $that->descriptif_bref = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue; 
    //     $that->descriptif_court = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue; 
    //     return $that;
    // }
}