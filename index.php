<?php 
require_once dirname(__FILE__). '/ds_config.php';
require_once dirname(__FILE__). '/src/DocuSignController.php';
$docuSign = new DocuSignController();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$authData = @$_SESSION['authData'];


if(@$_GET['page']=='connect'){
   $docuSign->connect(); 

}elseif(@$_GET['page']=='sign'){
   $docuSign->signDocument();
}
else{
  
  if(@$_GET['code'] && ( !array_key_exists('authData',$_SESSION) || property_exists($authData,'error') )){
        $docuSign->callback();
  }
  
  //session_destroy();
  $docuSign->index();

}