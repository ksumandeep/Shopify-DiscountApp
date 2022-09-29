<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DatabaseController;
use DB;

class APIController extends Controller
{
    public $shop;


    public function apiCall($action)
    {

        $shopifyAuth 	= new DatabaseController();
        $storeInfo 		= $shopifyAuth->validateApiCall($_REQUEST['token']);
		$validation		= $storeInfo[0]->name;
		
        $shop = $validation;
        dd($shop);

        if (!$validation) {
            die("Failed authentication");
        } 
        else 
        {

            $shop = $validation;
            $this->apiCallget($_REQUEST['action']);
        }
    }

    public function apiCallget($action)
    {
        switch ($action) {
            case "settings":
                $this->updateSettings($_REQUEST);
                break;
            case "getProducts":
                $this->getShopifyProducts();
                break;
            case "assignProduct":
                $this->assignProduct($_REQUEST);
                break;
            case "disableProduct":
                $this->disableProduct($_REQUEST);
                break;
        }
    }
    
    /*
     * Connect Trustmeup product with Shopify
     */
    public static function assignProduct($params) {
       //print_r($params);
        $mysql = new DatabaseController();
        $shopifyId = $params['1'];
        $trustmeupId = $params['2'];
        $mysql->assignProduct($shopifyId, $trustmeupId, $this->shop);
        echo json_encode(array('status' => 200, 'message' => 'OK'));
		
    }
    /*
     * Disable product
     */
    public static function disableProduct($params) {
        $trustmeupId = $params['1'];
        $mysql = new DatabaseController();
        $mysql->deleteProduct($this->shop, $trustmeupId);
        echo json_encode(array('status' => 200, 'message' => 'OK'));
    }

    /*
     * Settings page update values
     */
    public function updateSettings($params)
    {
        $mysql = new DatabaseController();
        $mysql->updateAppSettings($this->shop, $params);
    }

    /*
     * Get Shopify products
     */
	 
    public function getShopifyProducts()
    {
        

        $shopifyAuth = new DatabaseController();
        $storeInfo 		= $shopifyAuth->validateApiCall($_REQUEST['token']);
		$validation		= $storeInfo[0]->name;
		
        $shop = $validation;

        $db = DB::table("users")->where('name', $shop)->get();
        foreach($db as $data){
            $token = $data->password;

        }

        // Run API call to get products
        $url = "https://" . $shop . "/admin/api/2021-07/products.json?fields=id,title,images,created_at";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Accept: application/json",
            "X-Shopify-Access-Token: " . $token,
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $products = curl_exec($curl);
        curl_close($curl);
        echo $products; 
		
    }


    public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array())
    {

        // Build URL
        $url = "https://" . $shop . $api_endpoint;
        if (!is_null($query) && in_array($method, array('GET', 'DELETE'))) $url = $url . "?" . http_build_query($query);

        // Configure cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
        // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Setup headers
        $request_headers[] = "";
        if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) $query = http_build_query($query);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }

        // Send request to Shopify and capture any errors
        $response = curl_exec($curl);
        $error_number = curl_errno($curl);
        $error_message = curl_error($curl);

        // Close cURL to be nice
        curl_close($curl);

        // Return an error is cURL has a problem
        if ($error_number) {
            return $error_message;
        } else {

            // No error, return Shopify's response by parsing out the body and the headers
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

            // Convert headers into an array
            $headers = array();
            $header_data = explode("\n", $response[0]);
            $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
            array_shift($header_data); // Remove status, we've already set it above
            foreach ($header_data as $part) {
                $h = explode(":", $part);
                $headers[trim($h[0])] = trim($h[1]);
            }

            // Return headers and Shopify's response
            return array('headers' => $headers, 'response' => $response[1]);

        }
    }



    }





