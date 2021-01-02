
</div>

<div><br/></div>
<h1>Admin specialist tools</h1>

<div class="admin2">
<style>
	.specialist-title{
		border: 1px solid #868686;
		background: white;
		padding: 10px;
		border-radius: 5px;
	}
</style>

	@include('includes.notifications')
	
		<!-- Monitor management -->
		<div class="specialist-title"><a href="#"><b>Monitor</b></a></div>
		<br />
		<br />

		<ul>

		<li class=""><a href="{{ URL::to('temperature_control') }}">* Temperature Control Panel *</a></li>
		View & manage meters who are shut-off but receiving hot water & vice versa

		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/ping') }}">* Reboot / Ping SIMs *</a></li>
		Reboot any offline sims, & ping them to check their current status.
		
		<br/><br/>
		

		<li class="" ><a href="{{ URL::to('system_monitor') }}">System Monitor</a></li>
		Checks the system for any potential issues
		
		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('whos-online') }}">Who's online?</a></li>
		View who's using the PrepagoAdmin & their activity
		
		<br/><br/>
		
		<li class=""><a href="{{ URL::to('shut_offs') }}">Shut offs</a></li>
		View shut-offs from today / other days
		
		<br/><br/>
		
		<li class=""><a href="{{ URL::to('ious') }}">IOU's</a></li>
		View a list of customer's with IOU's
		
		<br/><br/>
		
		<li class=""><a href="{{ URL::to('away_modes') }}">Away modes</a></li>
		View current active away modes
		
		<br/><br/>
		
		</ul>
		
		
		<!-- Global management -->
		<div class="specialist-title"><a href="#"><b>Setup management</b></a></div>
		<br />
		<br />

		<ul>

		<li class=""><a href="{{ URL::to('settings/meter_setup') }}">* Insert</a></li>
		Quickly insert records of a list of meter #'s/scu #'s into permanent_meter_data & mbus_address_translations

		<br/><br/>
		
		<li class=""><a href="{{ URL::to('settings/simulator') }}">* Simulate scheme</a></li>
		Simulate/copy a specific scheme
		<br/><br/>
		
		<!--
		<li class="" ><a href="{{ URL::to('scheme-setup') }}">Scheme Setup</a></li>
		Setup a new scheme
		
		<br/><br/>
		-->

		<li class=""><a href="{{ URL::to('settings/utility_user_setup') }}">Operator/Installer Setup</a></li>
		Setup a new operator

		<br/><br/>
		

		</ul>
		
		<div class="specialist-title"><a href="#"><b>Backup management</b></a></div>
		<br />
		<br />
		
		
		<ul>

		<li class="" ><a href="{{ URL::to('backup/database') }}">Backup Database</a></li>
		Backup database / individual tables / customers
		
		<br/><br/>

		</ul>
		
		
		
		<!-- Remote management -->

		<div class="specialist-title"><a href="#"><b>Remote management</b></a></div>
		<br />
		<br />

		<ul>
		
		<li class="" ><a href="{{ URL::to('datalogger') }}">Remote Datalogger</a></li>
		Execute datalogger for all schemes & read all meters

		<br/><br/>


		</ul>
		
		<!-- Reports -->

		<div class="specialist-title"><a href="#"><b>Settings management</b></a></div>
		<br />
		<br />

		<ul>

		<li class="" ><a href="{{ URL::to('settings/system_settings') }}">* [All] System settings</a></li>
		Edit all settings, meter settings, email settings, etc

		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/sms_presets') }}">* SMS Preset settings </a></li>
		Manage SMS Presets for 'Report a Bug' section.
		
		
		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/autotopup') }}">* Auto topup settings </a></li>
		Manage auto topup settings
		
		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/paypal') }}"> Paypal settings </a></li>
		Manage paypal settings
		
		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/system_programs/billing_engine') }}"> Billing engine settings </a></li>
		Manage billing engine settings, modify kWh threshold, test/debugging
		
		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('settings/email_settings') }}">Email settings</a></li>
		Edit emailing settings

		<br/><br/>

		<li class=""><a href="{{ URL::to('settings/meter_lookup') }}">Meter lookup</a></li>
		Edit meter lookup dictionary


		</ul>
		
		
		<!-- Reports -->

		<div class="specialist-title"><a href="#"><b>Reports management</b></a> </div>
		<br />
		<br />

		<ul>
		
		<li class="" ><a href="{{ URL::to('system_reports/sim_reports') }}">SIM Reports</a></li>
		Reports of SIM usage within Prepago

		<br/><br/>
		
		<li class="" ><a href="{{ URL::to('system_reports/tracking_reports') }}">Tracking Reports</a></li>
		Reports of tracking within Prepago

		<br/><br/>

		<li class=""><a href="{{ URL::to('system_reports/payzone_payout_reports') }}">Payzone Pay-out</a></li>
		View Payzone payments report over 'x' months
		
		<br/><br/>
		
		<li class=""><a href="{{ URL::to('system_reports/paypal_payout_reports') }}">PayPal Pay-out</a></li>
		View PayPal payments report over 'x' months
		
		<br/><br/>

		<li class=""><a href="{{ URL::to('system_reports/paypoint_reports') }}">Paypoint Top-up</a></li>
		View paypoint top-ups
		
		<br/><br/>
			
		<li class=""><a href="{{ URL::to('system_reports/inconsistent_usage') }}">Inconsistent DHU</a></li>
		View customers with inconsistent district_heating_usage
		
		<br/><br/>
		
		
		<li class=""><a href="{{ URL::to('system_reports/missing_dhu') }}">Missing Standing Charge</a></li>
		View customers with missing standing_charge

		<br/><br/>
		
		<li class=""><a href="{{ URL::to('system_reports/missing_dhu') }}">Missing DHU</a></li>
		View customers with missing district_heating_usage

		<br/><br/>
		
		<li class=""><a href="{{ URL::to('system_reports/duplicate_dhm') }}">Duplicate DHM</a></li>
		View customers with duplicate district_heating_meters


		<br/><br/>

		</ul>
		
		<!-- System Management -->

		<div class="specialist-title"><a href="#"><b>Programs management</b></a></div>
		<br />
		<br />

		<ul>
		
		<li class="" ><a href="{{ URL::to('settings/system_programs/cronjobs') }}"> Cronjobs </a></li>
		Manage Laravel PHP Cronjobs
		
		<br/><br/>
		
		<li class=""><a href="{{ URL::to('system_programs/manage_schedule') }}">Manage Program Schedule</a></li>
		Manage Prepago program scheduling (Daily Records Engine, Billing Engine, Shut-Off Engine, Weather Program)
		
		<br/></br/>
		
		<li class="" ><a href="{{ URL::to('settings/system_programs/shut_off') }}">Holiday periods</a></li>
		Manage shut-off engine settings / shut-off Holiday periods

		<br/></br/>
		
		
		<li class="" ><a href="{{ URL::to('settings/system_programs/update') }}"> Update Prepago Services from Dropbox </a></li>
		Download & install latest ver. of Prepago Programs/Services from Dropbox - & reboot services
		
		<br/><br/>

		</ul>
		
			
		<!-- Customer Management -->

		<div class="specialist-title"><a href="#"><b>Customer management</b></a></div>
		<br />
		<br />

		<ul>
		
		<!--
		<li class="" ><a href="{{ URL::to('settings/customers/spread') }}"> Spread out charges </a></li>
		Spread out customers' charges over 'x' number of days in the event a scheme has been offline for a large time
		
		<br/><br/>
		-->

		</ul>
		
		

</div>