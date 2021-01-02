</div>

<div><br/></div>
<h1>Uptime report</h1>

<table width="100%">
		
		<a href="{!! URL::to('settings/ping') !!}">
			<button type="button" class="btn btn-info"><i class="fas fa-sim-card"></i>  Manage SIMs</button>
		</a>
		<a href="{!! URL::to('settings/ping') !!}">
			<button data-toggle="modal" data-target="#watch-schemes" class="btn btn-primary"><i class="fa fa-eye"></i> Watch Scheme</button>
		</a>
		<hr/>

	<tr>
		
		<td style='vertical-align:top;' width="10%">
			
			<input type="text" style='text-align:center' id="from" value="{!! date('Y-m-d', strtotime('3 days ago')) !!}" placeholder="From"><br/>
			<input type="text" style='text-align:center' id="date" value="{!! date('Y-m-d') !!}" placeholder="To"><br/>
		
				@foreach($all_schemes as $s) 
					<label class="check"><div style='border:1px solid #ccc;border-radius:3px;margin-bottom:10px;padding:5px;' value="{!! $s->scheme_number !!}">
					<input id="s_{!! $s->scheme_number !!}" class="scheme_{!! $s->scheme_number !!}" type="checkbox">
					&nbsp;{!! ucfirst($s->scheme_nickname) !!}
					</div>
					</label>
				@endforeach
	

		</td>
		
		<td style='vertical-align:top;' width="90%">
			
			
			<canvas id="uptime_canvas" width="1000" height="400"></canvas>
		
		
		</td>
		
	<tr/>

</table>


@include('modals.watch_schemes')



<script type="text/javascript">

   var selected_schemes = [];
   var myChart = null;

   $('input').on('click', function(){
	   
	   var from = $('#from').val();
	   var date = $('#date').val();
	   
	   if($(this).attr('class').indexOf('scheme_') == -1)
		   return;
	   
	   
	   var scheme = $(this).attr('class').split('scheme_')[1];
	   var checked = $(this).is(':checked');
	   if(checked) {
		   if(selected_schemes.indexOf(scheme) === -1)
			   selected_schemes.push(scheme);
	   }
	   else {
		  if(selected_schemes.indexOf(scheme) != -1)
		  {
			  $.each(selected_schemes, function(k,v){
				  if(v == scheme) {
					selected_schemes.splice(k, 1);
				  }
			  });
		  }
	   }
	   
	  $.ajax({
		     
		   url: 'sim_reports/get',
		   data: {schemes: selected_schemes, date: date, from: from},
		   type: 'POST',
		   dataType: 'JSON',
		   success: function(scheme_data){
			   
			   var labels = [];
			   var data_sets = [];
			   
					   
			   $.each(scheme_data, function(k, v){
				   
				    var data_set = {
						label: v.scheme,
						data: [],
						borderColor: v.chart_colour,
						borderWidth: 2,
						fill: false,
					};
					
					labels = v.labels;
					data_set.data = v.values;
					
					console.log(data_set);
					
					data_sets.push(data_set);
					
					//
					/*
				   $.each(v.values, function(k1,v1){
					   
						
						  if(v1[0] == undefined)
						  {
							  
							  if(k == 0)
							  labels.push(v1[1]);
						  
							  data_set.data.push(1);
						  }
						  else
						  {
							  
							  if(k == 0)
							  labels.push(v1[0]);
							
							  data_set.data.push(0);
						  }
					});   
					
					
					
					*/
					
			   });
			   
			   //console.log(labels);
			   
			   var data = {
					labels: labels,
					datasets: data_sets,
				};
			   
			    if(myChart == null) {
				var options = {
				responsive: true,
				title: {
					display: true,
					text: 'Chart.js Line Chart'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
					}],
					yAxes: [{
						display: true,
						ticks: {suggestedMin: 0,fixedStepSize: 1, precision:0, suggestedMax: 1}
						}]
					}
				};
			    myChart = new Chart(document.getElementById('uptime_canvas').getContext("2d"), {
					type: 'line',
					options: options,
					data: data,
				});
				}
				else {
					myChart.config.data = data;
					myChart.update();
				}
		   },
		   error: function(){
			   
		   },	
		   
	   });
	   
	   
   });
   
   $(document).ready(function(){
	   
	      
   if(window.location.hash) {
		$(window.location.hash).trigger('click');
	} else {
	  // Fragment doesn't exist
	}

   });
</script>


<div class="admin2">
		
</div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
