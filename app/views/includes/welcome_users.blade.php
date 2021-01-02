@if (count($info))
	
	<table class="table table-bordered tablesorter sortthistable" width="100%">
		
		<thead>
			@if ($fromSystemReports)
				<th>Commencement Date</th>
			@endif
			@if (!$fromSystemReports)
				<th width="10%" class="barcode">Barcode</th>
			@endif
			<th width="10%">Username</th>
			<th width="10%">Temp&deg;C </th>
			@if ($fromSystemReports)
				<th>Address</th>
			@endif
			<th width="20%">Last reading</th>
			<th width="15%">First Name</th>
			<th width="15%">Surname</th>
			<th width="10%">Balance</th>
			<th width="10%">&nbsp;</th>
		</thead>
		

		<tbody>
		<?php
		//if ($info == "")
		//    echo "There are no data to show";
		//else
			foreach ($info as $user):
            ?>
            @if ($type == 'blue' || ($type !== 'blue' && (!$user->districtHeatingMeter || !$user->districtHeatingMeter->permanentMeterData || $user->districtHeatingMeter->permanentMeterData->is_bill_paid_customer != 1)))
                <tr class="customer_row">
                    
					@if ($fromSystemReports)
						<td>{{ $user->commencement_date }}</td>
					@endif
					@if (!$fromSystemReports)
						<td @if($user->subscription && Auth::user()->isUserTest()) class='green' @endif ><?php echo $user->barcode ?></td>
					@endif
					@if($user->simulator > 0)
						<td>{{ Customer::find($user->id)->username }}</td>
					@else
						@if(Auth::user() && Auth::user()->scheme_number == 20)
							 <td>{{ $user->username }}</td>
						@else
							<td>
								@if( substr($user->username, 0, 1 ) == 'h')
									{{ str_replace('h', '', str_replace('fair', 'Fair', $user->username)) }}
									<br/><center><font style='font-size:0.6rem;text-align:center;'>(Fairways Hall)</font></center>
								@else
									<?php echo (int)$user->username  ?>{{ ucfirst(preg_replace('/[0-9]+/', '', $user->username)) }}
								@endif
							</td>
						@endif
					@endif
					
                    <td @if($user->permanentMeter) @if($user->permanentMeter->awayMode) style='background-color:#d5f6ff;' @endif @endif>@if($user->districtMeter) {{ $user->districtMeter->last_flow_temp }}&deg;C @else N/A @endif</td>
                    @if ($fromSystemReports)
                        <td>
                            {{{ $user->house_number_name ? $user->house_number_name . ', ' : '' }}}
                            {{{ $user->street1 ? $user->street1 . ', ' : '' }}}
                            {{{ $user->street2 ? $user->street2 . ', ' : '' }}}
                            {{{ $user->town ? $user->town . ', ' : ''  }}}
                            {{{ $user->county ? $user->county . ($user->country ? ', ' : '') : '' }}}
                            {{{ $user->country ? $user->country : '' }}}
                        </td>
                    @endif
                    <td>@if($user->districtMeter) {{ $user->districtMeter->sudo_reading_time }} @else N/A @endif</td>
                    <td><?php echo $user->first_name ?></td>
                    <td><?php echo $user->surname ?></td>
                    <td class="{{ $type }}">{{ $currencySign }}<?php echo $user->balance ?></td>
                    <td><a  class="btn btn-info" type="button" href="<?php echo URL::to('customer_tabview_controller/show/'.$user->id) ?>">View</a></td>

                </tr>
            @endif
        <?php endforeach; ?>
		</tbody>
		
	</table>


		<script>
		$(document).ready(function() {
			$(function() {
				$(".sortthistable").tablesorter({
					
					headers: {
					  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
					  2: { sorter: "digit", string: "max" }, // non-numeric content is treated as a MAX value
					  3: { sorter: "digit", string: "min" }  // non-numeric content is treated as a MIN value
					},
					sortList: [[6, 1]]
					
				});
			});
		});
	</script>
	
@endif