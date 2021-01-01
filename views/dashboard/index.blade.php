<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.9/css/all.css" integrity="sha384-5SOiIsAziJl6AWe0HWRKTXlfcSHKmYV4RBF18PPJ173Kzn7jzMyFuTtk8JA7QQG1" crossorigin="anonymous">

@if ($message = Session::get('successMessage'))
	<div style="color: #468847;background-color: #dff0d8;border-color: #d6e9c6;padding: 14px;margin: 10px 0;">
		{{ $message }}
	</div>
@endif

@if ($message = Session::get('errorMessage'))
	<div style="color: #b94a48;background-color: #f2dede; border-color: #eed3d7;padding: 14px;margin: 10px 0;">
		{{ $message }}
	</div>
@endif

@if( Auth::user() )
<div class="search">
	{{ Form::open(array('url' => 'prepago_installer/search')) }}
	{{ Form::text('search') }}
	{{ Form::submit('', array('class' => 'btn_search')) }}
	{{ Form::close() }}
</div>
@endif

<div class="title"><div class="ico_units"></div>
@if($searched)
	<h1>Dashboard: Searched Units</h1>
@else
	<h1>Dashboard: Installed Units</h1>
@endif
<h2>{{ count($installed_units) }}</h2></div>
<button 
type="button" id="manage-box" onclick="read_all_meters()" class="btn btn-primary"><i class="fa fa-burn"></i> Read all meters</button>
<!--<button 
type="button" id="manage-box" class="btn btn-warning"><i class="fa fa-edit"></i> [Disabled] Auto-fill missing secondary addresses</button>-->
<br/><br/>

<script>
	
	$('#manage-box').on('click', function(){
		$('.modal-fill').html('Preparing to run..');
		$('.modal').fadeIn();
		$('.close').on('click', function(){
			$('.modal').fadeOut();
		});
	});
	
	

	function fillMissingSecondary()
	{
		
			
		
	}
	
</script>

<style>
.header {
    width: 100%;
    background: #F7F7F7;
    border-top: 0.5em solid #337ab7;
    border-bottom: 1px solid #E5E5E5;
    display: block;
    padding: 0.5em 0em 0.5em 0em;
}
</style>

	
<div id="read-all-modal" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Read all meters</h4>
	  </div>
	 
	  <div class="modal-body">
			
			
			<table class="modal-body-table">
				
			</table>
			
	  </div>
	  
	  <!--
	  <div class="modal-footer">
		<button type="submit" class="btn btn-primary">Submit</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	  -->
	  
	</div>

</div>
</div>

<table class="table sortthistable" width="100%">
	<thead style="background: #337ab7; color: white;">
	<tr>
		<th width="10%">Meter</th>
		<th width="32%">Address</th>
		<th width="11%">Reading</th>
		<th width="11%">Temp</th>
		<th width="11%">SCU</th>
		<th width="21%">Install date</th>
	</tr>
	</thead>
	<tbody>
	@if(count($installed_units) > 0)
		@foreach($installed_units as $unit)
			<tr unit-id="{{ $unit->ID }}" unit-username="{{ $unit->username }}" class="unit unit_row{{ $unit->installation_confirmed == 0 ? ' warn-red' : ''}}">
				<td class="unit_cell unit_number" >
					<a href="{{ URL::to('prepago_installer/edit-unit/'.$unit->ID) }}" title="Edit">
						{{ $unit->meter_number }}
					</a>
				</td>
				<td class="unit_cell unit_type">
					@if ($unit->ev_rs_address)
						{{ $unit->ev_rs_address }}
					@else
						{{ $unit->house_name_number }}, {{ $unit->street1 }}, {{ $unit->town }}, {{ $unit->county }}
					@endif
				</td>
				<td>
				{{ $unit->lastReading }} kWh 	
				@if ($unit->scu_type == 'm')
						@if(!$unit->SCUReady) <span style="background-color: yellow;padding: 0 3px;margin-left:1px;">S</span>  @endif 
						@if(!$unit->MeterReady) <span style="background-color: yellow;padding: 0 3px;margin-left:1px;">M</span> @endif
				@endif
				</td>
				<td>
					@if(strpos($unit->lastTemp, "n/a") === false)
						<i style="color:#62c462;" class="fa fa-check"></i>&nbsp;{{ $unit->lastTemp }}&deg;C
					@else
						<i style="color:#ee5f5b;" class="fa fa-times"></i>&nbsp;n/a
					@endif
				</td>
				<td>
					@if(strlen($unit->last_valve) > 3)
						<i style="color:#62c462;" class="fa fa-check"></i>
						@if($unit->last_valve == 'closed') 
						(C)
						@elseif($unit->last_valve == 'open')
						(O)
						@else
						(U)
						@endif
					@else
						<i style="color:#ee5f5b;" class="fa fa-times"></i>
					@endif
				</td>
				<td>
					{{ Carbon\Carbon::parse($unit->install_date)->format('d/m/Y') }}
				</td>
				<!--<td class="unit_cell unit_installed" style="width:15%">{{ $unit->install_date }}</td>-->
			</tr>
		@endforeach
	@else
		<tr class="unit_row">
			<td class="unit_cell unit_type">No Units Found</td>
			<td class="unit_cell unit_type">&nbsp;</td>
			<td class="unit_cell unit_type">&nbsp;</td>
			<td class="unit_cell unit_type">&nbsp;</td>
			<td class="unit_cell unit_type">&nbsp;</td>
		</tr>
	@endif
	</tbody>
</table>


<script type="text/javascript" src="{{asset('resources/js/installer.js')}}?<?php echo time(); ?>"></script>
<script>
	$(document).ready(function() {
		$(function() {
			//
			$(".sortthistable").tablesorter({
				
				dateFormat: "dd/mm/yyyy",
				headers: {
				  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
				  2: { sorter: "digit", string: "min" }, // non-numeric content is treated as a MAX value
				  3: { sorter: "digit", string: "min" },  // non-numeric content is treated as a MIN value
				  4: { sorter: "digit", string: "min" },  // non-numeric content is treated as a MIN value
				  5: { sorter: "digit", string: "min" },  // non-numeric content is treated as a MIN value
				  6: { sorter: "date", dateFormat: "dd/mm/yyyy",},  // non-numeric content is treated as a MIN value
				},
				sortList: [[1, 0]],
				cssHeader: '',
				
			});
		});
	});
</script>
	