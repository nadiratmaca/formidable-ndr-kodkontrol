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

  if (!$tcl_response = ndr_TclCacheControl($kod.'-srv')) {

    $tcl_response = service_arsenalcampaign_validate($kod);
    ndr_TclCacheControl($kod.'-srv', $tcl_response);
  
  }

//$tcl_response = service_arsenalcampaign_validate($kod);

if (!isset($tcl_response->Success)) {
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


  $test      = $kod == 'TEST12345678';
  $tcl_valid = $tcl_response->Success == 1 && (int)$tcl_response->Message > 3 ? true : false;
  



  $response_status    = $tcl_valid ? 200 : $tcl_response->HttpStatusCode;
  $response_message = $tcl_valid ? "Kod Geçerli" : "Kod HATALI!";
  $response_code = $tcl_valid ? 0 : 1;
  $response_body    = $tcl_response;


  $return = new WP_REST_Response(
    array(
      'code'       =>  $response_code,
      'status'       => $response_status,
      'message'       => !$test ? $response_message : $response_message . ' (TEST)',
      'data' => $response_body
    ),
    $response_status
  );

   return $return;

  }

/******************************************************************* */
function service_arsenalcampaign_validate($kod)
  {
    $response = new stdClass;

  if (ndr_tcl_kod_pre_valid($kod) == false) {
    return false;
    }
  $url         = 'https://api.servissoft.net/v1/bilkom/arsenalcampaign_validate';
  $requestBody = wp_json_encode(array("Code" => $kod));
  /*
  Request Body: 
  '{
  "Code":"'.$kod.'"
  }'

  Response :  Status Code : 200,400

  {
    "Success": 1,
    "Message": "4"
}


  */
  try {
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_HTTPHEADER     => array(
          'Authorization: Basic Ymlsa29tOnNlcnZpc3NvZnQ6YXJzZW5hbDo=',
          'Content-Type: application/json'
        ),
      )
    );

    $response                 = json_decode(curl_exec($curl));
    if(!$response){
      curl_close($curl);
      return false;
    }

   $response->HttpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);


    if ($response->HttpStatusCode != 200 && $response->HttpStatusCode != 400) {

      $subject = 'APRN-TCL-Kampanya servis : ' . $response->HttpStatusCode;
      $body    = $subject . "<hr>";
      $body .= '<pre>';
      $body .= "<hr>Request Body : " . var_export($requestBody, true);
      $body .= "<hr>Backent Response : " . var_export($response, true);
      $body .= "<hr>CURL info: " . var_export(curl_getinfo($curl), true);
      $body .= "<hr>SERVER info: " . var_export($_SERVER, true);
      $body .= '</pre>';
      ndr_send_mail($subject, $body);
      }


    curl_close($curl);

    //trace($response);

    return $response;
    } catch (Exception $e) {


    $subject = 'APRN-TCL-SERVİS ERİŞİM HATASI ' . $response->HttpStatusCode;
    $body    = $subject . "<hr>";
    $body .= '<pre>';
    $body .= "<hr>Request Body : " . var_export($requestBody, true);
    $body .= "<hr>Backent Response : " . var_export($response, true);
    $body .= "<hr>CURL info: " . var_export(curl_getinfo($curl), true);
    $body .= "<hr>SERVER info: " . var_export($_SERVER, true);
    $body .= '</pre>';
    ndr_send_mail($subject, $body);

    return false;
    throw new Exception("Invalid URL OR CURL error", 0, $e);

    }

  return false;

  }


