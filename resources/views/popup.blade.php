<br>
        <div class="row justify-content-center">

                <div class="top-logo">
                    <img src="{{ asset('/images/logotmu.png') }}" alt="" />
                </div>

        </div>

        <table class="table table-responsive-xl" id="shopifyTable">
            <thead>
            <tr>
                <th>Product</th>
                <th>Created at</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
			
				@foreach($value as $item)
					@foreach($item as $vals)
						<tr class="alert" role="alert">
							<td class="d-flex align-items-center">
								@if(isset($vals['images'][0]['src']))
								<div class="img" style="background-image: url({{ $vals['images'][0]['src']}}"></div>
								@else
								<div class="img"></div>
								@endif
								<div class="pl-3 email">
									<span>{{ $vals['title'] }}</span>
									<span>ID: {{ $vals['id'] }}</span>
								</div>
							</td>
							<td>{{ date('M d,Y', strtotime($vals['created_at'])) }}</td>
							@if($vals['active'] == 1)
								<td class="status" id="status_{{ $vals['id'] }}"><span class="active">Assigned</span></td>
								<td id="trustDisable_{{ $vals['id'] }}">
									<a class="close" style="float: left" href="#" class="close" onclick="disableSingleProduct('{{ $vals['id'] }}','{{ $vals['trustMeId'] }}')"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></a>
								</td>
							@else
								<td class="status" id="status_{{ $vals['id'] }}"><span class="waiting clickBtn" onclick="assignShopifyProduct('{{ $vals['id'] }}', '{{ $_REQUEST['id'] }}','{{ $discountCode }}','{{ $discountName }}','{{ $discountValue }}')">Click to assign this product</span></td>
								<td id="trustDisable_{{ $vals['id'] }}"><span aria-hidden="true"><i class="fa fa-low-vision fa-2x"></i></span></td>
							@endif
						</tr>
					@endforeach
				@endforeach
				
				
            </tbody>
        </table>