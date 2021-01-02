
</div>

<div><br/></div>
<h1>Whos online</h1>


<div class="admin2">

	@foreach($whosonline as $w) 
	
	<table width="100%">
		<tr>
			<td><h4 style='color: #62c462'>{!! $w->username !!}</h5><h5>Viewing {!! $w->is_online_page !!}</h3>{!!$w->is_online_time!!} ({!!Carbon\Carbon::parse($w->is_online_time)->diffForHumans()!!})</td>
		</tr>
	</table>
	
	<hr>
	
	@endforeach

</div>

<h1 style='color:#ccc;'>Offline</h1>
	
<div class="admin2">

	@foreach($whosoffline as $w2) 
	
	<table width="100%">
		<tr style='color: #ccc'>
			<td><h4>{!! $w2->username !!}</h5><h5>Viewing {!! $w2->is_online_page !!}</h3>{!!$w2->is_online_time!!} ({!!Carbon\Carbon::parse($w2->is_online_time)->diffForHumans()!!})</td>
		</tr>
	</table>
	
	<hr>
	
	@endforeach

</div>

	