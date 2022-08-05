<?php

// SHOW ERROR
function eko($variable,$exit=1,$vardump=true){

    if( $_COOKIE['no_tracking'] == 1 ){
        if( $vardump ) var_dump($variable);
        else echo $variable;
        if( $exit ) exit();
    }

}

// GET ENTRIES
function getEntries($entries,$langue_id){

    $requete = 'select entry_id, entry_traduction from vn_entries_trad where entry_id in ('.$entries.') and langue_id = '.$langue_id;
    $res = new db();
    $res->exec($requete);

    while( $ligne = $res->getAssoc() ){
        $tab[$ligne['entry_id']] = html_entity_decode($ligne['entry_traduction']);
    }

    return $tab;

}

// PRICE FORMAT
function priceFormat($montant,$decimale=2,$point='.',$th=' '){

    if( is_numeric($montant) ){
        $montant = str_replace(" ","",$montant);
        if( $decimale == 0 ) $montant = ceil($montant);
        $montant = number_format($montant,$decimale,$point,$th);
    }

    return $montant;

}

// LAST SEARCH
function getLastSearchCookie($id_langue){

    global $boot_code;
    global $boot_domain_id;

    $last_search = '';

    if( count($_COOKIE['last_search']) > 0 ) {

        $getext = getEntries('1180,1092,1093,1181,948,593',$id_langue);

        $last_search  = '';
        $last_search .= '<div class="last-search">';
        $last_search .= '<h3>'.$getext[1180].' <a class="last-search-close"><i class="fa fa-times"></i> '.$getext[593].'</a></h3>';
        $last_search .= '<ul>';

        foreach ($_COOKIE['last_search'] as $cookie) {

            $cookie = json_decode($cookie);

            $cookie_id = $cookie[0];
            $cookie_type = $cookie[1];
            $cookie_checkin = $cookie[2];
            $cookie_checkout = $cookie[3];
            $cookie_adults = $cookie[4];
            $cookie_children = $cookie[5] > 0 ? $cookie[5] : 0;
            $cookie_babies = $cookie[6] > 0 ? $cookie[6] : 0;
            $cookie_travelers = $cookie_adults + $cookie_children + $cookie_babies;
            $cookie_travelers_text = $cookie_travelers . ' ' . ($cookie_travelers == 1 ? $getext[1092] : $getext[1093]);

            if ($cookie_checkin != '' and $cookie_checkout != '') {
                $cookie_dates = $cookie_checkin . ' - ' . $cookie_checkout;
            } else {
                $cookie_dates = $getext[1181];
            }

            if ($cookie_type == 'zone') {

                $requete_cookie_zone = 'select 
                                        vn_zones.zone_id, vn_zones.zone_level, vn_zones_trad.zone_name, vn_zones_trad.zone_slug, 
                                        vn_zones_parents_trad.zone_name as zone_parent_name
                                        from vn_zones
                                        join vn_zones_trad
                                        on vn_zones.zone_id = vn_zones_trad.zone_id and vn_zones_trad.langue_id = ' . $id_langue . '
                                        left join vn_zones as vn_zones_parents
                                        on vn_zones.zone_parent_id = vn_zones_parents.zone_id and vn_zones_parents.zone_level > 0
                                        left join  vn_zones_trad as vn_zones_parents_trad
                                        on vn_zones_parents.zone_id = vn_zones_parents_trad.zone_id and vn_zones_parents_trad.langue_id = ' . $id_langue . '
                                        where vn_zones.zone_id = ' . $cookie_id;

                $res_cookie_zone = new db();
                $res_cookie_zone->exec($requete_cookie_zone);

                if ($ligne_cookie_zone = $res_cookie_zone->getAssoc()) {

                    $cookie_zone_name = $ligne_cookie_zone['zone_parent_name'] != '' ? $ligne_cookie_zone['zone_parent_name'] . ', ' . $ligne_cookie_zone['zone_name'] : $ligne_cookie_zone['zone_name'];
                    $cookie_zone_slug = $ligne_cookie_zone['zone_slug'];

                    if ($ligne_cookie_zone['zone_level'] < 3) {
                        $cookie_zone_name .= ', (' . $getext[948] . ')';
                    }

                    $last_search .= '<li>';
                    $last_search .= '<a href="/'.$boot_code.'goto/' . $cookie_zone_slug . '/?type=' . $cookie_type . '&checkin=' . $cookie_checkin . '&checkout=' . $cookie_checkout . '&adults=' . $cookie_adults . '&children=' . $cookie_children . '&babies=' . $cookie_babies . '">';
                    $last_search .= '<strong><i class="fa fa-map-marker"></i> ' . $cookie_zone_name . '</strong>';
                    $last_search .= '<span>' . $cookie_dates . ', ' . $cookie_travelers_text . '</span>';
                    $last_search .= '</a>';
                    $last_search .= '</li>';

                }

            }
            else if ($cookie_type == 'villa') {

                $requete_cookie_villa = 'select vn_villas.villa_id, villa_public_name, villa_slug, z2t.zone_name as ville, z3t.zone_name as pays
                                         from vn_villas
                                         join vn_villas_domains
                                         on vn_villas.villa_id = vn_villas_domains.villa_id 
                                         and vn_villas_domains.domain_id = '.$boot_domain_id.' and vn_villas_domains.is_hidden = 0
                                         join vn_zones as z1
                                         on vn_villas.zone_id = z1.zone_id
                                         join vn_zones_trad as z1t
                                         on z1.zone_id = z1t.zone_id and z1t.langue_id = ' . $id_langue . '
                                         join vn_zones as z2
                                         on z1.zone_parent_id = z2.zone_id
                                         join vn_zones_trad as z2t
                                         on z2.zone_id = z2t.zone_id and z2t.langue_id = ' . $id_langue . '
                                         join vn_zones as z3
                                         on z2.zone_parent_id = z3.zone_id
                                         join vn_zones_trad as z3t
                                         on z3.zone_id = z3t.zone_id and z3t.langue_id = ' . $id_langue . '
                                         where vn_villas.villa_id = ' . $cookie_id . ' and villa_state_id = 1';

                $res_cookie_villa = new db();
                $res_cookie_villa->exec($requete_cookie_villa);

                if ($ligne_cookie_villa = $res_cookie_villa->getAssoc()) {

                    $cookie_villa_name = $ligne_cookie_villa['villa_public_name'];
                    $cookie_villa_slug = $ligne_cookie_villa['villa_slug'];
                    $cookie_villa_ville = $ligne_cookie_villa['ville'];
                    $cookie_villa_pays = $ligne_cookie_villa['pays'];

                    $last_search .= '<li>';
                    $last_search .= '<a href="/'.$boot_code.'goto/' . $cookie_villa_slug . '/?type=' . $cookie_type . '&checkin=' . $cookie_checkin . '&checkout=' . $cookie_checkout . '&adults=' . $cookie_adults . '&children=' . $cookie_children . '&babies=' . $cookie_babies . '">';
                    $last_search .= '<strong><i class="fa fa-home"></i> ' . $cookie_villa_name . ', ' . $cookie_villa_ville . ', ' . $cookie_villa_pays . '</strong>';
                    $last_search .= '<span>' . $cookie_dates . ', ' . $cookie_travelers_text . '</span>';
                    $last_search .= '</a>';
                    $last_search .= '</li>';

                }

            }
            else if ($cookie_type == 'collection') {

                $requete_cookie_collection = 'select vn_collections.collection_id, vn_collections_trad.collection_name, vn_collections_trad.collection_slug
                                              from vn_collections
                                              join vn_collections_domains
                                              on vn_collections.collection_id = vn_collections_domains.collection_id and vn_collections_domains.domain_id = '.$boot_domain_id.'
                                              join vn_collections_trad
                                              on vn_collections.collection_id = vn_collections_trad.collection_id and vn_collections_trad.langue_id = '.$id_langue.'
                                              where vn_collections.collection_id = ' . $cookie_id;

                //eko($requete_cookie_collection);

                $res_cookie_collection = new db();
                $res_cookie_collection->exec($requete_cookie_collection);

                if ($ligne_cookie_collection = $res_cookie_collection->getAssoc()) {

                    $cookie_collection_name = $ligne_cookie_collection['collection_name'];
                    $cookie_collection_slug = $ligne_cookie_collection['collection_slug'];

                    $last_search .= '<li>';
                    $last_search .= '<a href="/'.$boot_code.'goto/' . $cookie_collection_slug . '/?type=' . $cookie_type . '&checkin=' . $cookie_checkin . '&checkout=' . $cookie_checkout . '&adults=' . $cookie_adults . '&children=' . $cookie_children . '&babies=' . $cookie_babies . '">';
                    $last_search .= '<strong><i class="fa fa-list"></i> ' . $cookie_collection_name . '</strong>';
                    $last_search .= '<span>' . $cookie_dates . ', ' . $cookie_travelers_text . '</span>';
                    $last_search .= '</a>';
                    $last_search .= '</li>';

                }

            }

        }

        $last_search .= '</ul>';
        $last_search .= '</div>';

    }

    return $last_search;

}

