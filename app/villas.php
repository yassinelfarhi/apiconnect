<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 12/01/2019
 * Time: 21:33
 */

class villas extends db
{

    public $lng_id;
    public $boot_currency_id;
    public $boot_domain_id;

    public $villa_id;
    public $manager_id;
    public $created_at;
    public $published_at;
    public $villa_state_id;
    public $villa_type_id;
    public $villa_stats_nbcontrats;

    public $is_hidden;
    public $domain_zone_id;
    public $domain_zone_level;

    public $villa_currency_id;
    public $villa_currency_rate;
    public $villa_currency_code;
    public $villa_currency_name;

    public $currency_id_to;
    public $currency_rate_to;
    public $currency_code_to;
    public $currency_name_to;

    public $villa_public_name;
    public $villa_private_name;
    public $villa_license_number;
    public $zone_id;
    public $villa_slug;
    public $villa_occupancy;
    public $villa_occupancy_max;
    public $used_occupancy_max;
    public $villa_baths;
    public $villa_bedrooms;
    public $villa_bedrooms_max;
    public $villa_bedrooms_max_from;
    public $used_room;
    public $used_room_from;
    public $villa_display_prices;
    public $villa_min_price;
    public $villa_tva;
    public $villa_commission_tva;
    public $villa_latitude;
    public $villa_longitude;
    public $villa_is_instant;
    public $villa_is_hd;

    public $villa_title;
    public $villa_resume;
    public $villa_description;
    public $villa_description_updated;
    public $villa_description_api_old;
    public $villa_description_api_new;
    public $villa_description_api_new_updated;
    public $description_lng_id;
    public $villa_comment;
    public $villa_state_name;
    public $villa_type_name;

    public $villa_vod_video;
    public $villa_vod_image;

    public $villa_photo;
    public $villa_photo_ext;

    public $villa_address;
    public $villa_district_id;
    public $villa_district;
    public $villa_district_slug;
    public $villa_city_id;
    public $villa_city;
    public $villa_city_slug;
    public $villa_country_id;
    public $villa_country;
    public $villa_country_slug;
    public $villa_continent_id;
    public $villa_continent;
    public $villa_continent_slug;

    public $villa_vcity_id;
    public $villa_vcity;
    public $villa_vcountry_id;
    public $villa_vcountry;

    public $reviews_number;
    public $reviews_recommended_count;
    public $reviews_recommended_percent;
    public $reviews_impression_avr;
    public $reviews_location_avr;
    public $reviews_parts_avr;
    public $reviews_welcome_avr;
    public $reviews_rooms_avr;
    public $reviews_service_avr;
    public $reviews_food_avr;
    public $reviews_total_avr;

    /**
     * villas constructor.
     */
    public function __construct($villa=0,$type='min',$lng_id=0)
    {

        parent::__construct();

        global $boot_currency_id;
        global $boot_domain_id;
        global $boot_lng_id;

        $this->boot_currency_id = $boot_currency_id > 0 ? $boot_currency_id : 1;
        $this->boot_domain_id = $boot_domain_id > 0 ? $boot_domain_id : 0;

        $this->lng_id = $lng_id === 0 ? $boot_lng_id : $lng_id;

        if( $villa !== 0 ){

            if( $this->boot_domain_id > 1 )
                $sql = ' and vd.domain_id in ( select domain_id from vn_domains where zone_id = z1.zone_id or zone_id = z2.zone_id or zone_id = z3.zone_id )';
                //$sql = ' and ( z1.zone_id = d.domain_id or z2.zone_id = d.domain_id or z3.zone_id = d.domain_id )';

            if( $type == 'min' ){

                $query = 'select
					
                          v.villa_id, v.manager_id,
                          v.villa_public_name, v.villa_private_name, v.villa_slug, 
                          v.villa_occupancy, v.villa_occupancy_max, 
                          v.villa_state_id, v.villa_is_hd, v.villa_bedrooms, v.villa_address,
                          v.villa_bedrooms_max, v.villa_bedrooms_max_from, v.villa_is_instant,
                          v.villa_vod_video, v.villa_vod_image,
                          v.villa_commission_tva,
                          vd.is_hidden, d.zone_id as domain_zone_id, zd.zone_level as domain_zone_level,
                          photos.photo_name, photos.photo_ext, 
                          
                          ct.country_id as vcountry_id, ct.country_name as vcountry,
                          ci.city_id as vcity_id, ci.city_name as vcity,
                          
                          z1t.zone_id as district_id, z1t.zone_slug as district_slug, z1t.zone_name as district,
                          z2t.zone_id as city_id, z2t.zone_slug as city_slug, z2t.zone_name as city,
                          z3t.zone_id as country_id, z3t.zone_slug as country_slug, z3t.zone_name as country,
                          z4t.zone_id as continent_id, z4t.zone_slug as continent_slug
                          
                          from vn_villas as v
                          
                          join vn_villas_slugs as vs
                          on v.villa_id = vs.villa_id
                          
                          join vn_zones as z1
                          on v.zone_id = z1.zone_id
                          join vn_zones_trad as z1t
                          on z1.zone_id = z1t.zone_id and z1t.langue_id = '.$this->lng_id.'
                          join vn_zones as z2
                          on z1.zone_parent_id = z2.zone_id
                          join vn_zones_trad as z2t
                          on z2.zone_id = z2t.zone_id and z2t.langue_id = '.$this->lng_id.'
                          join vn_zones as z3
                          on z2.zone_parent_id = z3.zone_id 
                          join vn_zones_trad as z3t
                          on z3.zone_id = z3t.zone_id and z3t.langue_id = '.$this->lng_id.'
                          join vn_zones as z4
                          on z3.zone_parent_id = z4.zone_id 
                          join vn_zones_trad as z4t
                          on z4.zone_id = z4t.zone_id and z4t.langue_id = '.$this->lng_id.'
                          
                          left join vn_villas_domains as vd
                          on v.villa_id = vd.villa_id and vd.domain_id = '.$this->boot_domain_id.$sql.'
                          left join vn_domains as d
                          on vd.domain_id = d.domain_id
                          left join vn_zones as zd
                          on d.zone_id = zd.zone_id

                          join vn_villas_photos as photos
                          on v.villa_id = photos.villa_id and photos.photo_main = 1

                          join vn_countries_trad as ct
                          on v.country_id = ct.country_id and ct.langue_id = '.$this->lng_id.'
                          left join vn_cities as ci
                          on v.city_id = ci.city_id
                          
                          where v.villa_id = "'.$villa.'" or vs.villa_slug like "'.$villa.'"
                          order by vs.villa_slug_time desc';

                //eko($query);

                $this->exec($query);

                if( $row = $this->getAssoc() ){

                    $this->villa_id = $row['villa_id'];
                    $this->manager_id = $row['manager_id'];
                    $this->villa_public_name = $row['villa_public_name'];
                    $this->villa_private_name = $row['villa_private_name'];
                    $this->villa_slug = $row['villa_slug'];
                    $this->villa_state_id = $row['villa_state_id'];

                    $this->is_hidden = $row['is_hidden'];
                    $this->domain_zone_id = $row['domain_zone_id'];
                    $this->domain_zone_level = $row['domain_zone_level'];


                    $this->villa_occupancy = $row['villa_occupancy'];
                    $this->villa_occupancy_max = $row['villa_occupancy_max'];
                    $this->villa_bedrooms = $row['villa_bedrooms'];
                    $this->villa_bedrooms_max = $row['villa_bedrooms_max'];
                    $this->villa_bedrooms_max_from = $row['villa_bedrooms_max_from'];
                    $this->villa_photo = htmlentities($row['photo_name']);
                    $this->villa_photo_ext = $row['photo_ext'];
                    $this->villa_is_hd = $row['villa_is_hd'];
                    $this->villa_is_instant = $row['villa_is_instant'];
                    $this->villa_vod_video = $row['villa_vod_video'];
                    $this->villa_vod_image = $row['villa_vod_image'];
                    $this->villa_commission_tva = $row['villa_commission_tva'];

                    $this->villa_vcountry_id = $row['vcountry_id'];
                    $this->villa_vcountry = $row['vcountry'];
                    $this->villa_vcity_id = $row['vcity_id'];
                    $this->villa_vcity = $row['vcity'];

                    $this->villa_address = $row['villa_address'];
                    $this->villa_district_id = $row['district_id'];
                    $this->villa_district = $row['district'];
                    $this->villa_district_slug = $row['district_slug'];
                    $this->villa_city_id = $row['city_id'];
                    $this->villa_city = $row['city'];
                    $this->villa_city_slug = $row['city_slug'];
                    $this->villa_country_id = $row['country_id'];
                    $this->villa_country = $row['country'];
                    $this->villa_country_slug = $row['country_slug'];
                    $this->villa_continent_id = $row['continent_id'];
                    $this->villa_continent_slug = $row['continent_slug'];

                }

            }
            else{

                $query = 'select
					
                          v.villa_id, v.manager_id, v.villa_time, v.published_at, v.villa_state_id, 
                          v.villa_public_name, v.villa_private_name, v.villa_slug, v.villa_license_number,
                          v.villa_is_hd, v.villa_is_instant, v.villa_occupancy, v.villa_occupancy_max, 
                          v.villa_bedrooms, v.villa_bedrooms_max, v.villa_bedrooms_max_from, v.villa_address,
                          v.villa_baths, v.villa_stats_nbcontrats,
                          v.villa_display_prices, v.villa_min_price, v.villa_tva, v.villa_commission_tva,
                          v.villa_vod_video, v.villa_vod_image,
                          v.villa_latitude, v.villa_longitude, v.villa_commission,
                          vd.is_hidden, zd.zone_id as domain_zone_id, zd.zone_level as domain_zone_level,
                          vn_villas_states_trad.villa_state_name,
                          vn_villas_types_trad.villa_type_name,
                          photos.photo_name, photos.photo_ext,
                          
                          vcur.currency_id, vcur.currency_taux, vcur.currency_code, vcurt.currency_name,
                          vcurto.currency_id as currency_id_to, vcurto.currency_taux as currency_rate_to,
                          vcurto.currency_code as currency_code_to, vcurtot.currency_name as currency_name_to,
                          
                          ct.country_id as vcountry_id, ct.country_name as vcountry,
                          ci.city_id as vcity_id, ci.city_name as vcity,
                          
                          z1_trad.zone_id as quartier_id, z1_trad.zone_slug as quartier_slug, z1_trad.zone_name as quartier,
                          z2_trad.zone_id as ville_id, z2_trad.zone_slug as ville_slug, z2_trad.zone_name as ville,
                          z3_trad.zone_id as pays_id, z3_trad.zone_slug as pays_slug, z3_trad.zone_name as pays,
                          z4_trad.zone_id as continent_id, z4_trad.zone_slug as continent_slug
                          
                          from vn_villas as v
                          join vn_villas_slugs as vs
                          on v.villa_id = vs.villa_id
                          
                          join vn_zones as z1
                          on v.zone_id = z1.zone_id
                          join vn_zones_trad as z1_trad
                          on z1.zone_id = z1_trad.zone_id and z1_trad.langue_id = '.$this->lng_id.'
                          join vn_zones as z2
                          on z1.zone_parent_id = z2.zone_id
                          join vn_zones_trad as z2_trad
                          on z2.zone_id = z2_trad.zone_id and z2_trad.langue_id = '.$this->lng_id.'
                          join vn_zones as z3
                          on z2.zone_parent_id = z3.zone_id 
                          join vn_zones_trad as z3_trad
                          on z3.zone_id = z3_trad.zone_id and z3_trad.langue_id = '.$this->lng_id.'
                          join vn_zones as z4
                          on z3.zone_parent_id = z4.zone_id 
                          join vn_zones_trad as z4_trad
                          on z4.zone_id = z4_trad.zone_id and z4_trad.langue_id = '.$this->lng_id.'
                          
                          left join vn_villas_domains as vd
                          on v.villa_id = vd.villa_id and vd.domain_id = '.$this->boot_domain_id.$sql.'
                          left join vn_domains as d
                          on vd.domain_id = d.domain_id
                          left join vn_zones as zd
                          on d.zone_id = zd.zone_id
                          
                          join vn_villas_states_trad
                          on v.villa_state_id = vn_villas_states_trad.villa_state_id and vn_villas_states_trad.langue_id = 1
                          join vn_villas_types_trad
                          on v.villa_type_id = vn_villas_types_trad.villa_type_id and vn_villas_types_trad.langue_id = '.$this->lng_id.'
                          join vn_villas_photos as photos
                          on v.villa_id = photos.villa_id and photos.photo_main = 1
                          join vn_currencies as vcur
                          on v.currency_id = vcur.currency_id
                          join vn_currencies_trad as vcurt
                          on vcur.currency_id = vcurt.currency_id and vcurt.langue_id = '.$this->lng_id.'
                          join vn_currencies as vcurto
                          on vcurto.currency_id = '.$this->boot_currency_id.'
                          join vn_currencies_trad as vcurtot
                          on vcurto.currency_id = vcurtot.currency_id and vcurtot.langue_id = '.$this->lng_id.'
                          
                          join vn_countries_trad as ct
                          on v.country_id = ct.country_id and ct.langue_id = '.$this->lng_id.'
                          left join vn_cities as ci
                          on v.city_id = ci.city_id
                          
                          where v.villa_id = "'.$villa.'" or vs.villa_slug like "'.$villa.'"
                          order by vs.villa_slug_time desc';

                //echo $query;
                //exit();

                //eko($query);

                $this->exec($query);

                if( $row = $this->getAssoc() ){

                    $this->villa_id = $row['villa_id'];
                    $this->manager_id = $row['manager_id'];
                    $this->created_at = $row['villa_time'];
                    $this->published_at = $row['published_at'];
                    $this->villa_state_id = $row['villa_state_id'];
                    $this->villa_type_id = $row['villa_type_id'];
                    $this->villa_stats_nbcontrats = $row['villa_stats_nbcontrats'] > 0 ? $row['villa_stats_nbcontrats'] : 0;

                    $this->is_hidden = $row['is_hidden'];
                    $this->domain_zone_id = $row['domain_zone_id'];
                    $this->domain_zone_level = $row['domain_zone_level'];

                    $this->villa_currency_id = $row['currency_id'];
                    $this->villa_currency_rate = $row['currency_taux'];
                    $this->villa_currency_code = $row['currency_code'];
                    $this->villa_currency_name = $row['currency_name'];

                    $this->currency_id_to = $row['currency_id_to'];
                    $this->currency_rate_to = $row['currency_rate_to'];
                    $this->currency_code_to = $row['currency_code_to'];
                    $this->currency_name_to = $row['currency_name_to'];

                    $this->villa_public_name = $row['villa_public_name'];
                    $this->villa_private_name = $row['villa_private_name'];
                    $this->villa_slug = $row['villa_slug'];
                    $this->villa_license_number = $row['villa_license_number'];
                    $this->villa_occupancy = $row['villa_occupancy'];
                    $this->villa_occupancy_max = $row['villa_occupancy_max'];
                    $this->villa_baths = $row['villa_baths'];
                    $this->villa_bedrooms = $row['villa_bedrooms'];
                    $this->villa_bedrooms_max = $row['villa_bedrooms_max'];
                    $this->villa_bedrooms_max_from = $row['villa_bedrooms_max_from'];
                    $this->villa_display_prices = $row['villa_display_prices'];
                    $this->villa_min_price = $row['villa_min_price'];
                    $this->villa_tva = $row['villa_tva'];
                    $this->villa_commission_tva = $row['villa_commission_tva'];
                    $this->villa_photo = htmlentities($row['photo_name']);
                    $this->villa_photo_ext = $row['photo_ext'];
                    $this->villa_latitude = $row['villa_latitude'];
                    $this->villa_longitude = $row['villa_longitude'];
                    $this->villa_is_instant = $row['villa_is_instant'];
                    $this->villa_is_hd = $row['villa_is_hd'];

                    $this->villa_state_name = $row['villa_state_name'];
                    $this->villa_type_name = $row['villa_type_name'];

                    $this->villa_vod_video = $row['villa_vod_video'];
                    $this->villa_vod_image = $row['villa_vod_image'];

                    $this->villa_vcountry_id = $row['vcountry_id'];
                    $this->villa_vcountry = $row['vcountry'];
                    $this->villa_vcity_id = $row['vcity_id'];
                    $this->villa_vcity = $row['vcity'];

                    $this->villa_address = $row['villa_address'];
                    $this->villa_district_id = $row['quartier_id'];
                    $this->villa_district = $row['quartier'];
                    $this->villa_district_slug = $row['quartier_slug'];
                    $this->villa_city_id = $row['ville_id'];
                    $this->villa_city = $row['ville'];
                    $this->villa_city_slug = $row['ville_slug'];
                    $this->villa_country_id = $row['pays_id'];
                    $this->villa_country = $row['pays'];
                    $this->villa_country_slug = $row['pays_slug'];
                    $this->villa_continent_id = $row['continent_id'];
                    $this->villa_continent_slug = $row['continent_slug'];

                }

            }

        }

    }

