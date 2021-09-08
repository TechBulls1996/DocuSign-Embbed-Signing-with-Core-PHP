<?php
// ds_config.php
// 
// DocuSign configuration settings
$DS_CONFIG = [
    'quickstart' => 'true',
    'ds_client_id' => 'f4838871-60b9-482f-a372-54a78bca83c3',  // The app's DocuSign integration key
    'ds_client_secret' => '762789e7-df03-45db-8f24-1ec1a70bed63', // The app's DocuSign integration key's secret
    'signer_email' => 'suryap@neuronimbus.com',
    'signer_name' => 'Surya Pratap',
    'app_url' => 'http://localhost/demo/esign/', // The url of the application.
    'account_id' => '1acfc4d4-4849-4fd8-979b-73a4da930c0a',
  
    'authorization_server' => 'https://account-d.docusign.com/',
    'api_url' => 'https://demo.docusign.net/restapi',
    'session_secret' => 'Mortgage@1996', // Secret for encrypting session cookie content
];

$EXAMPLES_API_TYPE = [
    'Rooms' => false,
    'ESignature' => true,
    'Click' => false,
    'Monitor' => false,
    'Admin' => false
];

$GLOBALS['DS_CONFIG'] = $DS_CONFIG;
$GLOBALS['EXAMPLES_API_TYPE'] = $EXAMPLES_API_TYPE;