// HTTP FILE EXISTS
function url_file_exists($url){
    $headers = get_headers($url);
    return stripos($headers[0],"200 OK") or stripos($headers[0],"302 Found") ? true : false;
}

// CHECK EMAIL
function isValidEmail($email){
    return !!filter_var(strtolower($email), FILTER_VALIDATE_EMAIL) and preg_match('/^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,10}$/',strtolower($email));
}

// GENERATE SELECT TAG
function selectTagFromBD($table,$value,$text,$selected='',$order='',$where=''){

    $cdt_where = $where != '' ? ' where '.$where : '';
    $cdt_order = $order != '' ? ' order by '.$order : '';

    $query = 'select '.$value.','.$text.' from '.$table.' '.$cdt_where.' '.$cdt_order;
    $res = new db();
    $res->exec($query);

    while( $rows = $res->getAssoc() ){
        $isSelected = ( $rows[$value] == $selected and $selected != '' ) ? ' selected="selected"' : '';
        echo '<option value="'.$rows[$value].'"'.$isSelected.'>'.$rows[$text].'</option>';
    }

}

// PASSWORD GENERATOR
function pwGenerate($size=8){

    $passe = '';

    $tab_car = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        '0','1','2','3','4','5','6','7','8','9'
    ];

    for( $index = 0 ; $index < $size ; $index++ ){
        $passe .= $tab_car[array_rand($tab_car)];
    }

    return $passe;

}

