<?php

declare(strict_types=1);


namespace App\Services;


class CleverReachService extends CleverReachClient
{
    private string $cleverreachClientId;
    private string $cleverreachClientSecret;
    
    public function __construct(string $cleverreachClientId, string $cleverreachClientSecret)
    {
        parent::__construct('https://rest.cleverreach.com/v3');
        $this->cleverreachClientId = $cleverreachClientId;
        $this->cleverreachClientSecret = $cleverreachClientSecret;
    }
    
    public function addEmail(string $email)
    {
        // Values from your OAuth App.
        $clientid = $this->cleverreachClientId;
        $clientsecret = $this->cleverreachClientSecret;
        
        $token_url = "https://rest.cleverreach.com/oauth/token.php";
        
        $fields["grant_type"] = "client_credentials";
    
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $token_url);
        curl_setopt($curl,CURLOPT_USERPWD, "{$clientid}:{$clientsecret}");
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close ($curl);
        
        $data = json_decode($result);
    
        $this->setAuthMode('jwt', $data->access_token);
        
        $new_user = array(
            "email"      => $email,
            "registered" => time(),  //current date
            "activated"  => 1,       //NOT active, will be set by DOI
            "source"     => "Import",
            "attributes" => array(
                "firstname" => "Steffen",
                "lastname"  => "Grell",
                "gender"    => "male"
            )
        );
        
        $target_group_id = '1068268';
//        $form_id = '252003';
    
        $this->post("/groups/{$target_group_id}/receivers", $new_user);
//        if( $success =  ) {
//            $this->post("/forms/{$form_id}/send/activate", array(
//                "email"   => $new_user["email"],
//                "doidata" => array(
//                    "user_ip"    => $_SERVER["REMOTE_ADDR"],
//                    "referer"    => $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
//                    "user_agent" => $_SERVER["HTTP_USER_AGENT"]
//                )
//            ));
//        }
    }
    
}
