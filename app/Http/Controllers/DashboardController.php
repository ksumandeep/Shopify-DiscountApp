<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TrustmeupAPI;
use App\DatabaseController;

use Shopify\Clients\Graphql;
use Illuminate\Support\Facades\URL;

use DB;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public $appApiUrl;
	public $appUrls;
	public $getApiProducts;
	public $trustmeupAPI;
	public $dbModal;
	public $appToken;
	
	public $appApiKey;
	
	public function __construct(){
		$this->appApiUrl 		= "https://shopifyapp.satlujwebsolution.com/api/";
		$this->getApiProducts	= "https://shopifyapp.satlujwebsolution.com/getProducts/";	
		$this->appUrls 			= "https://shopifyapp.satlujwebsolution.com/";
		
		$this->dbModal			= new DatabaseController();
		
		$this->appApiKey        = "c20776b382888e4d8dcc884d755468a3";
	}

    public function getStoreDetails($shopName){
		$token = sha1($shopName);
		
		$query = $this->dbModal->getStoreApiToken($shopName);
		//dd($query[0]->app_token);
		if($query[0]->app_token != null){
		    $this->app_token = $query[0]->app_token;
		}else{
		    $this->dbModal->insertStoreApiToken($shopName, $token);
		    $this->app_token = $token;
		}
		Session::put('app_Token', $this->app_token);
		$this->appStorePw = $query[0]->password;
	} 

    public function dashboard()
    {	
        $this->getStoreDetails(Auth::user()->name);
		$storeName          = Auth::user()->name;
		
        $apiUrl 			= $this->appApiUrl;
        $appUrl 			= $this->appUrls;
		
        //$shop_name 	= $_REQUEST['shop'];
		$trustmeupAPI		= new TrustmeupAPI();
        // Products from Trustmeup API
        $productList		= $trustmeupAPI->getProducts();
        if($productList == false){
			return view('settings');
		}
		
        $productData 		= $productList->results;
        $count 				= $productList->count;
        $app_token 			= $this->app_token;
		
		$assignedProduct 	= $this->dbModal->getCurrentAssignedProducts($storeName);
		
		$tempData 			= array(); 
		
        for($i=0;$i<count($assignedProduct);$i++){
			if(!isset($tempData[$assignedProduct[$i]->trustmeup_id])){
				$tempData[$assignedProduct[$i]->trustmeup_id] = [];
			}
			if( (int)$assignedProduct[$i]->active == 1){
				array_push($tempData[$assignedProduct[$i]->trustmeup_id], $assignedProduct[$i]);
			}
		}
		
		for($i=0;$i<count($productData);$i++){
			if (array_key_exists($productData[$i]->id,$tempData)){
				if(count($tempData[$productData[$i]->id]) > 0){
					$productData[$i]->active = "1";
				}else{
					$productData[$i]->active = "0";
				}
			}else{
			    $productData[$i]->active = "0";
			}
		}
		
		$arg = array(
                        "script_tag" => array(
                            "event" => "onload",
                            "src" =>"https://shopifyapp.satlujwebsolution.com/js/flashypawz.js",
                        )
                    );
					
					
		$url 					= "https://".$this->appApiKey.":".$this->appStorePw."@".$storeName."/admin/api/2022-07/script_tags.json";			
		//$inject_js = $shopifyClint->call('POST', '/admin'.Yii::$app->params['API_DATE'].'/script_tags.json', $arg);
		
		
	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arg));

		$headers = array();
		//$headers[] = 'X-Shopify-Access-Token: {access_token}';
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} 
		curl_close($ch);
		$json = json_decode($result);
		
		//dd($json);
		
        //return view('dashboard', compact(['productData', 'count', 'shop_name', 'apiUrl', 'appUrl','app_token' ]) );
		return view('dashboard', compact(['productData', 'count', 'apiUrl', 'appUrl','app_token' ]) );
    }

    public function shopifyProducts(Request $request)
    {   
		// dd($_REQUEST);     
        // $this->getStoreDetails(Auth::user()->name);
		// $storeName          = Auth::user()->name;
		
		$token 			= $_REQUEST['token'];		
        $storeInfo 		= $this->dbModal->validateApiCall($token);     
		$storeName		= $storeInfo[0]->name;

        $apiProductUrl 	= $this->getApiProducts;
        $appUrl 		= $this->appUrls;

        $data 			= file_get_contents($apiProductUrl. "?token=" . $token . "&action=getProducts");
		
		$product_get 	= $this->dbModal->getStoreProductList($storeName, 1);
				
        $productList 	= (array) $product_get;
        $json['value'] 	= json_decode($data, true);
		foreach($json['value'] as $value){
			foreach($value as $key => $items){
				$products 	= $this->dbModal->getAssignedStoreProducts($storeName, $_REQUEST['id'], $items['id']);
				if($products > 0){
					$json['value']['products'][$key]['active'] 		= 1;
					$json['value']['products'][$key]['trustMeId'] 	= $_REQUEST['id'];
				}else{
					$json['value']['products'][$key]['active'] 		= 0;
					$json['value']['products'][$key]['trustMeId'] 	= $_REQUEST['id'];
				}
			}
		}
        $json['product_get'] 	= $productList;
		$json['discountName'] 	= $_REQUEST['discountName'];
		$json['discountCode'] 	= $_REQUEST['discountCode'];
		$json['discountValue']	= $_REQUEST['discountVal'];
		//dd($json);
        return view('popup',$json);
    }
	
	/*
     * Check Active Discount
     */
	public function checkActiveDiscount(Request $request){
		$token 			= $_REQUEST['token'];		
        $storeInfo 		= $this->dbModal->validateApiCall($token);     
		$storeName		= $storeInfo[0]->name;
		
		$trustmeupId 	= $request['trustMeId'];
		$sql			= $this->dbModal->getCurrentStoreProducts($storeName, $trustmeupId);
		if(empty($sql)){
			echo json_encode(array('status' => 200, 'message' => 'OK'));
		}else{
			echo json_encode(array('status' => 404, 'message' => 'EMPTY'));
		}
	}
    /*
     * Connect Trustmeup product with Shopify
     */
	 
	 public function discounCodeApiCommon($apiUrl){
		 
			//echo $apiUrl;
			$token      = explode("://", $apiUrl); 
			$token12    = explode(":", $token[1]);
			$appApiKey  = $token12[0];
			
			$token123   = explode("@", $token12[1]);
			$storeAppPw = $token123[0];
			//dd($token123);
			/*$tokenHeader = base64_decode($tokenParts[0]);
			$tokenPayload = base64_decode($tokenParts[1]);
			$jwtPayload = json_decode($tokenPayload);  */
		
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"content-type: application/json",
				"postman-token: $appApiKey",
				"x-shopify-access-token: $storeAppPw"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			return json_decode($response);
		
		
	}
	 
	 
    public function assignProduct(Request $request) {
        if(isset($_GET['shop'])){
		    $storeName          = $_GET['shop'];
	    }else{
	       //dd($_REQUEST);
	       $storeInfo 		= $this->dbModal->validateApiCall($_REQUEST['token']);   
	       //dd($storeInfo);
		   $storeName		= $storeInfo[0]->name;
	    }
	   
	    $appStorePw = $this->dbModal->getapptoken($storeName);
		
    	$app_storePw ="";
    		
    	foreach($appStorePw as $appStorePw1){
    			$app_storePw = $appStorePw1->password;
    	}
		
		
		
		//echo $app_token;
		//echo $appStorePw[0]['password'];
		//echo $appStorePw->password;
		//$token 			= Session::get('app_Token');		
        //$storeInfo 		= $this->dbModal->validateApiCall($token);   
		//$storeName		= $storeInfo->name;
		//$this->getStoreDetails($storeName);
        //dd($re)
		
		
		
    //Cart-page Get-Response
	if(isset($request['otp']) && $request['otp'] !=''){

		//echo "i am here?";
		
		//die();
	//GET DISCOUNT PRICE RULE_ID
		session_start();
			$opt=$request['otp'];
			$tokenParts = explode(".", $opt);  
			$tokenHeader = base64_decode($tokenParts[0]);
			$tokenPayload = base64_decode($tokenParts[1]);
			$jwtPayload = json_decode($tokenPayload);
			
			
			
			
			if(!empty($jwtPayload->pk) ){
					$arr = array(
							"status"=>'1',
							"code"=>'validate'
						);
					$str = json_encode($arr);
			}else{
				$arr = array(
						"status"=>'0',
						"code"=>'notvalidate'
					);
				$str = json_encode($arr);
			}
		return $str;
		
		exit();	

	}elseif(isset($request['cart']) && $request['cart'] =='yes'){
		
		$cart_ids = $request['cart_ids'];
		
		$ex_cart=explode(",",$cart_ids);
		
		$price_id ='';

			$apiUrl = "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules.json";
		
		//echo $apiUrl;
		//echo "<br/>";
			$response = $this->discounCodeApiCommon($apiUrl);
			
			$price_arr = array();
			if(!empty($response->price_rules)){
				foreach($response->price_rules as $res){
					$p_arr = $res->entitled_product_ids;
					
					for($i=0;$i<count($p_arr);$i++){
						$p_id = $p_arr[$i];
						if(in_array($p_id,$ex_cart)){
							$price_id = $res->id;
						}
					}
					
				}
			}
			
			if(!empty($price_id)){
				  
				  $apiUrl = "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules/".$price_id."/discount_codes.json";
					$response = $this->discounCodeApiCommon($apiUrl);
					
					
					
					if($response->discount_codes){
					  foreach($response->discount_codes as $res){
						
						  $code_id =  $res->code;
					  }
					} 
					
					  if(!empty($code_id)){
						 $arr = array(
								"status"=>'1',
								"code"=>$code_id
							);
						$str = json_encode($arr); 
					  }else{
						  $arr = array(
								"status"=>'0',
								"code"=>''
							);
						$str = json_encode($arr);
					  }
						
						
				  
				  
			  }else{
				  $arr = array(
						"status"=>'0',
						"code"=>'error data'
					);
				$str = json_encode($arr);
			  }
			
			
		//dd($str);	
		return $str;
		exit();	

	}else{
        $token 					= $_REQUEST['token'];		
        $storeInfo 				= $this->dbModal->validateApiCall($token);     
		$storeName				= $storeInfo[0]->name;
        $shopifyId 				= $request['shopifyId'];
        $trustmeupId 			= $request['trustMeId'];
		
		$products 	= $this->dbModal->getAssignedStoreProducts1($storeName, $trustmeupId, $shopifyId);
	
		if($products <= 0){
			$checkIfExist	= $this->dbModal->checkIfExist($storeName, $trustmeupId, $_REQUEST['discountCode']);	
			
			if(empty($checkIfExist)){
				
				$discountStartDate 		= date('Y-m-d',strtotime("-1 days"));			
				$prdctId				= $shopifyId.',';
				
				$productIds				= array($shopifyId);
				
				/* Add and Create Price Rule */
				$query 					= '{
												"price_rule":
												{
													"title":"'.$_REQUEST['discountCode'].'",
													"target_type":"line_item",
													"target_selection":"entitled",
													"allocation_method":"across",
													"value_type":"percentage",
													"value":"-10.0",
													"customer_selection":"all",
													"entitled_product_ids": ['.implode($productIds).'],
													"starts_at":"'.$discountStartDate.'T17:59:10Z"
												}
											}';
				$url 					= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules.json";
				$priceRuleId 			= $this->discounCodeApi($query, $url, 'POST', 1);
				
				/* Add Discount Code to Price Rule */
				$addDiscountCodeQuery 	= '{
												"discount_code":
												{
													"code":"'.$_REQUEST['discountCode'].'"
												}
											}';
				$addDiscountCodeUrl 	= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules/".$priceRuleId."/discount_codes.json";
				$this->discounCodeApi($addDiscountCodeQuery, $addDiscountCodeUrl, 'POST', 0);
				
				$this->dbModal->discountTableQ($storeName, $trustmeupId, $priceRuleId, $_REQUEST['discountCode'], $productIds);
				
			}elseif($checkIfExist[0]->active == 0){
				
				$discountStartDate 		= date('Y-m-d',strtotime("-1 days"));			
				$prdctId				= $shopifyId.',';
				
				$productIds				= array($shopifyId);
				
				/* Add and Create Price Rule */
				$query 					= '{
												"price_rule":
												{
													"title":"'.$_REQUEST['discountCode'].'",
													"target_type":"line_item",
													"target_selection":"entitled",
													"allocation_method":"across",
													"value_type":"percentage",
													"value":"-10.0",
													"customer_selection":"all",
													"entitled_product_ids": ['.implode($productIds).'],
													"starts_at":"'.$discountStartDate.'T17:59:10Z"
												}
											}';
				$url 					= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules.json";
				$priceRuleId 			= $this->discounCodeApi($query, $url, 'POST', 1);
				
				/* Add Discount Code to Price Rule */
				$addDiscountCodeQuery 	= '{
												"discount_code":
												{
													"code":"'.$_REQUEST['discountCode'].'"
												}
											}';
				$addDiscountCodeUrl 	= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules/".$priceRuleId."/discount_codes.json";
				$this->discounCodeApi($addDiscountCodeQuery, $addDiscountCodeUrl, 'POST', 0);
				
				$this->dbModal->updateProductsDiscountTable($storeName, $trustmeupId, $shopifyId, $priceRuleId, 1);
				
			}else{
				
				$getValue			= $this->dbModal->getPriceRuleId($storeName, $trustmeupId);
				$shopifyIds 		= explode(",",$getValue[0]->product_Id);
				array_pop($shopifyIds);
				array_push($shopifyIds,$shopifyId);
				
				
				$updatedProducts	= implode(',',$shopifyIds);
				
				$priceRuleId 		= $getValue[0]->price_rule_Id;
				$query 					= '{
											"price_rule":
												{
													"entitled_product_ids": [
														'.$updatedProducts.'
													]
												}
										}';
				$url 					= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules/".$priceRuleId.".json";

				$this->discounCodeApi($query, $url, 'PUT', 0);
				
				$this->dbModal->updateProductsDiscountTable($storeName, $trustmeupId, $updatedProducts, $priceRuleId, 1);
				
			}
		}else{
			
			$getValue			= $this->dbModal->getPriceRuleId($storeName, $trustmeupId);
			$shopifyIds 		= explode(",",$getValue[0]->product_Id);
			array_pop($shopifyIds);
			if(!in_array($shopifyId,$shopifyIds)){
				array_push($shopifyIds,$shopifyId);
			}	
			$updatedProducts	= implode(',',$shopifyIds);
				
			$priceRuleId 		= $getValue[0]->price_rule_Id;
			$query 					= '{
											"price_rule":
												{
													"entitled_product_ids": [
														'.$updatedProducts.'
													]
												}
										}';
			$url 					= "https://".$this->appApiKey.":".$app_storePw."@".$storeName."/admin/api/2022-07/price_rules/".$priceRuleId.".json";
			
			$this->discounCodeApi($query, $url, 'PUT', 0);
			
			$this->dbModal->updateProductsDiscountTable($storeName, $trustmeupId, $updatedProducts, $priceRuleId, 1);
				
		}		
		
        $this->dbModal->assignProduct($shopifyId, $trustmeupId, $storeName);
        echo json_encode(array('status' => 200, 'message' => 'OK'));
    }
    }

    /*
     * Disable product
     */
    public function disableAllProduct(Request $request) {
		
        // $this->getStoreDetails(Auth::user()->name);
		// $storeName          = Auth::user()->name;

		$token 			= $_REQUEST['token'];		
        $storeInfo 		= $this->dbModal->validateApiCall($token);     
		$storeName		= $storeInfo[0]->name;
				
        $trustmeupId = $request['trustMeId'];
		
		/* Delete discount */
		$getValue			= $this->dbModal->getPriceRuleId($storeName, $trustmeupId);
		$priceRuleId 		= $getValue[0]->price_rule_Id;
		
		$this->deleteDiscountApi($priceRuleId);
		/* End Discount */
		
		$this->dbModal->updateProductStatus($storeName, $trustmeupId);
		$this->dbModal->updateDiscountStatus($storeName, $trustmeupId);
        echo json_encode(array('status' => 200, 'message' => 'OK'));
    }
	
	public function disableSingleProduct(Request $request) {
		
		// $this->getStoreDetails(Auth::user()->name);
		// $storeName          = Auth::user()->name;

		$token 			= $_REQUEST['token'];		
        $storeInfo 		= $this->dbModal->validateApiCall($token);     
		$storeName		= $storeInfo[0]->name;
		$this->getStoreDetails($storeName);
				
		$trustmeupId 	= $request['trustMeId'];
		$productId		= $request['productId'];

		$getValue			= $this->dbModal->getPriceRuleId($storeName, $trustmeupId);
		$shopifyIds 		= explode(",",$getValue[0]->product_Id);
		array_pop($shopifyIds);
		
		if (($key = array_search($productId, $shopifyIds)) !== false) {
			unset($shopifyIds[$key]);
		}
		
		$updatedProducts	= implode(',',$shopifyIds);
				
		$priceRuleId 		= $getValue[0]->price_rule_Id;
		$query 					= '{
											"price_rule":
												{
													"entitled_product_ids": [
														'.$updatedProducts.'
													]
												}
										}';
		$url 					= "https://".$this->appApiKey.":".$this->appStorePw."@".$storeName."/admin/api/2022-07/price_rules/".$priceRuleId.".json";
			
		$this->discounCodeApi($query, $url, 'PUT', 0);
		
		$this->dbModal->updateSingleProductStatus($storeName, $trustmeupId, $productId);
		
		$products 	= $this->dbModal->getAssignedStoreProducts1($storeName, $trustmeupId, $productId);
		
		if($products <= 0){
			$active = 0;
			$this->deleteDiscountApi($priceRuleId);
		}else{
			$active = 1;
		}
		
		$this->dbModal->updateProductsDiscountTable($storeName, $trustmeupId, $updatedProducts, $priceRuleId, $active);
		
		//dd($shopifyIds);
		
		
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
        $mysql = new DatabaseController();
        $credentials = $mysql->getTrustApiCredentials($this->shop);
        $token = $credentials['token'];
        // Run API call to get products
        $url = "https://" . $this->shop . "/admin/api/2021-07/products.json?fields=id,title,images,created_at";
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
	
	public function settingsIndex(){
		/*if(Auth::user() == null){
			$storeName = $_REQUEST['shop'];
			$this->getStoreDetails($storeName);
		}else{			
			$this->getStoreDetails(Auth::user()->name);
			$storeName          = Auth::user()->name;
		}*/
		$this->getStoreDetails(Auth::user()->name);
		$storeName          = Auth::user()->name;
		
		$getStoreDetails	= $this->dbModal->getTrustApiCredentials($storeName, $this->app_token);
		return view('settings', compact(['getStoreDetails'])); 
	}
	
	public function updateClientInfo(Request $request){
	    
		$this->getStoreDetails(Auth::user()->name);
		$storeName          = Auth::user()->name;
		
		$clientId 		= $request->apiClient;
		$clientPassword = $request->apiPassword;
		try {
			$this->dbModal->updateAppSettings($storeName, $this->app_token, $clientId, $clientPassword);
			$message = "Your Credentials are updated Successfully.";
		} catch (\Illuminate\Database\QueryException $e) {
			// something went wrong with the query
			$message = "Sorry something wrong please try after some time.";
		} catch (\Exception $e) {
			// something else happened
			$message = "Sorry something wrong please try after some time.";
		}
		Session::put("message",$message);
		return $this->settingsIndex();
	}
	
	
	
	
	public function discounCodeApi($query, $apiUrl, $type, $returnVal){
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "$type");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

		$headers = array();
		//$headers[] = 'X-Shopify-Access-Token: {access_token}';
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		$json = json_decode($result);
		
		//dd($json);
		
		if($returnVal == 1){
			return $json->price_rule->id;
		}
	}
	
	public function deleteDiscountApi($priceRuleId){
	    // $this->getStoreDetails(Auth::user()->name);
		// $storeName          = Auth::user()->name;

		$token 			= $_REQUEST['token'];		
        $storeInfo 		= $this->dbModal->validateApiCall($token);     
		$storeName		= $storeInfo[0]->name;
		$this->getStoreDetails($storeName);
	    
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://'.$this->appApiKey.':'.$this->appStorePw.'@'.$storeName.'/admin/api/2021-10/price_rules/'.$priceRuleId.'.json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');


		$headers = array();
		//$headers[] = 'X-Shopify-Access-Token: {access_token}';
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
	}
}