function generateCalendarCode($size=12){

    $code = pwGenerate($size);

    $query = 'select villa_id from vn_villas where villa_calendar_code like "'.$code.'"';
    $res = new db();
    $res->exec($query);

    if( $res->numRows() > 0 ) generateCalendarCode($size);
    else return $code;

}

function generateCalendarManagerCode($size=12){

    $code = pwGenerate($size);

    $query = 'select manager_id from vn_managers where manager_schedule like "'.$code.'"';
    $res = new db();
    $res->exec($query);

    if( $res->numRows() > 0 ) generateCalendarManagerCode($size);
    else return $code;

}

// CHECK TITLE TAG
function checkTitleTag($title,$text=''){

    global $boot_domain_title;

    if( strtolower(substr(trim($title),-9,9)) == 'villanovo' ){
        $title = trim($title);
        $title = substr($title,0,strlen($title)-9);
        $parts = explode('|',$title);
        if( count($parts) > 1 ){
            array_pop($parts);
            $title = trim(implode('|',$parts));
        }
        $parts = explode(':',$title);
        if( count($parts) > 1 ){
            array_pop($parts);
            $title = trim(implode(':',$parts));
        }
    }

    $domain_title = $boot_domain_title != '' ? $boot_domain_title : 'Villanovo';

    if( $text != '' )
        $title .= ' '.$text;

    if( strpos(strtolower($title),strtolower($domain_title)) === false ){
        $title .= ' | '.$domain_title;
    }

    return $title;

}

// COMPLETE NUMBER WITH ZEROS
function completeWithZeros($number,$size){
    $len = strlen($number);
    if( $len < $size ){
        $number = '0'.$number;
        return completeWithZeros($number,$size);
    }
    else{
        return $number;
    }
}

// CONVERT FILTERS STRING TO ARRAY
function filtersToArray($string){
    $filters_parts = explode(';',$string);
    if( count($filters_parts) ){
        foreach ($filters_parts as $filters_part){
            $filter_part = explode(':',$filters_part);
            if( $filter_part[1] != '' ) $filters[$filter_part[0]] = $filter_part[1];
        }
    }
    return $filters;
}

// CONVERT FILTERS ARRAY TO STRING
function filtersToStr($array){
    if( is_array($array) and count($array) > 0 ){
        $filters = str_replace('=',':',http_build_query($array,'',';')).'/';
    }
    else{
        $filters = '';
    }
    return $filters;
}

// CONVERT ARRAY TO BREADCRUMBS
function arrayToBreadcrumb($breadcrumbs){

    $iCrumb = 0;
    $bread_url = '';
    $result = '';

    foreach ($breadcrumbs as $title=>$url){

        $iCrumb++;

        $bread_url .= $url;

        $crumbLevel = !isset($crumbPrevLevel) ? 'bread-home' : $crumbPrevLevel;
        $crumbNextLevel = 'bread-level-'.$iCrumb;

        $crumbNext = $iCrumb < count($breadcrumbs) ? ' &raquo;' : '';
        $crumbProp = $iCrumb == 1 ? '' : ' itemprop="child"';
        $result .= '<div id="'.$crumbLevel.'" itemscope itemtype="https://data-vocabulary.org/Breadcrumb"'.$crumbProp.' itemref="'.$crumbNextLevel.'">';
        $result .= '<a href="'.$bread_url.'" itemprop="url">';
        $result .= '<span itemprop="title">'.$title.'</span>';
        $result .= '</a>'.$crumbNext;
        $result .= '</div>';

        $crumbPrevLevel = $crumbNextLevel;

    }

    return $result;

}

// COMPARE TWO FLOATS
function compareFloats($float1,$float2){
    return abs($float1-$float2) < 0.01 ;
}

