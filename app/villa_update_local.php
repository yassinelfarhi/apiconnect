<?php

require_once '../include/boot.php';
require_once PATH_CLASS.'villas.php';
require_once PATH_CLASS.'photos.php';
require_once PATH_CLASS.'lngs.php';
require_once PATH_CLASS.'Notifications.php';
require_once PATH_CLASS.'errors.php';

function XMLtoArray($url) {
    $previous_value = libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $xml = file_get_contents($url);
    $dom->loadXml($xml);
    libxml_use_internal_errors($previous_value);
    if (libxml_get_errors()) {
        return [];
    }
    return DOMtoArray($dom);
}

function DOMtoArray($root) {

    $result = [];

    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }

    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if (in_array($child->nodeType,[XML_TEXT_NODE,XML_CDATA_SECTION_NODE])) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }

        }
        $groups = [];
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = DOMtoArray($child);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = DOMtoArray($child);
            }
        }
    }

    if( isset($result['ielv']) ) $result = $result['ielv'];

    return $result;
}

function xml_to_array($url) {

    $xmlstring = file_get_contents($url);
    $xml = simplexml_load_string($xmlstring);
    $json = json_encode($xml);
    $result = json_decode($json,TRUE);

    return $result;
}

function checkArray($array){
    $result = [];
    if( isset($array[0]) ) $result = $array;
    else $result[0] = $array;
    return $result;
}

function removeNewLines($string){
    return str_replace(["\n","\r"],'',$string);
}

function startsAndEndsWith($string,$character){
    return str_starts_with(trim($string),$character) && str_ends_with(trim($string),$character);
}

$api_source_id = 1;

$isNewVillaNotification = false;
$isNewElementNotification = false;
$isNewDescNotification = false;
$isNewPhotosNotification = false;

// SOURCE
$query = 'select * from vn_api_sources where api_source_id = '.$api_source_id;
$res = new db();
$res->exec($query);

if( $row = $res->getAssoc() ){
    $api_source_name = $row['api_source_url'];
    $api_source_url = $row['api_source_url'];
    $manager_id = $row['manager_id'];
    $lng_id = $row['lng_id'];
    $currency_id = $row['currency_id'];
    $villa_contract = $row['api_contract'];
    $villa_tva = $row['api_tva'];
    $villa_commission = $row['api_commission'];
    $villa_commission_tva = $row['api_commission_tva'];
    $villa_acompte1_rate = $row['api_acompte1_rate'];
    $villa_acompte2_rate = $row['api_acompte2_rate'];
    $villa_acompte2_days = $row['api_acompte2_days'];
    $villa_acompte3_rate = $row['api_acompte3_rate'];
    $villa_acompte3_days = $row['api_acompte3_days'];
}

// API ZONES
$query = 'select az.*, z1t.zone_name as district_name, z2t.zone_name as city_name, z2t.zone_slug as city_slug, z3.country_id as country_id
          from vn_api_zones as az 
          left join vn_zones as z1
          on az.zone_id = z1.zone_id
          left join vn_zones_trad as z1t
          on z1.zone_id = z1t.zone_id and z1t.langue_id = 1
          left join vn_zones as z2
          on z1.zone_parent_id = z2.zone_id
          left join vn_zones_trad as z2t
          on z2.zone_id = z2t.zone_id and z2t.langue_id = 1
          left join vn_zones as z3
          on z2.zone_parent_id = z3.zone_id
          where api_source_id = '.$api_source_id;

$res->exec($query);
$zones = [];
$api_zones = [];

while( $rows = $res->getAssoc() ){
    $zones[$rows['zone_name']] = [
        'zone_id' => $rows['zone_id'],
        'district_name' => $rows['district_name'],
        'city_name' => $rows['city_name'],
        'city_slug' => $rows['city_slug'],
        'country_id' => $rows['country_id']
    ];
}

// API AREAS
$query = 'select * from vn_api_areas where api_source_id = '.$api_source_id;
$res->exec($query);
$areas = [];
$api_areas = [];

while( $rows = $res->getAssoc() ){
    $areas[$rows['area_distance_name']] = [
        'id' => $rows['area_distance_id'],
        'disabled' => $rows['disabled_at'] != '' ? 1 : 0
    ];
}

// API EQUIPMENTS
$query = 'select * from vn_api_equipments where api_source_id = '.$api_source_id;
$res->exec($query);
$equips = [];
$api_equips = [];

