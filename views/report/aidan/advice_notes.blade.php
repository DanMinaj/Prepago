</div>

<div><br/></div>
<h1>Advice Note Reports</h1>

<style>
	.advice_note{
		font-size: 0.9rem;
	}
</style>
<div style="float: right">

    <form method="get" action="" class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" name="start_date" placeholder="Start Date" type="text" value="{{ date('d-m-Y', strtotime($start_date)) }}">
        <label>To</label>
        <input id="to" name="end_date" type="text" placeholder="End Date" value="{{ date('d-m-Y', strtotime($end_date)) }}">
        <input type="submit" name="search" value="search" class="btn-success"/>
 

</div>


<div class="admin2">
	
	<div style="clear:both"></div>
	<div class="row">
		<div class="span8">
			<table width="100%" class="table table-bordered">
				
				@if($scheme_id != 'all')
				<tr>
					<td><b> Company </b></td>
					<td>
						<textarea name="company_name" class="company_name" placeholder="Company">{{ $company_name }}</textarea>
					</td>
				</tr>
				@endif
				<tr>
					<td><b> Scheme </b></td>
					<td>
						<select id="scheme" name="scheme">
							
							@foreach(Scheme::active() as $s)
							@if($s->simulator == 0) 
								<option @if($scheme_id == $s->scheme_number) selected @endif value="{{ $s->scheme_number }}">{{ $s->scheme_nickname }}</option>
							@endif
							@endforeach
							<option @if($scheme_id == 'all') selected @endif value="all">- All Schemes -</option>
							<option value="select">- Select Schemes -</option>
						</select>	
					</td>
				</tr>
				
				<tr>
					<td><b> VAT Number </b></td>
					<td><input type="text" name="vat_number" value="{{ $vat_number }}" placeholder="VAT Number"> </td>
				</tr>
				<tr>
					<td><b> VAT </b></td>
					<td><input type="text" name="vat" value="{{ $vat }}" placeholder="VAT"> %</td>
				</tr>
				<tr>
					<td><b> Payments % Charge </b></td>
					<td><input type="text" name="payments_charge" value="{{ $payments_charge }}" placeholder="Payments % Charge"> %</td>
				</tr>
				<tr>
					<td><b> App Charge </b></td>
					<td>&euro; <input type="text" name="app_charge" value="{{ number_format($app_charge, 2) }}" placeholder="App Charge"></td>
				</tr>
				<tr>
					<td><b> Meter Charge </b></td>
					<td>&euro; <input type="text" name="meter_charge" value="{{ number_format($meter_charge, 2) }}" placeholder="Meter Charge"></td>
				</tr>
				<tr>
					<td><b> IOU Charge </b></td>
					<td>&euro; <input type="text" name="iou_charge" value="{{ number_format($iou_charge, 2) }}" placeholder="IOU Charge"></td>
				</tr>
				<tr>
					<td><b> Statements Charge </b></td>
					<td>&euro; <input type="text" name="statements_charge" value="{{ number_format($statements_charge, 2) }}" placeholder="Statements Charge"></td>
				</tr>
				<tr>
					<td><b> App Support </b></td>
					<td>&euro; <input type="text" name="app_support" value="{{ $app_support }}" placeholder="App Support"></td>
				</tr>
				<tr>
					<td><b> Autotopup Charge </b></td>
					<td>&euro; <input type="text" name="autotopup_charge" value="{{ $autotopup_charge }}" placeholder="Autotopup Charge"></td>
				</tr>
				<tr>
					<td colspan="2">
					<center>
						<button type="submit" name="save" value='1' class="btn btn-success btn-lg">
						Save changes
						</button>
					</center>
					</td>
				</tr>
			</table>
		</div>
			<div class="span4">
			<table width="50%" class="table table-bordered">
				
				<tr>
					<td><b> Days </b></td>
					<td>{{ $days }}</td>
				</tr>
				
				<tr>
					<td><b> Start date </b></td>
					<td>{{ date('d-m-Y', strtotime($start_date)) }}</td>
				</tr>
				
				<tr>
					<td><b> End date </b></td>
					<td>{{ date('d-m-Y', strtotime($end_date)) }}</td>
				</tr>
			</table>
			
			@if(count($scheme) > 1) 
			<?php
				$ids = [];
				foreach($scheme as $k => $v) {
					$ids[] = $v->scheme_number;
				}
				$str = implode(',', $ids);
				
			?>
	
			<table width="50%" class="table table-bordered">
			<tr>
				<td colspan='2'>
					<br/><input type="checkbox" name="check_all" id="check_all">&nbsp;<span class="check_all">Check all</span>
					<br/><input type="checkbox" name="pdf" id="pdf"> PDF &horbar; (*cannot use PDF with total report)
					
				</td>
			</tr>
			<tr>
				
				
			</tr>
			@foreach($scheme as $k => $s) 
				@if( $k == 0 || ($k % 2 == 0))
					<tr>
					<td>
						<input type="checkbox" id='scheme_check_{{ $s->scheme_number }}' class='scheme_check' scheme_number="{{ $s->scheme_number }}"> {{ $s->scheme_nickname }}
					</td>
				@else
					<td>
						<input type="checkbox" id='scheme_check_{{ $s->scheme_number }}' class='scheme_check' scheme_number="{{ $s->scheme_number }}"> {{ $s->scheme_nickname }}
					</td>
					</tr>
				@endif
				
			@endforeach	
			</table>
			<button class="btn btn-primary cover_note" multi='true' scheme_number="{{ $str }}">
				<i class='fa fa-file'></i> Cover Note
			</button>
			<button class="btn btn-primary total_report" multi='true' scheme_number="{{ $str }}">
				<i class='fa fa-file'></i> Total Report
			</button>
			@endif
			
			</div>
			
			
	</div>
	</form>
	
	<hr/>
	<div class="progress progress-striped active">
		 <div id="progress_bar" class="bar" style="width: 0%;"></div>
	</div>
	<button class="btn btn-primary download_all" multi='true'>
				<i class='fa fa-file-archive'></i> ZIP All Pdf's
	</button>
	<button class="btn btn-warning download_ready" style='display:none' disabled multi='true'>
			<i class='fa fa-download'></i> Preparing...
	</button>
	
	<hr/>
	
	<div class="row">
	
	<div class="accordion" id="accordion2">

	@foreach($scheme as $k => $s) 
	  <div class="accordion-group {{ $s->scheme_number }}_accordion">
		<div class="accordion-heading">
		  <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse{{ $s->scheme_number }}">
		  {{ $s->scheme_nickname }}
		  </a>
		</div>
		<div id="collapse{{ $s->scheme_number }}" class="accordion-body collapse @if(count($scheme) == 1) in @endif">
			<br/>
			
			<button class="btn btn-primary getpdf" scheme_number='{{ $s->scheme_number }}'>
				<i class='fa fa-file'></i> Download PDF
			</button>
			
			<button class="btn btn-primary fullscreen" scheme_number='{{ $s->scheme_number }}'>
				<i class='fa fa-expand-arrows-alt'></i> View Fullscreen
			</button>
			
		 	<br/>
		  <div class="accordion-inner" style="font-family: Arial !important;" id="{{ $s->scheme_nickname }}">
				
				<table style='height: 100%; display: flex; align-items: center; justify-content: center;' width="100%">
						<tr>
							<td width='40%' style="vertical-align:middle"><img width='20%' src='/snugzone/images/logo.png'></td>
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
							<td>&euro;{{ number_format($app_support, 2) }}</td>
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
							<td><b>{{ $s->iou_charge }}</b></td>
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
							<td>{{ $vat }}%</td>
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
				
				
				
				<table style='padding-top: 15%;height: 100%; display: flex; align-items: center; justify-content: center;' width="100%">
						<tr>
							<td width='40%' style="vertical-align:middle"><img width='20%' src='/snugzone/images/logo.png'></td>
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
							{{ $vat/100 }}
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
							<b>{{ $vat_number }}</b>
						</td>
					</tr>
				</table>
				<div style=''>
					<h2 style='color: red'>Paid in Full</h2>
				</div>
				</div>
				<br/><br/>
				<div style='float:right;font-size:1rem;text-align:right;'>
					Registered office: 1 Woodbine Avenue, Blackrock, Co Dublin, Ireland<br/>
				Company Number 517221<br/>
				Directors; Aidan O&#39;Neill, Roslyn O&#39;Neill<br/><br/><br/>
				</div>
				
				
		  </div>
		</div>
	  </div>
	 @endforeach
	 

	</div>


	</div>
	
	
