<?php 

namespace Villanovo\Cimalpes\Dtos;

class BienDto{
    

    public $id;
    public $nom;
    

    static function fromNodeListing($node){
        $that = new self();
        $that->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
        $that->nom = $node->getElementsByTagName('nom_bien')->item(0)->nodeValue; 
        return $that;
    }

    // static function fromNodeDetail($node){
    //     $that = new self();
        
    //     $that->descriptif_bref = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue; 
    //     $that->descriptif_court = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue; 
    //     return $that;
    // }

    // static function fromNodeDisponibilities($node){
    //     $that = new self();
        
    //     $that->descriptif_bref = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue; 
    //     $that->descriptif_court = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue; 
    //     return $that;
    // }
}