// CONVERT PRICE
function convertPrice($price,$rate_from,$rate_to=0){
    global $boot_currency_rate;
    if( $rate_to == 0 ) $rate_to = $boot_currency_rate;
    $price = $price*($rate_from/$rate_to);
    if( ($price < -10 or $price > 10) and $rate_to != $rate_from ) $price = ceil($price);
    return $price;
}

function getFees($amount){

    $query = 'select fee_scale_amount from vn_fees_scales where '.$amount.' >= fee_scale_start order by fee_scale_start desc';
    $res = new db();
    $res->exec($query);

    if( $row = $res->getAssoc() ){
        $fees = $row['fee_scale_amount'];
    }
    else{
        $fees = 50;
    }

    return $fees;

}

// UPLOAD FILE
function uploadFile($file,$folder){

    $name = '';

    if( $file['name'] != '' ){

        $file_tmp = $file['tmp_name'];
        $file_error = $file['error'];
        $file_name = $file['name'];
        $file_ext = strtolower(array_pop(explode('.',$file_name)));

        // 	Extensions autorisés
        $tab_ext = ['jpg','gif','png'];

        if( is_uploaded_file($file_tmp) ){

            //if( in_array($file_ext,$tab_ext) ){

            if ( $file_error ){
                switch( $file_error ){
                    case 1: // UPLOAD_ERR_INI_SIZE
                        $msg_err = "Le fichier dépasse la limite autorisée par le serveur (fichier php.ini) ! \n";
                        break;
                    case 2: // UPLOAD_ERR_FORM_SIZE
                        $msg_err = "Le fichier dépasse la limite autorisée dans le formulaire HTML ! \n";
                        break;
                    case 3: // UPLOAD_ERR_PARTIAL
                        $msg_err = "L'envoi du fichier a été interrompu pendant le transfert ! \n";
                        break;
                    case 4: // UPLOAD_ERR_NO_FILE
                        $msg_err = "Le fichier que vous avez envoyé a une taille nulle ! \n";
                        break;
                }
                //eko($msg_err);
            }
            else{

                if( !is_dir($folder) ){
                    if( mkdir($folder,0775) ) chmod($folder,0775);
                }

                if( is_dir($folder) ){
                    if( move_uploaded_file($file_tmp, $folder.'/'.$file_name) ) $name = $file_name;
                }

            }

            //}

        }

    }

    return $name;

}



// GET ENTRY
function getEntry($entry_id,$decode=true){

    $query = 'select entry_id, entry_traduction, langue_id from vn_entries_trad where entry_id = '.$entry_id.' order by langue_id';
    $res = new db();
    $res->exec($query);

    while( $rows = $res->getAssoc() ){
        $tab[$rows['langue_id']] = $decode ? htmlspecialchars_decode($rows['entry_traduction']) : $rows['entry_traduction'];
    }

    return $tab;

}

// PICTURE TAG GENERATOR
function pictureTag($photo,$sizes,$islazy=0){

    $parts = explode('.',$photo);

    $ext = strtolower(array_pop($parts));

    if( in_array(end($parts),[1920,1366]) ) array_pop($parts);

    $photo = implode('.',$parts);

    $dimensions = [
        '(min-width : 320px) and (max-width : 480px)',
        '(min-width : 481px) and (max-width : 1023px)',
        '(min-width : 1024px) and (max-width : 1366px)',
        '(min-width : 1367px)'
    ];

    $srcset = $islazy ? 'data-srcset' : 'srcset';

    $sources = '';

    foreach( $dimensions as $key=>$dimension ){

        $size = $sizes[$key];

        if( $size !== 0 ){
            $photo_size = $size > 0 ? '.'.$size.'.' : '.';
            $sources .= '<source '.$srcset.'="'.$photo.$photo_size.$ext.'" media="'.$dimension.'">';
        }

    }

    return $sources;

}



// CSS TAG GENERATOR
function linkTagGenerate($links){

    global $boot_root;

    echo "\t";

    foreach( $links as $link ){

        $link_media = $link[1] != '' ? ' media="' . $link[1] . '"' : '';

        $link_url = $boot_root.ltrim($link[0],'/');
        $link_app = dirname(__FILE__).'/..'.$link[0];

        if( is_file($link_url) or is_file($link_app) ){
            $file = is_file($link_url) ? $link_url : $link_app;
            $last_edit = filemtime($file) != '' ? '?'.filemtime($file) : '';
            echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $link[0] . $last_edit . "\"" . $link_media . " />\n\t";
        }
    }

    echo "\n";

}

