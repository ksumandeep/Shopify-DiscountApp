@include('header')
		@if (session('message'))
			 <div class="text-center alert alert-success">
				 {{ session('message') }}
				 {{ Session::forget('message') }}
			 </div>
		@endif
		<div class="Polaris-Page">
            <div class="Polaris-Page__Content">
                <div class="Polaris-Layout">
                    <div class="Polaris-Layout__AnnotatedSection">
                        <div class="Polaris-Layout__AnnotationWrapper">
                            <div class="Polaris-Layout__Annotation">
                                <div class="Polaris-TextContainer">
                                    <h2 class="Polaris-Heading">API credentials</h2>
                                    <div class="Polaris-Layout__AnnotationDescription">
                                        <p>Visit the Developers section of your TrustMeUp Merchant account to find your client ID and password.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="Polaris-Layout__AnnotationContent">
                                <div class="Polaris-Card">
                                    <div class="Polaris-Card__Section">
                                        <form method="post" action="{{ url('updateClientInfo') }}">
                                            <div class="Polaris-FormLayout">
                                                <div class="Polaris-FormLayout__Item">
                                                    <div class="">
                                                        <div class="Polaris-Labelled__LabelWrapper">
                                                            <div class="Polaris-Label"><label id="PolarisTextField3Label" for="PolarisTextField3" class="Polaris-Label__Text">API client ID</label></div>
                                                        </div>
                                                        <div class="Polaris-Connected">
                                                            <div class="Polaris-Connected__Item Polaris-Connected__Item--primary">
                                                                <div class="Polaris-TextField Polaris-TextField--hasValue">
                                                                    <input id="apiClient" class="Polaris-TextField__Input" name="apiClient" type="text" aria-labelledby="PolarisTextField3Label" aria-invalid="false" value="{{ $getStoreDetails[0]->client_id ?? '' }}">
                                                                    <div class="Polaris-TextField__Backdrop"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="Polaris-FormLayout__Item">
                                                    <div class="">
                                                        <div class="Polaris-Labelled__LabelWrapper">
                                                            <div class="Polaris-Label"><label id="PolarisTextField4Label" for="PolarisTextField4" class="Polaris-Label__Text">API password</label></div>
                                                        </div>
                                                        <div class="Polaris-Connected">
                                                            <div class="Polaris-Connected__Item Polaris-Connected__Item--primary">
                                                                <div class="Polaris-TextField Polaris-TextField--hasValue">
                                                                    <input id="apiPassword" class="Polaris-TextField__Input" name="apiPassword" type="text" aria-labelledby="PolarisTextField4Label" aria-invalid="false" value="{{ $getStoreDetails[0]->client_password ?? '' }}">
                                                                    <div class="Polaris-TextField__Backdrop"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="Polaris-FormLayout__Item">
                                                    <div class="Polaris-Stack Polaris-Stack--distributionTrailing">
                                                        <div class="Polaris-Stack__Item"><button class="Polaris-Button Polaris-Button--primary" type="submit"><span class="Polaris-Button__Content"><span class="Polaris-Button__Text">Save</span></span></button></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="Polaris-VisuallyHidden"><button type="submit" id="settingsBtn" aria-hidden="true" tabindex="-1">Submit</button></span>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>