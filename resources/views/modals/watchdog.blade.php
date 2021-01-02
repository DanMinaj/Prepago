

<div id="watchdog" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Watchdog</h4>
		</div>

		<div class="modal-body">
			@if(!WatchDog::runningInScheme())
			<form id="watchdog-form" action="/start_watchdog" method="POST">
				<table width="100%">

					<tr>
						@if(isset($data) && isset($data['permanent_meter_id']) && isset($data['id']))
						<input type='hidden' name='permanent_meter_id' value="{{ $data['permanent_meter_id'] }}">
						<input type='hidden' name='customer_id' value="{{ $data['id'] }}">
						<td width="15%"><b>Port: </b> </td>
						<td width="25%"> <input id="port" readonly name="port" style="width:30%" class="form-control" type="number" value="2221"> </td>
						@endif
					</tr>
					
					<tr>
						<td><br/></td>
					</tr>
					
					<tr>
						
						<td width="15%"><b>Run interval: </b> </td> <td width="25%"> 
						<input type='text' name='run_every' id="run_every" value='3'>
						&nbsp;&nbsp;hours</td>
						
					</tr>
					
					<tr>
						<td><br/></td>
					</tr>
					
					<tr>
						
						<td width="15%"><b>Run: </b> </td> <td width="25%"> 
						<input type='text' name='run_times' id="run_times" value='8'> times</td>
						
					</tr>
					
					<tr>
						<td><br/></td>
					</tr>
					
					<tr>
						<td colspan='2' width="100%">
							<center>
								<b>Your watchdog summary:</b><br/>
								<span class='info'></span>
							</center>
						</td>
					</tr>
					
				</table>
			@else
				Stop the current watchdog.
				
			@endif
			
		</div>
		  
		<div class="modal-footer">
			
			<table width="100">
				<tr>
					@if(!WatchDog::runningInScheme())
						<td style='float:left;vertical-align:top;'>
							<button type="submit" class="btn btn-success">Start</button>
						</td>
						</form>
					@else
						<td style='float:left;vertical-align:top;'>
							<form action="{{URL::to('stop_watchdog')}}" method="POST">
								<button type="submit" class="btn btn-danger">Stop</button>
							</form>
						</td>
					@endif
				</tr>
			</table>
			
		</div>
		  
	</div>

	</div>
	</div>
<script>
	$(function(){
		
		var run_every = $('input[name=run_every]');
		var run_times = $('input[name=run_times]');
		
		function refresh()
		{
			
			var run_every_times = run_every.val();
			var run_times_times = run_times.val();
			var info = $('.info');
			
			info.html('Watchdog run will for a total of <b>' + (run_every_times * run_times_times) + '</b> hours, at intervals of <b>' + run_every_times + '</b> hour(s) or ' + (run_every_times*60) + ' minute(s).');
			
		}
		
		run_every.on('change keyup keydown', function(){
			refresh();
		});
		
		run_times.on('change keyup keydown', function(){
			refresh();
		});
		
		refresh();
		
		
	});
</script>	