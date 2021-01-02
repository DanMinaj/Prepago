<table width="100%">
	<tr>
	@foreach($schemes as $k => $s) 
		<td>
			<table width="50%" class="advice_note">
						<tr>
							<td><b>Our Ref {!! $s->abbreviation !!}</b></td>
							<td><b>{!! $s->ref_pa !!}</b></td>
						</tr>
						<tr>
							<td><b>Month</b></td>
							<td><b>{!! $s->month !!}</b></td>
						</tr>
						<tr>
							<td><b>Date</b></td>
							<td><b>{!! $s->date !!}</b></td>
						</tr>
						<tr>
							<td><b>Number Of Days</b></td>
							<td><b>{!! $s->days !!}</b></td>
						</tr>
						<tr>
							<td><b>Amount of payments</b></td>
							<td><b>{!! $s->amount_of_payments !!}</b></td>
						</tr>
						<tr>
							<td><b>Value of payments</b></td>
							<td><b>&euro;{!! number_format($s->value_of_payments, 2) !!}</b></td>
						</tr>
						<tr>
							<td><b>Payments % Charge</b></td>
							<td><b>{!! $s->payments_charge !!}%</b></td>
						</tr>
						<tr>
							<td><b>Cost Of Topups inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->cost_of_topups_inc_vat, 2) !!}</b></td>
						</tr>
						<tr>
							<td><b>Cost Of Topups ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->cost_of_topups_ex_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>SMS Messages</b></td>
							<td><b>{!! $s->sms_msgs!!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>SMS Cost</b></td>
							<td><b>&euro;{!! $s->sms_cost !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>SMS Total inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->sms_total_inc_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>SMS Total ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->sms_total_ex_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>Premium SMS Messages</b></td>
							<td><b>{!! $s->premium_sms_msgs !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>Premium SMS Cost</b></td>
							<td><b>&euro;{!! number_format($s->premium_sms_cost, 2) !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>Premium SMS Total ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->premium_sms_total_ex_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#00b050;'>
							<td><b>Premium SMS Total inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->premium_sms_total_inc_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#ff0f0f;'>
							<td><b>Apps Installed</b></td>
							<td><b>{!! $s->apps_installed !!}</b></td>
						</tr>
						<tr style='color:#ff0f0f;'>
							<td><b>Apps Charge</b></td>
							<td><b>&euro;{!! number_format($s->app_charge, 2) !!}</b></td>
						</tr>
						<tr style='color:#ff0f0f;'>
							<td><b>Apps Total inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->app_total_inc_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#ff0f0f;'>
							<td><b>Apps Total ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->app_total_ex_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>App support</td>
							<td>&euro;{!! number_format($s->app_support, 2) !!}</td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>App support inc VAT</b></td>
							<td>&euro;{!! number_format($s->app_support_inc_vat, 2) !!}</td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>App support ex VAT</b></td>
							<td>&euro;{!! number_format($s->app_support_ex_vat, 2) !!}</td>
						</tr>
						<tr style='color:#538dd5;'>
							<td><b>IOU Chargeable</b></td>
							<td><b>{!! $s->iou_chargeable !!}</b></td>
						</tr>
						<tr style='color:#538dd5;'>
							<td><b>IOUs</b></td>
							<td><b>{!! $s->ious !!}</b></td>
						</tr>
						<tr style='color:#538dd5;'>
							<td><b>IOU Charge</b></td>
							<td><b>&euro;{!! $s->iou_charge !!}</b></td>
						</tr>
						<tr style='color:#538dd5;'>
							<td><b>IOUs Charge inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->iou_charge_inc_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#538dd5;'>
							<td><b>IOUs Charge ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->iou_charge_ex_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#366092;'>
							<td><b>Statements Issued</b></td>
							<td><b>{!! $s->statements_issued !!}</b></td>
						</tr>
						<tr style='color:#366092;'>
							<td><b>Statements Charge</b></td>
							<td><b>&euro;{!! $s->statements_charge !!}</b></td>
						</tr>
						<tr style='color:#366092;'>
							<td><b>Statements Total inc VAT</b></td>
							<td><b>&euro;{!! number_format($s->statements_total_inc_vat, 2) !!}</b></td>
						</tr>
						<tr style='color:#366092;'>
							<td><b>Statements Total ex VAT</b></td>
							<td><b>&euro;{!! number_format($s->statements_total_ex_vat, 2) !!}</b></td>
						</tr>
						<tr>
							<td>Number Of Meters</td>
							<td>{!! $s->no_of_meters !!}</td>
						</tr>
						<tr>
							<td>Meter Charge</td>
							<td>&euro;{!! $s->meter_charge !!}</td>
						</tr>
						<tr>
							<td>Meter Total inc vat</td>
							<td>&euro;{!! number_format($s->meter_total_inc_vat, 2) !!}</td>
						</tr>
						<tr>
							<td>Meter Total ex vat</td>
							<td>&euro;{!! number_format($s->meter_total_ex_vat, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: blue;">
							<td>Blue Accounts</td>
							<td>{!! $s->blue_accounts !!}</td>
						</tr>
						<tr style="font-weight: bold; color: blue;">
							<td>Blue Accounts Charge</td>
							<td>&euro;{!! number_format($s->blue_accounts_charge, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: blue;">
							<td>Blue Accounts Cost ex vat</td>
							<td>&euro;{!! number_format($s->blue_accounts_ex_vat, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: blue;">
							<td>Blue Accounts Cost inc vat</td>
							<td>&euro;{!! number_format($s->blue_accounts_inc_vat, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: #404040;">
							<td>Closed Accounts</td>
							<td>{!! $s->closed_accounts !!}</td>
						</tr>
						<tr style="font-weight: bold; color: #404040;">
							<td>Closed Accounts Charge</td>
							<td>&euro;{!! number_format($s->closed_accounts_charge, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: #404040;">
							<td>Closed Accounts Cost ex vat</td>
							<td>&euro;{!! number_format($s->closed_accounts_charge_ex_vat, 2) !!}</td>
						</tr>
						<tr style="font-weight: bold; color: #404040;">
							<td>Closed Accounts Cost inc vat</td>
							<td>&euro;{!! number_format($s->closed_accounts_charge_inc_vat, 2) !!}</td>
						</tr>
						<tr>
							<td>VAT Rate</td>
							<td>{!! $s->vat !!}%</td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>..</td>
							<td>&euro;{!! number_format($s->invoiced_amount, 2) !!}</td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>VAT Payment</b></td>
							<td>&euro;{!! number_format($s->vat_payment, 2) !!}</td>
						</tr>
						<tr style='color:#ff2323;'>
							<td>Scheme Payment</b></td>
							<td>&euro;{!! number_format($s->scheme_payment, 2) !!}</td>
						</tr>
					</table>
		</td>
	@endforeach
	</tr>
</table>