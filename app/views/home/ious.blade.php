
</div>

<div><br/></div>
<h1>IOUs ({{ count($ious) }})</h1>


<div class="admin2">
	
	@include('includes.notifications')
	
	<div>
		<table class="table" width="100%">
			<tr>
				<td width='5%'><div style='width:20px;height:20px;background:#f3adab'></div></td>
				<td>customer shut off/pending shut off as they have <b>exceeded</b> their IOU</td>
			</tr>
			<tr>
				<td width='5%'><div style='width:20px;height:20px;background:#abf3b1'></div></td>
				<td>customer still in service as they have <b><i>not</i> exceeded</b> their IOU</td>
			</tr>
		</table>
	</div>

	<table width="100%" class="table table-bordered">
		
		<tr>
			<th width="10%"><b>Customer</b></th>
			<th width="10%"><b>Used At</b></th>
			<th width="10%"><b>Balance</b></th>
			<th width="10%"><b>Remaining IOU</b></th>
			<th width="10%"><b>Current Temp</b></th>
		</tr>
		
		@foreach($ious as $iou) 
			
			@if($iou->deleted_at == null)
			<tr @if($iou->exceeded) style='background:#f3adab;' @else style='background:#abf3b1;' @endif>
			@else
			<tr style='background:#ccc;'>
			@endif
				<td><a href="{{ URL::to('customer_tabview_controller/show/' . $iou->id) }}" target="_blank">({{ $iou->id }}) {{ $iou->username }}</a></td>
				<td>
					@if(isset($iou->info) && count($iou->info) > 0)
						{{ $iou->info[0]->time_date }}
					@else
						
					@endif
				</td>
				<td>&euro;{{ $iou->balance }} </td>
				<td>
					@if($iou->scheme()->first())
						@if($iou->balance < -$iou->scheme()->first()->IOU_amount)
							&euro;0.00
						@else
							
							@if($iou->balance > 0.00)
								&euro;{{ number_format($iou->scheme()->first()->IOU_amount, 2) }}
							@else
								&euro;{{ $iou->balance + $iou->scheme()->first()->IOU_amount }}
							@endif
						
						@endif
					@else
						Cannot find scheme.
					@endif
				</td>
				<td>@if($iou->districtMeter) {{ $iou->districtMeter->last_flow_temp }}&deg;C @else No meter @endif</td>
				
			</tr>
		
		@endforeach
		
	</table>
	
	
	
</div>

	