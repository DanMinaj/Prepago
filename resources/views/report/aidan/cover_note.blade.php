<style>
.page_break { page-break-before: always; }
	*{ font-family: Arial !important; }
</style>
<title>
Cover Note - {!! $company_name !!}
</title>
<table border='1' style='border-collapse: collapse' width="100%">
	<tr>
		<td> <b> Month </b> </td>
		<td colspan='3'> {!! $schemes[0]->month !!} </td>
	</tr>	
	<tr>
		<td> <b> Date </b> </td>
		<td colspan='3'> {!! $schemes[0]->date !!} </td>
	</tr>
	<tr>
		<td> <b> Days </b> </td>
		<td colspan='3'> {!! $schemes[0]->days !!} </td>
	</tr>
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	<tr>
		<td> <b> Advice Note Number </b> </td>
		<td colspan='3'> {!! $schemes[0]->ref_pa !!} </td>
	</tr>
	<tr>
		<td> <b> Invoice Number </b> </td>
		<td colspan='3'> {!! $schemes[0]->ref_pa !!} </td>
	</tr>
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	<tr>
		<td> <b> Collected </b> </td>
		<td colspan='3'>&euro;{!! number_format($collected, 2) !!}</td>
	</tr>
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	@foreach($schemes as $k => $s)
	<tr>
		<td> {!! $s->scheme_nickname !!} </td>
		<td> &euro;{!! number_format($s->scheme_payment, 2) !!} </td>
		<td> 
			@if($s->scheme_payment <= 0) 
				scheme_payment was 0
			@else
				{!! number_format( ($s->invoiced_amount/$s->scheme_payment), 7) !!}
			@endif
		</td>
		<td> &euro;{!! number_format($s->invoiced_amount, 2) !!} </td>
	</tr>
	@endforeach
	<tr>
		<td> <b> payments out </b> </td>
		<td>&euro;{!! number_format($payments_out_total, 2) !!}</td>
		<td></td>
		<td>&euro;{!! number_format($payments_out_vat, 2) !!}</td>
	</tr>
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	<tr>
		<td colspan='1'> <b> # of active Autotopups paid @ - &euro;{!! $s->autotopup_charge !!} </b> </td>
		<td colspan='3'>{!! $active_autotopups !!}</td>
	</tr>
	<tr>
		<td colspan='1'> <b>  Autotopups value ex VAT </b> </td>
		<td colspan='3'>&euro;{!! number_format($active_autotopups_ex_vat, 2) !!}</td>
	</tr>
	<tr>
		<td colspan='1'> <b>  Autotopups value inc VAT </b> </td>
		<td colspan='3'>&euro;{!! number_format($active_autotopups_inc_vat, 2) !!}</td>
	</tr>
	
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	<tr>
		<td> <b> To Prepago </b> </td>
		<td>&euro;{!! number_format($payments_out_vat, 2) !!}</td>
		<td colspan='2'>{!! number_format( ($payments_per_total*100), 3) !!}%</td>
	</tr>
	<tr>
		<td> <b> VAT Number </b> </td>
		<td colspan='3'>{!! $schemes[0]->vat_number !!}</td>
	</tr>
	<tr>
		<td colspan='4'><br/></td>
	</tr>
	<tr>
		<td> <b>Autotopups VAT</b> </td>
		<td colspan='3'>&euro;{!! number_format( ($active_autotopups_inc_vat - $active_autotopups_ex_vat) , 2) !!}</td>
	</tr>
	<tr style='color:#538dd5;'>
		<td colspan='4'><b>The VAT</b></td>
	</tr>
	@foreach($schemes as $k => $s)
	<tr>
		<td style='color:#538dd5;'><b>{!! $s->scheme_nickname !!}</b></td>
		<td colspan='3'> &euro;{!! number_format($s->vat_payment, 2) !!} </td>
	</tr>
	@endforeach
	<tr>
		<td style='color:#538dd5;'><b> VAT Owed</b></td>
		<td colspan='3'>&euro;{!! number_format($vat_owed, 2) !!}</td>
	</tr>
</table>