
	(function(url, position, callback){
		// default values
		url = url || 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js';
		position = position || 0;
		
		// Check is jQuery exists
		if (!window.jQuery) {
			// Initialize <head>
			var head = document.getElementsByTagName('head')[0];
			// Create <script> element
			var script = document.createElement("script");
			// Append URL
			script.src = url;
			// Append type
			script.type = 'text/javascript';
			// Append script to <head>
			head.appendChild(script);
			// Move script on proper position
			head.insertBefore(script,head.childNodes[position]);
      
			script.onload = function(){
				if(typeof callback == 'function') {
					callback(jQuery);
				}
			};
		} else {
			if(typeof callback == 'function') {
				callback(jQuery);
			}
		}
	}('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', 5, function($){ 
		getResponse();
	
	var x = getCookie('code');
	if (x) {
		getCartResponse();
	}}));

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function eraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function getResponse(){
    var shop = Shopify.shop;
     var loc = window.location;
    var pathName = $(loc).attr('search'); 
	if(pathName !=''){
		var lArr= pathName.split('=');
		//alert(lArr[1]);
		if(lArr[0] !='?otp' || lArr[1] !=''){
			 var param ='shop='+shop+"&otp="+lArr[1];
				jQuery.ajax({
					type: "GET",
					url: "https://shopifyapp.satlujwebsolution.com/assignProduct?tokenn=1234567890",
					data: param,
					success: function(data){
					  var all_data = jQuery.parseJSON(data);
						var all_arr = all_data['code'];
						var status = all_data['status'];
						if(status == '1'){
							console.log(all_arr);
							setCookie('code',all_arr,1);					
						}
						

					}
				 }); 
			
			
		}
	}
   
}


function getCartResponse(){
	jQuery.ajax({
        type: 'GET',
        url: '/cart.js',
        cache: false,
        dataType: 'json',
        success: function(cart) {
			
			var items = cart.items;
			
			var ids ='';
			
			for(i=0;i<items.length;i++){
				if(i == 0){
					console.log("success"); 
					ids = items[i]['product_id'];
				}else{
					ids = ids+", "+items[i]['product_id'];
				}
			}
			console.log(ids);
           var shop = Shopify.shop; 
			 var param ='shop='+shop+"&cart=yes&cart_ids="+ids+""; 
			 jQuery.ajax({
				 type: "GET",
				 url: "https://shopifyapp.satlujwebsolution.com/assignProduct",
				 data: param,
				 success: function(data){
				   var all_data = jQuery.parseJSON(data);
					 var all_arr = all_data['code'];
					 var status = all_data['status'];
						
					 if(status == '1'){
						//console.log("here is code"+all_arr);
						 jQuery( "form#cart" ).append('<input type="hidden" name="discount" value="'+all_arr+'" >');
						 if(!jQuery("div").hasClass("cou_pan")){   
								jQuery( ".cart__footer-wrapper .js-contents" ).after('<div class="cou_pan" style="border: 1px solid #dfdcdc; padding: 25px; background-color: #f3f3f3;"><span  style="font-weight: 900;">Discount Code: '+all_arr+'</span><h2 style="font-weight: 700; font-size: 15px; color: #0f84ae;	margin-bottom:0px;">Messaggio da TrustMeUp: Dona Comprando!</h2><p style="font-weight: 600; font-size: 12px; text-align: justify; line-height: 22px; letter-spacing: 0px; margin-top: 0px;">Sostieni la tua associazione preferita SOS Villaggi del Bamhini, dona 43/A36 che ti verranno immediatamente scontati dal prezzo del fucacauistol <a href="https://www.trustmeup.com/" style="font-size: 15px; color: #0f84ae; font-weight: 600;">Dona ora</a><p></div>');
						 }		
					}else{
						 console.log("cookie not set");
					 }					
					 
					

				 
        }
        });
    }
	
	
    
				
	
   
});

}