// SCRIPT TAG GENERATOR
function scriptTagGenerate($scripts){

    global $boot_root;

    foreach( $scripts as $script ){

        $script_url = $boot_root.ltrim($script,'/');
        $script_app = dirname(__FILE__).'/..'.$script;

        if( is_file($script_url) or is_file($script_app) ){
            $file = is_file($script_url) ? $script_url : $script_app;
            $last_edit = filemtime($file) != '' ? '?'.filemtime($file) : '';
            echo "<script type=\"text/javascript\" src=\"" . $script . $last_edit . "\"></script>\n";
        }
    }

}

/********** SLUG ***************/

function my_str_split($string){
    $sArray = [];
    $slen = strlen($string);
    for( $i = 0 ; $i < $slen ; $i++ ){
        $sArray[$i] = $string[$i];
    }
    return $sArray;
}

function noDiacritics($string){

    //cyrylic transcription
    $cyrylicFrom = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $cyrylicTo   = array('A', 'B', 'W', 'G', 'D', 'Ie', 'Io', 'Z', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Tch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia', 'a', 'b', 'w', 'g', 'd', 'ie', 'io', 'z', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'tch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia');

    $from = array("Á", "À", "Â", "Ä", "Ă", "Ā", "Ã", "Å", "Ą", "Æ", "Ć", "Ċ", "Ĉ", "Č", "Ç", "Ď", "Đ", "Ð", "É", "È", "Ė", "Ê", "Ë", "Ě", "Ē", "Ę", "Ə", "Ġ", "Ĝ", "Ğ", "Ģ", "á", "à", "â", "ä", "ă", "ā", "ã", "å", "ą", "æ", "ć", "ċ", "ĉ", "č", "ç", "ď", "đ", "ð", "é", "è", "ė", "ê", "ë", "ě", "ē", "ę", "ə", "ġ", "ĝ", "ğ", "ģ", "Ĥ", "Ħ", "I", "Í", "Ì", "İ", "Î", "Ï", "Ī", "Į", "Ĳ", "Ĵ", "Ķ", "Ļ", "Ł", "Ń", "Ň", "Ñ", "Ņ", "Ó", "Ò", "Ô", "Ö", "Õ", "Ő", "Ø", "Ơ", "Œ", "ĥ", "ħ", "ı", "í", "ì", "i", "î", "ï", "ī", "į", "ĳ", "ĵ", "ķ", "ļ", "ł", "ń", "ň", "ñ", "ņ", "ó", "ò", "ô", "ö", "õ", "ő", "ø", "ơ", "œ", "Ŕ", "Ř", "Ś", "Ŝ", "Š", "Ş", "Ť", "Ţ", "Þ", "Ú", "Ù", "Û", "Ü", "Ŭ", "Ū", "Ů", "Ų", "Ű", "Ư", "Ŵ", "Ý", "Ŷ", "Ÿ", "Ź", "Ż", "Ž", "ŕ", "ř", "ś", "ŝ", "š", "ş", "ß", "ť", "ţ", "þ", "ú", "ù", "û", "ü", "ŭ", "ū", "ů", "ų", "ű", "ư", "ŵ", "ý", "ŷ", "ÿ", "ź", "ż", "ž");
    $to   = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "AE", "C", "C", "C", "C", "C", "D", "D", "D", "E", "E", "E", "E", "E", "E", "E", "E", "G", "G", "G", "G", "G", "a", "a", "a", "a", "a", "a", "a", "a", "a", "ae", "c", "c", "c", "c", "c", "d", "d", "d", "e", "e", "e", "e", "e", "e", "e", "e", "g", "g", "g", "g", "g", "H", "H", "I", "I", "I", "I", "I", "I", "I", "I", "IJ", "J", "K", "L", "L", "N", "N", "N", "N", "O", "O", "O", "O", "O", "O", "O", "O", "CE", "h", "h", "i", "i", "i", "i", "i", "i", "i", "i", "ij", "j", "k", "l", "l", "n", "n", "n", "n", "o", "o", "o", "o", "o", "o", "o", "o", "o", "R", "R", "S", "S", "S", "S", "T", "T", "T", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "W", "Y", "Y", "Y", "Z", "Z", "Z", "r", "r", "s", "s", "s", "s", "B", "t", "t", "b", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "w", "y", "y", "y", "z", "z", "z");


    $from = array_merge($from, $cyrylicFrom);
    $to   = array_merge($to, $cyrylicTo);

    $newstring = str_replace($from, $to, $string);
    return $newstring;

}

function makeSlugs($string, $maxlen=0){

    $newStringTab = array();
    $string = strtolower(noDiacritics($string));

    if( function_exists('str_split') ){
        $stringTab = str_split($string);
    }
    else{
        $stringTab = my_str_split($string);
    }

    $numbers = array("0","1","2","3","4","5","6","7","8","9","-");
    //$numbers=array("0","1","2","3","4","5","6","7","8","9");

    foreach($stringTab as $letter){
        if( in_array($letter, range("a", "z")) || in_array($letter, $numbers) ){
            $newStringTab[] = $letter;
            //print($letter);
        }
        elseif( $letter == " " ){
            $newStringTab[] = "-";
        }
    }

    if( count($newStringTab) ){
        $newString = implode($newStringTab);
        if( $maxlen > 0 ){
            $newString = substr($newString, 0, $maxlen);
        }
        $newString = removeDuplicates('--', '-', $newString);
    }
    else{
        $newString = '';
    }

    return $newString;
}

