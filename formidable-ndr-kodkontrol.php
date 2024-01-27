<?php

/**
 * Plugin Name:       Formidable (NDR) TCL Kod Kontrol 
 * Plugin URI:        https://nadiratmaca.com/wp-plugins
 * Description:       Bu eklenti Formidable Form eklentisi için TLC kampanya kodu  sorgulama özelliği kazandırmaktadır.
 * Version:           24.01.27.1
 * Requires PHP:      7.4
 * Author:            Nadir Atmaca
 * Author URI:        https://nadiratmaca.com
 * Text Domain:       formidable-ndr-kodkontrol
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('Bu dosya doğrudan  çalışmaz.');

/*
^([A-Z0-9]{12}+)$

*/
if (!defined('_NDR_TCL')) {
    defined('_NDR_TCL') or define('_NDR_TCL', TRUE);

    include('functions.php');
    include('service.php');



    add_action( 'admin_init', function (){
      include('FieldBackend.php');
    });

    if(!is_admin()){ 
         add_action( 'init', function (){      
             include('FieldFrontend.php');
        }); 
    }
    




/*********************/
}