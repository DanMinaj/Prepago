
</div>

<div><br/></div>
<h1>ARREGLO <font style='font-size: 1.2rem; font-family: arial; color: #a4a4a4;'>Last updated: {{ $todaysGraphData->updated_at }}</font> </h1>

<div class="admin2">

<style>
	.specialist-title{
		border: 1px solid #868686;
		background: white;
		padding: 10px;
		border-radius: 5px;
	}
	.h4{
		color: #666 !important;
		font-size:1.1em;
	}
	.title{
		font-size: 1.0em;
		font-weight: bold;
		text-transform: uppercase;
		padding-bottom: 6px;
		color: #666;
	}
</style>

	@include('includes.notifications')
<style>
		td{
			vertical-align: top;
		}
		.traffic-light td.sub {
		    padding-right: 10px;
			text-shadow: 0px -1px 0px #dedede;
		}
		.well.well-sm{
			color: white;
			text-shadow: none;
		}
		center b{
			font-size: 17px !important;
		}
		td center{
			font-size:15px;
			margin-top: 3px;
		}
		.green, .yellow, .red, .blue{
			background-color: white;
			color: #8898aa !important;
		}
		
		.figure{
			font-size: 20px;
			color: #32325d;
		}
		.circle{
			color: #ffffff;
			padding: 14px;
			width:15px;
			height:15px;
			border-radius: 90%!important;
			display: inline-flex;
			justify-content: center;
			align-items: center;
			text-align: center;
			font-size: 1.4em;
		}
		.circle.greenc{
			background-color: #62c462;
		}
		.circle.yellowc{
			background-color: #fbb450;
		}
		.circle.redc{
			background-color: #ee5f5b;
		}
		.circle.bluec{
			background-color: #5bc0de;
		}
		.circle.whitec{
			background-color: #fff;
			color: black !important;
			border:1px solid #ccc;
		}
		.well{
			background-color: #fff;
		}
		.paypal{
			color: #027dbb;
			font-size: 1.5em;
			padding: 14px;
			width: 10px;
			height: 10px;
			background: #f2f2f2;
			border-radius: 90%!important;
			display: inline-flex;
			justify-content: center;
			align-items: center;
			text-align: center;
		}
	</style>
	
	<table width="100%">
		
		<tr>
			<td width="50%">
				<div class="well well-sm" style="height:210px;color:black;">
					<table width="100%">
						<tr>
							<td width="60%">
								<font style="font-size:1.3rem;">SMS Credit Balance: {{ $sms_data["credit"] }} - </font>
								<font style="font-size:1.1rem">{{ $sms_data["used_today"] }} used today</font>
							</td>
							<td width="40%">
								<a href="https://app.sendmode.com/index.aspx?tab=login">
									<button class="btn btn-info"> Top up </button>
								</a>
							</td>
						</tr>	
					</table>
				</div>
			</td>
			<td width="50%">
				<div class="well well-sm" style="height:210px;color:black;">
						<table width="100%">
						<tr>
							<td colspan='2' style="height:200" width="100%">
								{{ $smsChart }}
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		
	</table>
	<table width="100%">
		<tr>
			
			<!-- Four container boxes -->
			<td width="40%">
				
				<table width="100%">
					<tr class="traffic-light">
						<td class="sub">
							<div class="well well-sm green">
								<table width="100%">	
									
									<!-- Title, Figure & Circle image -->
									<tr>
										
										<!-- Title & Figure -->
										<td width="80%">
											<table width="100%">
												<tr>
													<td class='title'>Green Customers</td>
												</tr>
												<tr>
													<td class='figure'><b>{{ $customer_statuses->greenCustomers }}</b></td>
												</tr>
											</table>
										</td>
										
										<!-- Circle image -->
										<td width="20%">
											<a href="/view/sub_view/greencustomers" ajaxload="true" title="Green customers"><i class="fa fa-users circle greenc"></i></a>
										</td>
										
									</tr>
									
									<!-- % increase info -->
									<tr>
										<td colspan='2'>
											<br/>
											@if($customer_statuses->greenCustomers_pc > 0) 
												<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format(abs($customer_statuses->greenCustomers_pc), 0) }}%</font> Since yesterday
											@else
												<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($customer_statuses->greenCustomers_pc), 0) }}%</font> Since yesterday
											@endif
										</td>
									</tr>
									
								</table>
							</div>
						</td>
						<td class="sub">
							<div class="well well-sm yellow">
							<table width="100%">
									
									<!-- Title, Figure & Circle image -->
									<tr>
										
										<!-- Title & Figure -->
										<td width="80%">
											<table width="100%">
												<tr>
													<td class='title'>Yellow Customers</td>
												</tr>
												<tr>
													<td class='figure'><b>{{ $customer_statuses->yellowCustomers }}</b></td>
												</tr>
											</table>
										</td>
										
										<!-- Circle image -->
										<td width="20%">
											<a href="/view/sub_view/yellowcustomers" ajaxload="true" title="Yellow customers"><i class="fa fa-users circle yellowc"></i></a>
										</td>
										
									</tr>
									
									<!-- % increase info -->
									<tr>
										<td colspan='2'>
											<br/>
											@if($customer_statuses->yellowCustomers_pc > 0) 
												<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format(abs($customer_statuses->yellowCustomers_pc), 0) }}%</font> Since yesterday
											@else
												<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($customer_statuses->yellowCustomers_pc), 0) }}%</font> Since yesterday
											@endif
										</td>
									</tr>
									
								</table>
							</div>
						</td>
						<td class="sub">
							<div class="well well-sm red">
							<table width="100%">
									<!-- Title, Figure & Circle image -->
									<tr>
										
										<!-- Title & Figure -->
										<td width="80%">
											<table width="100%">
												<tr>
													<td class='title'>Red Customers</td>
												</tr>
												<tr>
													<td class='figure'><b>{{ $customer_statuses->redCustomers }}</b></td>
												</tr>
											</table>
										</td>
										
										<!-- Circle image -->
										<td width="20%">
											<a href="/view/sub_view/redcustomers" ajaxload="true" title="Red customers"><i class="fa fa-users circle redc"></i></a>
										</td>
										
									</tr>
									
									<!-- % increase info -->
									<tr>
										<td colspan='2'>
											<br/>
											@if($customer_statuses->redCustomers_pc > 0) 
												<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format(abs($customer_statuses->redCustomers_pc), 0) }}%</font> Since yesterday
											@else
												<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($customer_statuses->redCustomers_pc), 0) }}%</font> Since yesterday
											@endif
										</td>
									</tr>
						
									
								</table>
							</div>
						</td>
						<td class="sub">
							<div class="well well-sm blue">
							<table width="100%">
									<!-- Title, Figure & Circle image -->
									<tr>
										
										<!-- Title & Figure -->
										<td width="80%">
											<table width="100%">
												<tr>
													<td class='title'>Blue Customers</td>
												</tr>
												<tr>
													<td class='figure'><b>{{ $customer_statuses->blueCustomers }}</b></td>
												</tr>
											</table>
										</td>
										
										<!-- Circle image -->
										<td width="20%">
											<a href="/view/sub_view/bluecustomers" ajaxload="true" title="Blue customers"><i class="fa fa-users circle bluec"></i></a>
										</td>
										
									</tr>
									
									<!-- % increase info -->
									<tr>
										<td colspan='2'>
											<br/>
											@if($customer_statuses->blueCustomers_pc > 0) 
												<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format(abs($customer_statuses->blueCustomers_pc), 0) }}%</font> Since yesterday
											@else
												<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($customer_statuses->blueCustomers_pc), 0) }}%</font> Since yesterday
											@endif
										</td>
									</tr>
									
								</table>
							</div>
						</td>
						<td class="sub">
							<div class="well well-sm blue">
							<table width="100%">
									<!-- Title, Figure & Circle image -->
									<tr>
										
										<!-- Title & Figure -->
										<td width="80%">
											<table width="100%">
												<tr>
													<td class='title'>Unoccupied Apts.</td>
												</tr>
												<tr>
													<td class='figure'><b>{{ $customer_statuses->whiteCustomers }}</b></td>
												</tr>
											</table>
										</td>
										
										<!-- Circle image -->
										<td width="20%">
											<a href="/view/sub_view/whitecustomers" ajaxload="true" title="Blue customers"><i class="fa fa-users circle whitec"></i></a>
										</td>
										
									</tr>
									
									<!-- % increase info -->
									<tr>
										<td colspan='2'>
											<br/>
											@if($customer_statuses->whiteCustomers_pc > 0) 
												<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format(abs($customer_statuses->whiteCustomers_pc), 2) }}%</font> Since yesterday
											@else
												<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($customer_statuses->whiteCustomers_pc), 2) }}%</font> Since yesterday
											@endif
										</td>
									</tr>
									
								</table>
							</div>
						</td>
					</tr>
				</table>
				
			</td>
			
		</tr>
	
	</table>
		

		

