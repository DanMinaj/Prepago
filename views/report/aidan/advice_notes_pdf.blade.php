<!DOCTYPE HTML>
<html><head>
	<title>
	   Advice Note & Invoice - {{ $s->scheme_nickname }} - {{ $s->month }} ({{ $s->start_date }} -> {{ $s->end_date }})
	</title>
	<style>
	.page_break { page-break-before: always; }
	*{ font-family: Arial !important; }
	</style>
</head><body>
@if(isset($fullscreen) && $fullscreen) 
<div style="width:50%;">
   @endif
   
   <table style='' width="100%">
      <tr>
         <td width='40%' style="vertical-align:middle">
            <img @if($fullscreen) src='https://www.snugzone.biz/images/logo.png' @else src='/var/www/html/snugzone/images/logo.png' @endif>
         </td>
         <td width='60%' style="vertical-align:top">
            <table width="100%">
               <tr>
                  <td>
                     <center>
                        <div style='font-size:30px;'>
                           Prepago Platform Ltd<br/>
                           1 Woodbine Avenue<br/>
                           Blackrock<br/>
                           Co Dublin<br/>
                           <br/>
                           Tel 087 2534708<br/>
                           www.snugzone.biz<br/>
                        </div>
                     </center>
                  </td>
               </tr>
            </table>
         </td>
      </tr>
   </table>
   <table width='100%'>
      <tr>
         <td width='70%' style='text-align: center;'>
            <font style='float:center;font-size:1.5rem;font-weight:bold;'>Advice Note</font>
         </td>
         <td width='60%'>
            <div style='float:right;text-align:right;font-size:1rem;font-weight:bold;'>
               {{ nl2br($s->company_address) }}<br/>
            </div>
         </td>
      </tr>
   </table>
   <div class="span8">
      <table width="50%" class="advice_note">
         <tr>
            <td><b>Our Ref {{ $s->abbreviation }}</b></td>
            <td><b>{{ $s->ref_pa }}</b></td>
         </tr>
         <tr>
            <td><b>Month</b></td>
            <td><b>{{ $s->month }}</b></td>
         </tr>
         <tr>
            <td><b>Date</b></td>
            <td><b>{{ $s->date }}</b></td>
         </tr>
         <tr>
            <td><b>Number Of Days</b></td>
            <td><b>{{ $s->days }}</b></td>
         </tr>
         <tr>
            <td><b>Amount of payments</b></td>
            <td><b>{{ $s->amount_of_payments }}</b></td>
         </tr>
         <tr>
            <td><b>Value of payments</b></td>
            <td><b>&euro;{{ number_format($s->value_of_payments, 2) }}</b></td>
         </tr>
         <tr>
            <td><b>Payments % Charge</b></td>
            <td><b>{{ $s->payments_charge }}%</b></td>
         </tr>
         <tr>
            <td><b>Cost Of Topups inc VAT</b></td>
            <td><b>&euro;{{ number_format($s->cost_of_topups_inc_vat, 2) }}</b></td>
         </tr>
         <tr>
            <td><b>Cost Of Topups ex VAT</b></td>
            <td><b>&euro;{{ number_format($s->cost_of_topups_ex_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#00b050;'>
            <td><b>SMS Messages</b></td>
            <td><b>{{ $s->sms_msgs}}</b></td>
         </tr>
         <tr style='color:#00b050;'>
            <td><b>SMS Cost</b></td>
            <td><b>&euro;{{ $s->sms_cost }}</b></td>
         </tr>
         <tr style='color:#00b050;'>
            <td><b>SMS Total inc VAT</b></td>
            <td><b>&euro;{{ number_format($s->sms_total_inc_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#00b050;'>
            <td><b>SMS Total ex VAT</b></td>
            <td><b>&euro;{{ number_format($s->sms_total_ex_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#ff0f0f;'>
            <td><b>Apps Installed</b></td>
            <td><b>{{ $s->apps_installed }}</b></td>
         </tr>
         <tr style='color:#ff0f0f;'>
            <td><b>Apps Charge</b></td>
            <td><b>&euro;{{ number_format($s->app_charge, 2) }}</b></td>
         </tr>
         <tr style='color:#ff0f0f;'>
            <td><b>Apps Total inc VAT</b></td>
            <td><b>&euro;{{ number_format($s->app_total_inc_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#ff0f0f;'>
            <td><b>Apps Total ex VAT</b></td>
            <td><b>&euro;{{ number_format($s->app_total_ex_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>App support</td>
            <td>&euro;{{ number_format($s->app_support, 2) }}</td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>App support inc VAT</b></td>
            <td>&euro;{{ number_format($s->app_support_inc_vat, 2) }}</td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>App support ex VAT</b></td>
            <td>&euro;{{ number_format($s->app_support_ex_vat, 2) }}</td>
         </tr>
         <tr style='color:#538dd5;'>
            <td><b>IOU Chargeable</b></td>
            <td><b>{{ $s->iou_chargeable }}</b></td>
         </tr>
         <tr style='color:#538dd5;'>
            <td><b>IOUs</b></td>
            <td><b>{{ $s->ious }}</b></td>
         </tr>
         <tr style='color:#538dd5;'>
            <td><b>IOU Charge</b></td>
            <td><b>&euro;{{ $s->iou_charge }}</b></td>
         </tr>
         <tr style='color:#538dd5;'>
            <td><b>IOUs Charge inc VAT</b></td>
            <td><b>&euro;{{ number_format($s->iou_charge_inc_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#538dd5;'>
            <td><b>IOUs Charge ex VAT</b></td>
            <td><b>&euro;{{ number_format($s->iou_charge_ex_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#366092;'>
            <td><b>Statements Issued</b></td>
            <td><b>{{ $s->statements_issued }}</b></td>
         </tr>
         <tr style='color:#366092;'>
            <td><b>Statements Charge</b></td>
            <td><b>&euro;{{ $s->statements_charge }}</b></td>
         </tr>
         <tr style='color:#366092;'>
            <td><b>Statements Total inc VAT</b></td>
            <td><b>&euro;{{ number_format($s->statements_total_inc_vat, 2) }}</b></td>
         </tr>
         <tr style='color:#366092;'>
            <td><b>Statements Total ex VAT</b></td>
            <td><b>&euro;{{ number_format($s->statements_total_ex_vat, 2) }}</b></td>
         </tr>
         <tr>
            <td>Number Of Meters</td>
            <td>{{ $s->no_of_meters }}</td>
         </tr>
         <tr>
            <td>Meter Charge</td>
            <td>&euro;{{ $s->meter_charge }}</td>
         </tr>
         <tr>
            <td>Meter Total inc vat</td>
            <td>&euro;{{ number_format($s->meter_total_inc_vat, 2) }}</td>
         </tr>
         <tr>
            <td>Meter Total ex vat</td>
            <td>&euro;{{ number_format($s->meter_total_ex_vat, 2) }}</td>
         </tr>
         <tr>
            <td>VAT Rate</td>
            <td>{{ $s->vat }}%</td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>..</td>
            <td>&euro;{{ number_format($s->invoiced_amount, 2) }}</td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>VAT Payment</b></td>
            <td>&euro;{{ number_format($s->vat_payment, 2) }}</td>
         </tr>
         <tr style='color:#ff2323;'>
            <td>Scheme Payment</b></td>
            <td>&euro;{{ number_format($s->scheme_payment, 2) }}</td>
         </tr>
         <tr>
            <td><br/><br/></td>
         </tr>
         <tr style='color:#a65755;'>
            <td><b>Avg Daily kWh</b></td>
            <td><b>{{ number_format($s->avg_daily_kwh, 2) }}</b></td>
         </tr>
         <tr style='color:#a65755;'>
            <td><b>Avg Daily Cost</b></td>
            <td><b>&euro;{{ number_format($s->avg_daily_cost, 2) }}</b></td>
         </tr>
         <!--
            <tr style='color:#7a6790;'>
            	<td><b>Annual Avg kWh - day</b></td>
            	<td><b>{{ number_format($s->annual_avg_kwh_day, 2) }}</b></td>
            </tr>
            <tr style='color:#7a6790;'>
            	<td><b>Annual Avg Cost - day</b></td>
            	<td><b>&euro;{{ number_format($s->annual_avg_cost_day, 2) }}</b></td>
            </tr>-->
      </table>
   </div>
   <br/><br/>	
   <div class="page_break"></div>
   <table style='' width="100%">
      <tr>
         <td width='40%' style="vertical-align:middle">
            <img @if($fullscreen) src='https://www.snugzone.biz/images/logo.png' @else src='/var/www/html/snugzone/images/logo.png' @endif>
         </td>
         <td width='60%' style="vertical-align:top">
            <table width="100%">
               <tr>
                  <td>
                     <center>
                        <div style='font-size:30px;'>
                           Prepago Platform Ltd<br/>
                           1 Woodbine Avenue<br/>
                           Blackrock<br/>
                           Co Dublin<br/>
                           <br/>
                           Tel 087 2534708<br/>
                           www.snugzone.biz<br/>
                        </div>
                     </center>
                  </td>
               </tr>
            </table>
         </td>
      </tr>
   </table>
   <table width='100%'>
      <tr>
         <td width='70%' style='text-align: center;'>
            <font style='float:center;font-size:1.5rem;font-weight:bold;'>Invoice</font>
         </td>
         <td width='60%'>
            <div style='float:right;text-align:right;font-size:1rem;font-weight:bold;'>
               {{ nl2br($s->company_address) }}<br/>
            </div>
         </td>
      </tr>
   </table>
   <div class="span8">
      <table width="50%" class="advice_note">
         <tr>
            <td>
               Our Ref {{ $s->abbreviation }}
            </td>
            <td>
               {{ $s->ref_pa }}
            </td>
         </tr>
         <tr>
            <td>
               Month
            </td>
            <td>
               {{ $s->month }}
            </td>
         </tr>
         <tr>
            <td>
               Date
            </td>
            <td>
               {{ $s->date }}
            </td>
         </tr>
         <tr>
            <td>
               Prepago Payment
            </td>
            <td>
               &euro;{{ number_format($s->invoiced_amount, 2) }}
            </td>
         </tr>
         <tr>
            <td>
               VAT Payment
            </td>
            <td>
               &euro;{{ number_format($s->vat_payment, 2) }}
            </td>
         </tr>
         <tr>
            <td>
               VAT Rate
            </td>
            <td>
               {{ $s->vat/100 }}
            </td>
         </tr>
         <tr>
            <td>
               Total
            </td>
            <td>
               &euro;{{ number_format($s->total_payment, 2) }}
            </td>
         </tr>
         <tr>
            <td>
               <b>VAT Number</b>
            </td>
            <td>
               <b>{{ $s->vat_number }}</b>
            </td>
         </tr>
      </table>
      <div style=''>
         <h2 style='color: red'>Paid in Full</h2>
      </div>
   </div>
   <br/><br/>
   <div style='position:fixed;
      left:0px;
      bottom:0px;
      height:30px;
      width:100%;float:right;font-size:1rem;text-align:right;'>
      Registered office: 1 Woodbine Avenue, Blackrock, Co Dublin, Ireland<br/>
      Company Number 517221<br/>
      Directors; Aidan O&#39;Neill, Roslyn O&#39;Neill<br/><br/><br/>
   </div>
   
@if(isset($fullscreen) && $fullscreen) 
</div>
@endif
</body></html>