</div>

<div class="cl"></div>
<h1>Deleted accounts</h1>
<div class="cl"></div>

@include('includes.notifications')

<table width="100%" class="table table-bordered">
	<tr>
		<th> <b> Customer ID </b> </th>
		<th> <b> Username </b> </th>
		<th> <b> Deleted at </b> </th>
		<th> <b> Replaced </b> </th>
		<th> <b> Actions </b> </th>
	</tr>
	@foreach($deletedCustomers as $c)
		<tr>
			<td><a href="/customer/{{ $c->id }}">{{ $c->id }}</a></td>
			<td>{{ $c->username }}</td>
			<td>{{ $c->deleted_at }} &horbar; {{ Carbon\Carbon::parse($c->deleted_at)->diffForHumans() }}</td>
			<td>
				@if($c->replaced)
					<a href="/customer/{{ $c->replacement->id }}">Customer {{ $c->replacement->id }} &horbar; 
					{{ Carbon\Carbon::parse($c->replacement->commencement_date)->diffForHumans() }}</a>
				@else
					<center> none </center>
				@endif
			</td>
			<td>
				@if(!$c->replaced)
					<a href="{{ URL::to('reinstate_account/confirm', ['id' => $c->id]) }}">
						<button class="btn btn-success"><i class="fa fa-unlock"></i> Reinstate</button>
					</a>
				@else
					<a href="{{ URL::to('reinstate_account/confirm', ['id' => $c->id]) }}">
						<button class="btn btn-success"><i class="fa fa-unlock"></i> Reinstate</button>
					</a>
				@endif
			</td>
		</tr>
	@endforeach
</table>
