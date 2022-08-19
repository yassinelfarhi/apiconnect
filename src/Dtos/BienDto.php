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
    public $calendars;
    public $photos;
    public $updatedAt;
    public $chambres;
    public $periods;
    /**
     * @var array
     * */
    public $equipments;

    public $descriptions;
}