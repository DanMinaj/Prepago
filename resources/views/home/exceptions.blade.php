
</div>

<div><br/></div>
<h1>System exceptions</h1>


<div class="admin2">

<style>
	td {
		vertical-align: top;
	}
</style>

	<form action="">
	<table width="100%">
		<tr>
			<td width='5%'>
				<input type="text" name="date" placeholder="Date" value="{{ $date }}">
			</td>
			<td width='90%'>
				<button type="submit" class="btn btn-primary">Go</button>
			</td>
		</tr>
	</table>
	</form>
	
	@if($exceptions == null)
		<h4> No exception log file exists for {{ $date }} {{ $exception_file }} </h4>
	@else
		
	<h4> Last Exception </h4>
	{{ nl2br($last_exception) }}
	
	<hr/>
		
	<h4> All Exceptions </h4>
		<textarea id="ta" style='width:98%;height:400px;'>@foreach($exceptions as $e){{ $e }}@endforeach</textarea>
	@endif
	
</div>

<script>
	$(function(){
		
		var t = document.getElementById('ta');
		t.scrollTop = t.scrollHeight;
		
	});
</script>