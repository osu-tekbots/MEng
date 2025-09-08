<?php

function AuthAPI($identitiesApiId, $identitiesApiSecret) {
    $data = Array(
        'client_id' => $identitiesApiId,
        'client_secret' => $identitiesApiSecret,
        'grant_type' => 'client_credentials'
        );

    $header = Array(
        'Content-Type: application/x-www-form-urlencoded',
        'accept: application/json'
        );

    $values = json_decode(CallAPI("POST", "https://api.oregonstate.edu/oauth2/token", $header, $data), true);
    if (!array_key_exists("access_token", $values)){
        echo "No access token. Could not Authenticate.";
        exit(1);
    }
    $access_token = $values['access_token'];

    return $access_token;
}

function CallAPI($method, $url, $header, $data = false) {
    $curl = curl_init();
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            break;
        case "GET":
            curl_setopt($curl, CURLOPT_HTTPGET, true);
			if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
			break;
    }
	
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);

    $result = curl_exec($curl);
	
	curl_close($curl);
 
    return $result;
}

?>