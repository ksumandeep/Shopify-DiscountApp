
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
	}));



function getResponse(){
    var shop = Shopify.shop;
    var param ='shop='+shop;
  
	jQuery.ajax({
		type: "POST",
		url: "getcharitybox",
		data: param,
		success: function(str){
		   
			var all_data = jQuery.parseJSON(str);
			var all_arr = all_data['data'];
			var status = all_data['status'];
				
			if(status == '1'){
				//alert(all_arr)
				jQuery("#charity-section").html(all_arr);
			
			}
			

		}
     }); 
}

