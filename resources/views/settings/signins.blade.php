</div>
<div class="cl"></div>
<div class="admin2">
<h1>SIGN INS</h1>
   
    
   <table class="table table-bordered" width="100%">
	<tr>
		<th width="13%"><b>IP Address</b></th>
		<th width="26%"><b>First sign in</b></th>
		<th width="26%"><b>Last sign in</b></th>
		<th width="35%"><b>Info</b></th>
	</tr>
   @foreach($signins as $s) 
  
		<tr>
			<td>{!! $s->IP !!}</td>
			<td>{!! $s->created_at !!} ({!! Carbon\Carbon::parse($s->created_at)->diffForHumans() !!})</td>
			<td>{!! $s->updated_at !!} ({!! Carbon\Carbon::parse($s->updated_at)->diffForHumans() !!})</td>
			<td>{!! $s->info !!}</td>
		</tr>
  
   @endforeach
   </table>
   
</div>