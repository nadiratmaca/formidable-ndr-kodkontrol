<?php



defined('ABSPATH') or die('Bu dosya doğrudan çağırılarak çalışmaz.');

if (!function_exists("ndr_tr_uppercase")) {
    function ndr_tr_uppercase($string)
        {
        $string = trim($string);
        $kucuk  = array("ç", "i", "ı", "ğ", "ö", "ş", "ü");
        $buyuk  = array("Ç", "İ", "I", "Ğ", "Ö", "Ş", "Ü");
        $string = str_replace($kucuk, $buyuk, $string);
        $string = strtoupper($string);
        return $string;
        }
    }

if (!function_exists("trace")) {
    function trace($content, $point = null, $echo = 0)
        {

        $ret = '<pre style="max-width:100%;font-size:12px;background-color:#f1f1f1;padding:10px;margin:5px;display:block; border:solid 1px gray;">';
        //$ret.= "<hr>";
        $ret .= '<strong style="color:red;">Point : </strong>' . ($point ? $point : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']);
        $ret .= $point;
        $ret .= "<br>";
        $ret .= '<strong style="color:red;">type : </strong> ' . gettype($content);
        $ret .= "<br>";
        $ret .= '<strong style="color:red;">value : </strong> ';
        $ret .= gettype($content) == "string" ? $content : (gettype($content) == "array" || gettype($content) == "object" ? print_r($content, true) : var_export($content, true));
        $ret .= "</pre>";
        //   echo $ret;
        return null;
        }
    }


/****************************************************************************************** */

if (!function_exists("ndr_tcl_kod_pre_valid")) {
    function ndr_tcl_kod_pre_valid($fld_kod)
        {
        $fld_kod = ndr_tr_uppercase($fld_kod); //Büyük Harf Dönüşümü

        $fld_kod = trim($fld_kod);
        if (strlen($fld_kod) != 12) {
            return false;
            }
        $pattern = '/^[A-Z0-9]{12}$/';
        return trim($fld_kod) == "" ? false : preg_match($pattern, $fld_kod);
        }
    }
/****************************************************************************************** */

if (!function_exists("ndr_send_mail")) {
    function ndr_send_mail($subject = false, $body = "")
        {
        $to      = 'nadiratmaca@gmail.com';
        $subject = $subject ? $subject : 'APRN-TCL-Kampanya ';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, $subject, $body, $headers);
        }
    }

/****************************************************************************************** */




if (!function_exists("ndr_TclCacheControl")) {

    function ndr_TclCacheControl($kod, $cacheData = "nocache")
        {


        $cache_expiration_time =10; // (60*60)*4;

         
        if (trim($kod) == "" || $cache_expiration_time == 0){
             @unlink($cacheFile);
            return false;
        }
           

        $cacheDir  = WP_CONTENT_DIR . '/cache_tcl/';
        $cacheFile = $cacheDir . $kod . ".cache";
        if (!is_dir($cacheDir))
            mkdir($cacheDir);

        if (isset($_REQUEST["cache"]) && $_REQUEST["cache"] == "purge" && file_exists($cacheFile)) {
             @unlink($cacheFile);
            //$cache_expiration_time = 0;

            }

        if ($cacheData =="nocache") {            
                  
            $cahe_info = array(
                "timestamp" => time(), 
                "timeout" => $cache_expiration_time, 
                'date' => date('Y-m-d H:i:s'),
                'data' => $cacheData,
            );
            
            $data_type = gettype($cacheData);

            switch ($data_type) {
                case 'array':                 
                    $cahe_info['data'] = json_encode($cacheData);   
                    break;
                case 'object':
                    $cahe_info['data'] = json_encode($cacheData);                   
                    break;
                case 'string':
                    $cahe_info['data'] = $cacheData;
                    break;
                }

              
                
                if($cacheData){
                    return !file_put_contents($cacheFile, json_encode($cahe_info));
                }else{
                    return @unlink($cacheFile);
                }
            
            }


        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cache_expiration_time)) {
            // Önbellekteki veriyi oku ve geri döndür
            //echo "Cache DATA<br>";
            return json_decode(file_get_contents($cacheFile));
            }

        return false;

        }

    }








    /*



    $kod=2;
$data=false;
//$data=time();

if(!$data=ndr_TclCacheControl_old("TESTKOD".$kod)){
echo "sleep";
sleep(2);

$data=time();
var_dump(ndr_TclCacheControl_old("TESTKOD".$kod,$data));
}else{
echo $data;

}*/