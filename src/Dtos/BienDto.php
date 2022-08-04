<?php 

namespace Villanovo\Cimalpes\Dtos;

use Exception;

class BienDto{
    

    public $id;
    public $nom;
    public $slug;
    public $type;
    public $occupancy;
    public $bedrooms;
    public $quartier;
    public $station;
    public $latitude;
    public $longitude;
    public $baths;


    /**
     * @var array
     */
    public $descriptionBref;
      /**
     * @var array
     */
    public $descriptionCourt;
    

    static function fromNodeListing($node){
        $bien = new self();
        $bien->id = $node->getElementsByTagName('id_bien')->item(0)->nodeValue; 
        $bien->nom = $node->getElementsByTagName('nom_bien')->item(0)->nodeValue; 
        $bien->station = $node->getElementsByTagName('nom_station')->item(0)->nodeValue;
        $bien->quartier = $node->getElementsByTagName('nom_quartier')->item(0)->nodeValue;
        return $bien;
    }

    static function fromNodeDetail($node, $bien){

       
        if ($node->getElementsByTagName('descriptif_bref')->item(0) !== null) {
            $bien->descriptionBref['fr'] = $node->getElementsByTagName('descriptif_bref')->item(0)->nodeValue;
        } else {
            $bien->descriptionBref['fr'] = "";
        }
        

        if ($node->getElementsByTagName('descriptif_court')->item(0) !== null) {
            $bien->descriptionCourt['fr'] = $node->getElementsByTagName('descriptif_court')->item(0)->nodeValue;
        } else {
            $bien->descriptionCourt['fr'] = "";
        }

        if ($node->getElementsByTagName('descriptif_bref_en')->item(0) !== null) {
            $bien->descriptionBref['en'] = $node->getElementsByTagName('descriptif_bref_en')->item(0)->nodeValue;
        } else {
            $bien->descriptionBref['en'] = "" ;
        }

        if ( $node->getElementsByTagName('descriptif_court_en')->item(0) !== null ) {
            $bien->descriptionCourt['en'] = $node->getElementsByTagName('descriptif_court_en')->item(0)->nodeValue;
        } else {
            $bien->descriptionCourt['en'] = "";
        }

        if ( $node->getElementsByTagName('type_bien')->item(0) !== null ) {
            $bien->type = $node->getElementsByTagName('type_bien')->item(0)->nodeValue;
        } else {
            $bien->type = "";
        }

        if ( $node->getElementsByTagName('nombre_chambres')->item(0) !== null ) {
            $bien->bedrooms = $node->getElementsByTagName('nombre_chambres')->item(0)->nodeValue;
        } else {
            $bien->bedrooms = "";
        }

        if ( $node->getElementsByTagName('latitude')->item(0) !== null ) {
            $bien->latitude = $node->getElementsByTagName('latitude')->item(0)->nodeValue;
        } else {
            $bien->latitude = "";
        }

        if ( $node->getElementsByTagName('longitude')->item(0) !== null ) {
            $bien->longitude = $node->getElementsByTagName('longitude')->item(0)->nodeValue;
        } else {
            $bien->longitude = "";
        }
            

            return $bien;
    
        
       
      
    }


     

 
}