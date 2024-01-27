<?php
/********************* */
defined( 'ABSPATH' ) or die( 'Bu dosya doğrudan çağırılarak çalışmaz.' );

/*******************************
Rest api EndPoint Oluşturuluyor.
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'tcl/v1', '/kod_kontrol/(?P<kod>[A-Z0-9]{12}+)', array(
    'methods'=> WP_REST_Server::READABLE,
    'callback' => 'ndr_tcl_kod_kontrol_api',
    'args' => array(
      'key' => array(
        'validate_callback' => function($param, $request, $key) {
          return ndr_tcl_kod_pre_valid($param); 
        }
      ),
    ),
    'permission_callback' => function () {
      return true;
      return current_user_can( 'edit_others_posts' );
    }
  ) );
} );

 
/*******************************
Rest api EndPoint CallBack fonsiyonu.
*/

function ndr_tcl_kod_kontrol_api( $data ) {


$kod=trim($data['kod']);



  $tcl_response = arsenalcampaign_validate($kod);

$test = $kod=='TEST12345678' ;
$tcl_valid= $tcl_response->Success == 1 || $test  ? true : false;



  $response_code = $tcl_valid ? 200 : $tcl_response->HttpStatusCode;
  $response_message = $tcl_valid ? "Kod Geçerli" : "Kod HATALI!";
  $response_body =  $tcl_response ;

  $return =  new WP_REST_Response(
    array(
      'status' => $response_code,
      'response' =>  !$test ? $response_message : $response_message.' (TEST)',
      'tcl_response' => $response_body
    ),  $response_code );

return rest_ensure_response($return);

#Hatalı kod
return new WP_Error( 'no_author', 'Invalid author', array( 'status' => 404 ) );

}
 

function arsenalcampaign_validate($kod) {

$url='https://api.servissoft.net/v1/bilkom/arsenalcampaign_validate';
$body = wp_json_encode(array( "Code"=> $kod ));
/*
'{
"Code":"'.$kod.'"
}'

*/
try{
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$body,
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic Ymlsa29tOnNlcnZpc3NvZnQ6YXJzZW5hbDo=',
    'Content-Type: application/json'
  ),
));

$response = json_decode(curl_exec($curl));
$response->HttpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);



return $response;
}catch(Exception $e){
    throw new Exception("Invalid URL OR CURL error",0,$e);
}



}


