<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopifyAuthController extends Controller
{
    public $secret;

    function __construct() {
        //require_once(dirname(__DIR__, 1) . "/inc/config.php");
        $this->secret = $sharedSecret;
    }

    /*
     * $params - $_REQUEST
     */
    public function checkHmac($params)
    {
        $hmac = $params['hmac']; // Retrieve HMAC request parameter
        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexographically
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $this->secret);

        // Use hmac data to check that the response is from Shopify or not
        if (hash_equals($hmac, $computed_hmac)) {
            return true;
        }
        return false;
    }
}
