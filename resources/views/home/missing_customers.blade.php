
</div>

<div><br/></div>
<h1>Missing customers</h1>


<div class="admin2">

		
	@foreach($schemes as $s)
			
		<table width="100%" class="table table-bordered">
	
			<tr>
				<th width="30%">
					<b>{!!$s->company_name!!}</b>
				</th>
				<th width="20%">
					 {!! $s->viewable_count !!}/{!! $s->actual_count !!} customers showing
				</th>
				<th width="10%">
					{!! $s->red_count !!} reds
				</th>
				<th width="10%">
					{!! $s->yellow_count !!} yellows
				</th>
				<th width="10%">
					{!! $s->green_count !!} greens
				</th>
				<th width="20%">
					<b>Visit</b>
				</th>
			</tr>
			@foreach($s->customers as $c) 
				<tr>
					<td colspan="5" style="background-color: {!! $c->color !!};">
					 {!! $c->username !!}
					</td>
					<td>
						<a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $c->id]) !!}">
							<button type="button" class="btn btn-primary">View</button>
						</a>
					</td>
				</tr>
			@endforeach
		
		</table>
		
	@endforeach

	</table>
	
</div>