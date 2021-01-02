
</div>

<div><br/></div>
<h1>Customer error</h1>


<div class="admin2">
	<a href="">
		<button type="button" class="btn btn-info">&lt;&lt; Go back</button>
	</a>
	<br/><br/>
	
	<center>
	
	@if($custom != null)
			
		<h2> An error occured </h2>
		<h4>
		{!! $custom !!}
		</h4>
	@else
		@if($customerInfo != null)
				
			@if($dhmInfo == null)
			
			<h4> Customer {!! $customer_id !!} does not have a meter associated with it, hence is marked deleted. </h4>
			
			@else
			
			<h4> Customer {!! $customer_id !!} is currently marked as deleted as of {!! $customerInfo->deleted_at !!} </h4>
			
			@endif
		
		@else
		
		<h4> Customer {!! $customer_id !!} does not exist. </h4>
		
		@endif
	@endif
	
	</center>
	
</div>
