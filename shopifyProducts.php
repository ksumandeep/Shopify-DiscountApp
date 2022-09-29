<?php
header('Access-Control-Allow-Origin: *');
$query=array("query"=>'
				mutation {
  discountAutomaticBxgyCreate(automaticBxgyDiscount: {
    title: "trustme_GeneralStoreDiscount_10_00",
    startsAt: "2022-09-05",
    endsAt: "2022-09-14T11:59Z",
    usesPerOrderLimit: "1",
    customerBuys: {
      value: {
        #Accepts quantity, amount
        quantity: "1"
      }
      items: {
        products: {
          productsToAdd: ["gid://shopify/Product/7828708950268"]
        }
      }
    },
    customerGets: {
      value: {
        discountOnQuantity: {
          quantity: "1",
          effect: {
            percentage: 1.00
          }
        }
      }
      items: {
        products: {
          productsToAdd: ["gid://shopify/Product/7828708950268"]
        }
      }
    }}) {
    userErrors { field message code }
    automaticDiscountNode {
        automaticDiscount {
        ... on DiscountAutomaticBxgy {
          title
          summary
          status
        }
      }
    }
  }
}
');
//print_r($query);

  $db = new mysqli("127.0.0.1:3306", "laravel_app", "tmudns888!%", "laravel_app");
		
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
/*dgfdbdfb
$token='1234567890';
$version='2022-07';
$shop='discount-custom-app-test.myshopify.com';
$url='https://'.$shop. '/admin/api/'.$version.'/graphql.json';
		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_HEADER,true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($curl,CURLOPT_MAXREDIRS,10);
		$headers[]="";
		$headers[]="Content-Type: application/json";
		if(!is_null($token)){
			$headers[]="X-Shopify-Access-Token: ".$token;
			curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
		}
		curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($query));
		curl_setopt($curl,CURLOPT_POST,true);
		$response= curl_exec($curl);
		$error=curl_errno($curl);
		$error_msg=curl_error($curl);
		curl_close($curl);
		if($error){
			return $error_msg;
		}else{
			$response=preg_split("/\r\n\r\n|\n\n|\r\r/",$response,2);
			$headers=array();
			$headers_content=explode("\n",$response[0]);
			$headers['status']=$headers_content[0];
			array_shift($headers_content);
			foreach($headers_content as $content){
				$data=explode(':',$content);
				$headers[trim($data[0])]=trim($data[1]);
			}
			return array('headers'=>$headers,'body'=>$response[1]);
		}*/
?>