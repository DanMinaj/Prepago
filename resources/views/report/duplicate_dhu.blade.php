
</div>

<div><br/></div>
<h1>Duplicate Usage ({!! $num !!})</h1>


<div class="admin2">
	
   @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! $message !!}
        </div>
	@elseif ($message = Session::get('errorMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! $message !!}
        </div>
    @endif
	
	
	<form action="" method="POST">
		<button type="submit" class="btn btn-primary">Rectify all</button>
	</form>
	
	<table width="100%">
		
		<tr>
			
			<td width="10%">
				<h4>Total</h4>
			</td>
			
			<td width="90%">
				<h4>{!! count($customers) !!}</h4>
			</td>
			
		</tr>
		
		
	</table>
	
	@foreach($customers as $c)
	
	<table width="100%" class="table table-bordered">
		<tr>
			<td width="100%">
				<b>
					<a href="https://www.prepago-admin.biz/customer_tabview_controller/show/{!!$c->id!!}">
						Customer #{!! $c->id !!} - {!! $c->username !!}
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
						<td width="10%"><b>Reading change</b></td>
						<td width="10%"><b>Actions</b></td>
					</tr>
				</table>
			</td>
		</tr>
		@foreach($c->duplicate_entries as $e)
		<tr>
			<td width="100%">
				<table width="100%" class=" table-bordered">
					<tr>
						<td width="30%">{!! $e->id !!}</td>
						<td width="10%">&euro;{!! $e->cost_of_day !!}</td>
						<td width="10%">&euro;{!! $e->standing_charge !!}</td>
						<td width="10%">{!! $e->start_day_reading !!}->{!! $e->end_day_reading !!}</td>
						
						@if($e->cost_of_day == 0 || $e->standing_charge == 0 )
						<td width="10%">
							<form action="{!! URL::to('system_reports/duplicate_dhu/singular') !!}" method="POST" style="margin:0px;padding:0px">	
								<input type="hidden" name="id" value="{!!$e->id!!}"/>
								<button type="submit" class="btn btn-danger">
									<i class="fa fa-trash"></i>
								</button>
							</form>
						</td>
						@else
						<td width="10%">
						</td>
						@endif
					</tr>
				</table>
			</td>
		</tr>
		@endforeach
	</table>
	
	
	
	
	@endforeach


</div>
