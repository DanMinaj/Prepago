
</div>

<div><br/></div>
<h1>Database Backup</h1>


<div class="admin2">
	
	@if ($message1 = Session::get('successMessage'))
	<div class="alert alert-success alert-block">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		{{ $message1 }}
	</div>
	@endif

	@if ($message2 = Session::get('warningMessage'))
	<div class="alert alert-warning alert-block">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		{{ $message2 }}
	</div>
	@endif

	@if ($message3 = Session::get('errorMessage'))
	<div class="alert alert-danger alert-block">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		{{ $message3 }}
	</div>
	@endif

	
	<div class="alert alert-info alert-block">
	Please be advised website will be un-accessable for a couple seconds while database backup is processing.
	</div>
	
	
	<ul class="nav nav-tabs" style="margin: 30px 0"> 
      <li class="active"><a href="#1" data-toggle="tab">Database Backups</a></li>
      <li class=""><a href="#2" data-toggle="tab">Table Backups</a></li>
      <li class=""><a href="#3" data-toggle="tab">Recent Backup & restorations</a></li>  
   </ul>
   
 
   	<div class="progress progress-striped active">
		  <div id="progress" class="bar" style="width: 0%;"></div>
	</div>	
   <div class="tab-content">
    
		<!-- Tab 1 -->
		<div class="tab-pane active" id="1" style="text-align: left">	
		<br/>
		<table class="table-bordered table" width="100%">
			
			<tr>
				<td colspan='2'>
					<button onclick="backupDatabase()" style="padding:3%;width:100%;" id="backup-database" class="btn btn-primary">
						Backup database
					</button>
				</td>
			</tr>
			<tr>
				<td colspan='2' width="100%"><b>Backups: </b> {{ count($databaseBackups) }}</td>
			</tr>
			<tr>
				<td colspan='2' width="100%"><b>Last backup: </b> {{ (count($databaseBackups) > 0) ? Carbon\Carbon::createFromTimestamp($databaseBackups[0]->time)->format('d M Y @ H:i:s') : "" }}</td>
			</tr>
			<tr>
				<td colspan='2' width="100%"><b>Size: </b> {{ $databaseSize }}MB</td>
			</tr>
			<tr>
				<td colspan='2' width="100%"><b>Approx backup time: </b> {{ ceil($estimatedTime) }}s</td>
			</tr>
			
		</table>
		
		
		<table class="table-bordered table" width="100%">
			
			@foreach($databaseBackups as $db) 
				
				<tr>
					<td>{{ $db->name }}</td>
					<td>{{ Carbon\Carbon::createFromTimestamp($db->time)->format('d M Y H:i:s') }}</td>
					<td>{{ $db->size_mb }}MB</td>
				</tr> 
			
			@endforeach
		
		</table>
		
		</div>
		
		<!-- Tab 2 -->
		<div class="tab-pane" id="2" style="text-align: left">		
		<br/>	
			<table class="table-bordered table" width="100%">
			<tr>
				<td style="vertical-align:middle">
					<b>Restore backups to Database</b>:
				</td>
				<td>
					<select name="databases">
						<option>prepago_debug</option>
						<option>prepago</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan='2' width="100%"><b>Tables: </b> {{ count($tables) }}</td>
			</tr>
		</table>
		@foreach($tables as $t)
		<table class="table-bordered table" width="100%">
			
			<tr>
				<td width="70%" style="vertical-align:middle;" colspan='2'><b>({{ count($t->backups) }}) {{ $t->size }}MB - {{ $t->Tables_in_prepago }}</b></td>
				<td width="30%" colspan='2'>
					<center>
						<button onclick="backupTable('{{ $t->Tables_in_prepago }}')" class="btn btn-primary" type="button" style="">Backup</button>
					</center>
				</td>
			</tr>
			@foreach($t->backups as $backups)
			<tr>
				<td width="20%"><i style='color:green;cursor:pointer;' onclick="restoreTable('{{ $backups->full_name }}')" class="fa fa-play-circle"></i> {{ $backups->name }}</td>
				<td width="20%">{{ Carbon\Carbon::createFromTimestamp($backups->time)->format('d M Y H:i:s') }}</td>
				<td width="10%">{{ $backups->size_mb }}MB</td>
				<td width="5%">
					<i style='color:red;cursor:pointer;' onclick="removeFile('{{ $backups->full_name }}')" class="fa fa-trash"></i>
				</td>
			</tr>
			@endforeach
			
		</table>
		@endforeach
		
		</div>
		
		<div class="tab-pane" id="3" style="text-align: left">	
		
		
		</div>
		
	</div>
	
</div>

