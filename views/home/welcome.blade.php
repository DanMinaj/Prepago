
                       </div>

<div><br/></div>

@if(isset($_GET['debug']))
	Scheme ID: {{$scheme_info->id}}
	<br/>
	Scheme Name/Nickname: {{$scheme_info->company_name}}/{{$scheme_info->scheme_nickname}}
	<br/>
	Scheme Prefix: {{$scheme_info->prefix}}
@endif

<table width="100%">
	<tr>
		<td>
			<h1>@if (!$fromSystemReports) Welcome To The Prepago Utility Dashboard @else List Of All Customers @endif
				<div style="float: right;margin-right: 3em;">
					<form method="post" action="https://www.prepago-admin.biz/search">
						<input type="submit" value="" style="height: 37px;width: 32px;float: right;background: url(/resources/images/search.png);"/>
						<input id="search_box" name="search_box" type="text" style="height: 35px;padding: 0 0 0 5px;">
					</form>
				</div>
			</h1>
		</td>
	</tr>
</table>

@if ($fromSystemReports)
    <h3><a href="{{ URL::to('create_csv/list_all_customers'); }}">Download CSV</a></h3>
@endif


<div class="row-fluid">
	<div class="span8">
			@if($last_topup) 
				<b>Last Topup: </b> &euro;{{$last_topup->amount}} at <u>{{$last_topup->time_date}}</u> by <a href="http://prepago-admin.biz/customer_tabview_controller/show/{{$last_topup->customer->id}}">({{$last_topup->customer->id}}) {{$last_topup->customer->first_name}} {{$last_topup->customer->surname}}</a> via {{$last_topup->topup_type}}
				@else
				<b>Last Topup:</b> None			
			@endif
	</div>
	@if(Auth::user()->isUserTest())
		<div style="margin-bottom: 1%;" class="span4">
			<button type="button"  style="padding: 6% 2% 5% 6%; font-size: 0.7rem; letter-spacing: 13px; box-shadow: 0px 0px 5px #000;" class="btn btn-mini btn-info" onclick="window.location.href='/dashboard'">
				ARREGLO
			</button>
		</div>
	@endif
</div>


@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if(Auth::user()->isUserTest() && BillingEngineFlag::pending()->count() > 0)
	<table width="100%" class="">
		<tr>
			<td>
				<div class="alert alert-warning alert-block">
					There are <b>{{ BillingEngineFlag::pending()->count() }}</b> billing engine flags that require your approval.
					<a href="{{ URL::to('billing/flags') }}">
						<button class="btn btn-success" style="float: right; display: inline-block; margin: 0px; padding: 0px 5px 0px 5px;" type="">
							Manage
						</button>
					</a>
				</div>
			</td>
		</tr>
	</table>
@endif

@if($scheme_info && !$scheme_info->tariff)
<table width="100%" class="">
	<tr>
		<td>
			<div class="alert alert-warning alert-block">
				This schemes' tariff is not set, would you like to set it?
				<a href="{{ URL::to('settings/tariff_setup/' . $scheme_info->scheme_number) }}">
					<button class="btn btn-success" style="float: right; display: inline-block; margin: 0px; padding: 0px 5px 0px 5px;" type="">
						Setup Tariff
					</button>
				</a>
			</div>
		</td>
	</tr>
</table>
@endif

	<?php
		$set = false;
		$set2 = false;
	?>
   <ul class="nav nav-pills">
	@foreach($categories as $k => $c)
		<li role="presentation" @if(!$set) class="active" @endif>
			<a href="#cat_{{ preg_replace('/\s+/', '', $k) }}_customers" data-toggle="tab">{{ ucfirst($k) }}</a>
		</li>
		<?php
			$set = true;
		?>
	@endforeach
	</ul>
	
	 <div class="tab-content">
		@foreach($categories as $k => $c)
		<div class="tab-pane @if(!$set2) active @endif" id="cat_{{ preg_replace('/\s+/', '', $k) }}_customers" style="">
			@include('includes.welcome_users', array('info'=> $c['red'], 'type' => 'red' ))
			@include('includes.welcome_users', array('info'=> $c['yellow'], 'type' => 'yellow' ))
			@include('includes.welcome_users', array('info'=> $c['green'], 'type' => 'green' ))
			@include('includes.welcome_users', array('info'=> $c['blue'], 'type' => 'blue' ))
		</div>
		<?php
			$set2 = true;
		?>
		@endforeach
	 </div>


<!-- Boiler Meters -->
@if ($boiler_meters->count())
    <table class="table table-bordered">
        <tr>
            <th>Meter Number</th>
            <th>Type</th>
            <th>Date Installed</th>
            <th>&nbsp;</th>
        </tr>
        @foreach ($boiler_meters as $boiler_meter)
            <tr style="background: #268FAF; color: #FFF">
                <td>{{{ $boiler_meter->meter_number }}}</td>
                <td>{{{ $boiler_meter->meter_type }}}</td>
                <td>{{{ $boiler_meter->install_date }}}</td>
                <td><a class="btn btn-info pull-right" type="button" href="<?php echo URL::to('customer_tabview_controller/show/meter/' . $boiler_meter->ID) ?>">View</a></td>
            </tr>
        @endforeach
    </table>
@endif

<div class="cl">&nbsp;</div>
</div>
</div>

<!-- Execute watchdog -->
<div id="execute-watchdog" class="modal fade" role="dialog">
  <div class="modal-dialog">
  
  <!-- Modal content-->
  <div class="modal-content">
	
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">Watchdog<div class='pull-right'>
	<button type="button" data-toggle="modal" data-target="#about-watchdog" id="refresh_services" class="btn btn-primary"><i class="fa fa-info"></i> About watchdog</button></div>
  </div></h4>
	
  <div class="modal-body">
		
		
  
  </div>
  
  <div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  </div>
  
</div>

</div>
</div>

<!-- About watchdog -->
<div id="about-watchdog" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">Watchdog<div class='pull-right'>
<!--<button type="button" id="refresh_services" class="btn btn-primary"><i class="fa fa-sync"></i> Refresh</button>-->
</div>
  </div></h4>
	
  <div class="modal-body">
	
  </div>
  <div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  </div>
</div>

</div>
</div>

</body>
</html>
