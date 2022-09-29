<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\support\Facades\DB;
use DB;
class DatabaseController extends Model
{
   

    /*
     * Get saved cart from database
     */
    public function getCart($id) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		//$result = DB::table("carts_table")->where('cart_id', addslashes($id))->where('active',  1)->get();
		
        $result = $db->query("SELECT * FROM `carts_table` WHERE `cart_id`='" . addslashes($id) . "' AND `active`='1'");
        $db->close();
        $row = $result->fetch_assoc();
        return $row;
    }

    /*
     * Get Shopify products which are connected to Trustmeup
     */
    public function getActiveShopifyProducts($shop) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		//$result = DB::table("products")->where('store', $shop)->where('active',  1)->get();
		
        $result = $db->query("SELECT * FROM `products` WHERE `store`='" . $shop . "' AND `active`='1'");
        while($row = $result->fetch_assoc())
        {
            $rows[] = $row['shopify_id'];
        }
        return $rows;
    }

    /*
     * Get App database products
     */
    public function getProductsFromDatabase($shop) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		//$result = DB::table("products")->where('store', $shop)->get();
		
        $result = $db->query("SELECT * FROM `products` WHERE `store`='" . $shop . "'");
        if ($result->num_rows === 0) {
            return false;
        }
        while($row = $result->fetch_assoc())
        {
            $rows[$row['trustmeup_id']] = $row;
        }
        return $rows;
    }


    /*
     * Save customer cart and discount details to database
     */
    public function saveCart($store, $discount, $discount_amount, $cartId, $items, $otp) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		/*$result = DB::table('carts_table')->insert([
					'store' => $store,
					'discount_code' => $discount,
					'discount_amount' => $discount_amount,
					'cart_id' => $cartId,
					'items' => $items,
					'active' => true,
					'otp' => $otp]);*/
		
        $result = $db->query("INSERT INTO `carts_table` (`store`, `discount_code`, `discount_amount`, `cart_id`, `items`, `active`, `otp`) values('$store', '$discount', '$discount_amount', '$cartId', '$items', true, '$otp')");
        $db->close();
    }

    /*
     * Assign Trustmeup product to Shopify
     */
    public function assignProduct($shopify, $trustmeup, $shop) {
		
		$getProduct = DB::table("products")
							->where('store_name', $shop)
							->where('trustmeup_id',  $trustmeup)
							->where('shopify_id', $shopify)
							->get()
							->toArray();
		if(!empty($getProduct)){					
			$updateStatus = DB::table('products')
						->where('trustmeup_id', $trustmeup)
						->where('store_name', $shop)
						->where('shopify_id', $shopify)
						->update(['active' => 1]);
		}else{
			$sql = DB::table('products')->insert([
						'shopify_id' => $shopify,
						'trustmeup_id' => $trustmeup,
						'store_name' => $shop,
						'active' => true]);
		}
    }
	
	/*public function getPriceRule(){
		
	}
	
	public function addDiscountProduct(){
		
	}*/

    /*
     * Delete Trustmeup product from Shopify shop
     */
    public function deleteProduct($shop, $id) {        
		$deleted = DB::table('products')
					->where('trustmeup_id', $id)
					->where('store_name', $shop)
					->delete();
    }
	
	/*
	 * Update Trustmeup product status
	 */
	public function updateProductStatus($shop, $trustmeup_id) {        
		$updateStatus = DB::table('products')
						->where('trustmeup_id', $trustmeup_id)
						->where('store_name', $shop)
						->update(['active' => 0]);
    }
	
	public function updateSingleProductStatus($shop, $trustmeup_id, $productId) {        
		$updateStatus = DB::table('products')
						->where('store_name', $shop)
						->where('trustmeup_id', $trustmeup_id)
						->where('shopify_id', $productId)
						->update(['active' => 0]);
    }

    /*
     * Returns Trustmeup API credentials
     */
    public function getTrustApiCredentials($storeName, $token) {			
		$result = DB::table("users")
					->where('name', addslashes($storeName))
					->where('app_token', $token)
					->get()
					->toArray();		
		return $result;
    }

    /*
     * Get store by token
     */
    public function getStorename($token) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		//$result = DB::table("users")->where('app_token', addslashes($token))->get();
		
        $result = $db->query("SELECT * FROM `users` WHERE `app_token`='" . addslashes($token) . "'");
        $db->close();
        $row = $result->fetch_assoc();
        return $row['store'];
    }
    
     /*
     * Get store by Check alrady
     */
    public function getStorenamecheck($shop) {
        //$db = new mysqli("localhost", $this->user, $this->password, $this->database);
		
		//$result = DB::table("users")->where('store', $shop)->get();
		
        $result = $db->query("SELECT * FROM `users` WHERE `store`='" . $shop . "'");
        $db->close();
        $row = $result->fetch_assoc();
        return $row['store'];
    }

    /*
     * Auth token validation between Shopify and App server
     */
	
	public function validateApiCall($token) {
        return DB::table("users")->select('name')->where('app_token', $token)->get()->toArray();    
    }
	
	public function getapptoken($appStorePw) {
        return DB::table("users")->select('password')->where('name', $appStorePw)->get()->toArray();    
    }
    
    public function getStoreApiToken($storeName) {
        return DB::table("users")->where('name', $storeName)->get()->toArray();  
    }
    
    public function insertStoreApiToken($storeName, $appToken) {
        DB::table('users')
			->where('name', $storeName)
			->update(['app_token' => $appToken]);
    }

    /*
     * Update App settings
     * @params - $shop, $client, $password
     */

    public function updateAppSettings($storeName, $token, $clientId, $clientPassword) {
        /*$client = addslashes($params['1']);
        $password = addslashes($params['2']);
        $store = addslashes($shop);		
        $sql = "UPDATE `users` SET `client_id`='" . $client . "', `client_password`='" . $password . "' WHERE `store`='" . $store . "'";
        $result = $db->query($sql);*/
		
		$sql = DB::table('users')
						->where('name', $storeName)
						->where('app_token', $token)
						->update(['client_id' => $clientId, 'client_password' => $clientPassword]);
    }
	
	public function getCurrentStoreProducts($storeName, $trustmeup_id){
		
		$productValues = DB::table("products")
							->where('store_name', $storeName)
							->where('trustmeup_id',  $trustmeup_id)
							->where('active', 1)
							->count();
		return $productValues;
	}
	
	public function getCurrentAssignedProducts($storeName){
		
		$productValues = DB::table("products")
							->where('store_name', $storeName)
							->get()
							->toArray();
		return $productValues;
	}
	
	public function getAssignedStoreProducts($storeName, $trustmeup_id, $productId){
		
		$productValues = DB::table("products")
							->where('store_name', $storeName)
							->where('shopify_id',  "$productId")
							->where('trustmeup_id',  $trustmeup_id)
							->where('active', 1)
							->count();		
		return $productValues;		
	}
	
	public function getAssignedStoreProducts1($storeName, $trustmeup_id, $productId){
		
		$productValues = DB::table("products")
							->where('store_name', $storeName)
							//->where('shopify_id',  "$productId")
							->where('trustmeup_id',  $trustmeup_id)
							->where('active', 1)
							->count();		
		return $productValues;		
	}
	
	public function getStoreProductList($storeName, $value){
		
		$productValues = DB::table("products")
							->where('store_name', $storeName)
							->where('active',  $value)
							->get()
							->toArray();
		return $productValues;
	}
	
	public function checkIfExist($storeName, $trustmeup_id, $discountCode){
		
		$sql 	= DB::table("discount_table")
					->where('store_name', $storeName)
					->where('trustmeup_id',  $trustmeup_id)
					->where('discount_code',  $discountCode)
					->get()->toArray();
		return $sql;
	}
	
	public function getPriceRuleId($storeName, $trustmeup_id){
		$sql 	= DB::table("discount_table")
					->where('store_name', $storeName)
					->where('trustmeup_id',  $trustmeup_id)
					->get()->toArray();
		return $sql;
	}
	
	public function discountTableQ($storeName, $trustmeup_id, $priceRuleId, $discountCode, $productIds){
		
		//DB::enableQueryLog();
		$sql = DB::table('discount_table')->insert([
						'store_name' 	=> $storeName,
						'trustmeup_id' 	=> $trustmeup_id,
						'price_rule_Id' => "$priceRuleId",
						'discount_code' => $discountCode,
						'product_Id'	=> implode($productIds).",",
						'active'		=> 1
					]);
		//dd(DB::getQueryLog());
	}
	
	public function updateProductsDiscountTable($storeName, $trustmeup_id, $productIds, $priceRuleId, $active){
		$updateproducts = DB::table('discount_table')
							->where('store_name' , $storeName)
							->where('trustmeup_id', $trustmeup_id)
							->update(
								[ 'product_Id' => $productIds.',' ,  'active' => $active , 'price_rule_Id' => "$priceRuleId"]
							);
	}
	
	public function updateDiscountStatus($shop, $trustmeup_id) {        
		$updateStatus = DB::table('discount_table')
						->where('store_name' , $shop)
						->where('trustmeup_id', $trustmeup_id)
						->update(['active' => 0]);
    }
}