function checkSlug($sSlug){
    if( ereg ("^[a-zA-Z0-9]+[a-zA-Z0-9\_\-]*$", $sSlug) ){
        return true;
    }
    return false;
}

function removeDuplicates($sSearch, $sReplace, $sSubject){

    $i = 0;

    do{

        $sSubject = str_replace($sSearch, $sReplace, $sSubject);
        $pos = strpos($sSubject, $sSearch);

        $i++;
        if( $i > 100 ){
            die('removeDuplicates() loop error');
        }

    }while( $pos !== false );

    return $sSubject;

}


/******* DATES ********/

function is_timestamp($timestamp){
    $check = (is_int($timestamp) OR is_float($timestamp))
        ? $timestamp
        : (string) (int) $timestamp;
    return  ($check === $timestamp)
        AND ( (int) $timestamp <=  PHP_INT_MAX)
        AND ( (int) $timestamp >= ~PHP_INT_MAX);
}

function is_specialdate($string){
    $string = str_replace([' '],[''],$string);
    return in_array(strlen($string),[8,14]);
}

// CONVERT TO GTM DATE
function gtmDate($date){

    if( $date != '' ){

        $tab_date = explode('/',$date);

        $jour = $tab_date[0];
        $mois = $tab_date[1];
        $annee = $tab_date[2];

        $gtm_date = $annee.'-'.$mois.'-'.$jour;

        return $gtm_date;

    }
    else{
        return '';
    }

}

// CONVERT TO SPECIAL DATE

function toSpecialDate($datetime='',$hour='current'){

    // IF SPECIAL DATE
    if( is_specialdate($datetime) ){
        $datetime = toDate($datetime,'hour');
    }

    // IF TIMESTAMP
    if( strpos($datetime,'/') === false ){
        $datetime = $datetime == '' ? date('d/m/Y H:i:s') : date('d/m/Y H:i:s',$datetime);
    }

    $parts = explode(' ',$datetime);
    $date_part = '';
    $hour_part = '';

    if( $parts[0] != '' ){
        $date_parts = explode('/',$parts[0]);
        if( $date_parts[0] != '' and $date_parts[1] != '' and $date_parts[2] != '' ){
            $date_part = $date_parts[2].$date_parts[1].$date_parts[0];
        }
    }

    if( $date_part != '' ){

        switch ($hour){

            case 'current':

                $hour_parts = explode(':',$parts[1]);
                if( $hour_parts[0] != '' and $hour_parts[1] != '' and $hour_parts[2] != '' ){
                    $hour_part = $hour_parts[0].$hour_parts[1].$hour_parts[2];
                }
                else{
                    $hour_part = '000000';
                }
                break;

            case 'begin':
                $hour_part = '000000';
                break;

            case 'end':
                $hour_part = '235959';
                break;

            default:
                $hour_part = '';
                break;
        }

    }

    return $date_part.$hour_part;

}

// CONVERT TO DATE
function toDate($datetime='',$format='date',$from='current'){

    if( $datetime === '' )
        $datetime = time();

    if( is_specialdate($datetime) ) {
        $datetime = toTime($datetime,'real');
    }

    $day = date('d',$datetime);
    $month = date('m',$datetime);
    $year = date('Y',$datetime);

    $datehour = $day.'/'.$month.'/'.$year;

    if( $format == 'hour' ){

        switch ($from){

            case 'noon':
                $hour = '12';
                $minute = '00';
                $second = '00';
                break;

            case 'begin':
                $hour = '00';
                $minute = '00';
                $second = '00';
                break;

            case 'end':
                $hour = '23';
                $minute = '59';
                $second = '59';
                break;

            default:
                $hour = date('H',$datetime);
                $minute = date('i',$datetime);
                $second = date('s',$datetime);
                break;

        }

        $datehour .= ' '.$hour.':'.$minute.':'.$second;

    }

    return $datehour;

}

