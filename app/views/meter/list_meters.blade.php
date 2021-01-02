                            
</div>

<div><br/></div>
<h1>Installed Meters</h1>
@include('includes.search_form', array('searchURL'=> URL::to('search_installed_meters') ))

<div style="clear:both"></div>

<ul class="nav nav-tabs">
	<li class="active"><a href="#installed_meters" data-toggle="tab">Meter Data</a></li>
	<li><a href="#meter_readings" data-toggle="tab">Meter Readings</a></li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="installed_meters">
		<table class="sortthistable table table-bordered">
			<thead>
				<th>Meter Number</th>
				<th>Install Date</th>
				<th>Addresss</th>
				<th>In Use</th>
				<th>&nbsp;</th>
			</thead>
			<tbody>
				<?php
				if ($meters == "")
					echo "There are no data to show";
				else
					foreach ($meters as $meter):
						?>
					<tr>
						<td><?php echo $meter['meter_number'] ?></td>
						<td><?php echo $meter['install_date'] ?></td>
						<td><?php echo $meter['house_name_number']. ' ' . $meter['street1'] ?></td>
						<td><?php echo ($meter['in_use'] == 0)? 'No' : 'Yes'; ?></td>
						<td><a  class="btn btn-info" type="button" href="<?php echo URL::to('installed_meters/meter_data/'.$meter['ID']) ?>">View</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="tab-pane" id="meter_readings">
		<div class="pull-left"><h3><a href="{{{ $csv_url }}}">Download CSV</a></h3></div>
		<div class="pull-right">
			<form method="post" action="<?php echo URL::to('installed_meters') ?>"class="form-inline">
				<label>From</label>
				<input id="from" name="from" type="text">
				<label>To</label>
				<input id="to" name="to" type="text">
				<input type="submit" value="search" class="btn-success"/>
			</form>
		</div>
		<table class="sortthistable table table-bordered">
			<thead>
				<th>Meter Number</th>
				<th>Date</th>
				<th>Reading</th>
			</thead>
			<tbody>
			@if ($meterReadings == "")
				There are no data to show
			@else
				@foreach ($meterReadings as $meterReading)
				<tr>
					<td>{{{ $meterReading->permanentMeter ? $meterReading->permanentMeter->meter_number : '' }}}</td>
					<td>{{{ $meterReading->time_date }}}</td>
					<td>{{{ $meterReading->reading1 }}}</td>
				</tr>
				@endforeach
			@endif
			</tbody>
		</table>
		
		@if ($meterReadings)
			<div class="pull-right" id="meter-reading-pagination">{{ $meterReadings->appends(Request::except('page'))->links(); }}</div>
		@endif
	</div>
</div>

<script type="text/javascript">
	
	$(document).ready(function() {
		$(".sortthistable").tablesorter();
		$("#to").datepicker({ dateFormat: 'dd-mm-yy' });
		$("#from").datepicker({ dateFormat: 'dd-mm-yy' });
		
		//keep last opened tab on refresh
		$('a[data-toggle="tab"]').on('shown', function (e) {
			//save the latest tab; use cookies if you like 'em better:
			localStorage.setItem('lastTab', $(e.target).attr('href').replace('#', ''));
		});

		//go to the latest tab, if it exists:
		var lastTab = localStorage.getItem('lastTab');
		if (lastTab) {
			$('a[href="#' + lastTab + '"]').tab('show');
		}
	});
</script>


<div class="cl">&nbsp;</div>
</div>
</div>
</body>
</html>
