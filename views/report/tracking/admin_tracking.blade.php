<br />
<div class="cl"></div>
<h1>Tracking topups</h1>

<div class="admin">


<ul class="nav nav-tabs" style="margin: 30px 0">
	<li class="active"><a href="#top_pages" data-toggle="tab">Top pages visits</a></li>
	<li><a href="#top_pages_usage" data-toggle="tab">Top page usage</a></li>
	<li><a href="#top_registered_platforms" data-toggle="tab">Top registered platforms</a></li>
	<li><a href="#top_login_platforms" data-toggle="tab">Top  login platforms</a></li>
	<li><a href="#top_topup_platforms" data-toggle="tab">Top app topup platforms</a></li>
	<li><a href="#customer_day_activity" data-toggle="tab">Customer day activity</a></li>
</ul>

</div>

<div class="admin2">
	<div class="tab-content">
   
		<!-- Chart for top 5 visited pages -->
		 <div class="tab-pane active" id="top_pages">
			<!--<h3><a href="#" id="downloadCSV">Download CSV</a></h3>-->
			<canvas id="top_pages_contex" width="1000" height="400"></canvas>
		 </div>
		 
		<!-- Chart for top 5 page durations -->
		 <div class="tab-pane" id="top_pages_usage">
			<canvas id="top_pages_duration_context" width="1000" height="400"></canvas>
		 </div>	
		 
		  <div class="tab-pane" id="top_registered_platforms">
			<canvas id="top_registered_platforms_context" width="1000" height="400"></canvas>
		 </div>
		 
		 <!-- Chart for top 5 page durations -->
		 <div class="tab-pane" id="top_login_platforms">
			<canvas id="top_login_platforms_context" width="1000" height="400"></canvas>
		 </div>
		 
		 <div class="tab-pane" id="top_topup_platforms">
			<canvas id="top_topup_platforms_context" width="1000" height="400"></canvas>
		 </div>
		 
		  <div class="tab-pane" id="customer_day_activity">
		  
		
				<select id="customer_day_activity_select" name="day">
					<option>On average (all 7 days)</option>
					<option>Monday</option>
					<option>Tuesday</option>
					<option>Wednesday</option>
					<option>Thursday</option>
					<option>Friday</option>
					<option>Saturday</option>
					<option>Sunday</option>
				</select>

			
			<canvas id="customer_day_activity_context" width="1000" height="400"></canvas>
			
		 </div>	 
		 
	 
	 </div>	
</div>

<script type="text/javascript">

    $(document).ready(function(){
		
		var default_bg_colours = ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'];
		var default_border_colours = ['rgba(255,99,132,1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'];
		
		var top_pages_contex = document.getElementById("top_pages_contex");
		var top_pages_duration_context = document.getElementById("top_pages_duration_context");
		var top_login_platforms_context = document.getElementById("top_login_platforms_context");
		var top_topup_platforms_context = document.getElementById("top_topup_platforms_context");
		var top_registered_platforms_context = document.getElementById("top_registered_platforms_context");
		var customer_day_activity_context = document.getElementById("customer_day_activity_context");
		
		function createChart(context, type, title, labels, data, bg_cols = default_bg_colours, border_cols = default_border_colours){
			var max = Math.max.apply(Math, data);
			var min = Math.min.apply(Math, data);
			var step = min;		
			
			if(min * 2 < max)
			{
				step = min * 2;
			}
			
			if(min * 3 < max)
			{
				step = min * 3;
			}
			
			var ctx = context;
     var LineGraph = new Chart(ctx, {
        type: 'line',
        data: []});
        LineGraph.destroy();
			
			var chart = new Chart(context, {
				type: type,
				data: {
					labels: labels,
					datasets: [
						{
							label: title,
							data: data,
							backgroundColor: bg_cols,
							borderColor: border_cols,
							borderWidth: 1
						},
					]
				},
				options: {
					scales: {
						yAxes: [{
							ticks: {
								fixedStepSize: step,
							}
						}],
						xAxes: [{
							ticks: {
								autoSkip: false,
								maxRotation: 20,
								minRotation: 20,
								fontSize: 10,
							}
						}]
					},
					responsive: false
				}
			});	
		}
		
		function visitChart(){
			$.ajax({
				
				url: "/data/admin_page_visit", 
				data: {},
				dataType: 'json',
				
			}).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				createChart(top_pages_contex, "bar", "Top 5 visited pages", labels, visits);
				
			});
		}
			
		function visitDurationChart(){
			$.ajax({
				
				url: "/data/admin_page_duration", 
				data: {},
				dataType: 'json',
				
			}).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				createChart(top_pages_duration_context, "line", "Top 5 pages with longest avg viewing time (minutes)", labels, visits);
				
			});
		}
		
		function custLoginChart(){
			$.ajax({
				
				url: "/data/customer_login_tracking", 
				data: {},
				dataType: 'json',
				
			}).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				createChart(top_login_platforms_context, "bar", "Most used platforms for logging in", labels, visits);
				
			});
		}
		
		function custTopupChart(){
			$.ajax({
				
				url: "/data/customer_topup_tracking", 
				data: {},
				dataType: 'json',
				
			}).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				createChart(top_topup_platforms_context, "bar", "Most used platforms for topping up", labels, visits);
				
			});
		}
		
		function custRegisteredChart(){
			$.ajax({
				
				url: "/data/customer_registered_platforms", 
				data: {},
				dataType: 'json',
				
			}).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				
				createChart(top_registered_platforms_context, "bar", "Top registered platforms", labels, visits);
				
			});
		}
		
		function custDayActivityChart(day = null){
			
			var settings = {
				url: "/data/customer_day_activity", 
				data: {},
				dataType: 'json',
			};
			
			//alert(day);
			
			if(day != null)
			{
				var settings = {
					url: "/data/customer_day_activity/day", 
					data: {day: day},
					method: 'POST',
					dataType: 'json',
				};
			}
			
			$.ajax(settings).done(function(data){
				
				var labels = Object.keys(data);
				var visits = Object.values(data);
				
				var bg_cols = ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(255, 159, 64, 0.2)'];
		var border_cols = ['rgba(255,99,132,1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'];
				
				createChart(customer_day_activity_context, "bar", "Active times", labels, visits, bg_cols, border_cols);
				
			});
			
		}
		
		
		
		visitChart();
		visitDurationChart();
		custLoginChart();
		custTopupChart();
		custRegisteredChart();
		custDayActivityChart();
		
		
		$('#customer_day_activity_select').on('change', function(){
			if($(this).val() == "On average (all 7 days)") {custDayActivityChart(); return}
			custDayActivityChart($(this).val());
		});
		
    });

</script>