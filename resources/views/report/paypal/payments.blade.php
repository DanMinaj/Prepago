
</div>

<div><br/></div>
<h1>Paypal payments <span class='extra_title'></span></h1>

<style>
	td{
		vertical-align: top;
	}
</style>

<div class="admin2">
	
	<form action="" method="POST">				
		<table width="100%">
						
			<tr>
				
				<td width="20%">		
					<input type="text" name="from" value="{!! $from !!}">
				</td>
				
				<td width="20%">		
					<input type="text" name="to" value="{!! $to !!}">
				</td>
				
				<td width="60%">
					<button class="btn btn-primary" id="ajax_submit" type="button">Submit</button>
				</td>
				
			</tr>
			
			<!--
			<tr>
				
				<td>
					<h4>accounts@prepago.ie: <span id="bal_1">Loading..</span></h4>
				</td>
			</tr>
			
			<tr>
				<td>
					<h4>noreply@snugzone.biz: <span id="bal_2">Loading..</span></h4>
					<br/><br/>
				</td>
			</tr>-->
		
			<tr>
				
				<td>
					Show results<br/><br/>
					<select name="results">
						<option>All</option>
						<option>10</option>
						<option>20</option>
						<option>40</option>
						<option>60</option>			
					</select>
				</td>
				
			</tr>
			
		</table>
	</form>
	
	<table id="results_table" class="table" width="100%">
		
	</table>

</div>

<script>
	$(function(){
		
		var table = $('#results_table');
		
		function getBalances()
		{
			$.ajax({
				
				url: "/pp/getbalances",
				data: {},
				method: "POST",
				success: function(data) {
					
					
					$('#bal_1').html(data.bal1);
					$('#bal_2').html(data.bal2);
					
				}
					
			});
		}
		
		function loadData()
		{
			
			$('.extra_title').html("");
			var from = $('input[name=from]').val();
			var to = $('input[name=to]').val();
			var results_to_show = $('select[name=results]').val();
			
			showLoading();
			
			$.ajax({
				
				url: "/pp/payments",
				data: {from: from, to: to, results: results_to_show},
				method: "POST",
				success: function(data){
					
					//console.log(data)
					var print = "";
					print += "<thead>";
					print += "<tr>";
						print += "<th><b>ID</b></th>";
						print += "<th><b>Amount</b></th>";
						print += "<th><b>Time</b></th>";
						print += "<th><b>Name</b></th>";
						print += "<th><b>Processed</b></th>";
					print += "</tr>";
					print += "</thead>";
					print += "<tbody>"
					$.each(data, function(k,v) {
						print += "<tr>";
							print += "<td>" + v.id + "</td>";
							print += "<td>&euro;" + v.amount + "</td>";
							print += "<td>" + v.time + " (GMT -1)</td>";
							print += "<td>" + v.name + "</td>";
							
							if(v.db_entry == undefined){
								print += "<td><b> Not found</b></td>";
							} else { 
								print += "<td style='background-color:#a2e8a2'>" + v.db_entry.time_date + "</td>";
							}
							
						print += "<tr>";
						
					
					//console.log('DB ENTRY' + v.db_entry);
					});
					print += "</tbody>";
					
					
					table.html(print);
					$('.extra_title').html("(" + data.length + ")");
				}
				
			});
		}

		function showLoading()
		{
			table.html('');
			table.html("<tr><td><center> Retrieving payments .. </center></td></tr>");
		}
		
		$('#ajax_submit').on('click', function(){
			loadData();
		});
		
		$('select[name=results]').on('change', function(){
			loadData();
		});
		
		
		getBalances();
		loadData();
		
		
	});
</script>