</div>

<script>

	$(document).ready(function()
	{
		$("#to").datepicker({ dateFormat: 'dd-mm-yy' });
		$("#from").datepicker({ dateFormat: 'dd-mm-yy' });
	});	
	
	$(function(){

		var checkComplete = null;
		getAdviceNote('all');
		
		$('.cover_note').on('click', function(e){
			
		
			e.preventDefault();
			
			if($(this).attr('multi') == 'true') {
				var schemes = [];
				$('.scheme_check').each(function(){
					if($(this). is(":checked")) {
						schemes.push($(this).attr('scheme_number'));
					}
				});
				
				var company_name = $('.company_name').val();
				
				if(schemes.length <= 0)
					return;
				
				$.notify('Getting cover note for ' + schemes.length + ' schemes..');
				
				schemes = schemes.join(',');
				
				window.open(window.location.href + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "pdf=" + $('#pdf').is(":checked") + "&multi=true&cover_note=true&schemes=" + schemes + "&company_name=" + company_name);
				
				return;
			}
			
		});
		
		$('#check_all').on('click', function(){

			if($('.check_all').text().indexOf('Check all') !== -1) {
				$('.check_all').text('Uncheck all');
				$('.scheme_check').each(function(){
					$(this).prop('checked', true);
				});
			} else {
				$('.check_all').text('Check all');
				$('.scheme_check').each(function(){
					$(this).prop('checked', false);
				});
			}
			
			$('.scheme_check').each(function(){
				var scheme = $(this).attr('scheme_number');
				console.log('hiding .' + scheme + '_accordion');
				console.log($('.' + scheme + '_accordion'));
				
				if($('.scheme_check:checked').length == 0) {
					$('.' + scheme + '_accordion').show();
				} else {
			
				if( !$('#scheme_check_' + scheme).is(":checked")) {
					$('.' + scheme + '_accordion').hide();
				}
				else 
					$('.' + scheme + '_accordion').show();
				}
			});
			
			
		});
		
		$('.scheme_check').on('click', function(){
			$('.scheme_check').each(function(){
				var scheme = $(this).attr('scheme_number');
				console.log('hiding .' + scheme + '_accordion');
				console.log($('.' + scheme + '_accordion'));
				
				if($('.scheme_check:checked').length == 0) {
					$('.' + scheme + '_accordion').show();
				} else {
			
				if( !$('#scheme_check_' + scheme).is(":checked")) {
					$('.' + scheme + '_accordion').hide();
				}
				else 
					$('.' + scheme + '_accordion').show();
				}
			});
		});
			
		$('.total_report').on('click', function(e){
			
		
			e.preventDefault();
			
			
			if($(this).attr('multi') == 'true') {
				var schemes = [];
				$('.scheme_check').each(function(){
					if($(this).is(":checked")) {
						schemes.push($(this).attr('scheme_number'));
					}
				});
				
				var company_name = $('.company_name').val();
				
				if(schemes.length <= 0)
					return;
				
				$.notify('Getting total report for ' + schemes.length + ' schemes..');
				
				window.open(window.location.href  + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "pdf=" + $('#pdf').is(":checked") + "&multi=true&total_report=true&schemes=" + schemes + "&company_name=" + company_name);
				
				schemes = schemes.join(',');
				
				return;
			}
			
		});
		
		$('.getpdf').on('click', function(){
			//alert();
			
			var scheme_number = $(this).attr('scheme_number');
			getPDF(scheme_number, false);
		});
		$('.fullscreen').on('click', function(){
			//alert();
			var scheme_number = $(this).attr('scheme_number');
			getFullScreen(scheme_number, false);
		});
		$('#scheme').on('change', function(){
			if($(this).val() == 'all') {
				getAdviceNote('')
			} else {
				getAdviceNote(parseInt($(this).val()))
			}
		});
		$('.download_all').on('click', function(){
			var scheme_number = $(this).attr('scheme_number');
			getAdviceNoteAll(scheme_number);
		});
		$('.download_ready').on('click', function(){
			window.location.href = $(this).attr('dl');
		});
		
		function getPDF(scheme_number, multi) {
			window.open(window.location.href + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "pdf=true&multi=false&scheme_number=" + scheme_number);
			
		}
		
		function getFullScreen(scheme_number, multi) {
			window.open(window.location.href + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "fullscreen=true&scheme_number=" + scheme_number);
			
		}
		
		function getAdviceNote(id) {
			
			if(id == 'all') {
				
			} else {
				if(window.location.href.indexOf('scheme=') == -1) {
					//alert('test');
					window.location.href = window.location.href + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "scheme=" + id;
				} else {
					window.location.href = "/system_reports/advice_notes?start_date={{ Input::get('start_date') }}&end_date={{ Input::get('end_date') }}&scheme=" + id;
				}
			}
		}
		
		function getAdviceNoteAll(scheme_number) {
			if(checkComplete != null) {
				clearInterval(checkComplete);
			}
			$('#progress_bar').animate({
				width: '0%',
			});
			$('.download_ready').text($('.download_ready').text().replace('Download now.', 'Preparing...'));
			$('.download_ready').attr('disabled', true);
			$('.download_ready').removeClass('btn-success');
			$('.download_ready').addClass('btn-warning');
			
			var schemes = "";
			$('.scheme_check:checked').each(function(){
				schemes += $(this).attr('scheme_number') + ",";
			});
			if(schemes.length <= 0) {
				$('.scheme_check:not(:checked)').each(function(){
					schemes += $(this).attr('scheme_number') + ",";
				});
			}
			schemes = schemes.slice(0, -1);
			var url = window.location.href + ((window.location.href.indexOf('?') !== -1) ? '&' : '?') + "pdf=true&mass=true&scheme_number=" + schemes;
			$.ajax({url: url, data: {}, success: function(data){
				var id = data.start.id;
				var opened = false;
				var waiting = false;
				checkComplete = setInterval(function(){
					if(waiting) return;
					waiting = true;
					
					try {
						$('.download_ready').show();
						$.ajax({url: '/system_reports/schedule/getInfo', data: {id: id}, success: function(data){
							var completion = data.completion;
							$('#progress_bar').animate({
								width: completion + '%',
							});
							if(completion >= 100 && !opened) {
								opened = true;
								console.log(data.dl)
								$('.download_ready').text($('.download_ready').text().replace('Preparing...', 'Download now.'));
								$('.download_ready').attr('dl', data.dl);
								$('.download_ready').removeAttr('disabled');
								$('.download_ready').removeClass('btn-warning');
								$('.download_ready').addClass('btn-success');
								clearInterval(checkComplete);
								return;
							}
							waiting = false;
						}, error: function(){ waiting = false; alert('Failed to download as PDF. Err #1. Contact system manager.'); }});
					}catch(e) { waiting = false; }
				}, 800);
			}, error: function(){ alert('Failed to download as PDF. Err #1. Contact system manager.');}});
		}
	});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js" integrity="sha384-NaWTHo/8YCBYJ59830LTz/P4aQZK1sS0SneOgAvhsIl3zBu8r9RevNg5lHCHAuQ/" crossorigin="anonymous"></script>

