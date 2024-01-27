<?php



defined('ABSPATH') or die('Bu dosya doğrudan çağırılarak çalışmaz.');

if(!function_exists("ndr_tr_uppercase")){
    function ndr_tr_uppercase($string)
    {
        $string= trim($string);
        $kucuk = array("ç", "i", "ı", "ğ", "ö", "ş", "ü");
        $buyuk = array("Ç", "İ", "I", "Ğ", "Ö", "Ş", "Ü");
        $string = str_replace($kucuk, $buyuk, $string);
        $string = strtoupper($string);
        return $string;
    }
}

/****************************************************************************************** */

if(!function_exists("ndr_tcl_kod_pre_valid")){
function ndr_tcl_kod_pre_valid($fld_kod)
{   
    $fld_kod=ndr_tr_uppercase($fld_kod);//Büyük Harf Dönüşümü

    $fld_kod=trim($fld_kod);     
    if (strlen($fld_kod) != 12) {return false;}
    $pattern = '/^[A-Z0-9]{12}$/';  
    return trim($fld_kod) == "" ? false : preg_match($pattern, $fld_kod);
 }
} 

/****************************************************************************************** */


if(!function_exists("ndr_TclCacheControl")){

    function ndr_TclCacheControl($kod,$cacheData=false){
    $expiration_time = (60*60)*4;



         if(trim($kod)=="") return false;

        $cacheDir=WP_CONTENT_DIR.'/cache_tcl/';
        $cacheFile=$cacheDir.$kod.".cache";
        if(!is_dir($cacheDir)) mkdir($cacheDir);
    

    if($cacheData!=false){ 
        echo "Cache Write<br>";
        return !file_put_contents($cacheFile,json_encode($cacheData)) ? false : $cacheData;
    }


 if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $expiration_time)) {
        // Önbellekteki veriyi oku ve geri döndür
        echo "Cache DATA<br>";
        return file_get_contents($cacheFile);
    }

return false;
    
    }

}

/*
$kod=2;
$data=false;
//$data=time();

if(!$data=ndr_TclCacheControl("TESTKOD".$kod)){
echo "sleep";
sleep(2);

$data=time();
var_dump(ndr_TclCacheControl("TESTKOD".$kod,$data));
}else{
echo $data;

}*/