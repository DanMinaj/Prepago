
</div>

<div><br/></div>
<h1>Inconsistent Usage ({{ $num }})</h1>


<div class="admin2">
	
   @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $message }}
        </div>
	@elseif ($message = Session::get('errorMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $message }}
        </div>
    @endif
	
	
	<form action="" method="POST">
		<button type="submit" class="btn btn-primary">Rectify all</button>
		<input type="hidden" value="{{ $date }}" name="date"/>
	</form>
	
	<table width="100%">
		
		<tr>
			
			<td width="10%">
				<h4>Total</h4>
			</td>
			
			<td width="90%">
				<h4>{{ count($customers) }}</h4>
			</td>
			
		</tr>
		
			
		<tr>
			
			<td width="10%">
				<h4>Date</h4>
			</td>
			
			<td width="90%">
				<h4>{{ $date }}</h4>
			</td>
			
		</tr>
		
		
	</table>
	
	<hr/>
	
	@foreach($customers as $c)
	
	<table width="100%" class="table table-bordered">
		<tr>
			<td width="100%">
				<b>
					<a href="https://www.prepago-admin.biz/customer_tabview_controller/show/{{$c->id}}">
						Customer #{{ $c->id }} - {{ $c->username }}
					</a>
				</b>
			</td>
		</tr>
		<tr>
			<td width="100%">
				<table width="100%" class=" table-bordered">
					<tr>
						<td width="30%"><b>DHU ID</b></td>
						<td width="10%"><b>Cost of day</b></td>
						<td width="10%"><b>Standing charge</b></td>
						<td width="10%"><b>Total usage</b></td>
						<td width="10%"><b>Start day</b></td>
						<td width="10%"><b>End day</b></td>
						<td width="10%"><b>Actions</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
		<?php
			$e = $c->entry;
			$p = $c->prev_entry;
		?>
			<td width="100%">
				<table width="100%" class=" table-bordered">
					@if($p)
					<tr>
						<td width="30%"><a href="{{ URL::to('edit_dhu/' . $p->id) }}"><i class="fa fa-pencil-alt"></i> #{{ $p->id }} - {{ $p->date }}</a></td>
						<td width="10%">&euro;{{ $p->cost_of_day }}</td>
						<td width="10%">&euro;{{ $p->standing_charge }}</td>
						<td width="10%">&euro;{{ $p->total_usage }}</td>
						<td width="10%">{{ $p->start_day_reading }}</td>
						<td width="10%">{{ $p->end_day_reading }}</td>
						<td width="10%">
						</td>
					</tr>
					@else
					<tr>
						<td colspan='7' width="100%">
							<center>Previous day entry does not exist</center>
						</td>
					</tr>
					@endif
					<tr>
						<td width="30%"><a href="{{ URL::to('edit_dhu/' . $e->id) }}"><i class="fa fa-pencil-alt"></i> #{{ $e->id }} - {{ $e->date }}</a></td>
						<td width="10%">&euro;{{ $e->cost_of_day }}</td>
						<td width="10%">&euro;{{ $e->standing_charge }}</td>
						<td width="10%">{{ $e->total_usage }}</td>
						<td width="10%">{{ $e->start_day_reading }}</td>
						<td width="10%">{{ $e->end_day_reading }}</td>
						<td width="10%">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	
	
	
	@endforeach


</div>
