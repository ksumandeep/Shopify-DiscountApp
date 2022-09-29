<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrustmeupAPIController extends Controller
{
    public $token;
    public $issetOtp = false;
    public $otp;
    public $shop;
    //protected $apiUrl = "https://platform.beta.trustmeup.com/api/e-merchant/v1/";
    protected $apiUrl = "https://api.trustmeup.com/api/e-merchant/v1/";

    /*
     * TrustmeupAPI constructor.
     */
    public function __construct()
    {
        $this->shop = $_REQUEST['shop'];
        $this->token = $this->getToken();
        
    }

    /*
     * Shop setter
     */
    public function setShop($shop) {
        $this->shop = $shop;
        $this->token = $this->getToken();
    }

    /*
    * Get token from API
    * @return string
    */
    public function getToken()
    {
        $mysql = new DatabaseController();
        $credentials = $mysql->getTrustApiCredentials($this->shop);
        $data = ['client_id' => $credentials['client_id'] , 'password' => $credentials['client_password'] ];
        $curl = curl_init($this->apiUrl . "auth/login/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        return $response->token;
    }


    /*
     * Get PAC balance
     * @return int
     */
    public function getBalance($otp)
    {
        $curl = curl_init($this->apiUrl . "users/user/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200)
        {
            return $response->pac_balance;
        }
        else
        {
            return 0;
        }
    }

    /*
     * Product synchronization
     * @return object
     */
    public function getProducts()
    {
        $curl = curl_init($this->apiUrl . "merchants/products/?limit=10000");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token]);
        $response = json_decode(curl_exec($curl));
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200)
        {
            $products = $response->results;
        }
        else
        {
            $products = $response;
        }
        return $response;
    }

    /*
     *  Delete Trustmeup cart
     *  @param $otp
     * @return mixed
     */
    public function voidCart($otp)
    {
        $curl = curl_init($this->apiUrl . "orders/void-cart/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $response;
    }
    /*
     * Add to cart
     * API request Body:
     *                  "product": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
     *                   "price": "string",
     *                   "quantity": 1,
     *                   "external_product_id": "string",
     *                   "external_product_name": "string"
     *
     */
    /*
     * Adds items to Trustmeup API cart
     *  @param $product
     *  @param $price
     *  @param $quantity
     *  @param $external_id
     *  @param $external_name
     *  @param $otp
     *  @return mixed
     */
    public function addToCart($product, $price, $quantity, $otp)
    {
        $data = ['product' => $product, 'price' => $price, 'quantity' => $quantity];
        $curl = curl_init($this->apiUrl . "orders/cart_items/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
    }
    /*
     * Get Trustmeup Cart
     *  @param $otp
     *  @return object
     */
    public function getCart($otp)
    {
        $curl = curl_init($this->apiUrl . "orders/cart_items/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        return $response;
    }

    /*
     *    Create Trustmeup provision
     *  @param $otp
     */
    public function createProvision($otp)
    {
        $curl = curl_init($this->apiUrl . "orders/create-provision/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        return $response;
    }


    /*
     * Release Trustmeup Provision
     */
    public function releaseProvision($otp)
    {
        $curl = curl_init($this->apiUrl . "orders/release-provision/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
    }

    /*
     *
     *       Do Trustmeup checkout
     *  @param $otp
     */
    public function doCheckout($otp)
    {
        $curl = curl_init($this->apiUrl . "orders/checkout/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Token ' . $this->token, 'OTP: ' . $otp]);
        $response = json_decode(curl_exec($curl));
        file_put_contents("checkouts.txt", print_r($response, true), FILE_APPEND);
        curl_close($curl);
    }
    
}
