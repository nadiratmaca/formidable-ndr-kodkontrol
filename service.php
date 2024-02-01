<?php

/********************* */
defined('ABSPATH') or die('Bu dosya doğrudan çağırılarak çalışmaz.');



/*******************************
Rest api EndPoint Oluşturuluyor.
 */
add_action('rest_api_init', function () {
  register_rest_route(
    'tcl/v1',
    '/kod_kontrol/(?P<kod>[A-Z0-9]{12}+)',
    array(
      'methods'             => WP_REST_Server::READABLE,
      'callback'            => 'ndr_tcl_kod_kontrol_api',
      'args'                => array(
        'key' => array(
          'validate_callback' => function ($param, $request, $key) {
            return ndr_tcl_kod_pre_valid($param);
          }
        ),
      ),
      'permission_callback' => function () {
        return true;
        return current_user_can('edit_others_posts');
      }
    )
  );
});


/*******************************
Rest api EndPoint CallBack fonsiyonu.
 */

function ndr_tcl_kod_kontrol_api($data)
{


  $kod = trim($data['kod']);
  $cacheData = ndr_TclCacheControl($kod.'-srv');



   if ($cacheData==false) {

   $tcl_response = service_arsenalcampaign_validate($kod);
   ndr_TclCacheControl($kod.'-srv', $tcl_response);  
  }else{

  
    $tcl_response =  $cacheData["data"];
    $tcl_response["cache"] =  unlink($cacheData["data"]);
  }

  if ($tcl_response==="error") {
    #Hatalı kod
    return  new WP_REST_Response(
      array(
        'code'       => 1,
        'status'       => 503,
        'message'       => 'TCL Servis Hatası',
        'data' => $tcl_response
      ),
      503
    );
  }

  $tcl_valid = $tcl_response===true ? true : false;

  $response_status    = $tcl_valid ? 200 : 400;
  $response_message = $tcl_valid ? "Kod Geçerli" : "Kod HATALI!";
  $response_code = $tcl_valid ? 0 : 1;
  $response_body    = $tcl_response;


  $return = new WP_REST_Response(
    array(
      'code'       =>  $response_code,
      'status'       => $response_status,
      'message'       => $response_message,
      'data' => $response_body
    ),
    $response_status
  );

  return $return;
}

/******************************************************************* */
function service_arsenalcampaign_validate($kod)
{
  $return = false;
  if (ndr_tcl_kod_pre_valid($kod) == false) {
    return  $return;
  }


  try {
    $url = 'https://api.servissoft.net/v1/bilkom/arsenalcampaign_checkCode?code=' . $kod;

    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        // CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_HTTPHEADER     => array(
          'Authorization: Basic Ymlsa29tOnNlcnZpc3NvZnQ6YXJzZW5hbDo=',
          'Content-Type: application/json'
        ),
      )
    );

    $response = curl_exec($curl); 
    curl_close($curl);
    $return = $response === "true";    
    if(!curl_getinfo($curl, CURLINFO_HTTP_CODE) > 499){ 
      $return= "error";       
    }    

    return  $return;
  

    // $return = array(
    //   'code'=> $response==true ? 0:1,
    //   'code'=> $response==true ? 0:1,
    //   'HttpStatusCode '=> curl_getinfo($curl, CURLINFO_HTTP_CODE)
    // );



    // if ($return->HttpStatusCode != 200 && $return->HttpStatusCode != 400) {

    //   $subject = 'APRN-TCL-Kampanya servis : ' . $return->HttpStatusCode;
    //   $body    = $subject . "<hr>";
    //   $body .= '<pre>';
    //   //$body .= "<hr>Request Body : " . var_export($requestBody, true);
    //   $body .= "<hr>Backent Response : " . var_export($return, true);
    //   $body .= "<hr>CURL info: " . var_export(curl_getinfo($curl), true);
    //   $body .= "<hr>SERVER info: " . var_export($_SERVER, true);
    //   $body .= '</pre>';
    //   ndr_send_mail($subject, $body);
    //   }


  } catch (Exception $e) {


    // $subject = 'APRN-TCL-SERVİS ERİŞİM HATASI ' . $return->HttpStatusCode;
    // $body    = $subject . "<hr>";
    // $body .= '<pre>';
    // $body .= "<hr>Backent Response : " . var_export($return, true);
    // $body .= "<hr>CURL info: " . var_export(curl_getinfo($curl), true);
    // $body .= "<hr>SERVER info: " . var_export($_SERVER, true);
    // $body .= '</pre>';
    // ndr_send_mail($subject, $body);

    return "error";
    throw new Exception("Invalid URL OR CURL error", 0, $e);
  }

  return  $return;
}
