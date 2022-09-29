<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopifyWebHooksController extends Controller
{

    public $shop;
    public $token;
    public $appUrl;

    public function __construct() {
        //include(dirname(__DIR__, 1) . "/inc/config.php");
        $this->appUrl = $appUrl;
    }

    /*
     * Register necessary hooks
     */
    public function registerHooks() {
        $this->orderPaidHook();
        $this->scriptTagHook();
    }

    /*
     * Shopify ScriptTag hook
     */
    public function scriptTagHook() {
        $url = "https://" . $this->shop . "/admin/api/2021-07/script_tags.json";
        $curl = curl_init($url);
        $src = $this->appUrl . "js/shopify/trust_v1.js";
        curl_setopt($curl, CURLOPT_URL, $url);
        $parameters = [
            'script_tag' => [
                'event' => 'onload',
                'src' => $src
            ]
        ];
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "X-Shopify-Access-Token: " . $this->token
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $discountResponse = json_decode(curl_exec($curl));
        curl_close($curl);
    }

    /*
     * Shopify OrderPaid hook
     */
    public function orderPaidHook() {
        $url = "https://" . $this->shop . "/admin/api/2021-07/webhooks.json";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        $hookUrl = $this->appUrl . "api/shopify/webhookOrder/";
        $parameters = [
            'webhook' => [
                'topic' => 'orders/paid',
                'address' => $hookUrl,
                'format' => 'json'
            ]
        ];
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "X-Shopify-Access-Token: " . $this->token
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $discountResponse = json_decode(curl_exec($curl));
        curl_close($curl);
    }


    /*
     * Token setter
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /*
     * Shop setter
     */
    public function setShop($shop) {
        $this->shop = $shop;
    }
}