    public function initPrice($room=1){

        $query = 'select
                  v.villa_occupancy, v.villa_occupancy_max, 
                  v.villa_display_prices, v.villa_min_price, v.villa_tva, vr.villa_room, vr.villa_room_from,
                  vcur.currency_id, vcur.currency_taux, vcur.currency_code,
                  vcurto.currency_taux as currency_rate_to, vcurto.currency_code as currency_code_to
                  from vn_villas as v
                  left join vn_villas_rooms as vr
                  on v.villa_id = vr.villa_id and vr.villa_room >= '.$room.'
                  join vn_currencies as vcur
                  on v.currency_id = vcur.currency_id
                  join vn_currencies as vcurto
                  on vcurto.currency_id = '.$this->boot_currency_id.'
                  where v.villa_id = '.$this->villa_id.'
                  order by vr.villa_room
                  limit 1';

        $this->exec($query);

        if( $row = $this->getAssoc($query) ){

            if( $row['villa_room'] > 0 ){
                $this->used_room = $row['villa_room'];
                $this->used_room_from = $row['villa_room_from'];
            }
            else{
                $this->used_room = $this->villa_bedrooms_max;
                $this->used_room_from = $this->villa_bedrooms_max_from;
            }

            $this->villa_occupancy = $row['villa_occupancy'];
            $this->villa_occupancy_max = $row['villa_occupancy_max'];
            $this->used_occupancy_max = $this->used_room == $this->villa_bedrooms_max ? $this->villa_occupancy_max : $this->used_room*2;

            $this->villa_display_prices = $row['villa_display_prices'];
            $this->villa_min_price = $row['villa_min_price'];
            $this->villa_tva = $row['villa_tva'];

            $this->villa_currency_id = $row['currency_id'];
            $this->villa_currency_rate = $row['currency_taux'];
            $this->villa_currency_code = $row['currency_code'];
            $this->currency_rate_to = $row['currency_rate_to'];
            $this->currency_code_to = $row['currency_code_to'];

            return true;

        }

    }

    public static function checkVilla($villa,$domain_id,$notHidden=1){

        if( $notHidden ) $cdt_hidden = ' and vd.is_hidden = 0';

        $query = 'select v.villa_id
                          
                  from vn_villas as v
                  join vn_villas_slugs as vs
                  on v.villa_id = vs.villa_id
                  join vn_villas_domains as vd
                  on v.villa_id = vd.villa_id and vd.domain_id = '.$domain_id.$cdt_hidden.'
                          
                  where ( v.villa_id = "'.$villa.'" or vs.villa_slug like "'.$villa.'" ) and v.villa_state_id = 1
                  order by vs.villa_slug_time desc';

        $res = new db();
        $res->exec($query);

        if( $row = $res->getAssoc() ){
            return $row['villa_id'];
        }


    }

