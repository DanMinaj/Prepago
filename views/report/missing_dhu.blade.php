
</div>

<div><br/></div>
<h1>Missing District Heating Usage</h1>


<div class="admin2">
	
	
	<table width="100%">
		<tr>
					
			<td width="80%"><a href="{{ URL::to('admin/specialist') }}"><button type="button" class="btn btn-primary">&lt;&lt; Go back</button></a></td>
			
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
						
		<tr>
			<th width="10%"><b>ID</b></th>
			<th width="15%"><b>Username</b></th>
			<th width="10%"><b>DHU Count</b></th>
			<th width="10%"><b>Post Nov Count</b></th>
		</tr>
		
		@if(count($customers) <= 0)
			<td colspan="4"> There are no customer with missing district_heating_usage records for this selection. </td>
		@else
			
		@foreach($customers as $c) 
		
		<tr>
			
			<td> <a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) }}">{{ $c->id }}</a> </td>
			<td> <a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) }}">{{ $c->username }}</a> </td>
			<td> {{ $c->dhu_count }} </td>
			<td> {{ $c->dhu_count_post_nov }} </td>
		
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