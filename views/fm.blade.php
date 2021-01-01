


</div>

<div><br/></div>
<h1>fm</h1>


<div class="admin2">

	<table width="100%">
		
		<tr>
			<td width="50%" style="font-size:1.8rem;font-weight:bold;" id="notifying">
			
			</td>
			<td width="50%" style="font-size:1.4rem;" id="sent">
			
			</td>
		</tr>
		<tr>
			<td colspan="2" style="font-size:1.8rem;font-weight:bold;">
				<hr/>
			</td>
		</tr>
		<tr>
			<td width="50%" style="vertical-align:top">
				<input style="padding:2%;font-size:1.8rem;width:60%;" value="Irreplaceable" type="text" id="song">
			</td>
			<td width="50%" style="vertical-align:top">
				<input style="padding:2%;font-size:1.8rem;width:60%;" value="Beyonc" type="text" id="artist">
			</td>
		</tr>
		<tr>
			<td width="20%" style="font-size:1.8rem;font-weight:bold;" id="">Current Song:</td>
			<td width="80%" style="font-size:1.6rem" id="cur_song">loading..</td>
		</tr>
		<tr>
			<td colspan="2" style="font-size:1.8rem;font-weight:bold;">
				<hr/>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="font-size:1.8rem;font-weight:bold;" id="iterations">Iterations: 0</td>
		</tr>

	</table>

</div>

<script>
	
	$(function(){
		
		let loading = false;
		let loading2 = false;
		let sent_emails = 0;
		let notify_interval = 5;
		let cur_notify = 5;
		let iterations = 0;
		
		$('#notifying').html('Waiting for song to play..');
		$('#iterations').html('Iterations: ' + iterations);
		
		function getCurrentSong(callback)
		{
			if(loading)
				return;
			
			loading = true;
			
			$.ajax({
				url: "https://s3-eu-west-1.amazonaws.com/storage.publisherplus.ie/media.radiocms.net/now-playing/98fm",
				data: {},
				success: function(data) {
					
					callback(data);
				}
			});
		}
		
		function notify(song, duration)
		{
			duration = duration.replaceAll('+00:00 ', '')
			duration = duration.replaceAll('T', ' ')
			
			if(loading2)
				return;
			
			loading2 = true;
			
			$.ajax({
				url: "/fm_email",
				data: {song: song, duration: duration},
				success: function(data) {
					
					loading2 = false;
					
					sent_emails++;
					
					$('#sent').html("Sent the email " + sent_emails + " times.");
					
				}
			});
		}
		
		
		var i = setInterval(function(){
			
			getCurrentSong(function(data) {
				
				console.log(data)
				iterations++;
				
				$('#iterations').html('Iterations: ' + iterations);
				
				var song = data.title;
				var artist = data.artist;
				var start = data.start;
				var end = data.end;
				var duration = start + " - " + end;
				
				loading = false;
				
				$('#cur_song').html(song + " - " + artist);
				
				var watch_artist = $('#artist').val().toLowerCase().trim();
				var watch_song = $('#song').val().toLowerCase().trim();
				
				var song_parsed = song.toLowerCase().trim();
				var artist_parsed = artist.toLowerCase().trim();
				
				if(watch_artist.length > 3 && watch_song.length > 3) {
					if(song_parsed.indexOf(watch_song) != -1 
					|| artist_parsed.indexOf(watch_artist) != -1) {
						if(cur_notify == 0) {
							cur_notify = notify_interval;
							notify((song + " - " + artist), duration);
						} else {
							cur_notify--;
							$('#notifying').html('Song played! Emailing in ' + cur_notify + ' seconds..');
						}
					}
				}
			});
			
			
			
		}, 1000);
		
	});

</script>
