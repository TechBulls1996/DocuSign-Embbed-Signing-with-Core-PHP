<?php
require_once dirname(__DIR__). '/vendor/autoload.php';

use DocuSign\eSign\Configuration;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;


class DocuSignController {
        
    /** hold config value */
    private $config;

    private $signer_client_id = 1000; # Used to indicate that the signer will use embedded

    /** Specific template arguments */
    private $args;


    public function index(){
      require_once dirname(__FILE__).'/views/index.php';
    
    }
    
    /**
     * Connect your application to docusign
     *
     * @return url
     */
    public function connect()
    {
        try {
            $params = [
                'response_type' => 'code',
                'scope' => 'signature',
                'client_id' => $GLOBALS['DS_CONFIG']['ds_client_id'],
                'redirect_uri' =>$GLOBALS['DS_CONFIG']['app_url'],
            ];
            $queryBuild = http_build_query($params);

            $url = "https://account-d.docusign.com/oauth/auth?";

            $botUrl = $url . $queryBuild;
            header('Location:'.$botUrl);
            
        } catch (Exception $e) {
            $_SESSION['message'] = 'error';
            return false;
        }
    }

    public function callback()
    {
        $code = $_GET['code'];

        $client_id =  $GLOBALS['DS_CONFIG']['ds_client_id'];
        $client_secret =  $GLOBALS['DS_CONFIG']['ds_client_secret'];

        $integrator_and_secret_key = "Basic " . utf8_decode(base64_encode("{$client_id}:{$client_secret}"));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,  $GLOBALS['DS_CONFIG']['authorization_server'].'/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $post = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $headers = array();
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = "authorization: $integrator_and_secret_key";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $decodedData = json_decode($result);
        $_SESSION['authData'] = $decodedData;
        $_SESSION['message'] = 'success';
        return true;
    }


    
    public function signDocument()
    {       
      try{
        $this->args = $this->getTemplateArgs();
        $args = $this->args;

        $envelope_args = $args["envelope_args"];
        
        # Create the envelope request object
        $envelope_definition = $this->make_envelope($args["envelope_args"]);
        $envelope_api = $this->getEnvelopeApi();
      
        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        
        $api_client = new \DocuSign\eSign\client\ApiClient($this->config);

        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);
        
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        
        $envelope_id = $results->getEnvelopeId();
        
        $authentication_method = 'None'; # How is this application authenticating
        # the signer? See the `authenticationMethod' definition
        # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient
        
        $recipient_view_request = new \DocuSign\eSign\Model\RecipientViewRequest([
            'authentication_method' => $authentication_method,
            'client_user_id' => $envelope_args['signer_client_id'],
            'recipient_id' => '1',
            'return_url' => $envelope_args['ds_return_url'],
            'user_name' => 'SuryaPratap', 'email' => 'soroutlove1996@gmail.com'
        ]);

        $results = $envelope_api->createRecipientView($args['account_id'], $envelope_id,$recipient_view_request);

        return header('Location:'.$results['url']);
        } catch (Exception $e) {
            
            print_r($e);
        }
        
    }


    private function make_envelope($args)
    {   
        
        $filename = 'World_Wide_Corp_lorem.pdf';

        $demo_docs_path = dirname(__DIR__)."/doc/".$filename;

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  

        $content_bytes = file_get_contents($demo_docs_path,false, stream_context_create($arrContextOptions));
        // dd($content_bytes);
        $base64_file_content = base64_encode($content_bytes);
        // dd($base64_file_content);
        # Create the document model
        $document = new \DocuSign\eSign\Model\Document([# create the DocuSign document object
        'document_base64' => $base64_file_content,
            'name' => 'Example document', # can be different from actual file name
            'file_extension' => 'pdf', # many different document types are accepted
            'document_id' => 1, # a label used to reference the doc
        ]);
        # Create the signer recipient model
        $signer = new \DocuSign\eSign\Model\Signer([# The signer
        'email' => 'soroutlove1996@gmail.com', 'name' => 'SuryaPratap',
            'recipient_id' => "1", 'routing_order' => "1",
            # Setting the client_user_id marks the signer as embedded
            'client_user_id' => $args['signer_client_id'],
        ]);
        # Create a sign_here tab (field on the document)
        $sign_here = new \DocuSign\eSign\Model\SignHere([# DocuSign SignHere field/tab
        'anchor_string' => '/sn1/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20',
        ]);
        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->settabs(new \DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$sign_here]]));
        # Next, create the top level envelope definition and populate it.

        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'email_subject' => "Please sign this Broker Agreement document - MortgageStreet",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new \DocuSign\eSign\Model\Recipients(['signers' => [$signer]]),
            'status' => "sent", # requests that the envelope be created and sent.
        ]);

        return $envelope_definition;
    }

    /**
     * Getter for the EnvelopesApi
     */
    public function getEnvelopeApi(): EnvelopesApi
    {   
        $this->config = new Configuration();
        $this->config->setHost($this->args['base_path']);
        $this->config->addDefaultHeader('Authorization', 'Bearer ' . $this->args['ds_access_token']);    
        $this->apiClient = new ApiClient($this->config);

        return new EnvelopesApi($this->apiClient);
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs()
    {   
        $envelope_args = [
            'signer_client_id' => $this->signer_client_id,
            'ds_return_url' => $GLOBALS['DS_CONFIG']['app_url']."?status=success",
        ];
        $args = [
            'account_id' =>  $GLOBALS['DS_CONFIG']['account_id'],
            'base_path' => $GLOBALS['DS_CONFIG']['api_url'],
            'ds_access_token' => $_SESSION['authData']->access_token,
            'envelope_args' => $envelope_args
        ];
        
        return $args;
        
    }


 
    
}
