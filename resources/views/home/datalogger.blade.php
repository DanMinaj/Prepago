	
@section('extra_scripts')

	{!! HTML::script('resources/js/datatable/datatables.min.js') !!}
	{!! HTML::style('resources/js/datatable/datatables.min.css') !!}
	
@stop


</div>
<div><br/></div>
<h1>Remote Data Logger <span><input type="text" id="read_attempts" style="width:30px;text-align:center;" value="3"/></span>
</h1>
<div class="admin">
	

  
   @include('includes.notifications')
   
   
   @if(!$running)
   <h4> Select datalogger type </h4>
   <select name='dataloggertype' class='dl_type'>
	<option value='read'>Read</option>
	<option value='valve'>Valve Check</option>
   </select>
   
   <h4> Select scheme(s) </h4>
   <table width="100%">
   @foreach(Scheme::active(false) as $k => $s) 
		@if(($k+1) % 5 == 0)
		<td>
			<input class='scheme_selection' type='checkbox' @if(in_array($s->scheme_number, $scheme_ids)) checked @endif scheme_id='{!! $s->scheme_number!!}'> {!! $s->scheme_nickname !!}
		</td>
		</tr>
		
		<tr>
		@else
		@if($k == 0) <tr> @endif
		<td>
			<input class='scheme_selection' type='checkbox' @if(in_array($s->scheme_number, $scheme_ids)) checked @endif scheme_id='{!! $s->scheme_number!!}'> {!! $s->scheme_nickname !!}
		</td>
		@endif
   @endforeach
   </table>
   @else
	<input type='hidden' class='dl_type' value='{!! $type !!}'>
	<h4> Running {!! $type !!} datalogger ..</h4>
   @endif
   
   <table width="100%">
		<tr>
			<td colspan='6'><button class='btn btn-primary' id="run">Run</button></td>
			@if($running)<td colspan='6'><button style='float:right' class='btn btn-danger' id="stop">Stop</button></td>@endif
		</tr>
   </table>
   
   
   <ul class="nav nav-tabs" style="margin: 30px 0">
    
      
      @foreach($dataloggers as $key => $dl)
	  
		@if($key == 0)
		<li style='font-size:11px;' class="active"><a href="#{!! $key !!}" data-toggle="tab">{!! $dl->name !!} (<span id="scheme_{!! $dl->scheme_number !!}_progress_num">0</span>%) </a></li>
		@else
		<li style='font-size:11px;'><a href="#{!! $key !!}" data-toggle="tab">{!! $dl->name !!} (<span id="scheme_{!! $dl->scheme_number !!}_progress_num">0</span>%) </a></li>
		@endif
	  
	  @endforeach

	  
   </ul>
   
   
   <div class="tab-content">
    
	
	@foreach($dataloggers as $key => $dl)
	
		@if($key == 0)
		<div class="tab-pane active" class="scheme_tab" id="{!! $key !!}" style="text-align: left">
		@else
		<div class="tab-pane" class="scheme_tab" id="{!! $key !!}" style="text-align: left">
		@endif
		
		<div class="progress progress-striped active">
		  <div id="scheme_{!! $dl->scheme_number !!}_progress" class="scheme_progress bar" style="width: 0%;"></div>
		</div>
					
		<table width="100%" class="table table-bordered scheme_table"  id="scheme_{!! $dl->scheme_number !!}_table" >
					
			<thead>
				<tr>
					<th>
						PID
					</th>
					<th>
						Username
					</th>
					
					@if($type == 'valve')
					<th>
						SCU number
					</th>
					<th>
						Last Valve
					</th>
					<th>
						Last Valve Time
					</th>
					<th>
						Current Valve
					</th>
					@else
					<th>
						Meter number
					</th>
					<th>
						Last reading
					</th>
					<th>
						Last temperature
					</th>
					<th>
						Current reading
					</th>
					@endif
			
					<th>
						Attempts
					</th>
					
					
				</tr>
			</thead>
			
			<tbody id="s_{!! $dl->scheme_number !!}">
			
			</tbody>
			
		</table>
		
		</div>
		
	@endforeach
   
   </div>
   
   
   
 </div>
 
 <input type="hidden" id="baseInstallerURL" value="{!! URL::to('prepago_installer') !!}">
{!! HTML::script('resources/js/util/remote_data_logger.js?' . time()) !!}
 <script>
	$(document).ready(function(){
		var get_dataloggers_url = "/datalogger/get_dataloggers"; 
		$('#run').on('click', function(){
		
			var checked_schemes = [];
			var url = '/datalogger?s=';
			
			$('.scheme_selection:checked').each(function() {
				checked_schemes.push($(this).attr('scheme_id'));
				url += $(this).attr('scheme_id') + ',';
			});
			
			url = url.substr(0, url.length - 1);
			url += '&type=' + $('.dl_type').val();
			window.location.href = url;
				
			// $.ajax({
				// type: 'POST',
				// url: get_dataloggers_url,
				// data: { scheme_ids: checked_schemes },
				// success: function(meters, textStatus) {
					// loadMeters(meters)
				// }
			// });
		});
		$('#stop').on('click', function(){
			window.location.href = '/datalogger';
		});
		loadMeters({!! $dataloggers !!});
		
	});
 </script>
