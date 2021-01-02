
</div>

<div><br/></div>
<h1>Missing Standing Charges ({{ count($customers) }}) - {{ Input::get('option') }}</h1>


<div class="admin2">
	
	@include('includes.notifications')
 
		<table width="100%">
			<tr>
						
				<td>
					<h4>Date: {{ $date }}</h4>
					<h4>Customers: {{ count($customers) }}</h4>
				</td>
			</tr>
		</table>
		
	<table width="100%">
		<tr>
					
			<td width="80%">
			<form action="" method="POST">
				<button type="submit" class="btn btn-primary">Rectify all</button>
				<input type="hidden" name="customers" value="{{ json_encode($customers->lists('id')) }}">
			</form>
			</td>
			
			<td width="20%">
			
			 <form id="option_form" action="" method="GET">
				<select name="option">
				
					
					@if(isset($_GET['option']))
						
						@if($_GET['option'] == 'All Schemes')
						<option value="{{ $_GET['option'] }}">{{ $_GET['option'] }}</option>
						<option value="{{ $scheme->company_name }}">{{ $scheme->company_name }}</option>
						@endif
						
						@if($_GET['option'] == $scheme->company_name)
						<option value="{{ $_GET['option'] }}">{{ $_GET['option'] }}</option>
						<option value="All Schemes">All Schemes</option>
						@endif
						
					@else
						
						<option value="{{ $scheme->company_name }}">{{ $scheme->company_name }}</option>
						<option value="All Schemes">All Schemes</option>			
						
					@endif
				</select>
			</form>
	
			</td>
		
		
		</tr>
	</table>
	
	

			
	<table class="table table-bordered">
						
		
		@if(count($customers) <= 0)
			@if(Input::get('option') == 'All Schemes')
			<td colspan="4"> There are no customer with missing standing charges.</td>
			@else
			<td colspan="4"> There are no customer with missing standing_charge for the scheme {{ Input::get('option') }} </td>
			@endif
		@else
			
		@foreach($customers as $c) 
		
		<tr>
			
			<td> <a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) }}">Customer #{{ $c->id }} - {{ $c->username }}</a> </td>
			@if($c->todaysDhu) 
				<td> <a href="{{ URL::to('edit_dhu/' . $c->todaysD) }}">DHU #{{ $c->todaysDhu->id }}</a> </td>
				<td> &euro;{{ $c->todaysDhu->standing_charge }} </td>
			@endif
			
			
		
		</tr>
		@endforeach
		
		@endif
		
	</table>
	
	
	


</div>

<script>
	
	$(function(){
		
	
	$('select[name=option]').on('change', function(){
		$('#option_form').submit();
	});
	
	
	
	});
	
</script>