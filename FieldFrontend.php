<?php


defined('ABSPATH') or die('Bu dosya doğrudan çağırılarak çalışmaz.');



add_filter('frm_validate_field_entry', 'ndr_formidable_tcl_kod_validator', 10, 3);
function ndr_formidable_tcl_kod_validator($errors, $field, $value)
    {

    if ($field->type == 'type_kodkontrol') {
        /********
         echo "<pre>";
        print_r($value);
        echo "<hr>"; 
        print_r($field);
        echo "</pre>"; 
        echo "<hr>"; 
        echo $field->field_options['fld_kod'];
        /********** */

        $fld_kod = trim($_POST['item_meta'][$field->field_options['fld_kod']]);

        if (!ndr_formidable_tcl_servis_sorgula($fld_kod)) {
            $errors['field' . $field->id] = $field->field_options['error_kodkontrol'];
            }
        }


//var_dump($errors);

    return $errors;
    }

/**************************
Local Servis validasyon
*/

function ndr_formidable_tcl_servis_sorgula($kod)
    {

       // echo "function : ndr_formidable_tcl_servis_sorgula";
    $fld_kod = trim($kod);

    if (ndr_tcl_kod_pre_valid($fld_kod) == false) {
        return false;
        }

    if ($cacheData = ndr_TclCacheControl($fld_kod)) {
        $response = json_decode($cacheData, true);
    
    } else {

        $fld_kod  = $fld_kod != "" ? $fld_kod : "TestKD";
        $endpoint = get_rest_url(null, 'tcl/v1/kod_kontrol/');
        $url      = $endpoint . $fld_kod;
        $args     = array(
            'timeout'     => 10,
            'httpversion' => '1.1',
        );
        $request  = wp_remote_get($url, $args);

        if (is_wp_error($request)) {
            return false;
            }
        $response = json_decode(wp_remote_retrieve_body($request), true);

        ndr_TclCacheControl($fld_kod, $response);
    }

    //var_dump($request);

    
     return $response["status"] == 200 ? true : false;



    }


