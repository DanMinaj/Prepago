<?php

/**
 * TEMPORARY STATIC DATA
 *
 * This group permision have been hard coded temporaraly
 *
 */

 
if(!Auth::user()) {
	die('Your session has expired. Please revisit the <a href="https://prepagoplatform.com">panel and login</a>');
}

if(isset($_GET['name'])) {
	
	$controllerName = Route::currentRouteAction();
	$viewName = Request::segment(1) . (Request::segment(2) ? "." . Request::segment(2) : "") . (Request::segment(3) ? "." . Request::segment(3) : "") . (Request::segment(4) ? "." . Request::segment(4) : "");
	echo "<span style='font-size:30px;'><i><b>Controller:</b></i> $controllerName</span>";
	echo "<br/>";
	echo "<span style='font-size:30px;'><i><b>View:</b></i> $viewName</span>";
}

	
$group = Auth::user()->group;

$green_data = Customer::getNormalCustomers()->count();
$yellow_data = Customer::getPendingCustomers()->count();
$red_data = Customer::getShutOffCustomers()->count();
$white_data = Customer::getEmptyCustomers()->count();

$white = $white_data;
$red = $red_data;
$yellow = $yellow_data;
$green = $green_data;


?>
<?php	

$current_page = $_SERVER['REQUEST_URI'];
if(strpos($current_page, 'customer_tabview') !== false)
	$current_page = 'customer_tabview';

?>
<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml">

<head>

     <meta name="viewport" content="width=1100" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'/>

    <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="https://prepagoplatform.com/resources/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>

	<link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/js/jquery-ui-1.12.1/jquery-ui.min.css">

	
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <title>Welcome To The Prepago Utility Dashboard</title>
    <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/bootstrap-responsive.min.css">

    <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/bootstrap.min.css">
 
    <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/style1.css">

    <!-- <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/daterangepicker-bs2.css">
 -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <!-- <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/example-fluid-layout.css">
 -->
	<link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/fontawesome/css/all.css">

	
	
    <script src="https://prepagoplatform.com/resources/js/bootstrap-transition.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-alert.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-modal.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-dropdown.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-scrollspy.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-tab.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-tooltip.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-popover.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-button.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-collapse.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-carousel.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-typeahead.js"></script>

    <script src="https://prepagoplatform.com/resources/js/bootstrap-datetimepicker.min.js"></script>

    <!-- <script src="https://prepagoplatform.com/resources/js/moment.js"></script>
 -->
    <!-- <script src="https://prepagoplatform.com/resources/js/daterangepicker.js"></script>
 -->
    <script src="https://prepagoplatform.com/resources/js/jquery.tablesorter.min.js"></script>

	<script src="https://prepagoplatform.com/resources/js/notify.js"></script>

	
	<!--<script src="https://prepagoplatform.com/resources/js/jquery.dataTables.min.js"></script>
-->
	<!--<link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/jquery.dataTables.min.css">
-->
	<!--<script src="https://prepagoplatform.com/resources/js/dataTables.bootstrap.min.js"></script>
-->
	
	
	<link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/js/bootstrap-toggle-master/css/bootstrap2-toggle.min.css">

	<script src="https://prepagoplatform.com/resources/js/bootstrap-toggle-master/js/bootstrap2-toggle.min.js"></script>

	
	<script src="https://prepagoplatform.com/resources/js/util/customer_tabview.js?9939393ss"></script>

	<script src="https://prepagoplatform.com/resources/js/util/stat.js?3993"></script>

	<script src="https://prepagoplatform.com/resources/js/util/support.js"></script>

	<script src="https://prepagoplatform.com/resources/js/util/services.js?7427"></script>

	<script src="https://prepagoplatform.com/resources/js/util/remember.js?21"></script>

	
	
	
	@yield('extra_scripts')

	@if(strpos(Request::url(), "tracking") !== false)
	{{ HTML::script('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js') }}
	@endif

</head>
<body>
<input type="hidden" name="user_ID" value="{{ Auth::user()->id }}">

    <div class="wrapper" >

@if($current_page != 'customer_tabview')
	@include('modals.support')	
@endif

<div id="ajaxload" style="padding:3%;" class="modal fade" role="dialog">
  <div class="modal-dialog">
	 <!-- Modal content-->
	 <div class="modal-content">
		<div class="modal-header">
		   <button type="button" class="close" data-dismiss="modal">&times;</button>
		   <h4 id="ajaxloadtitle" class="modal-title">Title</h4>
		</div>
		<div id="ajaxloadbody" class="modal-body">
		  // body
		</div>
		<div class="modal-footer">
		   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	 </div>
  </div>
