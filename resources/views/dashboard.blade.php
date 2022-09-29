@extends('shopify-app::layouts.default')
@include('header')
		<script type="text/javascript">
            var token 		= '{{ $app_token }}';
			
            /*var ajax =  'https://trustprint.trustmeup.info/api/';
            var appUrl = 'https://trustprint.trustmeup.info/';*/
			
			var ajax	= "{{ $apiUrl }}";
            var appUrl 	= "{{ $appUrl }}";

            const requestShopifyProducts = async () => {
                const response = await fetch(ajax + '?token=' + token + '&action=getProducts');
                const json = await response.json();
            }

            const openShopifyProducts = async (id, name, discountVal) => {
				var discountCodeVal = "trustme_" + name.replace(/\s/g, "") + "_" + discountVal;
				var discountCode	= discountCodeVal.replace(/\./g,"_");
               
				let contentUrl = appUrl + "shopifyProducts?token=" + token + "&id=" + id + "&discountCode=" + discountCode + "&discountVal=" + discountVal + "&discountName=" + name;
                const response = await fetch(contentUrl);
                const html = await response.text();
                // Update modal window content
                document.getElementById("shopifyProducts").innerHTML = html;
                $("#shopifyProducts").modal();
            }

            const assignShopifyProduct = async (shopifyId, trustmeupId, discount_code, discount_name, discount_value) => {
                let ajaxUrl = appUrl + "assignProduct?token=" + token + "&shopifyId=" + shopifyId + "&trustMeId=" + trustmeupId + "&discountCode=" + discount_code + "&discountName=" + discount_name + "&discountValue=" + discount_value;
                const response = await fetch(ajaxUrl);
                if (response.status == 200) {
                    // Change status on parent window
                    var statusElementId = "trustProduct_" + trustmeupId;
                    document.getElementById(statusElementId).innerHTML = '<span class="active">Active</span>';
					
					var connectedProduct = "connectedProduct_" + trustmeupId;
                    document.getElementById(connectedProduct).innerHTML = '<span class="active clickBtn" onclick="openShopifyProducts(\'' + trustmeupId + '\',\'' + discount_name + '\',\'' + discount_value + '\')">Assign more</span>';

                    // Show disable button for product
                    let disableElementId = "trustDisable_" + trustmeupId;
                    let buttonHtml = '<a class="close" style="float: left" href="#" onclick="disableAllProduct(\'' + trustmeupId + '\',\'' + discount_name + '\',\'' + discount_value + '\')"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></a>';
                    document.getElementById(disableElementId).innerHTML = buttonHtml;

                    // Change status on modal window
                    let modalStatusElementId = "status_" + shopifyId;
                    document.getElementById(modalStatusElementId).innerHTML = '<span class="active">Assigned</span>';
					
					//Inactive Disable Button
					let enableSingleElementId 	= "trustDisable_" + shopifyId;
					let toShowContent 			= '<a class="close" style="float: left" href="#" class="close" onclick="disableSingleProduct(\'' + shopifyId + '\',\'' + trustmeupId + '\')"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></a>';
					document.getElementById(enableSingleElementId).innerHTML = toShowContent;
					
                    setTimeout(function () {
                        $("#closeShopifyProducts").click();
                    }, 300);
                }
            }

            const disableAllProduct = async (trustmeupId, name, discountVal) => {
                let ajaxUrl = appUrl + "disableAllProduct?token=" + token + "&trustMeId=" + trustmeupId;
                const response = await fetch(ajaxUrl);
                if (response.status == 200) {
					var statusElementId = "trustProduct_" + trustmeupId;
                    document.getElementById(statusElementId).innerHTML = '<span class="waiting">In Active</span>';
						
                    // Show connect button on parent window
                    let productElementId 	= "connectedProduct_" + trustmeupId;
                    let activeHtml 			= '<span class="waiting clickBtn" onclick="openShopifyProducts(\'' + trustmeupId + '\',\'' + name + '\',\'' + discountVal + '\')">Click to connect</span>';
                    document.getElementById(productElementId).innerHTML = activeHtml;
					
					//Inactive Disable Button
					let disableElementId = "trustDisable_" + trustmeupId;
                    let toShowHtml = '<span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span>';
                    document.getElementById(disableElementId).innerHTML = toShowHtml;
                }
            }
			
			const disableSingleProduct = async (productId, trustmeupId) => {
				let ajaxUrl = appUrl + "disableSingleProduct?token=" + token + "&trustMeId=" + trustmeupId + "&productId=" + productId;				
				const response = await fetch(ajaxUrl);
				if (response.status == 200) {
					// Change status on modal window
                    let modalStatusElementId = "status_" + productId;
                    document.getElementById(modalStatusElementId).innerHTML = '<span class="waiting clickBtn" onclick="assignShopifyProduct(\'' + productId + '\', \'' + trustmeupId + '\')">Click to assign this product</span>';
					
					//Inactive Disable Button
					let disableSingleElementId 	= "trustDisable_" + productId;
					let toShowContent 			= '<span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span>';
					document.getElementById(disableSingleElementId).innerHTML = toShowContent;
					
					let checkActiveData = appUrl + "checkActiveDiscount?token=" + token + "&trustMeId=" + trustmeupId;
					const getResponse 	= await fetch(checkActiveData);
					const commits 		= await getResponse.json();
					if (commits.status == 200) {
						var statusElementId = "trustProduct_" + trustmeupId;
						document.getElementById(statusElementId).innerHTML = '<span class="waiting">In Active</span>';
							
						// Show connect button on parent window
						let productElementId = "connectedProduct_" + trustmeupId;
						let activeHtml = '<span class="waiting clickBtn" onclick="openShopifyProducts(\'' + trustmeupId + '\')">Click to connect</span>';
						document.getElementById(productElementId).innerHTML = activeHtml;
						
						//Inactive Disable Button
						let disableElementId = "trustDisable_" + trustmeupId;
						let toShowHtml = '<span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span>';
						document.getElementById(disableElementId).innerHTML = toShowHtml;
					}
					setTimeout(function () {
						$("#closeShopifyProducts").click();
					}, 300);
				}
			}
        </script>

