
</div>

<div><br/></div>
<h1>Mass spread cost</h1>


<div class="admin2">
	
	@include('includes.notifications')
 
	<div class="container-fluid">
		
		<div class="row-fluid">
			<div class="span2">
				<b> Scheme </b>
			</div>
			<div class="span4">
				<select id="schemes" name="schemes">
					@foreach($schemes as $s) 
						<option value="{!! $s->id !!}"> {!! $s->company_name !!} ({!! $s->id !!}) </option>
					@endforeach
				<select>
			</div>
			<div class="span2">
				<b> Date </b>
			</div>
			<div class="span4">
				<input id="date" type="text" name="date" value="{!! date('Y-m-d') !!}">			
			</div>
		</div>
		
		<div class="row-fluid">
			<div class="span2">
				<b> # of days </b>
			</div>
			<div class="span4">
				<input id="days" type="text" name="days" value="2">			
			</div>
			<div class="span2">
				<b> Threshold # &euro;  </b>
			</div>
			<div class="span4">
				<input id="threshold" type="text" name="threshold" value="7">		
			</div>
		</div>
		
		
	</div>
	
	
	
	<div class="ajax-title">
	
	</div>
	
	<table id="ajax-table" width="100%" class="table table-bordered">
		
		
		
	</table>
		
</div>

<script type="text/javascript">
$(function(){
	
	var checkHandler = null;
	var title = $('.ajax-title');
	var table = $('#ajax-table');
	
	
	$('#schemes, #date, #days, #threshold').on('change', function(){
		
		var scheme_id = $('#schemes').val();
		var date = $('#date').val();
		var num_days = $('#days').val();
		var threshold_amnt = $('#threshold').val();
		
		if(checkHandler != null) {
			clearTimeout(checkHandler);
			checkHandler = null;
		}
		
		checkHandler = setTimeout(function(){
			
			$.ajax({
				
				url: "/settings/customers/spread/check",
				data: {scheme_id: scheme_id, date: date, num_days: num_days, threshold_amnt: threshold_amnt},
				method: "POST",
				success: function(data){
					console.log(data);

					if(data.dhus.length > 0) {
							
						title.html("<h3> Spreading " + data.dhus.length + " charges over the threshold of &euro;" + threshold_amnt + " over " + num_days + " days (inclusive) <button id='ajax-spread' class='btn btn-warning'>Spread now</button></h3>");
							
						var a = "";
						a += "<thead>";
						a += "<tr>";
						a += "<th><b>Customer</b></th>";
						a += "<th><b>Cost of day</b></th>";
						a += "<th><b>kWh Usage/Cost</b></th>";
						a += "<th><b>Previous Cost of day</b></th>";
						a += "</tr>";
						a += "</thead>";
						a += "<tbody>";
						$.each(data.dhus, function(k, v) {
							a += "<tr>";
							a += "<td><a href='/customer/" + v.customer.id + "'>" + v.customer.id + "</a></td>";
							a += "<td>&euro;" + v.cost_of_day + "</td>";
							a += "<td>" + v.total_usage + "kWh / &euro;" + v.unit_charge + "</td>";
							a += "<td> &euro;" + v.prev.cost_of_day + "</td>";
							a += "</tr>";
						});
						a += "</tbody>";
						
						table.html(a);
						
						$('#ajax-spread').on('click', function(){
							$.ajax({
								url: "/settings/customers/spread",
								data: {scheme_id: scheme_id, date: date, num_days: num_days, threshold_amnt: threshold_amnt},
								method: "POST",
								success: function(data){ console.log(data) },
								error: function(){ alert('Error'); }
							});
						});
						
						//a += "";
						
					} else {
						
						title.html("<h3> There are no charges above the threshold of &euro;" + threshold_amnt + " to spread on the " + date + " </h3>");
						table.html('');
						
						//
				}
					
				},
				error: function(){
					
					alert('Error');
					
				}
				
			});
			
		}, 100);
		
	});

	
	
	
});
</script>

	