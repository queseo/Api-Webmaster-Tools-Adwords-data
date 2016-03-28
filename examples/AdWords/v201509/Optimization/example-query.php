<?php
    include('../src/Google/autoload.php');
    include('../src/Webmaster/Webmaster.php');
	
	//aqui el email que nos da la credencial de verificacion
    $service_email = 'xxxxxxxxx@atomic-voice-xxxxx.iam.gserviceaccount.com'; 
	
	//aquí el nombre del archivo .p12 mantenidendo la ruta "__DIR__.'/../src/config/"
    $private_key = file_get_contents(__DIR__.'/../src/config/xxxxxxxxxxxxx.p12');

    $webmaster_obj = new Webmaster($service_email, $private_key);
    $webmaster_obj->test();


    