</div>


        <div class="admin">
            <div class="admin_box">

	<style>
		#scheme_status	{
			border-radius: 8px;
			color: white;
			text-shadow: 0px 1px #999;
			/* border: 1px solid rgba(0,0,0,0.2); */
			{{ Auth::user()->scheme->statusAltCSS }}
		}
	</style>
	
                <div class="admin_nav" >
                    <ul>
						@if (hasAccess('open.close.account'))
							<li class="">
								<img src="{{ asset('resources/images/nav_icon3.png') }}" />
								<a href="#">open/close account</a>
								<ul>
									@if (hasAccess('customer.setup'))
                                        <li class="">
                                            <a href="{{ URL::to('open_account') }}">Customer Set Up</a>
                                        </li>
                                    @endif
									
									@if (hasAccess('close.account'))
                                        <li class="">
                                            <a href="{{ URL::to('close_account_alt') }}">Close Account</a>
                                        </li>
                                    @endif
									
									@if (hasAccess('close.account'))
                                        <li class="">
                                            <a href="{{ URL::to('close_account') }}">Alternative Close Account</a>
                                        </li>
                                    @endif
									
										
									@if (hasAccess('close.account'))
                                        <li class="">
                                            <a href="{{ URL::to('reinstate_account') }}">Reinstate/reopen closed Account</a>
                                        </li>
                                    @endif 
									
									@if (hasAccess('close.account') && Auth::user()->isUserTest())
                                        <li class="">
                                            <a href="{{ URL::to('open_account/queue') }}"><i class="fa fa-cogs"></i>&nbsp;Manage account queue</a>
                                        </li>
                                    @endif 
									
									<!-- Disabled 18/11/19 -->
									@if (1!=1 && hasAccess('installed.meters'))
                                        <li class="">
                                            <a href="{{ URL::to('installed_meters') }}">Installed Meters</a>
                                        </li>
                                    @endif
								</ul>
							</li>
						@endif	

						
						@if (hasAccess('crm.functions'))
							<li class="">
								<img src="{{ asset('resources/images/nav_icon4.png') }}" />
								<a href="#">CRM Function </a>
								<ul>
								
								<style>
									.issue-count{
										color: black;
										font-weight: bold;
										border-radius: 2px;
										background: white;
										float: right;
										padding: 2px;
										border: 1px solid black;
									}
								</style>
									
									<li class="" data-toggle="modal" data-target="#fix-an-issue" ><a href="{{ URL::to('support') }}">Fix An Issue</a></li>
								
									@if (\Auth::user()->isUserTest())
										<li class=""><a href="{{ URL::to('specialist') }}">Admin Specialist</a></li>
									@endif
									
									@if (\Auth::user()->isUserTest())
										<li class=""><a href="{{ URL::to('support') }}">Support Issues <span class="issue-count">{{SupportIssue::where('resolved', false)->where('started', false)->count()}}</span></a></li>
									@endif
								
									@if (hasAccess('customer.search'))
										<li class=""><a href="{{ URL::to('advanced_search') }}">Customer Search</a></li>
									@endif

									<!-- Disabled 18/11/19 -->
									@if (1!=1 && hasAccess('crm.barcode.reports'))
										<li class=""><a href="{{ URL::to('system_reports/barcode_reports') }}">Barcode Reports</a></li>
									@endif
									
									@if (hasAccess('message.all.customers'))
										<li class="">
											<a href="{{ URL::to('customer_messaging/scheme') }}">Message All Customers</a>
										</li>
									@endif
									
									@if(Auth::user()->isUserTest())
										<li class="">
											<a href="{{ URL::to('campaigns') }}">Campaigns</a>
										</li>
									@endif
									
									@if(Auth::user()->isUserTest())
										<li class="">
											<a href="{{ URL::to('announcements') }}">Announcements</a>
										</li>
									@endif
									
									
									@if(Auth::user()->isUserTest())
										<li class="">
											<a href="{{ URL::to('notifications') }}">In-App Notifications</a>
										</li>
									@endif
									
									
								</ul>
							</li>
						@endif	
						@if (hasAccess('system.reports'))
							<li class="">
								<img src="{{ asset('resources/images/nav_icon1.png') }}" />
								<a href="{{ URL::to('system_reports') }}">System reports </a>

								<ul>
								

									@if(1==1)
										<li class="">
											<a href="{{ URL::to('installed_meters') }}">Installed Meters</a>
										</li>
									@endif
									
									@if (hasAccess('supply.report.units'))
										<li class="">
										   <a href="{{ URL::to('system_reports/supply_report_units') }}">Supply Report-Units</a>
                                            @if (hasAccess('boiler.report'))
                                                <ul class="" style="left:-171%">
                                                    <li><a href="{{URL::to('system_reports/boiler_report')}}">Boiler Report</a></li>
                                                </ul>
                                            @endif
                                        </li>
                                    @endif

									@if (hasAccess('topup.reports'))
										<li class="">
											<a href="{{ URL::to('system_reports/topup_reports/customer_topup_history') }}">Scheme Top-Up Report</a>
										</li>
									@endif
									
									@if (hasAccess('barcode.reports'))
                                        <li class=""><a href="{{ URL::to('system_reports/barcode_reports') }}">Barcode Reports</a></li>
                                    @endif
									
									@if (hasAccess('sms.messages.sent'))
                                        <li class=""><a href="{{ URL::to('system_reports/sms_messages') }}">SMS Messages Sent</a></li>
                                    @endif
									
									@if (hasAccess('list.all.customers'))
                                        <li class=""><a href="{{ URL::to('system_reports/list_all_customers') }}">List Of All Customers</a></li>
                                    @endif
									
									@if (hasAccess('deleted.customer.report'))
                                        <li class=""><a href="{{ URL::to('system_reports/deleted_customers') }}">Deleted Customers Report</a></li>
                                    @endif
									
									@if (hasAccess('inactive.landlords.report'))
                                        <li class=""><a href="{{ URL::to('system_reports/inactive_landlords') }}">Inactive Landlords Report</a></li>
                                    @endif
									
									@if (hasAccess('deposit.report'))
                                        <li class=""><a href="{{ URL::to('customer_supply_status/deposit_reports') }}">Deposit Reports</a></li>
                                    @endif
									
									@if (hasAccess('credit.issue.report'))
										<li class=""><a href="{{ URL::to('system_reports/credit_issue_reports') }}">Credit Issue Reports</a>
											<ul class="" style="left:-171%">
												@if (hasAccess('iou.usage.display'))
                                                    <li class="" ><a href="{{ URL::to('system_reports/iou_usage_display') }}">IOU Usage Display</a></li>
                                                @endif
												
												@if (hasAccess('iou.extra.usage.display'))
                                                    <li class="" ><a href="{{ URL::to('system_reports/iou_extra_usage_display') }}">IOU Extra Usage Display</a></li>
                                                @endif
												
												@if (hasAccess('admin.issued.credit'))
                                                    <li class="" ><a href="{{ URL::to('system_reports/admin_issued_credit') }}">Admin Issued Credit</a></li>
                                                @endif
											</ul>
										</li>
									@endif	
										
									<!-- Disabled 18/11/19 -->
									@if (1!=1 && hasAccess('weather.report'))	
										<li class=""><a href="{{ URL::to('system_reports/weather_reports') }}">Weather Reports</a>
											<ul class="" style="left:-171%">
												@if (hasAccess('weather.vs.topups'))
                                                    <li class="" ><a href="{{ URL::to('weather_reports/topups') }}">Weather vs Top Ups</a></li>
                                                @endif
												
												@if (hasAccess('weather.vs.heat.usage'))
                                                    <li class="" ><a href="{{ URL::to('weather_reports/heat_usage') }}">Weather vs Heat Usage</a></li>
                                                @endif
											</ul>
										</li>
									@endif
									
									@if (hasAccess('bill.reports'))
                                        <li class=""><a href="{{ URL::to('system_reports/bill_reports') }}">Bill Reports</a></li>
                                    @endif

                                    @if (hasAccess('payout.reports'))
                                        <li class=""><a href="{{ URL::to('system_reports/payout_reports') }}">Payout Reports</a></li>
                                    @endif
									
									 @if (hasAccess('payout.reports'))
                                        <li class=""><a href="{{ URL::to('system_reports/advice_notes') }}">Advice Note Reports</a></li>
                                    @endif

									<!-- Disabled 18/11/19 -->
                                    @if (1!=1 && hasAccess('not.read.meters.reports'))
                                        <li class=""><a href="{{ URL::to('system_reports/not_read_meters') }}">Not Read Meters Reports</a></li>
                                    @endif								
									
								</ul>
							</li>
						@endif
						
                        <li class="">
                            <img src="{{ asset('resources/images/nav_icon2.png') }}" />
                            <a href="#">Settings</a>
                            <ul>          
								@if (hasAccess('admin.settings'))
									<li class="">
										<a href="#">Admin Settings </a>
										<ul class="" style="left:-171%">

											@if (hasAccess('sms.settings'))
                                                <li class="" ><a href="{{ URL::to('settings/sms_settings') }}">SMS Settings</a></li>
                                            @endif
											
											@if (hasAccess('faq.settings'))
                                                <li class="" ><a href="{{ URL::to('settings/faq') }}">FAQ Settings</a></li>
                                            @endif
											
											@if (hasAccess('tariff.settings'))
                                                <li class=""><a href="{{ URL::to('settings/tariff') }}">Tariff Settings</a></li>
                                            @endif
											
											@if (hasAccess('credit.setting'))
                                                <li class=""><a href="{{ URL::to('settings/credit_setting') }}">Credit Setting</a></li>
                                            @endif
											
											@if (hasAccess('access.control'))
                                               <!-- <li class=""><a href="{{ URL::to('settings/access_control') }}">Access Control</a></li>-->
                                            @endif 
											
											
											@if (hasAccess('unassigned.users'))
                                               <!-- <li class=""><a href="{{ URL::to('settings/unassigned_users') }}">Unassigned Users</a></li>-->
                                            @endif
											
											@if (hasAccess('groups.permissions'))
                                                <li class=""><a href="{{ URL::to('groups') }}">Groups & Permissions</a></li>
                                            @endif

                                            @if (hasAccess('schemes.list'))
                                                <li class=""><a href="{{ URL::to('schemes') }}">Schemes List</a></li>
                                            @endif
											
											 @if (Auth::user()->isUserTest())
                                               <!-- <li class=""><a href="{{ URL::to('settings/payments') }}">Stripe Settings</a></li>-->
                                            @endif
											

                                            <!--
                                            @if (hasAccess('multiple.account.close'))
                                                <li class=""><a href="{{ URL::to('settings/multiple_close') }}">Multiple Account Close</a></li>
                                            @endif
                                            -->
										</ul>
									</li>
								@endif	
									
                                <li class="">
                                    <a href="#">User Settings</a>
                                    <ul class="" style="left:-171%">
                                        <li class="" ><a href="{{ URL::to('user_settings/signins') }}">Recent Log-In's</a></li>
                                        <li class="" ><a href="{{ URL::to('user_settings/change_username') }}">Change Username</a></li>
                                        <li class="" ><a href="{{ URL::to('user_settings/change_password') }}">Change Password</a></li>
                                    </ul>
                                </li>
								<!--
								@if (hasAccess('boss'))
                                    <li class="">
                                        <a href="{{ URL::to('boss') }}">BOSS</a>
                                        <ul class="" style="left:-171%">
                                            @if ($bossLevel === 0 || $bossLevel == 1)
                                                <li class="" ><a href="{{ URL::to('settings/boss_restrictions') }}">Restrictions</a></li>
                                            @endif

                                            @if (hasAccess('boss.hierarchy'))
                                                <li class="" ><a href="{{ URL::to('boss-hierarchy') }}">Hierarchy</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @elseif ($bossLevel === 0 || $bossLevel == 1)
                                    <li class=""><a href="{{ URL::to('settings/boss_restrictions') }}">BOSS Restrictions</a></li>
                                @endif
								-->	
								
								
                                <li class=""><a href="{{ URL::to('settings/scheme_settings') }}"><i class="fas fa-wrench"></i> Manage Scheme</a></li>
                                <li class=""><a href="{{ URL::to('logout') }}">Log Out</a></li>
                            </ul>
                        </li>
                    </ul>

					
				<style>
					.changelog {	
						position: relative;
						left: 47.3%;
						background: url(../images/spc2.png) no-repeat left top;
						border-top-left-radius: 0px;
						border-top-right-radius: 0px;
						border-top: 0px white;
						margin-top: 0.04%;
						padding: 0.5%;
						background-color: #0088cc;
					}
					.guarddog {	
						position: relative;
						left: 29.3%;
						border-top-left-radius: 0px;
						border-top-right-radius: 0px;
						border-top: 0px white;
						margin-top: 0.04%;
						padding: 0.5%;
					}
					.changelog:hover {
						background: #208ddc;
					}
				</style>
				
				@if(Auth::user()->isUserTest())
				<a href="{{ URL::to('bug/reports') }}">
					<div class="btn btn-primary changelog">
						Bug Reports
					</div>
				</a>
				<a href="{{ URL::to('guarddog') }}">
					<div class="btn btn-warning guarddog">
						Guard Dog
					</div>
				</a>
				@endif
				
				
                </div>

                <table align="right" id="scheme_status" class="trafficlight">
                     <tr>
                        <td rowspan="3">
                            <a href="{{ URL::to('/') }}">
                                <img src="{{ asset('resources/img/traffic_light.png') }}" class="img-rounded" height="47" width="20">
                            </a>
                        </td>
                        <td><?php echo $red ?></td>
                    </tr>
                    <tr><td><?php echo $yellow ?></td></tr>
                    <tr><td><?php echo $green ?></td></tr>
                </table>

				@if (Auth::user()->schemes && Auth::user()->schemes()->count() > 1)
                    <div style="position: absolute; right: 0; margin-top: 65px;">
                        <a href="{{ URL::to('welcome-schemes') }}">List Schemes</a>
                    </div>
                @endif
				
				
                {{ $page }}

            </div>
        </div>
		
   </div>
	

	
</body>
</html>