<div id="shopifyProducts" class="modal" style="min-width: 70%;height: auto"></div>
    <div style="display: none"><a href="#shopifyProducts" id="closeShopifyProducts" rel="modal:close">Close</a></div>

    <p style="display: none"><a href="#shopifyProducts" id="closeShopifyProducts" rel="modal:close">Close</a></p>
    <section class="ftco-section" style="margin-top: -2%">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <table class="table table-responsive-xl" style="overflow: hidden !important">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;&nbsp;&nbsp;Product</th>
                            <th>Discount</th>
                            <th>Shopify status</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($productData as $products)
                        <tr class="alert" role="alert">
                            <td></td>
                            <td class="d-flex align-items-center">
                                <div class="pl-3 email">
                                    <span>{{ $products->name }}</span>
                                    <span>&nbsp;&nbsp;</span>
                                </div>
                            </td>
                            <td>{{ $products->max_pac_discount }} %</td>
							@if($products->active == 1)
								<td class="status" id="trustProduct_{{ $products->id }}"><span class="active">Active</span></td>
								<td class="status" id="connectedProduct_{{ $products->id }}"><span class="active clickBtn" onclick="openShopifyProducts('{{ $products->id }}','{{ $products->name }}','{{ $products->max_pac_discount }}')">Assign more</span></td>						
								<td id="trustDisable_{{ $products->id }}">
										<a class="close" style="float: left" href="#" class="close" onclick="disableAllProduct('{{ $products->id }}','{{ $products->name }}','{{ $products->max_pac_discount }}')"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></a>
								</td>
							@else										
								<td class="status" id="trustProduct_{{ $products->id }}"><span class="waiting">In Active</span></td>
								<td class="status" id="connectedProduct_{{ $products->id }}"><span class="waiting clickBtn" onclick="openShopifyProducts('{{ $products->id }}','{{ $products->name }}','{{ $products->max_pac_discount }}')">Click to connect</span></td>
								<td id="trustDisable_{{ $products->id }}"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></td>
							@endif
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                </div>
                </div>
            </div>
        </div>
    </section>
    </body>
</html>