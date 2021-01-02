
</div>

<div><br/></div>
<h1>Duplicate District Heating Meters</h1>


<div class="admin2">
	
   @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $message }}
        </div>
    @endif


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
			<th width="10%"><b>Customer ID</b></th>
			<th width="15%"><b>Username</b></th>
			<th width="30%"><b>Total Duplicate Meters</b></th>
			<th width="20%"> <i class="fa fa-trash"></i> </th>
		</tr>
		
		@if(count($customers) <= 0)
			<td colspan="4"> There are no customer with duplicate district_heating_meters records for this selection. </td>
		@else
			
		@foreach($customers as $c) 
		
		<tr>
			
			<td> <a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) }}">{{ $c->id }}</a> </td>
			<td> <a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) }}">{{ $c->username }}</a> </td>
			<td> <a href="#" data-toggle="modal" data-target="#duplicates_{{$c->id}}"> {{ count($c->duplicates) }} </a> </td>
			<td> <form style='margin: 0px;' action="" method="POST"><input type="hidden" name="customer_id" value="{{ $c->id }}"><button type="submit" class="btn btn-danger">Delete Duplicates</button></form> </td>
			
			
			  <div id="duplicates_{{$c->id}}" class="modal fade" role="dialog">
			  <div class="modal-dialog">
			  
			  <!-- Modal content-->
			  <div class="modal-content">
				
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Customer {{$c->id}} duplicate district_heating_meters</h4>
			  </div>
				
				
				<div class="modal-body">
						
						
							<h4 style='color:#3fb103;'> Original </h4>
							<b> meter_ID: </b> {{ $c->districtMeter->meter_ID }}
							<br/>
							<b> meter_number: </b> {{ $c->districtMeter->meter_number }}
							<br/>
							<b> latest_reading: </b> {{ $c->districtMeter->latest_reading }}
							<br/>
							<b> sudo_reading: </b> {{ $c->districtMeter->sudo_reading }}
							<br/>
							<hr/>
				
				
							@foreach($c->duplicates as $key=>$duplicate)
								
								
								<b> <u> Duplicate #{{ $key+1 }} </u> </b>
								<br/>
								<b> meter_ID: </b> {{ $duplicate->meter_ID }}
								<br/>
								<b> meter_number: </b> {{ $duplicate->meter_number }}
								<br/>
								<b> latest_reading: </b> {{ $duplicate->latest_reading }}
								<br/>
								<b> sudo_reading: </b> {{ $duplicate->sudo_reading }}
								<br/>
								<br/>
								
							@endforeach
							
						
				  </div>

				</div>
				</div>
				</div>
			
			
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