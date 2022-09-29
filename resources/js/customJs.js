var token = '<?php echo $this->settings['app_token']; ?>';
            var ajax = '<?php echo $this->ajax; ?>';
            var appUrl = '<?php echo $this->appUrl; ?>';

            const requestShopifyProducts = async () => {
                const response = await fetch(ajax + '?token=' + token + '&action=getProducts');
                const json = await response.json();
            }

            const openShopifyProducts = async (id) => {
                let contentUrl = appUrl + "pages/shopifyProducts.php?token=" + token + "&id=" + id;
                const response = await fetch(contentUrl);
                const html = await response.text();
                // Update modal window content
                document.getElementById("shopifyProducts").innerHTML = html;
                $("#shopifyProducts").modal();
            }

            const assignShopifyProduct = async (shopifyId, trustmeupId) => {
                let ajaxUrl = ajax + "?token=" + token + "&action=assignProduct" + "&1=" + shopifyId + "&2=" + trustmeupId;
                const response = await fetch(ajaxUrl);
                if (response.status == 200) {

                    // Change status on parent window
                    let statusElementId = "trustProduct_" + trustmeupId;
                    document.getElementById(statusElementId).innerHTML = '<span class="active">Active</span>';

                    // Show disable button for product
                    let disableElementId = "trustDisable_" + trustmeupId;
                    let buttonHtml = '<a class="close" style="float: left" href="#" onclick="disableProduct(\'' + trustmeupId + '\')"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></a>';
                    document.getElementById(disableElementId).innerHTML = buttonHtml;

                    // Change status on modal window
                    let modalStatusElementId = "status_" + shopifyId;
                    document.getElementById(modalStatusElementId).innerHTML = '<span class="active">Activated</span>';
                    setTimeout(function () {
                        $("#closeShopifyProducts").click();
                    }, 800);
                }
            }

            const disableProduct = async (trustmeupId) => {
                let ajaxUrl = ajax + "?token=" + token + "&action=disableProduct" + "&1=" + trustmeupId;
                console.log(ajaxUrl);
                const response = await fetch(ajaxUrl);
                if (response.status == 200) {
                    // Remove disable button on parent window
                    let statusElementId = "trustDisable_" + trustmeupId;
                    document.getElementById(statusElementId).innerHTML = '<span>&nbsp;</span><span>&nbsp;</span>';

                    // Show connect button on parent window
                    let productElementId = "trustProduct_" + trustmeupId;
                    let activeHtml = '<span class="waiting" onclick="openShopifyProducts(\'' + trustmeupId + '\')">Click to connect</span>';
                    document.getElementById(productElementId).innerHTML = activeHtml;
                }
            }