    public function getSearch($villa){

        $ids = [];

        if( $this->boot_domain_id > 1 )
            $cdt_villa = ' and ( z2.zone_id = d.zone_id or z3.zone_id = d.zone_id )';

        $query = 'select v.villa_id
                  from vn_villas as v
                  join vn_zones as z1
                  on v.zone_id = z1.zone_id
                  join vn_zones as z2
                  on z1.zone_parent_id = z2.zone_id
                  join vn_zones as z3
                  on z2.zone_parent_id = z3.zone_id
                  join vn_villas_domains as vd
                  on v.villa_id = vd.villa_id and vd.domain_id = '.$this->boot_domain_id.' and vd.is_hidden = 0
                  join vn_domains as d
                  on vd.domain_id = d.domain_id'.$cdt_villa.'
                  where v.villa_public_name like "%'.$villa.'%" and v.villa_state_id = 1
                  order by v.villa_public_name
                  limit 10';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $ids[] = $rows['villa_id'];
        }

        return $ids;

    }

    public function isActive(){
        return $this->villa_state_id == 1 ? true : false;
    }

    public function isInDomain(){
        return !is_null($this->is_hidden) and $this->isActive();
    }

    public function isPublished(){
        return $this->isInDomain() && $this->is_hidden == 0;
    }

    public function isHidden(){
        return $this->isInDomain() && $this->is_hidden == 1;
    }

    public function getVillaUrl($level=0){

        if( $level === 0 or is_null($level) ) $level = $this->domain_zone_level;

        $parts = [];
        $parts[] = $this->villa_continent_slug;
        $parts[] = $this->villa_country_slug;
        $parts[] = $this->villa_city_slug;
        $parts[] = $this->villa_district_slug;
        $parts[] = $this->villa_slug;

        if( !is_null($level) and $level > 0 )
            $parts = array_slice($parts,$level+1);

        return '/'.implode('/',$parts);
    }

    public function getMainPhotoUrl($size=0){
        if( $size === 0)
            $format = $this->villa_is_hd ? '.1920.' : '.';
        else
            $format = '.'.$size.'.';
        return  '/photos/'.$this->villa_id.'/'.$this->villa_photo.$format.$this->villa_photo_ext;
    }

    public function isNew(){
        return ( (time()-$this->published_at) / (60*60*24)) < 15 ? true : false;
    }

    public function getOptions($state=0){

        if( $state == 1 )
            $cdt_state = ' and villa_option_inclus = 1';
        else if( $state == 2 )
            $cdt_state = ' and villa_option_inclus = 0';
        else if( $state == 3 )
            $cdt_state = ' and villa_option_inclus = 2';

        $getext = getEntries('127,129',$this->lng_id);

        $query = 'select voi.*, vot.villa_option_name, 
                  c.currency_id, c.currency_code, c.currency_taux, ut.villa_option_unit_name
                  from vn_villas_options_ids as voi
                  join vn_villas_options_trad as vot
                  on voi.villa_option_id = vot.villa_option_id and vot.langue_id = '.$this->lng_id.'
                  join vn_currencies as c
                  on voi.villa_option_currency_id = c.currency_id
                  left join vn_villas_options_units_trad as ut
                  on voi.villa_option_unit_id = ut.villa_option_unit_id and ut.langue_id = '.$this->lng_id.'
                  where voi.villa_id = '.$this->villa_id.$cdt_state.'
                  order by voi.villa_option_inclus desc, vot.villa_option_name';

        $this->exec($query);

        $options = [];

        while( $rows = $this->getAssoc() ){

            $option = $rows['villa_option_name'];

            if( in_array($rows['villa_option_inclus'],[0,2]) ){

                if( $rows['villa_option_price'] > 0 ){

                    $option .= ' : ';

                    if( $rows['villa_option_from'] == 1 )
                        $option .= $getext[127].' ';

                    $option .= priceFormat(convertPrice($rows['villa_option_price'],$rows['currency_taux'],$this->currency_rate_to)).' ';
                    $option .= $this->currency_code_to;
                    if( $rows['villa_option_unit_name'] != '')
                        $option .= ' '.$getext[129].' '.$rows['villa_option_unit_name'];

                }
            }

            $options[] = $option;

        }

        return $options;

    }

    public function getMandatoriesOptions($checkin,$checkout,$travelers){

        if( !is_timestamp($checkin) ) $checkin = toTime($checkin);
        if( !is_timestamp($checkout) ) $checkout = toTime($checkout);

        $nbdays = daysBetween($checkin,$checkout);

        $query = 'select voi.*, vot.villa_option_name
                  from vn_villas_options_ids as voi
                  join vn_villas_options_trad as vot
                  on voi.villa_option_id = vot.villa_option_id and vot.langue_id = '.$this->lng_id.' 
                  where villa_option_inclus = 2 and villa_id = '.$this->villa_id;

        $this->exec($query);

        $options = [];

        while( $rows = $this->getAssoc() ){

            switch ($rows['villa_option_unit_id']){

                case 10:
                    $price = $rows['villa_option_price']*$nbdays;
                    break;

                case 5:
                    $price = $rows['villa_option_price']*$travelers;
                    break;

                case 13:
                    $price = $rows['villa_option_price'];
                    break;

                default:
                    $price = 0;
                    break;
            }

            if( $price > 0 ){
                $option = [];
                $option['id'] = $rows['villa_option_id'];
                $option['name'] = $rows['villa_option_name'];
                $option['unit_price'] = $this->convertPrice($rows['villa_option_price']);
                $option['total_price'] = $this->convertPrice($price);
                $options[] = $option;
            }

        }

        return $options;

    }

    public function getEquipments(){

        $query = 'select 
							
                    vn_villas_equipments_cats_trad.villa_equipment_cat_name, 
                    vn_villas_equipments_trad.villa_equipment_name 
                    
                    from 
                    
                    vn_villas_equipments_cats_trad
                    join vn_villas_equipments
                    on vn_villas_equipments_cats_trad.villa_equipment_cat_id = vn_villas_equipments.villa_equipment_cat_id
                    and vn_villas_equipments_cats_trad.langue_id = '.$this->lng_id.'
                    join vn_villas_equipments_trad
                    on vn_villas_equipments.villa_equipment_id = vn_villas_equipments_trad.villa_equipment_id
                    and vn_villas_equipments_trad.langue_id = '.$this->lng_id.'
                    join vn_villas_equipments_ids
                    on vn_villas_equipments.villa_equipment_id = vn_villas_equipments_ids.villa_equipment_id
                    
                    where vn_villas_equipments_ids.villa_id = '.$this->villa_id.'
                    order by vn_villas_equipments_cats_trad.villa_equipment_cat_name, vn_villas_equipments_trad.villa_equipment_name';

        $this->exec($query);

        $equips = [];

        while( $rows = $this->getAssoc() ){
            $equips[$rows['villa_equipment_cat_name']][] = $rows['villa_equipment_name'];
        }

        return $equips;

    }

    public function getPriceSchedule($all=false){

        $schedule = [];

        // ROOMS
        $rooms = $this->getPriceRooms();
        $nights = $this->getPriceNights();
        $periods = $this->getPricePeriods($all);

        foreach( $rooms as $room ){

            foreach( $periods as $key=>$period ){

                $schedule[$room][$key]['perioddeb'] = date('d/m/Y',$period['perioddeb']);
                $schedule[$room][$key]['periodfin'] = date('d/m/Y',$period['periodfin']);
                $schedule[$room][$key]['minstay'] = $period['minstay'];

                foreach( $nights as $night ){

                    $query_price = 'select price_value from vn_villas_prices 
									where villa_id = '.$this->villa_id.' and price_nbch = '.$room.' and price_nbnuit = '.$night.' 
									and ( price_time between '.$period['perioddeb'].' and '.$period['periodfin'].' ) and price_value <> 0';

                    $res_price = new db();
                    $res_price->exec($query_price);

                    if( $row_price = $res_price->getAssoc() ) $price_value = $row_price['price_value'];
                    else $price_value = '-';

                    $schedule[$room][$key]['price'][] = priceFormat($this->calculatePrice($price_value),0);

                }

            }

        }

        return $schedule;

    }

    public function getPricesSchedule($all=false){

        $schedule = [];

        $rooms = $this->getPriceRooms();
        $nights = $this->getPriceNights();
        $periods = $this->getPricePeriods($all);

        if( $this->villa_id == 3328 ){
            //var_dump($rooms,$nights,$periods);
        }

        foreach( $rooms as $room ){

            foreach( $nights as $night ){

                foreach( $periods as $period ){

                    $row = [];

                    $row['perioddeb'] = date('d/m/Y',$period['perioddeb']);
                    $row['periodfin'] = date('d/m/Y',$period['periodfin']);
                    $row['minstay'] = $period['minstay'];
                    $row['switchoverday'] = $period['switchoverday'];

                    $query_price = 'select price_value from vn_villas_prices 
                                    where villa_id = '.$this->villa_id.' and price_nbch = '.$room.' and price_nbnuit = '.$night.' 
                                    and ( price_time between '.$period['perioddeb'].' and '.$period['periodfin'].' ) and price_value <> 0';

                    $query_price = 'select rps.season_price as price_value
                                    from vn_rates_plans as rp
                                    join vn_rates_plans_seasons as rps
                                    on rp.rate_plan_id = rps.rate_plan_id
                                    where rp.villa_id = '.$this->villa_id.' and rp.room = '.$room.' and rp.night = '.$night.' 
                                    and rps.season_id = '.$period['season_id'].' and rps.season_price > 0';

                    //echo($query_price);

                    $res_price = new db();
                    $res_price->exec($query_price);

                    if( $row_price = $res_price->getAssoc() ) $price_value = $row_price['price_value'];
                    else $price_value = '-';

                    $row['price'] = priceFormat($this->calculatePrice($price_value),0);

                    $schedule[$room][$night][] = $row;

                }

            }

        }

        return $schedule;

    }

    public function getPriceNights(){
        $nights = [];
        $query = 'select distinct(night) as price_night from vn_rates_plans where villa_id = '.$this->villa_id.' order by price_night';
        $this->exec($query);
        while( $rows = $this->getAssoc() ) $nights[] = $rows['price_night'];
        return $nights;
    }

    public function getPriceRooms(){
        $rooms = [];
        $query = 'select distinct(price_nbch) as price_room from vn_villas_prices where villa_id = '.$this->villa_id.' order by price_room desc';
        $query = 'select distinct(room) as price_room from vn_rates_plans where villa_id = '.$this->villa_id.' order by price_room desc';
        $this->exec($query);
        while( $rows = $this->getAssoc() ) $rooms[] = $rows['price_room'];
        return $rooms;
    }

    public function getPricePeriods($all=false){

        if( $all === false )
            $cdt_all = ' and periode_fin > '.time();

        $periods = [];

        $query = 'select vp.*, ld.lng_day 
                  from vn_villas_periodes as vp 
                  left join vn_lngs_days as ld 
                  on vp.periode_week = ld.lng_day_index and ld.lng_id = 1
                  where villa_id = '.$this->villa_id.$cdt_all.' order by periode_deb';

        $this->exec($query);
        while( $rows = $this->getAssoc() ){
            $periods[] = [
                'season_id' => $rows['saison_id'],
                'perioddeb' => $rows['periode_deb'],
                'periodfin' => $rows['periode_fin'],
                'minstay' => $rows['periode_minstay'],
                'switchoverday' => $rows['lng_day'] != NULL ? strtolower($rows['lng_day']) : ''
            ];
            /*$periods[$rows['periode_id']]['perioddeb'] = date('d/m/Y',$rows['periode_deb']);
            $periods[$rows['periode_id']]['periodfin'] = date('d/m/Y',$rows['periode_fin']);
            $periods[$rows['periode_id']]['minstay'] = $rows['periode_minstay'];*/
        }
        return $periods;
    }

    public function getSeasons(){

        $seasons = [];

        $query = 'select * from vn_villas_saisons where villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){

            $season = [];

            $season['id'] = $rows['saison_id'];
            $season['name'] = $rows['saison_name'];
            $season['discount'] = $rows['is_discount'];

            $seasons[] = $season;
        }

        return $seasons;

    }

    public function getUsedSeasons(){

        $seasons = [];

        $query = 'select * from vn_villas_saisons
                  where villa_id = '.$this->villa_id.' 
                  and saison_id in (select saison_id from vn_villas_periodes)';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){

            $season = [];

            $season['id'] = $rows['saison_id'];
            $season['name'] = $rows['saison_name'];
            $season['discount'] = $rows['is_discount'];

            $seasons[] = $season;
        }

        return $seasons;

    }

    public function getRatePlans(){

        $query = 'select * from vn_rates_plans where villa_id = '.$this->villa_id;
        $this->exec($query);

        $plans = [];

        while( $rows = $this->getAssoc() ){

            $plan = [];
            $plan['id'] = $rows['rate_plan_id'];
            $plan['room'] = $rows['room'];
            $plan['night'] = $rows['night'];

            $query = 'select t.villa_option_id, t.villa_option_name
                      from vn_rates_plans_options as rpo
                      join vn_villas_options_trad as t
                      on rpo.option_id = t.villa_option_id and t.langue_id = '.$this->lng_id.'
                      where rpo.rate_plan_id = '.$plan['id'];

            $res = new db();
            $res->exec($query);

            $options = [];

            while( $rows_options = $res->getAssoc() ){
                $option = [];
                $option['id'] = $rows_options['villa_option_id'];
                $option['name'] = $rows_options['villa_option_name'];
                $options[] = $option;
            }

            $plan['options'] = $options;

            $plans[] = $plan;

        }

        return $plans;

    }

    public function getPhotos(){

        $photos = [];

        $query = 'select vn_villas_photos.villa_photo_id, vn_villas_photos.photo_name, vn_villas_photos.photo_ext, vn_photos_titles_trad.photo_title_name 
                  from vn_villas_photos 
                  left join vn_photos_titles_trad
                  on vn_villas_photos.photo_title_id = vn_photos_titles_trad.photo_title_id and vn_photos_titles_trad.langue_id = '.$this->lng_id.'
                  where vn_villas_photos.villa_id = '.$this->villa_id.'
                  order by photo_order';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $format = $this->villa_is_hd == 1 ? '.1920.' : '.';
            $photos[] = [
                'photo' => '/photos/'.$this->villa_id.'/'.htmlentities($rows['photo_name']).$format.$rows['photo_ext'],
                'title' => $rows['photo_title_name']
            ];
        }

        return $photos;

    }

    public function getSelectionPhotos(){

        $photos = [];

        $format = $this->villa_is_hd ? '.1920.' : '.';

        $query = 'select vn_villas_photos.photo_name, vn_villas_photos.photo_ext
                  from vn_villas_photos 
                  where vn_villas_photos.villa_id = '.$this->villa_id.'
                  order by photo_main desc, photo_order limit 5';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $photos[] = '/photos/'.$this->villa_id.'/'.htmlentities($rows['photo_name']).$format.$rows['photo_ext'];
        }

        return $photos;

    }

    public function getDistances(){

        $distances = [];

        $getext = getEntries('628,629,630,631,632,633,1389',$this->lng_id);

        $query = 'select area_distance_name, villa_distance, villa_distance_unite, villa_distance_time, villa_distance_time_unite, villa_distance_time_per
                  from vn_villas_distances_ids 
                  join vn_areas_distances_trad 
                  on vn_villas_distances_ids.area_distance_id = vn_areas_distances_trad.area_distance_id 
                  and vn_areas_distances_trad.langue_id = '.$this->lng_id.'
                  where vn_villas_distances_ids.villa_id = '.$this->villa_id;

        $this->exec($query);

        while( $rows = $this->getAssoc() ){

            $distance = [];

            if( $rows['villa_distance'] > 0 and $rows['villa_distance_unite'] > 0 ){

                switch( $rows['villa_distance_unite'] ){
                    case 1: $villa_distance_unite = 'KM'; break;
                    case 2: $villa_distance_unite = 'MILES'; break;
                }

                $distance[] = $rows['villa_distance'].' '.$villa_distance_unite;

            }

            if( $rows['villa_distance_time'] > 0 and $rows['villa_distance_time_unite'] > 0 and $rows['villa_distance_time_per'] > 0 ){

                switch( $rows['villa_distance_time_unite'] ){
                    case 1: $villa_distance_time_unite = $getext[628]; break;
                    case 2: $villa_distance_time_unite = $getext[629]; break;
                }

                switch( $rows['villa_distance_time_per'] ){
                    case 1: $villa_distance_time_per = $getext[630]; break;
                    case 2: $villa_distance_time_per = $getext[631]; break;
                    case 3: $villa_distance_time_per = $getext[632]; break;
                    case 4: $villa_distance_time_per = $getext[633]; break;
                    case 5: $villa_distance_time_per = $getext[1389]; break;
                }

                $distance[] = $rows['villa_distance_time'].' '.$villa_distance_time_unite.' '.$villa_distance_time_per;

            }

            $distances[] = $rows['area_distance_name'].' ('.implode(' / ',$distance).')';

        }

        return $distances;

    }

    public function getReviewsAverages($rating_id=0){

        if( $rating_id > 0 )
            $query = 'select 
                      rating_recommanderiez_vous, impression_generale, rating_localisation_de_lhotel_la_maison, parties_communes_de_lhotel_la_maison,
                      accueil_a_larrivee, les_chambres, qualite_du_service, petit_dejeuner_restauration
                      from resa_rating 
                      where rating_id = '.$rating_id;

        else
            $query = 'select
                      rating_id,
                      rating_recommanderiez_vous, impression_generale, rating_localisation_de_lhotel_la_maison, parties_communes_de_lhotel_la_maison,
                      accueil_a_larrivee, les_chambres, qualite_du_service, petit_dejeuner_restauration
                      from resa_rating
                      where 
                      ( 
                        resa_id in (select id from resa where id_prd = '.$this->villa_id.' and resa.resa_archive = 0 and resa.id_etat = 3)
                        or 
                        villa_id = '.$this->villa_id.'
                      )
                      and resa_rating.publie2 = 1';


        $this->exec($query);

        $this->reviews_number = $this->numRows();

        $recommended_count = 0;
        $is_recommended_count = 0;

        $impression_count = 0;
        $impression_sum = 0;
        $impression_avr = 0;

        $location_count = 0;
        $location_sum = 0;
        $location_avr = 0;

        $parts_count = 0;
        $parts_sum = 0;
        $parts_avr = 0;

        $welcome_count = 0;
        $welcome_sum = 0;
        $welcome_avr = 0;

        $rooms_count = 0;
        $rooms_sum = 0;
        $rooms_avr = 0;

        $service_count = 0;
        $service_sum = 0;
        $service_avr = 0;

        $food_count = 0;
        $food_sum = 0;
        $food_avr = 0;

        $iAvr = 0;

        if( $this->numRows() ){

            while( $rows = $this->getAssoc() ){

                if ($rows['rating_recommanderiez_vous'] > 0) {
                    $recommended_count += 1;
                    if ($rows['rating_recommanderiez_vous'] == 1) $is_recommended_count += 1;
                }

                if ($rows['impression_generale'] > 0) {
                    $impression_count++;
                    $impression_sum += $rows['impression_generale'];
                }

                if ($rows['rating_localisation_de_lhotel_la_maison'] > 0) {
                    $location_count++;
                    $location_sum += $rows['rating_localisation_de_lhotel_la_maison'];
                }

                if ($rows['parties_communes_de_lhotel_la_maison'] > 0) {
                    $parts_count++;
                    $parts_sum += $rows['parties_communes_de_lhotel_la_maison'];
                }

                if ($rows['accueil_a_larrivee'] > 0) {
                    $welcome_count++;
                    $welcome_sum += $rows['accueil_a_larrivee'];
                }

                if ($rows['les_chambres'] > 0) {
                    $rooms_count++;
                    $rooms_sum += $rows['les_chambres'];
                }

                if ($rows['qualite_du_service'] > 0) {
                    $service_count++;
                    $service_sum += $rows['qualite_du_service'];
                }

                if ($rows['petit_dejeuner_restauration'] > 0) {
                    $food_count++;
                    $food_sum += $rows['petit_dejeuner_restauration'];
                }

            }

            $recommended_percent = ceil(($is_recommended_count / $recommended_count) * 100);

            $this->reviews_recommended_count = $recommended_count;
            $this->reviews_recommended_percent = $recommended_percent;

            if ($impression_sum > 0) {
                $impression_avr = noteFormat(($impression_sum / $impression_count) * 2);
                $iAvr += 1;
            }
            if ($location_sum > 0) {
                $location_avr = noteFormat(($location_sum / $location_count) * 2);
                $iAvr += 1;
            }
            if ($parts_sum > 0) {
                $parts_avr = noteFormat(($parts_sum / $parts_count) * 2);
                $iAvr += 1;
            }
            if ($welcome_sum > 0) {
                $welcome_avr = noteFormat(($welcome_sum / $welcome_count) * 2);
                $iAvr += 1;
            }
            if ($rooms_sum > 0) {
                $rooms_avr = noteFormat(($rooms_sum / $rooms_count) * 2);
                $iAvr += 1;
            }
            if ($service_sum > 0) {
                $service_avr = noteFormat(($service_sum / $service_count) * 2);
                $iAvr += 1;
            }
            if ($food_sum > 0) {
                $food_avr = noteFormat(($food_sum / $food_count) * 2);
                $iAvr += 1;
            }

            /*var_dump($impression_avr,$location_avr,$parts_avr,$welcome_avr,$rooms_avr,$service_avr,$food_avr);
            exit();*/

            $total_avr = noteFormat(($impression_avr + $location_avr + $parts_avr + $welcome_avr + $rooms_avr + $service_avr + $food_avr) / $iAvr);

            $this->reviews_impression_avr = $impression_avr;
            $this->reviews_location_avr = $location_avr;
            $this->reviews_parts_avr = $parts_avr;
            $this->reviews_welcome_avr = $welcome_avr;
            $this->reviews_rooms_avr = $rooms_avr;
            $this->reviews_service_avr = $service_avr;
            $this->reviews_food_avr = $food_avr;
            $this->reviews_total_avr = $total_avr;

        }

        return $total_avr;

    }

    public function getReviewsComments(){

        $comments = [];

        $query = 'select 
                  rating_vos_commentaires, rating_vos_commentaires2, 
                  customer_lname, customer_fname, arrival_date, depart_date, rating_id,
                  vn_lngs.lng_code
                  from resa_rating 
                  join resa
                  on resa_rating.resa_id = resa.id
                  join vn_customers
                  on resa.id_client = vn_customers.customer_id
                  join vn_lngs
                  on resa.lang = vn_lngs.lng_id
                  where resa_rating.publie2 = 1 and resa.id_prd = '.$this->villa_id.' 
                  and resa.resa_archive = 0 and resa.id_etat = 3
                  and ( rating_vos_commentaires <> "" or rating_vos_commentaires2 <> "" )
                  union
                  select
                  rating_vos_commentaires, rating_vos_commentaires2,
                  rating_lname, rating_fname, rating_arrival, rating_departure, rating_id,
                  vn_lngs.lng_code
                  from resa_rating
                  join vn_lngs
                  on resa_rating.rating_lng_id = vn_lngs.lng_id
                  where publie2 = 1 and villa_id = '.$this->villa_id.' 
                  and ( rating_vos_commentaires <> "" or rating_vos_commentaires2 <> "" )
                  order by arrival_date desc';

        $res = new db();
        $res->exec($query);

        while( $rows = $res->getAssoc() ){

            $comments[] = [
                'plus' => nl2br($rows['rating_vos_commentaires']),
                'moins' => nl2br($rows['rating_vos_commentaires2']),
                'flag' => $rows['lng_code'].'.gif',
                'name' => ucfirst($rows['customer_fname']).' '.strtoupper(substr($rows['customer_lname'],0,1)).'.',
                'dates' => date('d/m/Y',$rows['arrival_date']).' - '.date('d/m/Y',$rows['depart_date']),
                'note' => $this->getReviewsAverages($rows['rating_id'])
            ];

        }

        return $comments;

    }

    public function checkAvailability($checkin,$checkout,$book_id=0){

        $cdt = $book_id == 0 ? '' : ' and resa_id <> '.$book_id;

        $query = 'select villa_isdispo from vn_villas_dispos 
                  where villa_id = '.$this->villa_id.$cdt.' and villa_dispo_time between '.$checkin.' and '.dayBefore($checkout);

        $this->exec($query);

        if( $this->numRows() > 0 ){

            $id_dispo = 1;

            while( $rows = $this->getAssoc() ){

                if( $rows['villa_isdispo'] == 0 ){
                    $id_dispo = 0;
                    break;
                }

            }

        }
        else{
            $id_dispo = 2;
        }

        return $id_dispo != 0;

    }

    public function getAvailabilityState($checkin,$checkout){

        $nbDays = daysBetween($checkin,$checkout);

        $query = 'select distinct(villa_isdispo) as villa_isdispo, count(villa_isdispo) as nb 
                  from vn_villas_dispos 
                  where villa_id = '.$this->villa_id.' 
                  and villa_dispo_time between '.$checkin.' and '.dayBefore($checkout).'
                  group by villa_isdispo
                  order by villa_isdispo
                  limit 1';

        $this->exec($query);

        $dispo = 2;

        if( $row = $this->getAssoc() ){

            if( $row['villa_isdispo'] == 0 ){
                $dispo = 0;
            }
            elseif( $row['villa_isdispo'] == 1 and $row['nb'] == $nbDays ){
                $dispo = 1;
            }

        }

        return $dispo;

    }

    public function clearCalendar($checkin,$checkout){

        $checkint = is_timestamp($checkin) ? $checkin : toTime($checkin);
        $checkoutt = is_timestamp($checkout) ? $checkout : toTime($checkout);

        $query = 'delete from vn_villas_dispos 
                  where villa_id = '.$this->villa_id.' and villa_dispo_time between '.$checkint.' and '.dayBefore($checkoutt);

        $this->exec($query);

        return $this->affectedRows();

    }

    public function getStayPrice($inTime,$outTime,$room,$convert='CUS'){

        if( $this->villa_display_prices == 1 ){

            $total = 0;
            $nbDays = daysBetween($inTime,$outTime);

            $query = 'select price_value from vn_villas_prices
                      where villa_id = '.$this->villa_id.' 
                      and ( price_time between '.$inTime.' and '.dayBefore($outTime).' ) 
                      and price_nbch = '.$room.' and price_nbnuit <= '.$nbDays.'
                      and price_value > 0
                      order by price_nbnuit desc
                      limit '.$nbDays;

            $this->exec($query);

            if( $this->numRows() == $nbDays){
                while( $rows = $this->getAssoc() ){
                    $total += $rows['price_value'];
                }
            }

            if( $total > 0 ){
                $total = $this->calculatePrice($total,$convert);
            }

            return $total;

        }
        else{
            return 0;
        }

    }

    public function getStayMinNights($inTime,$outTime){

        $query = 'select max(periode_minstay) as min_nights 
                  from vn_villas_periodes 
                  where villa_id = '.$this->villa_id.' and 
                  ( 
                      ( periode_deb between '.$inTime.' and '.dayBefore($outTime).' ) or 
                      ( periode_fin between '.$inTime.' and '.dayBefore($outTime).' ) or
                      ( periode_deb < '.$inTime.' and periode_fin > '.dayBefore($outTime).' )
                  )';

        $this->exec($query);

        $minNights = 1;

        if( $row = $this->getAssoc() )
            $minNights = $row['min_nights'] > 1 ? $row['min_nights'] : 1;

        return $minNights;

    }

    public function getPeriodConditions($inTime,$outTime){

        $query = 'select max(periode_minstay) as minstay, max(periode_week) as week
                  from vn_villas_periodes 
                  where villa_id = '.$this->villa_id.' and 
                  ( 
                      ( periode_deb between '.$inTime.' and '.dayBefore($outTime).' ) or 
                      ( periode_fin between '.$inTime.' and '.dayBefore($outTime).' ) or
                      ( periode_deb < '.$inTime.' and periode_fin > '.dayBefore($outTime).' )
                  )';

        $this->exec($query);

        $minstay = 1;
        $day = '';

        $dayIndex1 = date('N',$inTime);
        $dayIndex2 = date('N',$outTime);

        if( $row = $this->getAssoc() ) {

            $minstay = $row['minstay'] > 1 ? $row['minstay'] : 1;
            if( $row['week'] > 0 and ( $dayIndex1 != $row['week'] or $dayIndex2 != $row['week'] ) ){
                $lng_obj = new lngs();
                $day = $lng_obj->getDay($row['week']);
            }

        }

        $conditions = [];
        $conditions['minstay'] = $minstay;
        $conditions['day'] = $day;

        return $conditions;

    }

    public function getPriceFromTo($direction='from',$select='min',$convert=true){

        if( $this->villa_display_prices  == 1 ){

            $field = $direction == 'from' ? 'villa_room_from' : 'villa_room_to';

            switch ($select){
                case 'min':
                    $query = 'select min('.$field.') as '.$field.' from vn_villas_rooms where villa_id = '.$this->villa_id;
                    break;

                case 'max':
                    $query = 'select max('.$field.') as '.$field.' from vn_villas_rooms where villa_id = '.$this->villa_id;
                    break;

                default:
                    $query = 'select '.$field.' from vn_villas_rooms where villa_id = '.$this->villa_id.' and villa_room = '.$select;
                    break;
            }

            $this->exec($query);
            $row = $this->getAssoc();
            return $row[$field] > 0 ? $this->calculatePrice($row[$field],$convert) : 0;

        }
        else{
            return 0;
        }



    }

    public function calculatePrice($price,$convert='CUS'){
        if( $price > 0 ){
            if( $this->villa_tva > 0 ) $price += ($price*$this->villa_tva)/100;
            if( in_array($convert,['CUS','VN']) ) $price = $this->convertPrice($price,$convert);
        }
        return $price;
    }

    public function convertPrice($price,$currency='CUS'){
        $rate_to = $currency == 'CUS' ? $this->currency_rate_to : 1;
        $converted = $price*($this->villa_currency_rate/$rate_to);
        if( ($converted < -10 or $converted > 10)  )
            $converted = ceil($converted);

        return $converted;
    }

    public function getRooms($min=0){

        $rooms = [];

        if( $min > 0 )
            $cdt_room = ' and villa_room >= '.$min;

        $query = 'select villa_room from vn_villas_rooms where villa_id = '.$this->villa_id.$cdt_room.' order by villa_room';
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $rooms[] = $rows['villa_room'];
        }

        return $rooms;

    }

    public function isRoom($room,$min=0){

        $rooms = $this->getRooms($min);
        if( !in_array($this->villa_bedrooms,$rooms) ) $rooms[] = $this->villa_bedrooms;

        return $room <= max($rooms);

    }

    public function houseStaff(){

        $query = 'select 
                  vef.villa_equipment_filter_id
                  from vn_villas_equipments_filters as vef
                  join vn_villas_equipments_filters_ids vefi
                  on vef.villa_equipment_filter_id = vefi.villa_equipment_filter_id
                  join vn_villas_equipments_ids as vei
                  on vefi.villa_equipment_id = vei.villa_equipment_id
                  where vei.villa_id = '.$this->villa_id.' and vef.villa_equipment_filter_id = 8';

        $this->exec($query);

        return $this->numRows() > 0;

    }

    public function getFilters($showed=1){

        $filters = [];

        if( $showed == -1 )
            $showed = '0,1';

        $query = 'select 
                  vn_villas_equipments_filters.villa_equipment_filter_id
                  from vn_villas_equipments_filters
                  join vn_villas_equipments_filters_ids
                  on vn_villas_equipments_filters.villa_equipment_filter_id = vn_villas_equipments_filters_ids.villa_equipment_filter_id
                  join vn_villas_equipments_ids
                  on vn_villas_equipments_filters_ids.villa_equipment_id = vn_villas_equipments_ids.villa_equipment_id
                  where vn_villas_equipments_ids.villa_id = ' . $this->villa_id . '
                  and vn_villas_equipments_filters.villa_equipment_filter_showed in ('.$showed.')
                  group by vn_villas_equipments_filters.villa_equipment_filter_id';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $filters[] = $rows['villa_equipment_filter_id'];
        }

        return $filters;

    }

    public function isFilter($filter_id){

        $query = 'select 
                  vn_villas_equipments_filters_ids.villa_equipment_filter_id
                  from vn_villas_equipments_ids
                  join vn_villas_equipments_filters_ids
                  on vn_villas_equipments_ids.villa_equipment_id = vn_villas_equipments_filters_ids.villa_equipment_id
                  where vn_villas_equipments_ids.villa_id = ' . $this->villa_id . '
                  and vn_villas_equipments_filters_ids.villa_equipment_filter_id = '.$filter_id;

        $this->exec($query);

        return $this->numRows()>0;

    }

    public function getZone($level){

        switch($level){

            case 0:
                $zone_id = $this->villa_continent_id;
                break;

            case 1:
                $zone_id = $this->villa_country_id;
                break;

            case 2:
                $zone_id = $this->villa_city_id;
                break;

            case 3:
                $zone_id = $this->villa_district_id;
                break;

            default:
                $zone_id = 0;
                break;

        }

        return $zone_id;

    }

    public function incrementVisitor(){

        // Nb. visites/visiteurs
        if( !$_COOKIE['villa'.$this->villa_id] ){
            setcookie('villa'.$this->villa_id,1,time()+(60*60*24*30*12));
            $cdt_visitors = ', villa_stats_nbvisitors = villa_stats_nbvisitors+1';
        }

        $query = 'update vn_villas set villa_stats_nbvisites = villa_stats_nbvisites+1'.$cdt_visitors.' where villa_id = '.$this->villa_id;
        $this->exec($query);

    }

    public function incrementContracts(){

        $query = 'update vn_villas set villa_stats_nbcontrats = villa_stats_nbcontrats+1 where villa_id = '.$this->villa_id;
        $this->exec($query);

        if( $this->affectedRows() > 0 ){
            $this->villa_stats_nbcontrats++;
            return true;
        }
        else{
            return false;
        }

    }

    public function addToLastVillasVisited(){

        $array = [];

        foreach( $_COOKIE['last_villas'] as $key=>$id ){
            $array[] = $id;
            setcookie('last_villas['.$key.']','',time()-3600,'/');
        }

        if( in_array($this->villa_id,$array) ){
            $to_remove_tab = [$this->villa_id];
            $array = array_diff($array,$to_remove_tab);
        }
        else if( count($array) == 3 ){
            array_pop($array);
        }

        array_unshift($array,$this->villa_id);

        $iCookie = 0;
        foreach( $array as $cookie_villa_id ){
            $iCookie++;
            setcookie('last_villas['.$iCookie.']',$cookie_villa_id,time()+(60*60*24*30),'/');
        }

    }

    public function getResume(){
        if( strlen(trim($this->villa_resume)) < 50 ){
            $getext = getEntries('989',$this->lng_id);
            $from = ['$1','$2','$3','$4','$5'];
            $to = [$this->villa_public_name,$this->villa_city,$this->villa_bedrooms,$this->villa_occupancy_max,$this->villa_resume];
            $villa_resume = str_replace($from,$to,$getext[989]);
            return $villa_resume;
        }
        else{
            return $this->villa_resume;
        }
    }

    public function getFullDescription(){

        return str_replace('$rooms',$this->getBedsDescription(),$this->villa_description);

    }

    private function checkDomainTrad($domain_id,$lng_id){

        $query = 'select villa_domain_trad_id from vn_villas_domains_trad
                  where villa_id = '.$this->villa_id.' and domain_id = '.$domain_id.' and lng_id = '.$lng_id;

        $this->exec($query);

        if( $row = $this->getAssoc() ){
            return $row['villa_domain_trad_id'];
        }

    }

    public function updateDomainTrad($domain_id,$lng_id){

        $fields = [];

        $fields['villa_title'] = $this->villa_title != '' ? $this->villa_title : '';
        $fields['villa_resume'] = $this->villa_resume != '' ? $this->villa_resume : '';
        $fields['villa_description'] = $this->villa_description != '' ? $this->villa_description : '';

        if( $this->villa_description_api_old != '' ) $fields['villa_description_api_old'] = $this->villa_description_api_old;
        if( $this->villa_description_api_new != '' ) $fields['villa_description_api_new'] = $this->villa_description_api_new;


        if( $villa_domain_trad_id = $this->checkDomainTrad($domain_id,$lng_id)){

            if( $this->villa_description_updated != '' ) $fields['villa_description_updated'] = $this->villa_description_updated;
            if( $this->villa_description_api_new_updated != '' ) $fields['villa_description_api_new_updated'] = $this->villa_description_api_new_updated;

            foreach ($fields as $field=>$content){
                $sqls[] = $field.' = "'.$content.'"';
            }

            $query = 'update vn_villas_domains_trad set '.implode(', ',$sqls).' where villa_domain_trad_id = '.$villa_domain_trad_id;

        }
        else{
            $fields['villa_id'] = $this->villa_id;
            $fields['domain_id'] = $domain_id;
            $fields['lng_id'] = $lng_id;

            $query = 'insert into vn_villas_domains_trad('.implode(',',array_keys($fields)).')
                      values("'.implode('","',$fields).'")';
        }

        if( $this->villa_id == 6068 ){
            //echo $query;
            //exit();
        }

        $this->exec($query);

        $affected = $this->affectedRows();

        if( $affected > 0 ){
            $query = 'update vn_villas set villa_is_api_desc = 0 where villa_id = '.$this->villa_id;
            $this->exec($query);
        }

        return $affected;

    }

    public function getDomainTrad($default=true){

        $query = 'select
                  vtd.villa_title as title, vted.villa_title as title_en, vtv.villa_title as title2, vtev.villa_title as title2_en, 
                  vtd.villa_resume as resume, vted.villa_resume as resume_en, vtv.villa_resume as resume2, vtev.villa_resume as resume2_en, 
                  vtd.villa_description as description, vted.villa_description as description_en, 
                  vtv.villa_description as description2, vtev.villa_description as description2_en,
                  vtd.villa_description_api_old as description_api_old,
                  vtd.villa_description_api_new as description_api_new
                  from vn_villas as v
                  left join vn_villas_domains_trad as vtd
                  on v.villa_id = vtd.villa_id and vtd.domain_id = '.$this->boot_domain_id.' and vtd.lng_id = '.$this->lng_id.'
                  left join vn_villas_domains_trad as vted
                  on v.villa_id = vted.villa_id and vted.domain_id = '.$this->boot_domain_id.' and vted.lng_id = 1
                  left join vn_villas_domains_trad as vtv
                  on v.villa_id = vtv.villa_id and vtv.domain_id <> '.$this->boot_domain_id.' and vtv.lng_id = '.$this->lng_id.'
                  left join vn_villas_domains_trad as vtev
                  on v.villa_id = vtev.villa_id and vtev.domain_id <> '.$this->boot_domain_id.' and vtev.lng_id = 1
                  where v.villa_id = '.$this->villa_id;

        $this->exec($query);

        if( $row = $this->getAssoc() ){

            if( $default ){

                if( $row['title'] != '' )
                    $this->villa_title = $row['title'];
                else if( $row['title_en'] != '' )
                    $this->villa_title = $row['title_en'];
                else if( $row['title2'] != '' )
                    $this->villa_title = $row['title2'];
                else
                    $this->villa_title = $row['title2_en'];

                if( $row['resume'] != '' )
                    $this->villa_resume = $row['resume'];
                else if( $row['resume_en'] != '' )
                    $this->villa_resume = $row['resume_en'];
                else if( $row['resume2'] != '' )
                    $this->villa_resume = $row['resume2'];
                else
                    $this->villa_resume = $row['resume2_en'];

                if( $row['description'] != '' ){
                    $this->villa_description = $row['description'];
                    $this->description_lng_id = $this->lng_id;
                }
                else if( $row['description_en'] != '' ){
                    $this->villa_description = $row['description_en'];
                    $this->description_lng_id = 1;
                }
                else if( $row['description2'] != '' ){
                    $this->villa_description = $row['description2'];
                    $this->description_lng_id = $this->lng_id;
                }
                else{
                    $this->villa_description = $row['description2_en'];
                    $this->description_lng_id = 1;
                }

            }
            else{
                $this->villa_title = $row['title'];
                $this->villa_resume = $row['resume'];
                $this->villa_description = $row['description'];
                $this->villa_description_api_old = $row['description_api_old'];
                $this->villa_description_api_new = $row['description_api_new'];
            }

        }

    }

    public function getComment(){

        if( is_null($this->villa_comment) ){

            $query = 'select vtl.villa_comment as villa_comment_lng, vte.villa_comment as villa_comment_eng
                      from vn_villas as v
                      join vn_villas_trad as vtl
                      on v.villa_id = vtl.villa_id and vtl.langue_id = '.$this->lng_id.'
                      join vn_villas_trad as vte
                      on v.villa_id = vte.villa_id and vte.langue_id = 1
                      where v.villa_id = '.$this->villa_id;

            $this->exec($query);

            if( $row = $this->getAssoc() ){
                $this->villa_comment = $row['villa_comment_lng'] != '' ? $row['villa_comment_lng'] : $row['villa_comment_eng'];
            }

        }

        return $this->villa_comment;

    }

    public function getBedsDescription(){

        $finalText = '';
        $iRow = 0;

        $getext = getEntries('138,638,1276,1280',$this->description_lng_id);

        $query = 'select villa_bed_id from vn_villas_beds where villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){

            require_once PATH_CLASS.'beds.php';

            $iRow++;

            $bed = new beds($rows['villa_bed_id'],$this->description_lng_id);

            $finalText .= $getext[138].' '.$iRow;
            if( $bed->bed_name != '' ) $finalText .= ' - '.$bed->bed_name.' : ';

            $finalText .= '<br />';
            $finalText .= ucfirst($bed->type_name);
            if( $bed->floor_name != '' ) $finalText .= ', '.ucfirst($bed->floor_name);

            $accesses = $bed->getAccesses();
            if( count($accesses) > 0 ) $finalText .= ', '.implode(', ',$accesses);
            $finalText .= '. ';

            $finalText .= $bed->getLits();

            $baths1 = $bed->getBaths1();
            $baths2 = $bed->getBaths2();
            if( count($baths1) > 0 ) $finalText .= $getext[638].' '.implode(', ',$baths1);
            if( count($baths2) > 0 ) $finalText .= ', '.strtolower($getext[1280]).' '.implode(', ',$baths2);
            if( count($baths1) > 0  or count($baths2) > 0 ) $finalText .= '. ';

            $wcs = $bed->getWcs();
            if( count($wcs) > 0 ) $finalText .= implode(', ',$wcs).'. ';

            $equips = $bed->getEquips();
            if( count($equips) > 0 ) $finalText .= $getext[1276].' '.implode(', ',$equips).'. ';

            $finalText .= '<br><br>';

        }

        //return rtrim($finalText,"\r");
        return preg_replace('/(<br>)+$/', '', $finalText);

    }

    public function getUsedRoom($travelers){

        $query = 'select villa_room, villa_room_from
                  from vn_villas_rooms
                  where villa_id = '.$this->villa_id.' and villa_room >= '.ceil(intval($travelers)/2).'
                  order by vn_villas_rooms.villa_room
                  limit 1';

        $this->exec($query);

        if( $row = $this->getAssoc() ){
            $this->used_room = $row['villa_room'];
            $this->used_room_from = $row['villa_room_from'];
        }
        else{
            $this->used_room = $this->villa_bedrooms_max;
            $this->used_room_from = $this->villa_bedrooms_max_from;
        }

    }

    public function cusPayTerms(){

        $query = 'select * from vn_villas_conditions_ids where condition_id in (9,11,12,13,14) and villa_id = '.$this->villa_id;
        $this->exec($query);

        $conditions = [];

        while( $rows = $this->getAssoc() ){
            $conditions[$rows['condition_id']] = $rows['condition_value'];
        }

        $terms = [];

        $payment1_rate = $conditions[9] > 0 ? $conditions[9] : 100;
        $payment2_rate = $conditions[12];
        $payment2_days = $conditions[11];
        $payment3_rate = $conditions[14];
        $payment3_days = $conditions[13];

        $terms[] = ['rate'=>$payment1_rate,'key'=>1,'value'=>''];
        if( $payment2_rate > 0 ) $terms[] = ['rate'=>$payment2_rate,'key'=>2,'value'=>$payment2_days];
        if( $payment3_rate > 0 ) $terms[] = ['rate'=>$payment3_rate,'key'=>2,'value'=>$payment3_days];

        return $terms;

    }

    public function getDeadlines($checkin){

        $query = 'select * from vn_villas_conditions_ids where condition_id in (9,11,12,13,14) and villa_id = '.$this->villa_id;
        $this->exec($query);

        $conditions = [];

        while( $rows = $this->getAssoc() ){
            $conditions[$rows['condition_id']] = $rows['condition_value'];
        }

        $payment1_rate = $conditions[9] > 0 ? $conditions[9] : 100;
        $payment2_rate = $conditions[12];
        $payment2_days = $conditions[11];
        $payment3_rate = $conditions[14];
        $payment3_days = $conditions[13];

        $fromTime = toTime(toDate());
        $toTime = $checkin;

        $daysBetween = daysBetween($fromTime,$toTime);

        $payments = [];

        if( $payment1_rate != 100 ){

            if( !empty($payment3_rate) && !empty($payment3_days) ){
                if( $payment3_days < $daysBetween ) $payments[] = [strtotime('-'.$payment3_days.' days',$toTime),$payment3_days,$payment3_rate];
                else $payment1_rate += $payment3_rate;
            }
            if( !empty($payment2_rate) && !empty($payment2_days) ){
                if( $payment2_days < $daysBetween ) $payments[] = [strtotime('-'.$payment2_days.' days',$toTime),$payment2_days,$payment2_rate];
                else $payment1_rate += $payment2_rate;
            }

        }

        $payments[] = [$fromTime,$daysBetween,$payment1_rate];
        $payments = array_reverse($payments);

        return $payments;

    }

    public function getRentalConditions(){

        $array = [];

        $getext = getEntries('21,20,691,692,693,694,695,696,697,237,238,239,240,241,1179,1204,1206,1228,1229',$this->lng_id);

        $query = 'select condition_name 
				  from vn_villas_conditions_ids 
				  join vn_villas_conditions_trad
				  on vn_villas_conditions_ids.condition_value = vn_villas_conditions_trad.condition_id and vn_villas_conditions_trad.langue_id = '.$this->lng_id.' 
				  where villa_id = '.$this->villa_id.' 
				  and vn_villas_conditions_ids.condition_id = 1
				  order by condition_name';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $array[] = $rows['condition_name'];
        }


        $spoken = [1=>$getext[21],2=>$getext[20],3=>$getext[691],4=>$getext[692],5=>$getext[693],6=>$getext[694],7=>$getext[695],8=>$getext[696],9=>$getext[697],10=>$getext[1179]];

        $query = 'select condition_value from vn_villas_conditions_ids where villa_id = '.$this->villa_id.' and condition_id = 2';
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $lngs[] = $spoken[$rows['condition_value']];
        }

        $array[] = $getext[237].' : '.implode(' - ',$lngs);

        $query = 'select * from vn_villas_conditions_ids where villa_id = '.$this->villa_id.' and condition_id in (3,4,5,6)';
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $checks[$rows['condition_id']] = $rows['condition_value'];
        }

        if( $checks[3] > 0 and $checks[4] > 0 )
            $array[] = $getext[238].' : <strong>'.$checks[3].':00 h</strong> - '.$getext[239].' : <strong>'.$checks[4].':00 h</strong>';
        if( $checks[5] > 0 ){
            $array[] = str_replace('$1','<strong> '.priceFormat($this->convertPrice($checks[5])).' '.$this->currency_code_to.' </strong>',$getext[240]).(( $checks[6] ) ? ' ( '.$getext[241].' )'  : '');
        }

        return $array;

    }

    public function getBookConditions(){

        $array = [];

        $getext = getEntries('698,699,700,701,702,703,704,705,706,707,708,709,710,711,712,713,714,715,716,717,718,719,720,721,722',$this->lng_id);

        $tab_cdt_name = [9,10,11,12,13,14];

        // Paiement
        $query = 'select * from vn_villas_conditions_ids where condition_id in ('.implode(',',$tab_cdt_name).') and villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $tab_cdt[$rows['condition_id']] = $rows['condition_value'];
        }

        if( !empty($tab_cdt[9]) ){
            $array[] = $getext[698].' : <strong>'.$tab_cdt[9].' % </strong>';
        }
        if( !empty($tab_cdt[10]) ){

            if( $tab_cdt[10] == 1 ){
                $array[] = $getext[700].'  : <strong>'.$getext[701].'</strong>.';
            }
            else{

                if( !empty($tab_cdt[11]) && !empty($tab_cdt[12]) )
                    $array[] = $getext[703].' <strong>'.$tab_cdt[11].' '.$getext[704].'</strong> '.$getext[705].' : <strong>'.$tab_cdt[12].' % </strong> '. $getext[706].'.';

                if( !empty($tab_cdt[13]) && !empty($tab_cdt[14]) )
                    $array[] = $getext[707].' <strong>'.$tab_cdt[13].' '.$getext[704].'</strong> '.$getext[705].' : <strong>'.$tab_cdt[14].' % </strong> '. $getext[706].'.';
            }
        }

        // Mode de reglements acceptés pour les echeances
        $advance_payments_liste = [1=>$getext[709],2=>$getext[710],3=>$getext[711],4=>$getext[712],5=>$getext[713],6=>$getext[714]];

        /*$query = 'select * from vn_villas_conditions_ids where condition_id = 15 and villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $tab_advance[] = $advance_payments_liste[$rows['condition_value']];
        }

        if( count($tab_advance) > 0 )
            $array[] = $getext[715].' : '.implode(' - ',$tab_advance).'.';*/

        // Mode de reglements acceptés lors du séjour
        $on_site_payment_liste = [1=>$getext[709],2=>$getext[710],3=>$getext[711],4=>$getext[712],5=>$getext[713],6=>$getext[714],7=>$getext[716],8=>$getext[717]];

        /*$query = 'select * from vn_villas_conditions_ids where condition_id = 16 and villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $tab_site[] = $on_site_payment_liste[$rows['condition_value']];
        }

        if( count($tab_site) > 0 )
            $array[] = $getext[718].' : '.implode(' - ',$tab_site).'.';*/

        // Autres
        $query = 'select * from vn_villas_conditions_ids where condition_id in (17,18,19,20) and villa_id = '.$this->villa_id;
        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $tab_autres[$rows['condition_id']] = $rows['condition_value'];
        }

        if( !empty($tab_autres[17]) )
            $array[] = $getext[719].'.';

        if( !empty($tab_autres[18]) )
            $array[] = $getext[720].'.';

        if( !empty($tab_autres[19]) )
            $array[] = $getext[721].' '.$tab_autres[19].' % ';

        if( !empty($tab_autres[20]) )
            $array[] = $getext[722].'.';

        return $array;

    }

    public function getCancelConditions(){

        $array = [];

        $getext = getEntries('723,724,131,726,704,705,706,727,728,724,729,730,731,732,733,734,853,1380,1390,1463,1464,1465,1469,1472,1473,1494',$this->lng_id);

        $tab_cdt_name = [21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39];

        $query = 'select * from vn_villas_conditions_ids 
				  where condition_id in ('.implode(',',$tab_cdt_name).') and villa_id = '.$this->villa_id;

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $tab_cdt[$rows['condition_id']] = $rows['condition_value'];
        }

        $array[] = html_entity_decode($getext[723]);
        $array[] = $getext[724];

        if( !empty($tab_cdt[35]) )
            $array[] = $getext[1465].'.';

        if( !empty($tab_cdt[31]) )
            $array[] = $getext[1380].'.';

        if( !empty($tab_cdt[32]) )
            $array[] = $getext[1390].'.';

        if( !empty($tab_cdt[33]) )
            $array[] = $getext[1463].'.';

        if( !empty($tab_cdt[34]) )
            $array[] = $getext[1464].'.';

        if( !empty($tab_cdt[36]) )
            $array[] = $getext[1469].'.';

        if( !empty($tab_cdt[37]) )
            $array[] = $getext[1472].'.';

        if( !empty($tab_cdt[38]) )
            $array[] = $getext[1473].'.';

        if( !empty($tab_cdt[39]) )
            $array[] = $getext[1494].'.';

        if( !empty($tab_cdt[21]) )
            $array[] = $getext[732].'.';

        if( !empty($tab_cdt[22]) && !empty($tab_cdt[23]) )
            $array[] = $getext[726].' <strong>'.$tab_cdt[22].' '.$getext[704].'</strong> '.$getext[705].' : <strong>'.$tab_cdt[23].' % </strong> '. $getext[706].'.';

        if( !empty($tab_cdt[24]) && !empty($tab_cdt[25]) )
            $array[] = $getext[726].' <strong>'.$tab_cdt[24].' '.$getext[704].'</strong> '.$getext[705].' : <strong>'.$tab_cdt[25].' % </strong> '. $getext[706].'.';

        if( !empty($tab_cdt[26]) && !empty($tab_cdt[27]) )
            $array[] = $getext[726].' <strong>'.$tab_cdt[26].' '.$getext[704].'</strong> '.$getext[705].' : <strong>'.$tab_cdt[27].' % </strong> '. $getext[706].'.';

        if( !empty($tab_cdt[28]) ){
            $array[] = $getext[727].' <strong>'.$tab_cdt[28].' % </strong> '.$getext[706];
        }

        if( $tab_cdt[29] > 0 )
            $array[] = $getext[733].' : '.priceFormat($this->convertPrice($tab_cdt[29]),0).' '.$this->currency_code_to;

        if( $tab_cdt[30] > 0 )
            $array[] = $getext[853].' : '.priceFormat($this->convertPrice($tab_cdt[30]),0).' '.$this->currency_code_to;

        return $array;

    }

    public function generateCalendar($monthyear){

        $calendar = '';
        $todayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));

        $lng_obj = new lngs($this->lng_id);
        $shorts = $lng_obj->getDays('short');

        $parts = explode('/',$monthyear);
        $month = $parts[0];
        $year = $parts[1];

        $monthStartsTime = mktime(0,0,0,$month,1,$year);
        $monthDays = date('t',$monthStartsTime);
        $monthEndsTime = mktime(0,0,0,$month,$monthDays,$year);
        $firstDayIndex = date('N',$monthStartsTime);
        $lastDayIndex = date('N',$monthEndsTime);
        $firstBlank = $firstDayIndex - 1;
        $lastBlank = 7 - $lastDayIndex;
        $loopStart = 1;
        $loopEnd = $firstBlank + $monthDays + $lastBlank;

        $availabilities = $this->getAvailabilities($monthStartsTime,$monthEndsTime);

        $calendar .= '<div class="col-md-6 calendar-frame" data-month="'.$month.'/'.$year.'">';
        $calendar .= '<div class="calendar-month">';
        $calendar .= '<div class="calendar-month-title">'.$lng_obj->getMonth(date('n',$monthStartsTime)).' '.$year.'</div>';
        $calendar .= '<table class="table">';

        $calendar .= '<thead>';
        $calendar .= '<tr>';
        foreach( $shorts as $short)
            $calendar .= '<td>'.$short.'</td>';
        $calendar .= '</tr>';
        $calendar .= '</thead>';

        $calendar .= '<tbody>';
        $calendar .= '<tr>';

        $iDay = 0;
        for( $iLoop = $loopStart; $iLoop<=$loopEnd; $iLoop++){
            if( $iLoop > $firstBlank and $iLoop <= $loopEnd-$lastBlank ){
                $iDay++;
                $selectDayTime = mktime(0,0,0,$month,$iDay,$year);
                $selectDay = date('Ymd',$selectDayTime);
                if( $selectDayTime >= $todayTime ){
                    $class = in_array($selectDay,$availabilities) ? 'not-dispo' : 'ondemand';
                }
                else{
                    $class = 'passed';
                }
                $calendar .= '<td class="'.$class.'">'.$iDay.'</td>';
            }
            else{
                $calendar .= '<td></td>';
            }
            if( $iLoop%7 == 0 and $iLoop < $loopEnd){
                $calendar .= '</tr><tr>';
            }
        }

        $calendar .= '<tr>';
        $calendar .= '</tbody>';

        $calendar .= '</table>';
        $calendar .= '</div>';
        $calendar .= '</div>';

        return $calendar;

    }

    public function getAvailabilities($from=0,$to=0,$format='Ymd',$state=0){

        if( $from === 0 )
            $sql = '';
        elseif ($to === 0 ){
            $sql = ' and villa_dispo_time > '.time();
        }
        elseif ( is_timestamp($from) and is_timestamp($to) ){
            $from = toTime(toDate($from));
            $to = toTime(toDate($to));
            $sql = ' and villa_dispo_time between "'.$from.'" and "'.$to.'"';
        }

        $state = in_array($state,[0,1]) ? $state : 0;

        $array = [];

        $query = 'select villa_dispo_time 
                  from vn_villas_dispos 
                  where villa_id = '.$this->villa_id.' and villa_isdispo = '.$state.$sql;

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $array[] = date($format,$rows['villa_dispo_time']);
        }

        return $array;

    }

    public function getSimilarVillas(){

        $villas = [];

        $query = 'select 
                  vn_villas.villa_id,
                  case 
                  when z1.zone_id = '.$this->villa_district_id.' then 0
                  when z1.zone_id <> '.$this->villa_district_id.' and z2.zone_id = '.$this->villa_city_id.' then 1
                  when z1.zone_id <> '.$this->villa_district_id.' and z2.zone_id <> '.$this->villa_city_id.' and z3.zone_id = '.$this->villa_country_id.' then 2
                  end as zone_order
                  from vn_villas 
                  join vn_villas_domains as vd
                  on vn_villas.villa_id = vd.villa_id and vd.domain_id = '.$this->boot_domain_id.' and vd.is_hidden = 0
                  join vn_villas_photos
                  on vn_villas.villa_id = vn_villas_photos.villa_id and vn_villas_photos.photo_main = 1
                  join vn_zones as z1 
                  on vn_villas.zone_id = z1.zone_id
                  join vn_zones as z2
                  on z1.zone_parent_id = z2.zone_id 
                  join vn_zones as z3
                  on z2.zone_parent_id = z3.zone_id 
                  join vn_zones as z4
                  on z3.zone_parent_id = z4.zone_id 
                  where
                  vn_villas.villa_state_id = 1 and vn_villas.villa_id <> '.$this->villa_id.'
                  and ( z1.zone_id = '.$this->villa_district_id.' or z2.zone_id = '.$this->villa_city_id.' or z3.zone_id = '.$this->villa_country_id.')
                  and vn_villas.villa_bedrooms >= '.$this->villa_bedrooms.'
                  order by zone_order, villa_bedrooms
                  limit 3';

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $villas[] = $rows['villa_id'];
        }

        return $villas;

    }

    public function getCollections($domain_id=0,$showedOnly=false){

        $ids = [];

        if( $domain_id > 0 )
            $sql = ' and vn_collections.collection_id in (select collection_id from vn_collections_domains where domain_id = '.$domain_id.')';

        if( $showedOnly )
            $sql .= ' and vn_collections.collection_showed = 1';

        $query = 'select vn_collections.collection_id
                  from vn_collections
                  join vn_villas_collections
                  on vn_collections.collection_id = vn_villas_collections.collection_id
                  where vn_villas_collections.villa_id = '.$this->villa_id.$sql;

        $this->exec($query);

        while( $rows = $this->getAssoc() ){
            $ids[] = $rows['collection_id'];
        }

        return $ids;
    }

    public function getDomains(){
        $ids = [];
        $query = 'select domain_id, is_hidden from vn_villas_domains where villa_id = '.$this->villa_id;
        $this->exec($query);
        while( $rows = $this->getAssoc() ){
            $ids[$rows['domain_id']] = $rows['is_hidden'];
        }
        return $ids;
    }

    public function getDomainsList(){
        $domains = [];
        $query = 'select domain_id, domain_name from vn_domains where zone_id in ('.$this->villa_district_id.','.$this->villa_city_id.','.$this->villa_country_id.') or domain_id = 1';
        $this->exec($query);
        while( $rows = $this->getAssoc() ){
            $domains[$rows['domain_id']] = $rows['domain_name'];
        }
        return $domains;
    }



}