</div>

	<div class="row-fluid">
		<div class="span3">
			<div class="well">
				<table width="100%">
					<tr>
						<td width="20%">
							<i style="font-size:2rem;" class="fas fa-file-alt"></i>
						</td>
						<td style="vertical-align:middle" width="80%">
							<font style="font-size:0.9rem;font-weight:bold"> 
							{{ $misc_data['statements_this_month'] }} Statements issued this month </font>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="2">
							<br/>
							<center style="font-size: 0.7rem">
								{{ $misc_data['statements_last_month'] }} statements issued last month 
							</center>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				<table width="100%">
					<tr>
						<td width="20%">
							<i style="font-size:2rem;" class="fab fa-stripe-s"></i>
						</td>
						<td style="vertical-align:middle" width="80%">
							<font style="font-size:0.9rem;font-weight:bold"> 
							{{ $misc_data['autotopup_subscriptions_this_month'] }} auto topup renewals this month
							</font>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="2">
							<br/>
							<center style="font-size: 0.7rem">
								{{ $misc_data['autotopup_subscriptions_last_month'] }} auto topup renewals last month
							</center>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="span3">
			<div class="well">
			<table width="100%">
					<tr>
						<td width="20%">
							<i style="font-size:2rem;" class="fas fa-home"></i>
						</td>
						<td style="vertical-align:middle" width="80%">
							<font style="font-size:0.9rem;font-weight:bold"> 
							{{ $misc_data['closed_accounts_this_month'] }} closed accounts this month </font>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="2">
							<br/>
							<center style="font-size: 0.7rem">
								{{ $misc_data['closed_accounts_last_month'] }} closed accounts last month
							</center>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="span3">
			<div class="well">
			<table width="100%">
					<tr>
						<td width="20%">
							<i style="font-size:2rem;" class="fas fa-home"></i>
						</td>
						<td style="vertical-align:middle" width="80%">
							<font style="font-size:0.9rem;font-weight:bold"> 
							{{ $misc_data['opened_accounts_this_month'] }} opened accounts this month </font>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="2">
							<br/>
							<center style="font-size: 0.7rem">
								{{ $misc_data['opened_accounts_last_month'] }} opened accounts last month
							</center>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
		<div class="well">
		{{	$weekTopupChart	}}
		@if($topupWeekPercent > 0) 
			<center><font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($topupWeekPercent, 0) }}%</font> More Topups This Week</center>
		@else
			<center><font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($topupWeekPercent), 0) }}%</font> Less Topups This Week</center>
		@endif
		</div>
		</div>
		<div class="span3">
		<div class="well">
		{{	$awayModeChart	}}
		@if($awayModePercent > 0) 
			<center><font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($awayModePercent, 0) }}%</font> More Away modes This Week</center>
		@else
			<center><font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($awayModePercent), 0) }}%</font> Less Away modes This Week</center>
		@endif
		</div>
		</div>
		<div class="span3">
		<div class="well">
		{{ $iouChart }}
		@if($IOUPercent > 0) 
			<center><font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($IOUPercent, 0) }}%</font> More IOUs This Week</center>
		@else
			<center><font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($IOUPercent), 0) }}%</font> Less IOUs This Week</center>
		@endif
		</div>
		</div>
		<div class="span3">
		<div class="well">
		{{ $shutOffChart }}
		@if($shutOffPercent > 0) 
			<center><font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($shutOffPercent, 0) }}%</font> More Shutoffs This Week</center>
		@else
			<center><font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format(abs($shutOffPercent), 0) }}%</font> Less Shutoffs This Week</center>
		@endif
		</div>
		</div>
	</div>
	
	
	<div class="row-fluid">
		<div class="span4">
		<div class='well' style="height: 24rem;">
			<h4>Last 5 bug reports</h4>
			<hr/>
			<div class="row-fluid">
				<div class="span5"><b>Preview</b></div>
				<div class="span4"><b>Reported</b></div>
				<div class="span3"><b>Completed</b></div>
			</div>
			@foreach($last5BugReports as $b)
			<div class="row-fluid custom">
				<div class="span5" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'><a href="/bug/reports/view/{{ $b->id }}">{{ $b->preview }}</a></div>
				<div class="span4" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{{ Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</div>
				<div class="span3" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
					@if($b->resolved == 1)
							<i style="color:#62c462" class="fa fa-check"></i>
					@else
						<i style="color:#f89406" class="fa fa-ellipsis-h"></i>
					@endif
				</div>
			</div>
			<br/>
			@endforeach
		</div>
		</div>
		<div class="span4">
		<div class="well" style="height: 24rem;">
			<h4>Last 5 online operators</h4>
			<hr/>
			<div class="row-fluid">
				<div class="span5"><b>Name</b></div>
				<div class="span4"><b>Last online</b></div>
				<div class="span3"><b>Page</b></div>
			</div>
			@foreach($last5OnlineUsers as $o)
			<div class="row-fluid custom">
				<div class="span5" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'><a href="{{ URL::to('settings/scheme_settings/manage_operator/' . $o->id) }}">{{ $o->username }}</a></div>
				<div class="span4" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{{ Carbon\Carbon::parse($o->is_online_time)->diffForHumans() }}</div>
				<div class="span3" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'><a href="{{ $o->is_online_page }}">{{ $o->is_online_page }}</a></div>
			</div>
			<br/>
			@endforeach
		</div>
		</div>
		<div class="span4">
		<div class="well" style="height: 24rem;">
			<h4>Last offline schemes</h4>
			<div class="row-fluid">
				<div class="span5"><b>Name</b></div>
				<div class="span4"><b>Last offline</b></div>
				<div class="span3"><b>Uptime </b></div>
			</div>
			@foreach($last5OfflineSchemes as $o)
			<div class="row-fluid custom">
				<div class="span5" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'><a href="{{ URL::to('system_reports/sim_reports#s_' . $o->scheme_number ) }}">{{ Scheme::find($o->scheme_number)->scheme_nickname }}</a></div>
				<div class="span4" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{{ Carbon\Carbon::parse($o->last_offline)->diffForHumans() }}</div>
				<div class="span3" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{{ number_format($o->uptime_percentage, 0) }}%</div>
			</div>
			<br/>
			@endforeach
			<table width="100%">
				<tr>
					<td>
						<a href="{{ URL::to('settings/ping') }}">
							<button type="button" class="btn btn-info"><i class="fas fa-sim-card"></i>  Manage SIMs</button>
						</a>
					</td>
					<td>
						<a href="{{ URL::to('system_reports/sim_reports') }}">
							<button type="button" class="btn btn-warning"><i class="fas fa-chart-line"></i>  Graph</button>
						</a>
					</td>
				</tr>
			</table>
			
		</div>
		</div>
	</div>



	<div class="row-fluid">
		
		<div class="span6">
			<div class="well">
				
				<center>
					
					<h4>
					Topup method usage
					</h4>
					{{ $topupTypeChart }}
					
					<hr/>
					<center> 
					<div clas="row-fluid">
						<div class="span6">
							@if($topupPercent > 0) 
								<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($topupPercent, 0) }}%</font> More Topups a Month This Year
							@else
								<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format($topupPercent, 0) }}%</font> Less Topups a Month This Year	
							@endif
						</div>
						<div class="span6">
							@if($customerPercent > 0) 
								<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> {{ number_format($customerPercent, 0) }}%</font> More Customers This Year
							@else
								<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> {{ number_format($customerPercent, 0) }}%</font> Less Customers This Year	
							@endif
						</div>
					</div>
				</center>
					
				</center>  	
			</div>
		</div>
		
		<div class="span6" style="">
			<div class="row-fluid">
				<div class="span12">
					<div class="well" style="height: 28rem;">
					<h4>Stripe statistics</h4>
						<table width="100%">
							<tr>
								<td>
									<font style='color: #8898aa'>
										In transit to bank: <b>&euro;{{ number_format($stripe['total_in_transit'], 2) }}</b>
									</font>
								</td>
								<td>
									<font style='color: #8898aa'>
										Pending: <b>&euro;{{ number_format($stripe['total_pending'], 2) }}</b>
									</font>
								</td>
							</tr>
						</table>
					<h4>Stripe payouts</h4>
						<table width="100%" style="margin-left:0%;">
							<tr>
							@foreach($stripe['payouts'] as $k => $v) 
								<table width="100%">
									<tr>
										<td width="20%">
											@if($v->status == 'pending' || $v->status == 'in_transit')
												<i class="fa fa-truck-loading circle yellowc"></i>
											@elseif($v->status == 'paid')
												<i class="fa fa-check circle greenc"></i>
											@else
												<i class="fa fa-times circle redc"></i>
											@endif
										</td>
										<td width="40%">
												<br/><b>&euro;{{ number_format($v->amount, 2) }}</b>
										</td>
										<td width="40%" style="vertical-align:middle;">
												@if($v->status == 'paid')
													<font color="green"><b>Delivered {{ $v->arrival_date }}</b>&nbsp;<i class="fa fa-check-double"></i></font>
												@else
													est. {{ $v->arrival_date }} &nbsp;<i class="fa fa-spinner"></i></b>
												@endif
										</td>
									</tr>
								</table>
								<hr style="margin:0.5% 0 !important;">
							@endforeach
							</tr>
						</table>
					</div>
				</div>
			</div>
			
		</div>
		
	</div>
	
	<style>
	.row-fluid.custom{
			background: #f2f2f2;
			padding: 3px;
			border-radius: 5px;
			margin-bottom: 3px;
		}
	</style>
	
	<div class="row-fluid">
		<div class="span12">
			<div class="well">
				<table width="100%">
					<tr>
						<td width="50%">
						<h4> Top App platforms </h4>
						{{ $appPlatformsChart }}
						</td>
						
						<td width="50%">
						<h4 style="text-align:center;"> SnugZone Support statistics </h4><br/>
						@if($support_data['last_30_days'] == 0) 
							No data to show.
						@else
							<p style="text-align: right">
								<table width="100%" class="table">
									<tr>
										<th width="40%"></th>
										<th width="60%"></th>
									</tr>
									<tr>
										<td style="padding:5%;"><b> Tickets (30 days)</b> </td> 
										<td style="padding:5%;"> {{ $support_data['last_30_days'] }} </td>
									</tr>
									<!--
									<tr>
										<td style="padding:5%;"><b> Tickets (All Time)</b> </td> 
										<td style="padding:5%;"> {{ $support_data['all_time']['count'] }} </td>
									</tr>
									-->
									<tr>
										<td style="padding:5%;"><b> Responses (30 days)</b> </td> 
										<td style="padding:5%;"> 
										{{ number_format((($support_data['responses']/$support_data['last_30_days'])*100), 0) }}% response rate &horbar; ({{ $support_data['responses'] }}/{{ $support_data['last_30_days'] }})
										</td>
									</tr>
									<!--
									<tr>
										<td style="padding:5%;"><b> Responses (All Time)</b> </td> 
										<td style="padding:5%;"> 
										{{ number_format((($support_data["all_time"]['responses']/$support_data["all_time"]['count'])*100), 0) }}% response rate &horbar; ({{ $support_data["all_time"]['responses'] }}/{{ $support_data["all_time"]['count'] }})
										</td>
									</tr>
									-->
									<tr>
										<td style="padding:5%;"><b> Satisfaction (30 days)</b> </td> 
										<td style="padding:5%;"> 
										<b>{{ number_format((($support_data['happy']/$support_data['responses'])*100), 0)  }}%</b> <span style="font-weight:bold;color:green;">happy</span> ({{ $support_data['happy'] }})
											&horbar;
										<b>{{ number_format((($support_data['unhappy']/$support_data['responses'])*100), 0)  }}%</b> <span style="font-weight:bold;color:red;">unhappy</span> ({{ $support_data['unhappy'] }})  </td>
									</tr>
									<tr>
										<td style="padding:5%;"><b> Satisfaction (All Time)</b> </td> 
										<td style="padding:5%;"> 
										<b>{{ number_format((($support_data["all_time"]['happy']/$support_data["all_time"]['responses'])*100), 0)  }}%</b> <span style="font-weight:bold;color:green;">happy</span> ({{ $support_data["all_time"]['happy'] }})
											&horbar;
										<b>{{ number_format((($support_data["all_time"]['unhappy']/$support_data["all_time"]['responses'])*100), 0)  }}%</b> <span style="font-weight:bold;color:red;">unhappy</span> ({{ $support_data["all_time"]['unhappy'] }})  </td>
									</tr>
								</table>
							</p>
						@endif
						</td>
						
					</tr>
				</table>
				
				<table width="100%">
						<tr>
							<td colspan="2" width="100%"><h4 style="text-align:center;"> SnugZone Support Response statistics </h4><br/></td>
						</tr>
						<tr>
							<td width="65%">
								
								<table width="100%">
									<tr>
										<td><b> Ticket growth (Past 13 weeks)</b> <hr/></td>
									</tr>
									<tr>
										<td>{{ $ticketResponseChart }}</td>
									</tr>
								</table>
							</td> 
							<td width="35%" >
								<table width="100%">
									<tr>
										<td><b> Top responses (30 days)</b> <hr/></td>
									</tr>
									<tr>
										<td>
											@foreach($support_data['follow_up_reply'] as $k => $c) 
									{{ $k }} <br/><span class="badge badge-success">{{ number_format((($c/$support_data['responses'])*100), 0) }}% &horbar; {{ $c }}</span><br/><br/>
								@endforeach
										</td>
									</tr>
								</table>
							</td> 
						</tr>
				</table>
					
			</div>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span4">
			<div class="well" style='height: 23rem;'>
			<h4>Last 5 announcements</h4>
			<hr/>
			<div class="row-fluid">
				<div class="span6"><b>Title</b></div>
				<div class="span3"><b>Views</b></div>
				<div class="span3"><b>Comment %</b></div>
			</div>
			@foreach($announcements as $k => $a)
			<div class="row-fluid custom">
				<div class="span6" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
					<a href="/announcements/view/{{ $a->id }}">
						 {{ $a->title }}
					</a>
				</div>
				<div class="span3" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
					{{ $a->total_views }}
				</div>
				<div class="span3" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
					{{ ($a->total_views > 0) ? number_format(((count($a->comments)/$a->total_views) * 100), 0) : 0 }}%
				</div>
			</div>
			@endforeach
			</div>
		</div>
		<div class="span4">
			<div class="well" style='height: 23rem;'>
			<h4>Top 5 FAQs</h4>
			<hr/>
			<div class="span12"><b>Title</b></div>
			<div class="row-fluid">
			</div>
			@foreach($top5Faqs as $k => $f)
			<div class="row-fluid custom">
				<div class="span12" style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
					{{ $f->title }}
				</div>
			</div>
			@endforeach
			</div>
		</div>
		<div class="span4">
			<div class="well" style='height: 23rem;'>
			{{	$customerTrendChart	}}
			</div>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span3">
		<div class='well' style='min-height: 23rem;max-height: 23rem;overflow:scroll;'>
			<h4>Customers pending restoration <a href='/temperature_control'>({{ count($tcpCustomers['require_restoration']) }})</a></h4><hr/>
			<div class="row-fluid">
				
				<!--<button type="button" class="btn btn-success">Restore</button>-->
				
				<br/><br/>
				@foreach($tcpCustomers['require_restoration'] as $k => $v) 
				<div class="row-fluid">
					<div class="span4"><a href="/customer/{{ $v['customer']->username }}">{{ $v['customer']->username }}</a></div>
					<div class="span4"></div>
					<div class="span4">{{ $v->last_flow_temp }}&deg;C</div>
				</div>
				@endforeach
				
			</div>
		</div>
		</div>
		<div class="span3">
		<div class="well" style='min-height: 23rem;max-height: 23rem;overflow:scroll;'>
			<h4>Customers pending shut off <a href='/temperature_control'>({{ count($tcpCustomers['require_shut_off']) }})</a></h4><hr/>
			<!--<button type="button" class="btn btn-success">Restore</button>-->
				
				<br/><br/>
				@foreach($tcpCustomers['require_shut_off'] as $k => $v) 
				<div class="row-fluid">
					<div class="span4"><a href="/customer/{{ $v['customer']->username }}">{{ $v['customer']->username }}</a></div>
					<div class="span4"></div>
					<div class="span4">{{ $v->last_flow_temp }}&deg;C</div>
				</div>
				@endforeach
				
		</div>
		</div>
		<div class="span3">
		<div class="well" style='min-height: 23rem;max-height: 23rem;overflow:scroll;'>
			<h4>Customers pending away mode <a href='/temperature_control'>({{ count($tcpCustomers['require_away_mode']) }})</a></h4><hr/>
			<!--<button type="button" class="btn btn-success">Restore</button>-->
				
				<br/><br/>
				@foreach($tcpCustomers['require_away_mode'] as $k => $v) 
				<div class="row-fluid">
					<div class="span4"><a href="/customer/{{ $v['customer']->username }}">{{ $v['customer']->username }}</a></div>
					<div class="span4"></div>
					<div class="span4">{{ $v->last_flow_temp }}&deg;C</div>
				</div>
				@endforeach
				
		</div>
		</div>
		<div class="span3">
		<div class="well" style='min-height: 23rem;max-height: 23rem;overflow:scroll;'>
			<h4>Reconnections <a href='/shut_offs'>({{ count($reconnection_data['restored']) }}/{{ $reconnection_data['total'] }})</a></h4><hr/>
			<!--<button type="button" class="btn btn-success">Restore</button>-->
				
				<br/><br/>
				<b> Restored </b><hr/>
				@foreach($reconnection_data['restored'] as $k => $v) 
				<div class="row-fluid">
					<div class="span4"><a href="/customer/{{ $v->username }}">{{ $v->username }}</a></div>
					<div class="span4"></div>
					<div class="span4">{{ $v->temp }}&deg;C</div>
				</div>
				@endforeach
				<b> Unrestored </b><hr/>
				@foreach($reconnection_data['unrestored'] as $k => $v) 
				<div class="row-fluid">
					<div class="span4"><a href="/customer/{{ $v->username }}">{{ $v->username }}</a></div>
					<div class="span4"></div>
					<div class="span4">{{ $v->temp }}&deg;C</div>
				</div>
				@endforeach
		</div>
		</div>
	</div>
	
	
	
@section('extra_scripts')
<!--
{{ HTML::script('resources/js/Chart.min.js')	}}
{{ HTML::script('resources/js/driver.js')	}}
-->
{{ HTML::script('resources/chartjs/apexcharts.min.js')	}}
@endsection
<script type="text/javascript">

</script>