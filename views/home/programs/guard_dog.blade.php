</div>
<div><br/></div>
<h1>Guard Dogs</h1>
<div class="admin">
@include('includes.notifications')

	
@include('modals.guarddog') 
  <ul class="nav nav-tabs" style="">
	  <li class="active"><a href="#1" data-toggle="tab">Active ({{ count(GuardDog::active()) }})</a></li>
	  <li><a href="#2" data-toggle="tab">Create new</a></li>  
  </ul>

       
  <div class="tab-content">
  
	<div class="tab-pane active" id="1" style="">
	<table width="100%" class="table table-bordered">
		<tr>
			<th><b>#</b></th>
			<th><b>Username</b></th>
			<th><b>Created</b></th>
			<th><b>Status</b></th>
			<th><b>View</b></th>
		</tr>
		@foreach($guarddogs as $k => $gd) 
		<tr id="gd_tr_{{ $gd->id }}">
			<td style="vertical-align: middle">{{ $gd->id }}</td>
			<td style="vertical-align: middle">{{ $gd->username }}</td>
			<td style="vertical-align: middle">{{ Carbon\Carbon::parse($gd->created_at)->diffForHumans() }}</td>
			<td style="vertical-align: middle">{{ ($gd->competed) ? 'complete' : 'running' }}</td>
			<td style="vertical-align: middle">
				<center>
				<button class='btn btn-primary view_gd' id="gd_{{ $gd->id }}">View</button>
				</center>
			</td>
		</tr>
		@endforeach
	</table>
	</div>

	
	<div class="tab-pane" id="2" style="">
		<form action="/guarddog/start" method="POST">
		<table width="100%">
			<tr>
				<td>
					<input style='padding:2% 1% 2% 1%;width:97%;' type="text" name="customer" placeholder="Enter customer username or ID">
				</td>
			</tr>
			<tr>
				<td>
					<button style='padding:2% 10% 2% 10%;width:100%;' type="submit" class="btn btn-primary">Start Guard Dog</button>
				</td>
			</tr>
		</div>
		</form>
	</div>
	
	</div>
	

 </div>
 <script type="text/javascript">
 
	$('.gd_stop').on('click', function(){
		
		var gd_id = $('#gd_id').val();
		
		$.ajax({
			url: '/guarddog/stop',
			method: 'POST',
			data: { gd_id: gd_id },
			success: function(gd){
				$.notify(gd.message, "success");
				if(gd.message.toLowerCase().indexOf('success') !== -1) {
					$('#guarddog').modal('hide');
					$('#gd_tr_' + gd_id + '').hide();
				}
			}, error: function(){}
		});
		
	});
	$('.view_gd').on('click', function(){
		
		var gd_title = $('.gd_title');
		var gd_log = $('.gd_log');
		var gd_stop = $('.gd_stop');
		var gd_topups = $('.gd_topups');
		
		var id = $(this).attr('id').split('gd_')[1];
		
		$.ajax({
			url: '/guarddog/get',
			method: 'POST',
			data: { gd_id: id },
			success: function(gd){
				
				gd_title.html(': ' + "(#" + gd.id + ") &horbar; " + gd.username);
				
				var topup_data = "";
				topup_data += "<input type='hidden' id='gd_id' value='" + gd.id + "'>";
				topup_data += "<table width='100%' class='table table-bordered'>";
				topup_data += "<tr>";
				topup_data += "<th width='30%'><b>Time</b></th>";
				topup_data += "<th width='10%'><b>Amount</b></th>";
				topup_data += "<th width='20%'><b>Desc</b></th>";
				topup_data += "<th width='40%'><b>Status</b></th>";
				topup_data += "</tr>";
				$.each(gd.topups, function(k, v) {
					console.log(v);
					topup_data += "<tr " + (v.status == 'succeeded' ? "style='background:#a5e0a5;'" : "style='background:#fb817d;'") + ">";
					topup_data += "<td>" + v.created_at + " &horbar; (" + v.time + ")</td>";
					topup_data += "<td>&euro;" + v.amount + "</td>";
					topup_data += "<td>" + v.description + "</td>";
					topup_data += "<td>" + v.status + "</td>";
					topup_data += "</tr>";
				});
				topup_data += "</table>";
				gd_topups.html(topup_data);
				
				
				var log_data = "";
				log_data += "<table width='100%' class='table table-bordered'>";
				$.each(gd.log, function(k, v){
					log_data += "<tr>";
					log_data += "<th><b>" + v + "</b></th>";
					log_data += "</tr>";
				});
				log_data += "";
				log_data += "</table>";
				gd_log.html(log_data);
				
				
			}, error: function(){}
		});
		
		$('#guarddog').modal('show');
		
		
	});
 </script>