while( $rows = $res->getAssoc() ){
    $equips[$rows['api_equipment_name']] = [
        'id' => $rows['villa_equipment_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
}

// API OPTIONS
$query = 'select * from vn_api_options where api_source_id = '.$api_source_id;
$res->exec($query);
$options = [];
$api_options = [];
$matched_options = [];

while( $rows = $res->getAssoc() ){
    $options[$rows['api_option_name']] = [
        'id' => $rows['villa_option_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
    if( $rows['villa_option_id'] > 0 ) $matched_options[] = $rows['villa_option_id'];
}

// API CONDITIONS
$query = 'select * from vn_api_conditions where api_source_id = '.$api_source_id;
$res->exec($query);
$conditions = [];
$api_conditions = [];
$matched_conditions = [];

while( $rows = $res->getAssoc() ){
    $conditions[$rows['api_condition_name']] = [
        'id' => $rows['villa_condition_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
    if( $rows['villa_condition_id'] > 0 ) $matched_conditions[] = $rows['villa_condition_id'];
}
$matched_conditions = array_unique($matched_conditions);

// API BEDROOMS ACCESS
$query = 'select * from vn_api_beds_accesses where api_source_id = '.$api_source_id;
$res->exec($query);
$views = [];
$api_views = [];

while( $rows = $res->getAssoc() ){
    $views[$rows['api_bed_access_name']] = [
        'id' => $rows['bed_access_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
}

// API BEDROOMS EQUIPS
$query = 'select * from vn_api_beds_equips where api_source_id = '.$api_source_id;
$res->exec($query);
$bequips = [];
$api_bequips = [];

while( $rows = $res->getAssoc() ){
    $bequips[$rows['api_bed_equip_name']] = [
        'id' => $rows['bed_equip_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
}

// API BEDS SIZES
$query = 'select * from vn_api_beds_sizes where api_source_id = '.$api_source_id;
$res->exec($query);
$sizes = [];
$api_sizes = [];

while( $rows = $res->getAssoc() ){
    $sizes[$rows['api_bed_size']] = [
        'id' => $rows['bed_bw_id'],
        'disable' => $rows['disabled_at'] != '' ? 1 : 0
    ];
}


// API STATUS
$query = 'select * from vn_api_dispos where api_source_id = '.$api_source_id;
$res->exec($query);
$status = [];
$api_status = [];
while( $rows = $res->getAssoc()){
    $status[$rows['status_name']] = [
        'is_booked'=>$rows['is_booked'],
        'is_option'=>$rows['is_option']
    ];
}

$lngs = lngs::getCodes([1,2]);
$items = [];

// FILL ITEMS ARRAY
foreach( $lngs as $lng_id=>$lng_code ){

    $flow = XMLtoArray($api_source_url.'?lang='.$lng_code);
    $villas = $flow['villa'];

    foreach( $villas as $villa ){

        $id = $villa['id'];

        $description = [];
        $description['lng_id'] = $lng_id;
        $description['text'] = !empty($villa['description']) ? $villa['description'] : '';

        $key = array_search($id,array_column($items,'id'));

        if( $key !== false ){
            $items[$key]['descriptions'][] = $description;
        }
        else{
            $item = [];
            $item['id'] = $id;
            $item['updated_at'] = $villa['updated_at'];
            $item['descriptions'][] = $description;
            $items[] = $item;
        }

    }

}

//$idApi = 1324;
//$idApi = 956;
//$idApi = 1212;
//$idApi = 1793;
//$idApi = 1881;
//$idApi = 1977;
//$idApi = 1975;
//$idApi = 939;
//$idApi = 1674;
//$idApi = 1428;

/*$key = array_search($idApi,array_column($items,'id'));
$item = $items[$key];
$items = [];
$items[] = $item;*/

// VILLAS IN VN
/*$ids = array_column($items,'id');
$items2 = [];
$query = 'select api_villa_id from vn_villas where api_villa_id > 0';
$res->exec($query);
while( $rows = $res->getAssoc() ){
    $key = array_search($rows['api_villa_id'],$ids);
    $items2[] = $items[$key];
}
$items = $items2;*/


$total = count($items);
$iLoop = 0;

// LOOP VILLAS
foreach( $items as $item ){

    $api_villa_id = $item['id'];
    $villa_id = 0;
    $updated_at = toSpecialDate(strtotime($item['updated_at']));
    $descriptions = $item['descriptions'];
    $to_update = 0;

    $iLoop++;
    echo $iLoop.'/'.$total;
    echo "\n";
    echo 'ID : '.$api_villa_id;
    echo "\n";

    $query = 'select 
              v.villa_id, v.villa_slug, v.api_updated_at, v.api_to_update, z2t.zone_slug
              from vn_villas as v 
              join vn_zones as z1
              on v.zone_id = z1.zone_id
              join vn_zones as z2
              on z1.zone_parent_id = z2.zone_id
              join vn_zones_trad as z2t
              on z2.zone_id = z2t.zone_id and z2t.langue_id = '.$lng_id.'
              where v.api_villa_id = '.$api_villa_id.' and v.api_source_id = '.$api_source_id;

    $res->exec($query);

    $link = 'http://www.icietlavillas.com/api/4tnwy87mncoplys/villa.xml/'.$api_villa_id;

    if( $result = $res->getAssoc() ){

        // UPDATE VILLA
        if( $updated_at > $result['api_updated_at'] or $result['api_to_update'] == 1 ){

            $villa_id = $result['villa_id'];

            echo 'UPDATE VILLA ID : '.$villa_id."\n";

            $current_slug = $result['villa_slug'];
            $zone_slug = $result['zone_slug'];
            $data = XMLtoArray($link);
            $villa = $data['villa'];

            $villa_name = $villa['title'];
            $villa_slug = makeSlugs($villa_name);
            $villa_latitude = !empty($villa['latitude']) ? $villa['latitude'] : 0;
            $villa_longitude = !empty($villa['longitude']) ? $villa['longitude'] : 0;
            $villa_bedrooms = $villa['bedrooms'];
            $villa_baths = $villa['bathrooms'];
            $villa_occupancy_max = $villa['max_occupancy'];


            $query = 'update vn_villas set 
                      villa_private_name = "'.$villa_name.'", villa_public_name = "'.$villa_name.'", villa_slug = "'.$villa_slug.'"
                      villa_occupancy = '.$villa_occupancy_max.', villa_occupancy_max = '.$villa_occupancy_max.', villa_bedrooms = '.$villa_bedrooms.',
                      villa_baths = '.$villa_baths.', villa_latitude = '.$villa_latitude.', villa_longitude = '.$villa_longitude.',
                      where villa_id = '.$villa_id;

            $res->exec($query);

            if( $res->affectedRows() > 0 ){

                if( $current_slug != $villa_slug ){
                    $query = 'insert into vn_villas_slugs(villa_id,villa_slug,villa_slug_time) values('.$villa_id.',"'.$villa_slug.'","'.time().'")';
                    $res->exec($query);
                }

            }
            else{
                $error = new errors();
                $error->type_id = 4;
                $error->error_code = $api_source_name;
                $error->error_msg = $res->getError();
                $error->insert();
            }

            $query = 'update vn_villas set api_updated_at = '.$updated_at.' where villa_id = '.$villa_id;
            $res->exec($query);

        }

    }
    else{

        // INSERT NEW VILLA
        $data = XMLtoArray($link);
        $villa = $data['villa'];

        $api_zone = $villa['locations']['location'][0];

        if( array_key_exists($api_zone,$zones) ){

            $selected_zone = $zones[$api_zone];

            $zone_id = $selected_zone['zone_id'];
            $district_name = $selected_zone['district_name'];
            $zone_slug = $selected_zone['city_slug'];

            if( $zone_id > 0 ){

                $query = 'select city_id from vn_cities where city_name like "'.$district_name.'"';
                $res->exec($query);

                if( $row = $res->getAssoc() ){
                    $city_id = $row['city_id'];
                }
                else{
                    $query = 'insert into vn_cities(city_name) values("'.$district_name.'")';
                    $res->exec($query);
                    $city_id = $res->lastOID();
                }

                $villa_type = 1;
                $villa_state = 4;
                $country_id = $selected_zone['country_id'];
                $villa_zip = '';
                $villa_address = $district_name;
                $villa_name = $villa['title'];
                $villa_slug = makeSlugs($villa_name);
                $villa_latitude = !empty($villa['latitude']) ? $villa['latitude'] : 0;
                $villa_longitude = !empty($villa['longitude']) ? $villa['longitude'] : 0;
                $villa_bedrooms = $villa['bedrooms'];
                $villa_baths = $villa['bathrooms'];
                $villa_occupancy_max = $villa['max_occupancy'];

                $code = generateCalendarCode(12);

                $query = 'insert into vn_villas(api_source_id,api_villa_id,api_to_update,api_updated_at,villa_time,manager_id,villa_private_name,villa_public_name,villa_slug,villa_type_id,villa_state_id,currency_id,villa_occupancy,
                                                villa_occupancy_max,villa_bedrooms,villa_baths,zone_id,country_id,city_id,villa_zip,villa_address,villa_latitude,villa_longitude,villa_calendar_code,
                                                villa_contract,villa_tva,villa_commission,villa_commission_tva,villa_acompte1_rate,villa_acompte2_rate,villa_acompte2_days,villa_acompte3_rate,villa_acompte3_days)
                          values('.$api_source_id.','.$api_villa_id.',1,"'.$updated_at.'","'.time().'",'.$manager_id.',"'.$villa_name.'","'.$villa_name.'","'.$villa_slug.'",'.$villa_type.','.$villa_state.','.$currency_id.','.$villa_occupancy_max.',
                          '.$villa_occupancy_max.','.$villa_bedrooms.','.$villa_baths.','.$zone_id.','.$country_id.','.$city_id.',"'.$villa_zip.'","'.$villa_address.'",'.$villa_latitude.','.$villa_longitude.',"'.$code.'",
                          '.$villa_contract.','.$villa_tva.','.$villa_commission.','.$villa_commission_tva.','.$villa_acompte1_rate.','.$villa_acompte2_rate.','.$villa_acompte2_days.','.$villa_acompte3_rate.','.$villa_acompte3_days.')';

                //echo $query."\n";

                $res->exec($query);

                if( $res->affectedRows() > 0 ){

                    $isNewVillaNotification = true;

                    $villa_id = $res->lastOID();

                    echo 'INSERT NEW VILLA ID : '.$villa_id."\n";

                    $query = 'insert into vn_villas_slugs(villa_id,villa_slug,villa_slug_time) values('.$villa_id.',"'.$villa_slug.'","'.time().'")';
                    $res->exec($query);

                    $query = 'insert into vn_villas_domains(villa_id,domain_id,is_hidden) values('.$villa_id.',1,0)';
                    $res->exec($query);

                }
                else{
                    $error = new errors();
                    $error->type_id = 4;
                    $error->error_code = $api_source_name;
                    $error->error_msg = $res->getError();
                    $error->insert();
                }

            }

        }
        else{
            if( trim($api_zone) != '' ){
                $api_zones[] = trim($api_zone);
                $isNewElementNotification = true;
            }
        }

    }

    if( $villa_id > 0 ){

        $villa_obj = new villas();
        $villa_obj->villa_id = $villa_id;
        $villa_obj->boot_domain_id = 1;

        $isNewDescUpdate = false;

        //goto prices;
        //goto rooms;

        /** DESCRIPTION **/
        foreach( $descriptions as $description ){

            $lng_id = $description['lng_id'];
            $text = $description['text'] != '' ? strip_tags($description['text']) : '';

            $villa_obj->lng_id = $lng_id;
            $villa_obj->getDomainTrad(false);

            $desc = $villa_obj->villa_description;
            $desc_api_old = $villa_obj->villa_description_api_old;
            $desc_api_new = $villa_obj->villa_description_api_new;
            $villa_obj->villa_description_api_new_updated = '';

            $new_desc = clearString($desc);
            $new_desc_api_old = clearString($desc_api_old);
            $new_desc_api_new = clearString($desc_api_new);
            $diff1 = strcmp($new_desc,$new_desc_api_old);
            $diff2 = strcmp($new_desc_api_old,$new_desc_api_new);

            if( $desc === '' || $desc === NULL || ( $desc != '' && $diff1 === 0 ) ){
                $villa_obj->villa_description = $text;
                $villa_obj->villa_description_api_old = $text;
            }
            else{
                $new_text = clearString($text);
                $diff_text = strcmp($new_text,$new_desc_api_old);
                if( $diff_text !== 0 ){
                    // NOTIFICATION
                    $isNewDescNotification = true;
                    $isNewDescUpdate = true;
                    $villa_obj->villa_description_api_new_updated = toSpecialDate();
                }
            }

            $villa_obj->villa_description_api_new = $text;

            $villa_obj->updateDomainTrad(1,$lng_id);

        }

        if( $isNewDescUpdate ){
            $query = 'update vn_villas set villa_is_api_desc = 1 where villa_id = '.$villa_id;
            $res->exec($query);
        }

        /** DISTANCES **/
        $api_locations = checkArray($villa['locations']['location']);
        $api_locations = array_slice($api_locations,2);

        $query = 'delete from vn_villas_distances_ids where villa_id = '.$villa_id;
        $res->exec($query);

        $ids_inserts = [];

        foreach( $api_locations as $api_location ) {

            $parts = explode(':',$api_location);

            $location = trim($parts[0]);
            $time = trim(floatval($parts[1]));

            if( array_key_exists($location,$areas) ){

                if( $areas[$location]['disable'] == 0 ){
                    if( $areas[$location]['id'] > 0 ){
                        if( $time > 0 ) $ids_inserts[] = '('.$villa_id.','.$areas[$location]['id'].','.$time.',2,3)';
                    }
                    else{
                        $to_update = 1;
                    }
                }

            }
            else{
                if( trim($location) != '' ){
                    $api_areas[] = $location;
                    $isNewElementNotification = true;
                    $to_update = 1;
                }
            }

        }

        if( count($ids_inserts) > 0 ){

            $query = 'insert into vn_villas_distances_ids(villa_id,area_distance_id,villa_distance_time,villa_distance_time_unite,villa_distance_time_per) 
                      values'.implode(',',$ids_inserts);

            $res->exec($query);
        }

        /** OPTIONS **/

        // CLEAR VILLA OPTIONS
        $query = 'delete from vn_villas_options_ids where villa_id = '.$villa_id;
        $res->exec($query);

        // INSERT API DEFAULT OPTIONS
        $query = 'insert into vn_villas_options_ids(villa_id,villa_option_id,villa_option_inclus,villa_option_villa,villa_option_provider_id,villa_option_term_number1,villa_option_term_number2,
                                                    villa_option_term_same,villa_option_currency_id,villa_option_from,villa_option_price,villa_option_unit_id)
                  select 
                  '.$villa_id.', villa_option_id, api_option_type, api_option_for, api_option_provider_id, api_option_term_number1, api_option_term_number2, api_option_term_same, 
                  api_option_currency_id, api_option_from, api_option_price, api_option_unit_id
                  from vn_api_options_ids 
                  where api_source_id = '.$api_source_id.' and villa_option_id not in ('.implode(',',$matched_options).')';

        $res->exec($query);

        $api_services = checkArray($villa['services']['service']);

        $ids_inserts = [];

        foreach( $api_services as $api_service ){

            $explodes = [];

            if( strpos($api_service,'*') !== false ){
                $explodes = explode("*",trim($api_service,'*'));
            }
            else{
                $explodes[] = $api_service;
            }

            foreach( $explodes as $explode ){

                $explode = trim(removeNewLines($explode));

                if( array_key_exists($explode,$options) ){

                    if( $options[$explode]['disable'] == 0 ){
                        if( $options[$explode]['id'] > 0 ){
                            $ids_inserts[] = '('.$villa_id.','.$options[$explode]['id'].',1)';
                        }
                        else{
                            $to_update = 1;
                        }
                    }

                }
                else{
                    if( trim($explode) != '' ){
                        $api_options[] = $explode;
                        $isNewElementNotification = true;
                        $to_update = 1;
                    }
                }

            }

        }

        if( count($ids_inserts) > 0 ){
            $query = 'insert into vn_villas_options_ids(villa_id,villa_option_id,villa_option_inclus) values'.implode(',',$ids_inserts);
            $res->exec($query);
        }

        /** CONDITIONS **/

        // CLEAR VILLA CONDITIONS
        $query = 'delete from vn_villas_conditions_ids where villa_id = '.$villa_id;
        $res->exec($query);

        // INSERT API DEFAULT CONDITIONS
        $query = 'insert into vn_villas_conditions_ids(villa_id,condition_id,condition_value)
                  select '.$villa_id.', condition_key, condition_value 
                  from vn_api_conditions_ids 
                  where api_source_id = '.$api_source_id.' and ( 
                        condition_key <> 1 or 
                        ( 
                            condition_key = 1 and condition_value not in ('.implode(',',$matched_conditions).')
                        ) 
                  )';

        $res->exec($query);

        // INSERT API CONDITIONS
        $api_restrictions = checkArray($villa['restrictions']['restriction']);

        $ids_inserts = [];

        foreach( $api_restrictions as $api_restriction ) {

            if( array_key_exists($api_restriction,$conditions) ){

                if( $conditions[$api_restriction]['disable'] == 0 ){
                    if( $conditions[$api_restriction]['id'] > 0 ){
                        $ids_inserts[] = '('.$villa_id.',1,'.$conditions[$api_restriction]['id'].')';
                    }
                    else{
                        $to_update = 1;
                    }
                }

            }
            else{
                if( trim($api_restriction) != '' ){
                    $api_conditions[] = $api_restriction;
                    $isNewElementNotification = true;
                    $to_update = 1;
                }
            }

        }

        if( count($ids_inserts) > 0 ){
            $query = 'insert into vn_villas_conditions_ids(villa_id,condition_id,condition_value) values'.implode(',',$ids_inserts);
            $res->exec($query);
        }

        rooms:

        /** ROOMS **/
        $villa_rooms = [];
        $query = 'select * from vn_villas_beds where villa_id = '.$villa_id;
        $res->exec($query);
        while( $rows = $res->getAssoc() ){
            $villa_rooms[] = $rows['villa_bed_id'];
        }

        $api_villa_facilities = [];
        $iBedroom = 0;

        $api_rooms = checkArray($villa['rooms']['room']);

        foreach( $api_rooms as $api_room ){

            $attributes = $api_room['@attributes'];

            // ROOM EQUIPS
            $api_room_facilities = [];
            $api_room_others = [];
            $bed_name = '';
            if( $api_room['equipment'] != '' ) $api_room_facilities = explode(',',$api_room['equipment']);
            if( $api_room['other'] != '' ){
                $api_room_others = explode('*',$api_room['other']);
                foreach( $api_room_others as $api_room_other ){
                    if( trim($api_room_other) != '' ){
                        if( startsAndEndsWith($api_room_other,'"') ) $bed_name = trim(str_replace('"','',$api_room_other));
                        else $api_room_facilities[] = trim($api_room_other);
                    }
                }
            }

            if( $attributes['type'] == 'Bedroom' ){

                if( $villa_rooms[$iBedroom] > 0 ){
                    $bed_id = $villa_rooms[$iBedroom];
                    $query = 'update vn_villas_beds set bed_name = "'.$bed_name.'" where villa_bed_id = '.$bed_id;
                    $res->exec($query);
                }
                else{
                    $query = 'insert into vn_villas_beds(villa_id,created_at,bed_name,bed_type_id) values('.$villa_id.',"'.toSpecialDate().'","'.$bed_name.'",1)';
                    $res->exec($query);
                    $bed_id = $res->lastOID();
                }

                if( $bed_id > 0 ){

                    $query = 'delete from vn_villas_beds_equipments where villa_bed_id = '.$bed_id;
                    $res->exec($query);

                    // BEDROOM VIEW
                    $ids_inserts = [];

                    if( array_key_exists($api_room['view'],$views) ){

                        if( $views[$api_room['view']]['disable'] == 0 ){
                            if( $views[$api_room['view']]['id'] > 0 ){
                                $ids_inserts[] = '('.$bed_id.','.$views[$api_room['view']]['id'].')';
                            }
                            else{
                                $to_update = 1;
                            }
                        }

                    }
                    else{
                        if( trim($api_room['view']) != '' ){
                            $api_views[] = $api_room['view'];
                            $isNewElementNotification = true;
                            $to_update = 1;
                        }
                    }

                    foreach( $api_room_facilities as $api_room_facility ) {

                        $api_room_facility = trim($api_room_facility);

                        if( array_key_exists($api_room_facility,$bequips) ){

                            if( $bequips[$api_room_facility]['disable'] == 0 ){
                                if( $bequips[$api_room_facility]['id'] > 0 ){
                                    $ids_inserts[] = '('.$bed_id.','.$bequips[$api_room_facility]['id'].')';
                                }
                                else{
                                    $to_update = 1;
                                }
                            }

                        }
                        else{
                            if( trim($api_room_facility) != '' ){
                                $api_bequips[] = $api_room_facility;
                                $isNewElementNotification = true;
                                $to_update = 1;
                            }
                        }

                    }

                    if( count($ids_inserts) > 0 ){
                        $query = 'insert into vn_villas_beds_equipments(villa_bed_id,bed_equipment_id) values'.implode(',',$ids_inserts);
                        $res->exec($query);
                    }

                    // BEDS

                    $query = 'select * from vn_villas_beds_bs where villa_bed_id = '.$bed_id;
                    $res->exec($query);

                    $beds = [];
                    $bed_size_id = 0;

                    while( $rows = $res->getAssoc() ){
                        $beds[] = $rows['villa_bed_b_id'];
                    }

                    if( array_key_exists($api_room['bed_size'],$sizes) ){

                        if( $sizes[$api_room['bed_size']]['disable'] == 0 ){
                            if( $sizes[$api_room['bed_size']]['id'] > 0 ){
                                $bed_size_id = $sizes[$api_room['bed_size']]['id'];
                            }
                            else{
                                $to_update = 1;
                            }
                        }

                    }
                    else{
                        if( trim($api_room['bed_size']) != '' ){
                            $api_sizes[] = $api_room['bed_size'];
                            $isNewElementNotification = true;
                            $to_update = 1;
                        }
                    }

                    if( count($beds) > 0 ){
                        $query = 'update vn_villas_beds_bs set bed_bw_id = '.$bed_size_id.' where villa_bed_b_id = '.$beds[0];
                    }
                    else{
                        $query = 'insert into vn_villas_beds_bs(villa_bed_id,b_number,bed_bca_id,bed_bw_id) values('.$bed_id.',1,1,'.$bed_size_id.')';
                    }

                    $res->exec($query);

                    $iBedroom++;

                }

            }
            else{
                $api_villa_facilities = array_merge($api_villa_facilities,$api_room_facilities);
            }

        }

        // DELETE
        if( count($villa_rooms) > $iBedroom ){
            $villa_rooms = array_slice($villa_rooms,$iBedroom);
            $query = 'delete from vn_villas_beds where villa_id = '.$villa_id.' and villa_bed_id in ('.implode(',',$villa_rooms).')';
            $res->exec($query);
        }

        facilities:

        /** FACILITIES **/
        $api_facilities = checkArray($villa['facilities']['facility']);
        $pool = $villa['pools']['pool'];
        if( !empty($pool) ){
            $api_facilities[] = strpos($pool['description'],'heated') !== false ? 'heated pool' : 'pool';
        }

        $api_facilities = array_unique(array_merge($api_facilities,$api_villa_facilities));

        $query = 'delete from vn_villas_equipments_ids where villa_id = '.$villa_id;
        $res->exec($query);

        $ids_inserts = [];

        foreach( $api_facilities as $api_facility ) {

            $explodes = [];

            if( strpos($api_facility,'*') !== false ){
                $explodes = explode("*",trim($api_facility,'*'));
            }
            else{
                $explodes[] = $api_facility;
            }

            foreach( $explodes as $explode ){

                $explode = trim(removeNewLines($explode));

                if( array_key_exists($explode,$equips) ){

                    if( $equips[$explode]['disable'] == 0 ){
                        if( $equips[$explode]['id'] > 0 ){
                            $ids_inserts[] = '('.$villa_id.','.$equips[$explode]['id'].')';
                        }
                        else{
                            $to_update = 1;
                        }
                    }

                }
                else{
                    if( trim($explode) != '' ){
                        $api_equips[] = $explode;
                        $isNewElementNotification = true;
                        $to_update = 1;
                    }
                }
            }

        }

        $ids_inserts = array_unique($ids_inserts);

        if( count($ids_inserts) > 0 ){
            $query = 'insert into vn_villas_equipments_ids(villa_id,villa_equipment_id) values'.implode(',',$ids_inserts);
            $res->exec($query);
        }

        /** PHOTOS **/

        // VN PHOTOS
        $query = 'select villa_photo_id, photo_source_id from vn_villas_photos where villa_id = '.$villa_id.' and photo_source_id <> ""';
        $res->exec($query);
        $photos = [];
        $hasPhotos = false;
        while( $rows = $res->getAssoc() ){
            $photos[$rows['villa_photo_id']] = $rows['photo_source_id'];
            $hasPhotos = true;
        }

        $isUploadedPhoto = false;

        $api_photos = checkArray($villa['photos']['photo']);
        $uploads = [];
        foreach( $api_photos as $api_photo ){

            $photo_url = $api_photo['_value'];
            $attributes = $api_photo['@attributes'];
            $photo_index = $attributes['index'];
            $photo_width = $attributes['width'];
            $photo_height = $attributes['height'];

            $parts1 = explode('/',$photo_url);
            $last_part1 = array_pop($parts1);
            $parts2 = explode('.',$last_part1);
            $photo_source_id = $parts2[0];
            $photo_ext = $parts2[1];

            if( $photo_width >= 1200 ){

                // IF PHOTO ALREADY EXISTS
                if( ($key = array_search($photo_source_id, $photos)) !== false ){
                    //echo $key."\n";
                    unset($photos[$key]);
                }
                else{

                    $photo_name = $zone_slug.'-'.$villa_slug.'-'.uniqid(rand(),true);

                    $upload = [];
                    $upload['url'] = $photo_url;
                    $upload['name'] = $photo_name;
                    $upload['ext'] = $photo_ext;
                    $upload['source_id'] = $photo_source_id;
                    $uploads[] = $upload;

                }
            }

        }

        if( count($uploads) > 0 ){

            foreach( $uploads as $upload ){

                $photo_obj = new photos();
                $photo_obj->setVillaId($villa_id);
                $photo_obj->setName($upload['name']);
                $photo_obj->setExt($upload['ext']);
                $photo_obj->setSourceId($upload['source_id']);
                $photo_obj->setUploadMode('remote');

                if( $photo_obj->upload($upload['url']) ){

                    if( $photo_obj->add() ){
                        $photo_obj->resize('resize');
                        $photo_obj->resize(1920);
                        $photo_obj->resize(1366);
                        $photo_obj->resize(960);
                        $photo_obj->resize(750);
                        $isUploadedPhoto = true;
                    }

                }

            }

        }

        if( count($photos) > 0 ){
            foreach( $photos as $photo_id=>$photo_source){
                $photo_obj = photos::build($photo_id);
                $unlink = $photo_obj->delete();
            }
        }

        if( $hasPhotos && $isUploadedPhoto ){
            $isNewPhotosNotification = true;
        }

        //prices:

        /** PRICES **/
        $api_prices = checkArray($villa['prices']['price']);

        $query = 'delete from vn_villas_saisons where villa_id = '.$villa_id;
        $res->exec($query);

        $query = 'delete from vn_rates_plans where villa_id = '.$villa_id;
        $res->exec($query);

        $seasons = [];
        $plans = [];

        foreach( $api_prices as $api_price ){

            $attributes = $api_price['@attributes'];

            $season = $attributes['name'];
            $from = strtotime($attributes['from'].' + 12 hours');
            $to = strtotime($attributes['to'].' + 12 hours');

            if( array_key_exists($season,$seasons) ){
                $season_id = $seasons[$season];
            }
            else{
                $query = 'insert into vn_villas_saisons(villa_id,saison_name) values('.$villa_id.',"'.$season.'")';
                $res->exec($query);
                $season_id = $res->lastOID();
                if( $season_id > 0 ) $seasons[$season] = $season_id;
            }

            if( $season_id > 0 and $from <= $to ){

                $query = 'insert into vn_villas_periodes(villa_id,saison_id,periode_deb,periode_fin)
                          values('.$villa_id.','.$season_id.','.$from.','.$to.')';

                $res->exec($query);

                $bedroom_counts = checkArray($api_price['bedroom_count']);

                foreach( $bedroom_counts as $bedroom_count ){

                    $attrs = $bedroom_count['@attributes'];
                    $price = $bedroom_count['_value'];
                    $price = str_replace(['$',chr(194).chr(160),',',' '],['','','',''],$price);
                    $price = ceil(intval($price)/7);

                    $room = $attrs['bedroom'];

                    if( is_array($plans[$room]) ){
                        $plan_id = $plans[$room]['id'];
                        $min_price = $plans[$room]['min'];
                        $max_price = $plans[$room]['max'];
                        if( $price < $min_price ) $plans[$room]['min'] = $price;
                        if( $price > $max_price ) $plans[$room]['max'] = $price;
                    }
                    else{
                        $query = 'insert into vn_rates_plans(villa_id,created_at,room,night)
                                  values('.$villa_id.',"'.toSpecialDate().'",'.$room.',1)';
                        $res->exec($query);
                        $plan_id = $res->lastOID();
                        if( $plan_id > 0 ) $plans[$room] = ['id'=>$plan_id,'min'=>$price,'max'=>$price];
                    }

                    if( $plan_id > 0 ){

                        $query = 'insert into vn_rates_plans_seasons(rate_plan_id,season_id,season_price) values('.$plan_id.','.$season_id.','.$price.')';
                        $res->exec($query);

                        $inserts = [];

                        for( $iTime = $from ; $iTime <= $to ; $iTime += 86400 ){
                            $inserts[] = '('.$plan_id.','.$season_id.','.toSpecialDate($iTime,'none').','.$price.')';
                        }

                        if( $inserts > 0 ){

                            $inserts = array_chunk($inserts,100);

                            foreach( $inserts as $insert ){
                                $query  = 'insert into vn_rates(rate_plan_id,season_id,rate_date,rate_price) values';
                                $query .= implode(',',$insert);
                                $res->exec($query);
                            }
                        }

                    }

                }

            }

        }

        $query = 'delete from vn_villas_rooms where villa_id = '.$villa_id;
        $res->exec($query);

        $inserts = [];

        foreach( $plans as $room=>$plan ){
            $inserts[] = '('.$villa_id.','.$room.','.$plan['min'].','.$plan['max'].')';
        }

        if( $inserts > 0 ){
            $query = 'insert into vn_villas_rooms(villa_id,villa_room,villa_room_from,villa_room_to) values'.implode(',',$inserts);
            $res->exec($query);
        }

        /** AVAILABILITIES **/
        $today = mktime(12,0,0,date('m'),date('d'),date('Y'));
        $maxDay = strtotime('+18 months',$today);
        $villa_dispo_date = time();

        $query = 'delete from vn_villas_dispos where villa_id = '.$villa_id.' and villa_dispo_time >= '.$today.' and resa_id = 0 and villa_dispo_presa = 0';
        $res->exec($query);

        $periods = checkArray($villa['availability']['period']);

        foreach( $periods as $period ){

            $attributes = $period['@attributes'];
            $from = strtotime($attributes['from'].' + 12 hours');
            $to = strtotime($attributes['to'].' + 12 hours');

            if( $to >= $today ){

                $inserts = [];

                if( array_key_exists($period['status'],$status) ){

                    $array = $status[$period['status']];

                    if( $array['is_booked'] == 1 ){

                        $villa_dispo_option = $array['is_option'];

                        for( $time = $from; $time < $to; $time = strtotime('+1 day',$time)){
                            if( $time >= $today and $time <= $maxDay ) $inserts[] = '('.$villa_id.',"'.$time.'",0,"'.$villa_dispo_date.'",1,'.$villa_dispo_option.')';
                        }

                    }

                }
                else{
                    if( trim($period['status']) != '' ){
                        $api_status[] = $period['status'];
                        $isNewElementNotification = true;
                        $to_update = '1';
                    }

                }

                if( count($inserts) > 0 ){
                    $query = 'insert into vn_villas_dispos(villa_id,villa_dispo_time,villa_isdispo,villa_dispo_date,villa_dispo_api,villa_dispo_option) values'.implode(',',$inserts);
                    $res->exec($query);
                }

            }

        }

        // END OF THE SCRIPT AFTER ALL ITEMS ARE ADDED, IF THERE IS SOMETHING NOT MATCHED '$to_update' WILL BE 1, ELSE '$to_update' IS 0
        $query = 'update vn_villas set api_to_update = '.$to_update.' where villa_id = '.$villa_id;
        $res->exec($query);

    }

    if( $iLoop == 70 ) break;

}

if( count($api_zones) > 0 ){

    $api_zones = array_unique($api_zones);

    $inserts_zones = [];

    foreach( $api_zones as $api_zone ){
        $inserts_zones[] = '('.$api_source_id.',0,"'.$api_zone.'")';
    }

    $query  = 'insert into vn_api_zones(api_source_id,zone_id,zone_name) values';
    $query .= implode(',',$inserts_zones);
    $res->exec($query);

}

if( count($api_areas) > 0 ){

    $api_areas = array_unique($api_areas);

    $inserts_areas = [];

    foreach( $api_areas as $api_area ){
        $inserts_areas[] = '('.$api_source_id.',0,"'.$api_area.'")';
    }

    $query  = 'insert into vn_api_areas(api_source_id,area_distance_id,area_distance_name) values';
    $query .= implode(',',$inserts_areas);
    $res->exec($query);

}

if( count($api_equips) > 0 ){

    $api_equips = array_unique($api_equips);

    $inserts_equips = [];

    foreach( $api_equips as $api_equip ){
        $inserts_equips[] = '('.$api_source_id.',0,"'.$api_equip.'")';
    }

    $query  = 'insert into vn_api_equipments(api_source_id,villa_equipment_id,api_equipment_name) values';
    $query .= implode(',',$inserts_equips);
    $res->exec($query);

}

if( count($api_options) > 0 ){

    $api_options = array_unique($api_options);

    $inserts_options = [];

    foreach( $api_options as $api_option ){
        $inserts_options[] = '('.$api_source_id.',0,"'.$api_option.'")';
    }

    $query  = 'insert into vn_api_options(api_source_id,villa_option_id,api_option_name) values';
    $query .= implode(',',$inserts_options);
    $res->exec($query);

}

if( count($api_conditions) > 0 ){

    $api_conditions = array_unique($api_conditions);

    $inserts_conditions = [];

    foreach( $api_conditions as $api_condition ){
        $inserts_conditions[] = '('.$api_source_id.',0,"'.$api_condition.'")';
    }

    $query  = 'insert into vn_api_conditions(api_source_id,villa_condition_id,api_condition_name) values';
    $query .= implode(',',$inserts_conditions);
    $res->exec($query);

}

if( count($api_views) > 0 ){

    $api_views = array_unique($api_views);

    $inserts_views = [];

    foreach( $api_views as $api_view ){
        $inserts_views[] = '('.$api_source_id.',0,"'.$api_view.'")';
    }

    $query  = 'insert into vn_api_beds_accesses(api_source_id,bed_access_id,api_bed_access_name) values';
    $query .= implode(',',$inserts_views);
    $res->exec($query);

}

if( count($api_bequips) > 0 ){

    $api_bequips = array_unique($api_bequips);

    $inserts_bequips = [];

    foreach( $api_bequips as $api_bequip ){
        $inserts_bequips[] = '('.$api_source_id.',0,"'.$api_bequip.'")';
    }

    $query  = 'insert into vn_api_beds_equips(api_source_id,bed_equip_id,api_bed_equip_name) values';
    $query .= implode(',',$inserts_bequips);
    $res->exec($query);

}

if( count($api_sizes) > 0 ){

    $api_sizes = array_unique($api_sizes);

    $inserts_sizes = [];

    foreach( $api_sizes as $api_size ){
        $inserts_sizes[] = '('.$api_source_id.',0,"'.$api_size.'")';
    }

    $query  = 'insert into vn_api_beds_sizes(api_source_id,bed_bw_id,api_bed_size) values';
    $query .= implode(',',$inserts_sizes);
    $res->exec($query);

}

if( count($api_status) > 0 ){

    $api_status = array_unique($api_status);

    $inserts_status = [];

    foreach( $api_status as $api_statu ){
        $inserts_status[] = '('.$api_source_id.',"'.$api_statu.'",0,0)';
    }

    $query  = 'insert into vn_api_dispos(api_source_id,status_name,is_booked,is_option) values';
    $query .= implode(',',$inserts_status);
    eko($query);
    $res->exec($query);

}

$notificationsModels = [];

if( $isNewVillaNotification ) $notificationsModels[] = 7;
if( $isNewElementNotification ) $notificationsModels[] = 6;
if( $isNewDescNotification ) $notificationsModels[] = 4;
if( $isNewPhotosNotification ) $notificationsModels[] = 5;

foreach( $notificationsModels as $notificationsModel ){
    $notification = new Notifications();
    $notification->setModel($notificationsModel);
    $notification->insert();
}

?>