// CONVERT TO TIMESTAMP
function toTime($date,$from='noon'){

    if( is_specialdate($date) ){

        $year = substr($date,0,4);
        $month = substr($date,4,2);
        $day = substr($date,6,2);

        if( strlen($date) == 14 ){
            $real_hour = substr($date,8,2);
            $real_minute = substr($date,10,2);
            $real_second = substr($date,12,2);
        }
        else{
            $real_hour = '00';
            $real_minute = '00';
            $real_second = '00';
        }

        $date = $day.'/'.$month.'/'.$year.' '.$real_hour.':'.$real_minute.':'.$real_second;

    }

    if( is_timestamp($date) ){
        $date = date('d/m/Y H:i:s',$date);
    }

    $parts = explode(' ',$date);

    if( $parts[0] != '' ){

        $date_parts = explode('/',$parts[0]);

        $day = $date_parts[0];
        $month = $date_parts[1];
        $year = $date_parts[2];

        if( $parts[1] != '' ){
            $hour_parts = explode(':',$parts[1]);
            $real_hour = $hour_parts[0];
            $real_minute = $hour_parts[1];
            $real_second = $hour_parts[2];
        }
        else{
            $real_hour = '00';
            $real_minute = '00';
            $real_second = '00';
        }

        switch($from){

            case 'noon':
                $hour = '12';
                $minute = '00';
                $second = '00';
                break;

            case 'begin':
                $hour = '00';
                $minute = '00';
                $second = '00';
                break;

            case 'end':
                $hour = '23';
                $minute = '59';
                $second = '59';
                break;

            case 'current':
                $hour = date('H');
                $minute = date('i');
                $second = date('s');
                break;

            case 'real':
                $hour = $real_hour;
                $minute = $real_minute;
                $second = $real_second;
                break;

            default:
                $hour = '00';
                $minute = '00';
                $second = '00';
                break;

        }

        return mktime($hour,$minute,$second,$month,$day,$year);

    }

}

// DAYS BETWEEN TWO DATES
function daysBetween($time1,$time2){
    $nbJours = round( ($time2 - $time1) / 86400 );
    return $nbJours;
}

// DAY BEFORE
function dayBefore($time){
    return $time - 86400;
}

// FIRST/LAST DAY OF MONTH
function toMonth($time='',$to='first'){

    if( $time == '' ) $time = time();

    if( $to == 'first' ){
        $day = 1;
        $hour = 0;
        $minute = 0;
        $second = 0;
    }
    else{
        $day = date('t',$time);
        $hour = 23;
        $minute = 59;
        $second = 59;
    }

    return mktime($hour,$minute,$second,date('m',$time),$day,date('Y',$time));

}

function calculateTime($date){

    $now = time();
    $time = toTime($date,'real');

    $diff = $now-$time;

    if( $diff < 60 ){
        $text = 'Maintenant';
    }
    elseif( $diff < (60*60) ){
        $text = floor($diff/60).' min.';
    }
    elseif( $diff < (60*60*24) ){
        $text = floor($diff/(60*60)).' h.';
    }
    else{
        $text = floor($diff/(60*60*24)).' j.';
    }

    return $text;

}



// Round
function getRound($prix,$virgule=2){

    if( is_numeric($prix) ) $return_value = round($prix,$virgule);
    else $return_value = $prix;

    return $return_value;
}



