<?php 

namespace Villanovo\ThirdParties\Dtos;

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
    public $options;
    public $sejours;
    public $photos;
    public $updatedAt;
    /**
     * @var array
     * */
    public $equipments;

    /**
     * @var array
     */
    public $descriptionBref;
      /**
     * @var array
     */
    public $descriptionCourt;
}