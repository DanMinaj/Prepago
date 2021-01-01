<style>
.page_break { page-break-before: always; }
	*{ font-family: Arial !important; }
</style>
<title>
Total Report - {{ $company_name }}
</title>
<table style='' border='1' style='border-collapse: collapse' width="100%">
	<tr>
		<td>
			<table border='1' style='border-collapse: collapse' width="100%">
				<tr><td><b>Site</b></td></tr>
				<tr><td><hr/></tr>
				<tr><td><b>Ref</b></td></tr>
				<tr><td><b>Month</b></td></tr>
				<tr><td><b>Date</b></td></tr>
				<tr><td><b>Days</b></td></tr>
				<tr><td><b>Value of Payments</b></td></tr>
				<tr><td><b>Number of Payments</b></td></tr>
				<tr><td><b>Payments % Charge</b></td></tr>
				<tr><td><b>Cost of Topups inc VAT</b></td></tr>
				<tr><td><b>Cost of Topups ex VAT</b></td></tr>
				<tr style='color:#00b050;'><td><b>SMS Messages</b></td></tr>
				<tr style='color:#00b050;'><td><b>SMS Cost</b></td></tr>
				<tr style='color:#00b050;'><td><b>SMS Total inc VAT</b></td></tr>
				<tr style='color:#00b050;'><td><b>SMS Total ex VAT</b></td></tr>
				<tr style='color:#ff0f0f;'><td><b>Apps Installed</b></td></tr>
				<tr style='color:#ff0f0f;'><td><b>Apps Charge</b></td></tr>
				<tr style='color:#ff0f0f;'><td><b>Apps Total inc VAT</b></td></tr>
				<tr style='color:#ff0f0f;'><td><b>Apps Total ex VAT</b></td></tr>
				<tr style='color:#ff2323;'><td><b>Apps Support</b></td></tr>
				<tr style='color:#ff2323;'><td><b>Apps Support inc VAT</b></td></tr>
				<tr style='color:#ff2323;'><td><b>Apps Support ex VAT</b></td></tr>
				<tr style='color:#538dd5;'><td><b>IOU Chargeable</b></td></tr>
				<tr style='color:#538dd5;'><td><b>IOUs</b></td></tr>
				<tr style='color:#538dd5;'><td><b>IOU Charge</b></td></tr>
				<tr style='color:#538dd5;'><td><b>IOUs Charge inc VAT</b></td></tr>
				<tr style='color:#538dd5;'><td><b>IOUs Charge ex VAT</b></td></tr>
				<tr style='color:#366092;'><td><b>Statements Issued</b></td></tr>
				<tr style='color:#366092;'><td><b>Statements Charge</b></td></tr>
				<tr style='color:#366092;'><td><b>Statements Total inc VAT</b></td></tr>
				<tr style='color:#366092;'><td><b>Statements Total ex VAT</b></td></tr>
				<tr><td><b>Number Of Meters</b></td></tr>
				<tr><td><b>Meter Charge</b></td></tr>
				<tr><td><b>Meter Total inc vat</b></td></tr>
				<tr><td><b>Meter Total ex vat</b></td></tr>
				<tr><td><b>VAT Rate</b></td></tr>
				<tr><td><b>Prepago Payment</b></td></tr>
				<tr style='color:#ff2323;'><td><b>VAT Payment</b></td></tr>
				<tr style='color:#ff2323;'><td><b>Scheme Payment</b></td></tr>
				<tr style='color:#a65755;'><td><b>Avg daily kWh</b></td></tr>
				<tr style='color:#a65755;'><td><b>Avg daily Cost</b></td></tr>
				<tr style='color:#7a6790;'><td><b>Annual Avg kWh - day</b></td></tr>
				<tr style='color:#7a6790;'><td><b>Annual Avg Cost - day</b></td></tr>
				<tr><td><i>Tariff - kWh usage</i></td></tr>
				<tr><td><i>Tariff - Daily Charge</i></td></tr>
				<tr><td><i>Tariff - EV kWh usage</i></td></tr>
			</table>
		</td>
		@foreach($schemes as $k => $s)
		<td style='vertical-align:top'>
			<table border='1' style='border-collapse: collapse' width="100%">
				<tr><td>{{ $s->scheme_nickname }}</td></tr>
				<tr><td><hr/></tr>
				<tr><td>{{ $s->ref_pa }}</td></tr>
				<tr><td>{{ $s->month }}</td></tr>
				<tr><td>{{ $s->date }}</td></tr>
				<tr><td>{{ $s->days }}</td></tr>
				<tr><td>&euro;{{ number_format($s->value_of_payments, 2) }}</td></tr>
				<tr><td>{{ $s->amount_of_payments }}</td></tr>
				<tr><td>{{ number_format($s->payments_charge*100, 2) }}%</td></tr>
				<tr><td>&euro;{{ number_format($s->cost_of_topups_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->cost_of_topups_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->sms_msgs }}</td></tr>
				<tr><td>&euro;{{ number_format($s->sms_cost, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->sms_total_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->sms_total_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->apps_installed }}</td></tr>
				<tr><td>&euro;{{ number_format($s->app_charge, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->app_total_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->app_total_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->app_support }}</td></tr>
				<tr><td>&euro;{{ number_format($s->app_support_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->app_support_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->iou_chargeable }}</td></tr>
				<tr><td>{{ $s->ious }}</td></tr>
				<tr><td>&euro;{{ number_format($s->iou_charge, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->iou_charge_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->iou_charge_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->statements_issued }}</td></tr>
				<tr><td>&euro;{{ number_format($s->statements_charge, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->statements_total_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->statements_total_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->no_of_meters }}</td></tr>
				<tr><td>&euro;{{ number_format($s->meter_charge, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->meter_total_inc_vat, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->meter_total_ex_vat, 2) }}</td></tr>
				<tr><td>{{ $s->vat }}%</td></tr>
				<tr><td>&euro;{{ number_format($s->invoiced_amount, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->vat_payment, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->scheme_payment, 2) }}</td></tr>
				<tr><td>{{ number_format($s->avg_daily_kwh, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->avg_daily_cost, 2) }}</td></tr>
				<tr><td>{{ number_format($s->annual_avg_kwh_day, 2) }}</td></tr>
				<tr><td>&euro;{{ number_format($s->annual_avg_cost_day, 2) }}</td></tr>
				<tr><td>&euro;{{ ($s->tariff) ? ($s->tariff->tariff_1) : (0.00) }}</td></tr>
				<tr><td>&euro;{{ ($s->tariff) ? ($s->tariff->tariff_2) : (0.00) }}</td></tr>
				<tr><td>&euro;{{ ($s->tariff) ? ($s->tariff->tariff_3) : (0.00) }}</td></tr>
			</table>
		</td>
		@endforeach
	</tr>
</table>