function calculNote($product_id,$rating_id=0){

    $count_imp = $sum_imp = 0;
    $count_loc = $sum_loc = 0;
    $count_par = $sum_par = 0;
    $count_acc = $sum_acc = 0;
    $count_roo = $sum_roo = 0;
    $count_ser = $sum_ser = 0;
    $count_res = $sum_res = 0;

    $iAvr = 0;

    if( $rating_id > 0 ){
        $requete_rating = 'select 
                               rating_recommanderiez_vous, impression_generale, rating_localisation_de_lhotel_la_maison, parties_communes_de_lhotel_la_maison,
                               accueil_a_larrivee, les_chambres, qualite_du_service, petit_dejeuner_restauration 
                               from resa_rating 
                               where rating_id = '.$rating_id;
    }
    else{
        $requete_rating = 'select 
                               rating_recommanderiez_vous, impression_generale, rating_localisation_de_lhotel_la_maison, parties_communes_de_lhotel_la_maison,
                               accueil_a_larrivee, les_chambres, qualite_du_service, petit_dejeuner_restauration 
                               from resa_rating 
                               join resa
                               on resa_rating.resa_id = resa.id
                               where resa_rating.publie2 = 1 and resa.id_prd = '.$product_id;

        $requete_rating = 'select
                               rating_id,
                               rating_recommanderiez_vous, impression_generale, rating_localisation_de_lhotel_la_maison, parties_communes_de_lhotel_la_maison,
                               accueil_a_larrivee, les_chambres, qualite_du_service, petit_dejeuner_restauration
                               from resa_rating
                               where 
                               ( 
                                 resa_id in (select id from resa where id_prd = '.$product_id.' and resa.resa_archive = 0 and resa.id_etat = 3)
                                 or 
                                 villa_id = '.$product_id.'
                               )
                               and resa_rating.publie2 = 1';
    }


    $res_rating = new db();
    $res_rating->exec($requete_rating);

    while( $ligne_rating = $res_rating->getAssoc() ){

        if( $ligne_rating['impression_generale'] > 0 ){
            $count_imp++;
            $sum_imp += $ligne_rating['impression_generale'];
        }
        if( $ligne_rating['rating_localisation_de_lhotel_la_maison'] > 0 ){
            $count_loc++;
            $sum_loc += $ligne_rating['rating_localisation_de_lhotel_la_maison'];
        }
        if( $ligne_rating['parties_communes_de_lhotel_la_maison'] > 0 ){
            $count_par++;
            $sum_par += $ligne_rating['parties_communes_de_lhotel_la_maison'];
        }
        if( $ligne_rating['accueil_a_larrivee'] > 0 ){
            $count_acc++;
            $sum_acc += $ligne_rating['accueil_a_larrivee'];
        }
        if( $ligne_rating['les_chambres'] > 0 ){
            $count_roo++;
            $sum_roo += $ligne_rating['les_chambres'];
        }
        if( $ligne_rating['qualite_du_service'] > 0 ){
            $count_ser++;
            $sum_ser += $ligne_rating['qualite_du_service'];
        }
        if( $ligne_rating['petit_dejeuner_restauration'] > 0 ){
            $count_res++;
            $sum_res += $ligne_rating['petit_dejeuner_restauration'];
        }

    }

    if( $sum_imp > 0 ){
        $note_imp = ($sum_imp/$count_imp)*2;
        $iAvr += 1;
    }
    if( $sum_loc > 0 ){
        $note_loc = ($sum_loc/$count_loc)*2;
        $iAvr += 1;
    }
    if( $sum_par > 0 ){
        $note_par = ($sum_par/$count_par)*2;
        $iAvr += 1;
    }
    if( $sum_acc > 0 ){
        $note_acc = ($sum_acc/$count_acc)*2;
        $iAvr += 1;
    }
    if( $sum_roo > 0 ){
        $note_roo = ($sum_roo/$count_roo)*2;
        $iAvr += 1;
    }
    if( $sum_ser > 0 ){
        $note_ser = ($sum_ser/$count_ser)*2;
        $iAvr += 1;
    }
    if( $sum_res > 0 ){
        $note_res = ($sum_res/$count_res)*2;
        $iAvr += 1;
    }

    $noteAvr = ($note_imp+$note_loc+$note_par+$note_acc+$note_roo+$note_ser+$note_res)/$iAvr;

    return $noteAvr;

}

// AVERAGE FORMAT
function noteFormat($note){
    return number_format($note,1,'.','');
}



// CALCULATE RATE
function getCalculateHundred($amount,$rate){
    return getRound(($amount*$rate)/100);
}









function clearString($string){
    if( $string != '' )
    return str_replace(["\n","\r"," "],'',strip_tags(trim(strtolower(html_entity_decode($string)))));
    else return '';
}

function titleSplit($string){

    $position = strpos($string,'|');

    if ( $position !== false) {
        $strong  = trim(substr($string,$position+1));
        $label  = trim(substr($string,0,$position-1));
        return '<label>'.$label.'</label><strong>'.$strong.'</strong>';
    }
    else{
        return $string;
    }

}

function truncateString($string,$nb,$complete=''){
    $string = trim($string);
    if( strlen($string) > 0 and $nb > 0 ){
        if( strlen($string) > $nb ) $string = substr($string,0,$nb).$complete;
    }
    return $string;
}

// Generer no contrat assurance
function idGenerate(){

    $tab_range = range(0,9);

    $no_contrat = '';

    for( $chiffre = 0 ; $chiffre < 6 ; $chiffre++ ){
        $no_contrat .= array_rand($tab_range);
    }

    $requete_gen = 'select no_contrat_ass from resa where no_contrat_ass = "VIL'.$no_contrat.'"';
    $res_gen = new db();
    $res_gen->exec($requete_gen);

    if( $res_gen->numRows() > 0 ) idGenerate();
    else return $no_contrat;

}

function getCustomerTotalPaid($book_id,$for_id=1,$state_id=-1){

    if( $for_id > 0 )
        $cdt_for = ' and payment_for_id = '.$for_id;

    if( $state_id >= 0 )
        $cdt_state = ' and payment_state_id = '.$state_id;

    $requete_paid = 'select sum(payment_customer_detail_amount) as total_paid
                     from vn_resas_payments_customers_details
                     where vn_resas_payments_customers_details.book_id = '.$book_id.$cdt_for.$cdt_state;

    $res_paid = new db();
    $res_paid->exec($requete_paid);

    if( $ligne_paid = $res_paid->getAssoc() ) $total_paid = $ligne_paid['total_paid'];
    else $total_paid = 0;

    return $total_paid;

}