<script>

	var secs = Math.ceil({{$estimatedTime}});
	var completed = false;
	var cur = parseInt($('#progress').css('width'));
	var progress;
	var progress_bar = $('#progress'); 
	var progress_style = $('.progress');


	function startLoadProgress()
	{
		progress_style.removeClass('progress-success').addClass('active');
		progress = setInterval(function(){	
			cur++;
			
			if(secs == cur || completed) {
				console.log('Done');
				clearInterval(progress);
				return;
			}
			
			percent = Math.floor(((cur)/secs) * 100);
			progress_bar.animate({
				width: (percent-5) + '%'
			});
			
		}, 1000);
	}
	
	function stopLoadProgress()
	{
		completed = true;
		clearInterval(progress);
		progress_style.removeClass('active').addClass('progress-success');
		progress_bar.animate({
			width: '100%'
		});
	}
	
	function getFileInfo(directory)
	{
		
		var data = directory;
		var last_slash = data.lastIndexOf("/");
		var file_name = ((data.substr(last_slash+1, data.length)));
		var data = ((data.substr(last_slash+1, data.length)).split('.sql'))[0];
		var parts = data.split(',');
		var table_name = parts[0];
		var parts2 = (parts[1]).split('-');
		var day = parts2[0];
		var month = parts2[1];
		var year = parts2[2];
		var version = (parts2.length > 4) ? parts2[3] : 'v1';
		var date = day + "-" + month + "-" + year;
		
		return {
			table_name: table_name,
			file_name: file_name,
			day: day,
			month: month,
			year: year,
			version: version,
			date: date,
		};
	}
	
	function backupDatabase() {
		
		startLoadProgress();
		
		$.ajax({
			method: 'POST',
			url: "{{ URL::to('backup/database/submit') }}",
			data: {},
			success: function(data){
				stopLoadProgress();
				window.location.reload();
			}
		});
		
	}
	
	function backupTable(table)
	{
		startLoadProgress();
		
		$.ajax({
			method: 'POST',
			url: "{{ URL::to('backup/database/submitTable') }}",
			data: {table: table},
			success: function(data){
				stopLoadProgress();
				window.location.reload();
			}
		});
	}
	
	function removeFile(file) 
	{
		$.ajax({
			method: 'POST',
			url: "{{ URL::to('backup/database/remove') }}",
			data: {file: file},
			success: function(data){
				
				console.log(data);
				
				window.location.reload();
			}
		});
	}
	
	function restoreDatabase(file)
	{		
		startLoadProgress();
		
		$.ajax({
			method: 'POST',
			url: "{{ URL::to('backup/database/restore') }}",
			data: {file: file},
			success: function(data){
				
				stopLoadProgress();
				
			}
		});
	}
	
	function restoreTable(file)
	{
		var file_info = getFileInfo(file);
		var database = $('select[name=databases]').val();
		
		if(confirm("Are you sure you would like to restore the " + file_info.table_name + " to " + file_info.date + " version " + file_info.version + " to DATABASE '" + database + "'?")) {
			
			startLoadProgress();
			
			$.ajax({
				method: 'POST',
				url: "{{ URL::to('backup/database/restoreTable') }}",
				data: {file: file, database: database, file_info: file_info},
				success: function(data){
					stopLoadProgress();
				},
				error: function(data){
					console.log('Error: ');
				}
			});
			
		}
	}
	
	function rememberLastClick()
	{
		$('li').on('click', function(){
			var href = $(this).find('a');
			if(href.attr('data-toggle') && !href.attr('sub-toggle'))
			{
				localStorage.setItem("database_cookie_href", href.attr('href'));
				
				if(localStorage.getItem("database_cookie_href") != '' && localStorage.getItem("database_cookie_href") != '#')
				{
					//console.log('stored in database_cookie_href');
				}
			}
			
		});
		
		if(localStorage.getItem("database_cookie_href") != null)
		{
			if(localStorage.getItem("database_cookie_href") != '' && localStorage.getItem("database_cookie_href") != '#')
			{
				
				var href = $('a[href='+localStorage.getItem("database_cookie_href")+']');
				setTimeout(function(){
					href.click();
				}, 100);
				//console.log(localStorage.getItem("database_cookie_href") + ' is the href');
				
				if(localStorage.getItem("database_cookie_sub_href") != null)
				{
					if(localStorage.getItem("database_cookie_sub_href") != '' && localStorage.getItem("database_cookie_sub_href") != '#')
					{
						$('a[href='+localStorage.getItem("database_cookie_sub_href")+']').click();
						//console.log(localStorage.getItem("database_cookie_sub_href") + ' is the sub-href');
					}
				}
		
			}
			
		}
	}
	
	rememberLastClick();
</script>
	