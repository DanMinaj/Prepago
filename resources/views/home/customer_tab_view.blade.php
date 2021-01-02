<?php
   function round_up ( $value, $precision ) { 
   	$pow = pow ( 10, $precision ); 
   	return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow; 
   } 
   											
   
   ?>
</div>
<div><br/></div>
<h1>Customer View
   @include('includes.search_form', array('searchURL'=> URL::to('search') ))
</h1>
<div class="admin">
   @include('includes.notifications')
   @if($data['shut_off_device_status'] == 'Yes')
	<div class="alert alert-danger alert-block">
		This customer is currently shut off.
		<div style="float:right">
		  &#5760; {!!  \Carbon\Carbon::parse($data['last_shut_off_time'])->diffForHumans() !!}
		</div>
	</div>  
   @endif
   
   <ul class="nav nav-tabs" style="margin: 30px 0">
      <li class="<?php echo $data['home'] ?>"><a href="#home" data-toggle="tab">Customer Details</a></li>
      <li><a href="#profile" data-toggle="tab">Meter Details</a></li>
      @if (!is_null($evMeter))
      <!--<li><a href="#ev-meter" data-toggle="tab">EV Meter Details</a></li>-->
      @endif
      <li class="<?php echo $data['message'] ?>"><a href="#usage_details" data-toggle="tab">Usage Details</a></li>
      @if(Auth::user()->username == "test")<!--<li><a href="#daily-charges" data-toggle="tab">Daily charges</a></li>-->@endif
      @if(1!=1)
      <li><a href="#new-usage-details" data-toggle="tab">Advanced Usage</a></li>
      @endif
      <li><a href="#arrears" data-toggle="tab">Arrears</a></li>
      <li><a href="#topups" data-toggle="tab">Top Up</a></li>
      <li><a href="#new-topups" data-toggle="tab">Top Ups</a></li>
      <li><a href="#send-message" data-toggle="tab">Send Message</a></li>
      <li><a href="#utility-notes" data-toggle="tab">Notes</a></li>
      <li><a href="#iou-usage" data-toggle="tab">IOU Usage</a></li>
      <li><a href="#diagnostics" data-toggle="tab">Diagnostics</a></li>
   </ul>
   @include('modals.support', ['data' => $data])
   <div class="tab-content">
      <div class="tab-pane <?php echo $data['home'] ?>" id="home" style="text-align: left">
		
		@if(Auth::user()->isUserTest())
		<ul class="nav nav-pills">
            <li class="active"><a sub-toggle="true" href="#cust-information" data-toggle="tab">General information</a></li>
			<li><a sub-toggle="true" href="#auto-topup" data-toggle="tab">Auto top-up</a></li>
        </ul> 
		@endif
		
		<div class="tab-content">
		
		<div id="cust-information" class="tab-pane active">
        <div class="custome_left">
            <dl class="dl-horizontal">
               <dt>Is EV Owner:</dt>
               <dd><input type="checkbox" name="ev_owner" @if($data['ev_owner'])checked="checked"@endif onclick="toggleEVOwner(this.checked)" /></dd>
            </dl>
			@if(Auth::user()->isUserTest())
				<dl class="dl-horizontal">
				  <dt>Auto top-up</dt>
				  <dd> 
					@if($data['subscription']) 
						<font style="color:green;font-weight:bold;">Subscribed</font> 
						<br/> Renewing {!! Carbon\Carbon::parse($data['subscription']->end_at)->diffForHumans() !!}
					@else
						<i>Inactivate</i>
					@endif
				  </dd>
			   </dl>
			@endif
            <dl id="rs_codes" class="dl-horizontal" @if(!$data['ev_owner'])style="display:none"@endif>
            <dt>&nbsp;</dt>
            <dd><a class="btn btn-link" type="button" style="padding: 0" data-toggle="modal" href="#rsCodesModal">View RS Codes</a></dd>
            </dl>
            <div id="rsCodesModal" class="modal hide fade">
               <div class="modal-header">
                  <h4 id="myModalLabel">RS Codes for scheme {!! strtoupper($scheme_name) !!}</h4>
               </div>
               <div class="modal-body">
                  @foreach($rs_codes as $rs_code)
                  <div style="margin-bottom: 10px">
                     <strong>{{ $rs_code }}</strong>
                  </div>
                  @endforeach
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
               </div>
            </div>
            <dl id="maximum_recharge_fee" class="dl-horizontal" @if(!$data['ev_owner'])style="display:none"@endif>
            <dt>Maximum Recharge Fee</dt>
            <dd>
               {{ $currency }}<span id="maximum_recharge_fee_val">{{ $data['maximum_recharge_fee'] }}</span>
               <a class="btn btn-link" type="button" data-toggle="modal" href="#rechargeFeeModal">Edit</a>
            </dd>
            </dl>
            <div id="rechargeFeeModal" class="modal hide fade">
               <form id="form50" action="{!! URL::to('customer_tabview_controller/edit_max_recharge_fee/'.$data['id']); !!}" method="POST" class="form-horizontal">
                  <div class="modal-header">
                     <h3 id="myModalLabel">Maximum Recharge Fee</h3>
                  </div>
                  <div class="modal-body">
                     <div class="form-group" role="form">
                        <div>
                           <strong>{{ $currency }}</strong>
                           <input class="input-small" name="maximum_recharge_fee" id="maximum_recharge_fee_input" value="{!! $data['maximum_recharge_fee'] !!}" />
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                     <button type="submit" class="btn-danger">Change</button>
                  </div>
               </form>
            </div>
            <dl class="dl-horizontal" >
               <dt >Barcode Number:</dt>
               <dd><a href="{!! $data->getBarcodeLink() !!}">{!! $data['barcode'] !!}</a></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Balance:</dt>
               <dd> {{ $currency }}{!! $data['balance'] !!}</dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Starting Balance: </dt>
               <dd> {{ $currency }}{!! $data['starting_balance'] !!}</dd>
            </dl>
            <dl class="dl-horizontal">
               <dt style='white-space: normal;'>Arrears:</dt>
               <dd> {{ $currency }}{!! $data['arrears'] !!}</dd>
            </dl>
            <dl class="dl-horizontal">
               <dt style='white-space: normal;'>Daily Arrears Repayment:</dt>
               <dd> {{ $currency }}{!! $data['arrears_daily_repayment'] !!}</dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Last Top Up Date</dt>
               <dd> <?php echo $data['last_top_up'] ?></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt >Commencement Date: </dt>
               <dd> 
                  <?php echo $data['commencement_date'] ?>
                  <a class="btn btn-link" type="button" data-toggle="modal" href="#myModalCommencementDate">Edit</a>
               </dd>
            </dl>
			<dl class="dl-horizontal">
				<?php
					$engagement = CustomerEngagement::where('customer_id', $data['id'])->orderBy('updated_at', 'DESC')->first();
				?>
               <dt >Last app usage: </dt>
               <dd> 
				  @if($engagement)
					 @if(strlen($engagement->updated_at) > 3)
					  @if($engagement->ip == $_SERVER['REMOTE_ADDR'])
						  <b> Logged in by you </b>
					  @else
						  {!! $engagement->ip !!}
					  @endif
					 <br/>
					 {!! $engagement->updated_at !!}<br/>({!! \Carbon\Carbon::parse($engagement->updated_at)->diffForHumans() !!}) 
					 @endif
				  @else
					n/a
				  @endif
			   </dd>
            </dl>
			@if($engagement) 
			<dl class="dl-horizontal">
               <dt >Last app platform </dt>
               <dd> 
			   {!! $engagement->platform !!}<br/>{!! $engagement->make !!}
			   </dd>
            </dl>
			@endif
            <div id="myModalCommencementDate" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Commencement Date</h3>
               </div>
               <div class="modal-body">
                  <form id="form21" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <input type="hidden" name="formid" value="21">
                     <div class="form-group" role="form">
                        <input type="hidden" name="commencement_date" id="commencement_date" />
                        <?php
                           $date = null;
                           $year = date('Y');
                           $month = date('m');
                           $day = date('d');
                           
                           if((Session::has('search_date')))
                           {
                           	//echo Session::get('search_date');
                           	//die();
                           	$date = Session::get('search_date');
                           }
                           
                           if($date != null)
                           {
                           	$parts = explode('-', $date);
                           	$year = $parts[2];
                           	$month = $parts[1];
                           	$day = $parts[0];
                           }
                           
                           $date = $year . '-' . $month . '-' . $day;
                           ?>
                        <label style="font-size: 14px">Current Commencement Date: <strong>{!! $data['commencement_date'] !!}</strong></label>
                        <br />
                        <label style="font-size: 14px">Set a new commencement date (should be in the future):</label>
                        <div id="datepicker3"></div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(21)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Days Active</dt>
               <dd> <?php echo $data['no_of_days_active'] ?></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Shut Off Status: </dt>
               <dd> <?php echo $data['shut_off'] ?></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Credit Warning Sent: </dt>
               <dd> <?php echo $data['credit_warning_sent'] ?></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>IOU Status: </dt>
			   @if($data['IOU_used'])
				   @if($data['balance'] < -$data['scheme']['IOU_amount'])
						<dd><b>Exceeded {!! $currency !!}{{ number_format($data['scheme']['IOU_amount'], 2) }}</b></dd>
				   @else
						
						@if($data['balance'] > 0.00)
						<dd>
						<font style="color:green;font-weight:bold;">Active</font>
						<br/>
						<b>{!! $currency !!}0.00 / {!! $currency !!}{{ number_format($data['scheme']['IOU_amount'], 2) }}</b> used</dd>
						@else
						<dd>
						<font style="color:green;font-weight:bold;">Active</font>
						<br/>
						<b>{!! $currency !!}{{ number_format(abs($data['balance']), 4)  }} / {!! $currency !!}{{ number_format($data['scheme']['IOU_amount'], 2) }}</b> used</dd>
						@endif
				   @endif
			   @else
				<dd> <?php echo $data['iou_statas']; ?></dd>
			   @endif
            </dl>
			 <dl class="dl-horizontal">
               <dt>IOU Last Used: </dt>
				<dd> 
					@if($data->iou)
						{!! $data->iou->time_date !!} <br/>({!! \Carbon\Carbon::parse($data->iou->time_date)->diffForHumans() !!})
					@else
						Never
					@endif
					<br/><a href="/customer_tabview_controller/force_iou/{!! $data->id !!}">Apply IOU</a>
				</dd>
            </dl>
			@if(Auth::user()->isUserTest())
			<dl class="dl-horizontal">
               <dt>Used today: </dt>
               <dd>{!! $currency !!}<?php echo$data['used_today'] ?></dd>
            </dl>
			<dl class="dl-horizontal">
               <dt>Used yesterday: </dt>
               <dd>{!! $currency !!}<?php echo$data['used_yesterday'] ?></dd>
            </dl>
			@endif
            <!--<dl class="dl-horizontal">
               <dt>IOU Extra Status:  </dt>
               <dd> <?php echo $data['iou_extra_statas'] ?></dd>
            </dl>-->
            <dl class="dl-horizontal">
               <dt>Admin IOU: </dt>
               <dd> <?php echo $data['admin_IOU_in_use'] ?></dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Away Mode: </dt>
               <dd> <?php echo $awayMode ? "ACTIVE" : "INACTIVE" ?></dd>
            </dl>
         </div>
        <div class="custome_left">
            <br/>
        </div>
        <div class="custome_left" style="position:float-left;">
            <dl class="dl-horizontal">
               <dt>Name:</dt>
               <dd>
                  <?php echo $data['first_name'] . ' ' . $data['surname'] ?>
                  <a class="btn btn-link" type="button" data-toggle="modal" href="#myModalName">Edit</a>
               </dd>
            </dl>
            <div id="myModalName" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Name</h3>
               </div>
               <div class="modal-body">
                  <form id="form20" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <input type="hidden" name="formid" value="20">
                     <div class="form-group" role="form">
                        <label for="first_name" class="control-label" style="margin-right: 10px">First name: </label>
                        <input type="text" name="first_name" id="first_name" value="<?php echo $data['first_name']?>" required/>
                     </div>
                     <div class="form-group" role="form" style="clear:both">
                        <label for="surname" class="control-label" style="margin-right: 10px">Surname: </label>
                        <input type="text" name="surname" id="surname" value="<?php echo $data['surname']?>" required/>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(20)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt style='white-space: normal;'>Account Number/Username: </dt>
               <dd> <?php echo $data['username'] ?>
                  <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal2">Edit</a>
               </dd>
            </dl>
            <div id="myModal2" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Account Number/Username</h3>
               </div>
               <div class="modal-body">
                  <form id="form2" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Account Number/Username: </label>
                        <div>
                           <input type="hidden" name="formid" value="2">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['username']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(2)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Email: </dt>
               <dd> <?php echo $data['email_address'] ?>
                  <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal8">Edit</a>
               </dd>
            </dl>
            <div id="myModal8" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Email</h3>
               </div>
               <div class="modal-body">
                  <form id="form8" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Email: </label>
                        <div>
                           <input type="hidden" name="formid" value="8">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['email_address']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(8)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Password: </dt>
               <dd>
                  @if(strlen($data['password']) < 5) <font color='green'>Customer's password is already blank/reset</font> @endif
                  <a href="{!! URL::to('customer_tabview_controller/password-reset/' . $data['id']) !!}" class="btn btn-link" type="button">Reset</a>
               </dd>
            </dl>
            <dl class="dl-horizontal">
               <dt>Mobile Number: </dt>
               <dd> <?php echo $data['mobile_number'] ?>
                  <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal9">Edit</a>
               </dd>
            </dl>
            <div id="myModal9" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Mobile Number</h3>
               </div>
               <div class="modal-body">
                  <form id="form9" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Mobile Number: </label>
                        <div>
                           <input type="hidden" name="formid" value="9">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['mobile_number']?></textarea>
                           <br />
                          <!-- <p>Number should be with +353</p>-->
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(9)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Nominated Number: </dt>
               <dd> <?php echo $data['nominated_telephone'] ?>
                  <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal10">Edit</a>
               </dd>
            </dl>
            <div id="myModal10" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Nominated Number</h3>
               </div>
               <div class="modal-body">
                  <form id="form10" action="<?php echo URL::to('customer_tabview_controller/edit_nominated_phone_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Nominated Number: </label>
                        <div>
                           <input type="hidden" name="formid" value="10">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['nominated_telephone']?></textarea>
                           <br />
                           <p>Number should be with +353</p>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(10)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>House Number/Name: </dt>
               <dd>
                  <?php echo $data['house_number_name'] ?>
                   <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal3">Edit</a> 
               </dd>
            </dl>
            <div id="myModal3" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">House Number/Name</h3>
               </div>
               <div class="modal-body">
                  <form id="form3" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">House Number/Name: </label>
                        <div>
                           <input type="hidden" name="formid" value="3">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['house_number_name']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(3)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Address Line 1: </dt>
               <dd>
                  <?php echo $data['street1'] ?>
                 <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal4">Edit</a>
               </dd>
            </dl>
            <div id="myModal4" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Address Line 1</h3>
               </div>
               <div class="modal-body">
                  <form id="form4" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Address Line 1: </label>
                        <div>
                           <input type="hidden" name="formid" value="4">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['street1']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(4)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Address Line 2: </dt>
               <dd>
                  <?php echo $data['street2'] ?>
					<a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal5">Edit</a>
               </dd>
            </dl>
            <div id="myModal5" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Address Line 2</h3>
               </div>
               <div class="modal-body">
                  <form id="form5" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Address Line 2: </label>
                        <div>
                           <input type="hidden" name="formid" value="5">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['street2']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(5)">Change</a>
               </div>
            </div>
			<dl class="dl-horizontal">
               <dt>Town: </dt>
               <dd> 
					<?php echo $data['town'] ?>
					<a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal13">Edit</a> 
			   </dd>
            </dl>
			 <div id="myModal13" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Town</h3>
               </div>
               <div class="modal-body">
                  <form id="form6" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Town: </label>
                        <div>
                           <input type="hidden" name="formid" value="11">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['town']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(6)">Change</a>
               </div>
            </div>
            
            <dl class="dl-horizontal">
               <dt>Postcode: </dt>
               <dd> 
					<?php echo $data['postcode'] ?>
					<a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal12">Edit</a> 
			   </dd>
            </dl>
			 <div id="myModal12" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Postcode</h3>
               </div>
               <div class="modal-body">
                  <form id="form12" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Postcode: </label>
                        <div>
                           <input type="hidden" name="formid" value="10">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['postcode']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(12)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>County</dt>
               <dd>
                  <?php echo $data['county'] ?>
                  <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal6">Edit</a> 
               </dd>
            </dl>
            <div id="myModal6" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">County</h3>
               </div>
               <div class="modal-body">
                  <form id="form6" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">County: </label>
                        <div>
                           <input type="hidden" name="formid" value="6">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['county']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(6)">Change</a>
               </div>
            </div>
            <dl class="dl-horizontal">
               <dt>Country:</dt>
               <dd>
                  <?php echo $data['country'] ?>
                   <a  class="btn btn-link" type="button" data-toggle="modal" href="#myModal7">Edit</a> 
               </dd>
            </dl>
            <div id="myModal7" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Country</h3>
               </div>
               <div class="modal-body">
                  <form id="form7" action="<?php echo URL::to('customer_tabview_controller/edit_common_action/'.$data['id']); ?>" method="POST" class="form-horizontal">
                     <div class="form-group" role="form">
                        <label for="t_area" class="control-label">Country: </label>
                        <div>
                           <input type="hidden" name="formid" value="7">
                           <textarea class="field span5" rows="5" name="t_area" id="t_area"><?php echo $data['country']?></textarea>
                        </div>
                     </div>
                  </form>
                  <div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div>
               </div>
               <div class="modal-footer">
                  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                  <a href="#"  class="btn btn-danger"   onclick="issue(7)">Change</a>
               </div>
            </div>
			  <dl class="dl-horizontal">
               <dt>Full address:</dt>
               <dd>
                  <?php echo nl2br($data['address_formatted']) ?>
               </dd>
            </dl>
		 </div>
		</div>
		
		<div id="auto-topup" class="tab-pane">
			<div class="row-fluid">
				<div class="span6">
					<table width="100%">
						<tr>
							<td width="20%"> 
								<b style="font-size:0.9rem;">Status:</b> 
							</td> 
							<td width="80%">
								@if($data->subscription)
									<font style="color:green;font-size:0.9rem;"><b>Subscribed</b></font>
								@else
									<font style="color:red;font-size:0.9rem;">Inactive</font>
								@endif
							</td>
						</tr>
						@if(!$data->subscription)
						<tr>
							<td colspan="2">
								<br/>
								<form method="POST" action="/customer_tabview_controller/start_at/{!! $data->id !!}">
									<button type="submit" class="btn btn-success">Start</button>
								</form>
							</td>
						</tr>	
						@endif
						@if($data->subscription)
						<tr>
							<td width="40%"> 
								<b style="font-size:0.9rem;">Active since:</b> 
							</td> 
							<td width="60%">
								<font style="font-size:0.9rem;">{!! $data->subscription->start !!}</font>
							</td>
						</tr>
						<tr>
							<td width="40%"> 
								<b style="font-size:0.9rem;">Next renewal:</b> 
							</td> 
							<td width="60%">
								<font style="font-size:0.9rem;">{!! $data->subscription->end !!}</font>
							</td>
						</tr>
						<tr>
							<td width="40%"> 
								<b style="font-size:0.9rem;">Topup by:</b> 
							</td> 
							<td width="60%">
								<font style="font-size:0.9rem;">&euro;{!! number_format($data->subscription->topup_amount, 2) !!}</font>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2">
								<br/><br/>
								<font style="font-size:0.9rem;">
									<form method="POST" action="/customer_tabview_controller/stop_at/{!! $data->id !!}">
										<input type="text" name="reason" placeholder="Cancellation Reason"><br/>
										<button type="submit" class="btn btn-danger">Cancel</button>
									</form>
								</font>
							</td>
						</tr>
					
						@endif
						<tr>
							<td colspan="2">
								<br/>
								<hr/>
							</td>
						</tr>
						<tr>
							<td width="40%"> 
								<b style="font-size:0.9rem;">Autotopups:</b> 
							</td> 
							<td width="60%">
								<font style="font-size:0.9rem;">{!! count($data->subscriptionPayments) !!}</font>
							</td>
						</tr>
						
					</table>
				</div>
				<div class="span6">
					<table width="100%" class="table table-bordered">
						<thead>
							<tr>
								<th>Stripe Transaction ID</th>
								<th>Amount</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
							<font style="font-size:1.2rem">Subscription History</font>
							<hr/>
							@foreach($data->subscriptionTransactions as $k => $st)
								<tr>
									<td>{!! $st->token !!}</td>
									<td>&euro;{!! $st->amount !!}</td>
									<td>{!! $st->created_at !!}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		</div>
		
	  </div>
      <div class="tab-pane" id="profile">
         <ul class="nav nav-pills">
            <li class="active"><a sub-toggle="true" href="#meter-information" data-toggle="tab">General information</a></li>
			@if (\Auth::user()->isUserTest())
			<li><a sub-toggle="true" href="#meter-replace" data-toggle="tab">Replace Meter</a></li>
            <li><a sub-toggle="true" href="#rcs" data-toggle="tab">Remote Commands Sent</a></li>
            <li><a sub-toggle="true" href="#meter-edit" data-toggle="tab">Edit Meter Information</a></li>
            <!--<li><a href="/meter_stats/{!! $data->id !!}">Advanced Meter Stats</a></li>-->	
            @endif
            <!--<li><a href="#">Menu 1</a></li>
               <li><a href="#">Menu 2</a></li>
               <li><a href="#">Menu 3</a></li>-->
         </ul>
         <div class="tab-content">
		 
            <div id="meter-information" class="tab-pane active">
               <dl class="dl-horizontal">
                  <dt>Customer ID:</dt>
                  <dd class='customer_id'> <?php echo $data['customer_id'] ?></dd>
               </dl>
			     <dl class="dl-horizontal">
                  <dt>Username:</dt>
                  <dd class='customer_id'> <?php echo $data['username'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Meter ID Number:</dt>
                  <dd> <?php echo $data['meter_number'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Previous Meter Reading:</dt>
                  <dd> 
					{!! $data['latest_reading'] !!}
				  </dd>
               </dl>
			   <dl class="dl-horizontal">
					<dt>Latest Reading: </dt>
					<dd>
						{!! $data['sudo_reading'] !!} &horbar; ({!! $data['sudo_reading_time'] !!})
						@if($data['sudo_reading'] > $data['latest_reading']) 
							&horbar; {!! ($data['sudo_reading'] - $data['latest_reading']) !!}kWh pending bill
						@endif
					</dd>
			   </dl>
               <dl class="dl-horizontal">
                  <dt>Last Temperature:</dt>
                  <dd> <?php echo $data['last_flow_temp'] ?>&deg;C ({!! $data['last_temp_time'] !!})</dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Last Valve Status:</dt>
                  <dd> <?php echo empty($data['last_valve_status']) ? "Please run 'Check Valve' in diagnostics" : ($data['last_valve_status']) ?> ({!! $data['last_valve_status_time'] !!})</dd>
               </dl>
               @if($data->permanentMeter)
               <dl class="dl-horizontal">
                  <dt>Away Mode Status:</dt>
                  <dd>
				  
				      @if($data->permanentMeter)
							
						@if($data->permanentMeter->awayMode)
							<b>On</b> <a href="{!! URL::to('customer_tabview_controller/clear_away_mode', ['customer_id' => $data->id]) !!}">(Force stop)</a>
						@else
							Off <a href="{!! URL::to('customer_tabview_controller/activate_away_mode', ['customer_id' => $data->id]) !!}">(Force activate)</a>
						@endif
						
						@if($data->rcs)
							@if($data->rcs->last_start)
							(Last used: {!! $data->rcs->last_start->date_time !!})
							@endif
						@endif
						
					  @endif

                  </dd>
               </dl>
               @endif
			    <dl class="dl-horizontal">
                  <dt>Scheduled to Shut off:</dt>
                  <dd> @if($data['scheduled_to_shut_off']) Yes @else No @endif  </dd>
               </dl>
			    <dl class="dl-horizontal">
                  <dt>Shut off device status:</dt>
                  <dd> {!! $data['shut_off_device_status']  !!} </dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Customer marked Shut off:</dt>
                  <dd> @if($data->shut_off) Yes @else No @endif </dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Last Shut off Time:</dt>
                  <dd> <?php echo $data['last_shut_off_time'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Last Shut off Reading: </dt>
                  <dd> <?php echo $data['shut_off_reading'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>MBus:</dt>
                  <dd> <?php echo $data['scu_number'] ?> | <?php echo $data['scu_number_sixteen'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Meter:</dt>
                  <dd> <?php echo $data['meter_number'] ?> | <?php echo $data['meter_number_sixteen'] ?></dd>
               </dl>
               @if(Auth::user()->isUserTest())
               @if($data['districtMeter'])
               <dl class="dl-horizontal">
                  <dt style='white-space: normal;'>Invalid CRA:</dt>
                  <dd>{!! $data['districtMeter']->invalid_reading_attempts !!}</dd>
               </dl>
			   @endif
               <dl class="dl-horizontal">
                  <dt>Permanant meter ID:</dt>
                  <dd class='pmd_id'> <?php echo $data['pmd_id'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Districting meter ID:</dt>
                  <dd> <?php echo $data['d_id'] ?> (Usage entries: <?php echo $data['d_usage']; ?>)</dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Scheme/Datalogger IP:</dt>
                  <input type="hidden" id="scheme_ip" name="scheme_ip" value="{!! $data['scheme_ip'] !!}">
                  <dd><?php echo $data['scheme_ip'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Scheme ID:</dt>
                  <dd> <?php echo $data['scheme_number'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>Scheme Name:</dt>
                  <dd> <?php echo $data['company_name'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>[K] Tariff 1:</dt>
                  <dd> <?php echo $data['tariff_1'] ?></dd>
               </dl>
               <dl class="dl-horizontal">
                  <dt>[S] Tariff 2:</dt>
                  <dd> <?php echo $data['tariff_2'] ?></dd>
               </dl>
               @endif
               <input type="hidden" id="user_id" value="<?php echo $data['id']; ?>">
               <input type="hidden" id="base" value="<?php echo URL::to('/'); ?>">
               <!-- <a href="#resendLastModal" class="btn btn-info" data-toggle="modal">Resend last meter command</a> -->
               <div id="resendLastModal" class="modal hide fade" >
                  <div class="modal-header">
                     <h3 id="myModalLabel">Resend last meter command.</h3>
                  </div>
                  <div class="modal-body">
                     <form class="form-horizontal">
                        <div class="form-group" role="form">
                           <p>Are you sure you want to resend last meter command?</p>
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer">
                     <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                     <a href="#" class="btn btn-danger" onclick="resendLastCommand()">Yes</a>
                  </div>
               </div>
            </div>
			
            <div id="rcs" class="tab-pane">
			
			@if($lastCommand)
				<table width="100%">
					<tr>
						<td width="12%"><b>Last Command Sent:</b></td>
						<td style='text-align:left;' width="89%">
							@if($lastCommand->turn_service_on) 
								<b><span style='color:#62c462;'>On</span></b>
							@elseif($lastCommand->turn_service_off) 
								<b><span style='color:#ff5c5c;'>Off</span></b>
							@endif
							@ {!! $lastCommand->time_date !!} :  {!! $lastCommand->initiated !!}
						</td>
					</tr>
				</table>
				<br/>
			@endif
			
               <!-- Remote On Commands Table -->
               <table width="100%">
                  <tr>
                     <td style="vertical-align:top;" width="50%">
                        <table id="t1" class="table table-bordered">
                           <thead>
                              <tr>
                                 <th>
                                    Sent
                                 </th>
                                 <th>
                                    Complete
                                 </th>
                                 <th>
                                    Failed
                                 </th>
                                 <th>
                                    Restart
                                 </th>
                                 <th>
                                    Table
                                 </th>
                                 <th>
                                    Initiaited
                                 </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td colspan="6">
                                    <center>
                                       <h4> Remote-<font color='#33c932'>On</font> Commands </h4>
                                    </center>
                                 </td>
                              </tr>
                              @foreach($onCommands as $o)
                              <tr style='{!! $o->style !!}'>
                                 <td width="25%">
                                    {!! $o->time_date !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->complete) ? "Yes <i style='color:#33c932' class='fa fa-check-circle'></i>" : "No <i style='color:red' class='fa fa-times-circle'></i>" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->failed) ? "Yes <i style='color:red' class='fa fa-times-circle'></i>" : "No <i style='color:#33c932' class='fa fa-check-circle'></i>" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->restart_service) ? "Yes" : "No" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->rtu_command_que) ? 'rcq' : 'rcqw' !!}
                                 </td>
                                 <td width="5%">
                                    {!! $o->initiated !!}
                                 </td>
                              </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </td>
                     <td width="50%" style="vertical-align:top;">
                        <table id="t2" class="table table-bordered">
                           <thead>
                              <tr>
                                 <th>
                                    Sent
                                 </th>
                                 <th>
                                    Complete
                                 </th>
                                 <th>
                                    Failed
                                 </th>
                                 <th>
                                    Restart
                                 </th>
                                 <th>
                                    Table
                                 </th>
                                 <th>
                                    Initiated
                                 </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td colspan="6">
                                    <center>
                                       <h4> Remote-<font color='red'>Off</font> Commands </h4>
                                    </center>
                                 </td>
                              </tr>
                              @foreach($offCommands as $o)
                              <tr style='{!! $o->style !!}'>
                                 <td width="25%">
                                    {!! $o->time_date !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->complete) ? "Yes <i style='color:#33c932' class='fa fa-check-circle'></i>" : "No <i style='color:red' class='fa fa-times-circle'></i>" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->failed) ? "Yes <i style='color:red' class='fa fa-times-circle'></i>" : "No <i style='color:#33c932' class='fa fa-check-circle'></i>" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->restart_service) ? "Yes" : "No" !!}
                                 </td>
                                 <td width="5%">
                                    {!! ($o->rtu_command_que) ? 'rcq' : 'rcqw' !!}
                                 </td>
                                 <td width="5%">
                                    {!! $o->initiated !!}
                                 </td>
                              </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </table>
               <hr/>
            </div>
			
			<div id="meter-replace" class="tab-pane">
			
			<div style="display:none;padding-bottom: 33px;" class="mini_warning alert alert-warning alert-block">
				<div class="mini_warning_msg"></div>
			</div>

			<div style="display:none;padding-bottom: 33px;" class="mini_success alert alert-success alert-block">
				<div class="mini_success_msg"></div>
			</div>

			<div style="display:none;padding-bottom: 33px;" class="mini_error alert alert-danger alert-block">
				<div class="mini_error_msg"></div>
			</div>


               <br/>
               <form action="{!! URL::to('customer_tabview_controller/replace_meter', ['customer_id' => $data['id'] ]) !!}" method="POST">
				<button type="submit" class="btn btn-primary">Replace meter</button><br/><br/>
                  <table width="100%">
						<tr>
						
						<script>
						
							function useBtn(el) {
								var val = $(el).attr('digit');
								$('#secondary_meter_num').val($('#primary_meter_num').val() + val);
							}
							
							function showMsg(msg, type) {
								switch(type) {
									case "success":
										$('.mini_error_msg').html('');
										$('.mini_error').hide();
										$('.mini_warning_msg').html('');
										$('.mini_warning').hide();
										$('.mini_success_msg').html(msg);
										$('.mini_success').fadeIn();
									break;
									case "warning":
										$('.mini_success_msg').html('');
										$('.mini_success').hide();
										$('.mini_error_msg').html('');
										$('.mini_error').hide();
										$('.mini_warning_msg').html(msg);
										$('.mini_warning').fadeIn();
									break;
									case "error": 
										$('.mini_success_msg').html('');
										$('.mini_success').hide();
										$('.mini_warning_msg').html('');
										$('.mini_warning').hide();
										$('.mini_error_msg').html(msg);
										$('.mini_error').fadeIn();
									break;
								}
							}
								
							$(function(){
								
								function pollReplacement() {
									
									var customer_id = $('input[name=customer_id]').val();
									var primary = $('#primary_meter_num').val();
									
									$.ajax({
										url: "/customer_tabview_controller/poll_replace_meter/" + customer_id,
										data: {primary: primary},
										type: "POST",
										success: function(data) {
											
											if(data.errorMessage != null) {
												$('.mini_success_msg').html('');
												$('.mini_success').hide();
												$('.mini_error_msg').html(data.errorMessage);
												$('.mini_error').fadeIn();
												return;
											}

											$('.mini_error_msg').html('');
											$('.mini_error').hide();
											$('.mini_success_msg').html(data.successMessage);
											$('.mini_success').fadeIn();
											
										},
										error: function(){
											$('.mini_success_msg').html('');
											$('.mini_success').hide();
											$('.mini_error_msg').html(data.errorMessage);
											$('.mini_error').fadeIn();
										}
									});
								}
								
								function testNewMeter() {
									
									var customer_id = $('input[name=customer_id]').val();
									var primary = $('#primary_meter_num').val();
									var secondary = $('#secondary_meter_num').val();
									showMsg("Reading meter (" + secondary + ")..please wait..", 'warning');
									
									$.ajax({
										url: "/customer_tabview_controller/test_replace_meter/" + customer_id,
										data: {primary: primary, secondary: secondary},
										type: "POST",
										success: function(data) {
											
											if(data.errorMessage != null) {
												showMsg(data.errorMessage, 'error');
												return;
											}
											
											showMsg(data.successMessage, 'success');
											
										},
										error: function(){
											showMsg(data.errorMessage, 'error');
										}
									});
								}
								
								$('#primary_meter_num').on('keyup', function(){
									var val = $(this).val();
									if(val.length >= 8) {
										$('select[name=meter_types]').prop('disabled', false);
										pollReplacement();
									} else {
										$('select[name=meter_types]').prop('disabled', true);
									}
								});
								
								$('#btn_test_new_meter').on('click', function(){
									testNewMeter();
								});
								
								$('select[name=meter_types]').on('change', function(){							
									var val = $(this).val();
									if(val == '-') return;
									
									var primary_input = $('#primary_meter_num');
									var secondary_meter_num = $('#secondary_meter_num');
									
									if(primary_input.val() < 8) return;
									
									secondary_meter_num.val(primary_input.val() + "" + val);
								});
								
							});
						</script>
							<!-- left -->
							<td width="50%" style="vertical-align:top">
							<table width="100%">
									
									<tr>
										<td>
											<select disabled name="meter_types" style="width:50%">
												<option value="-">- Manually select meter make -</option>
												@foreach($meter_types as $k => $m)
													<option value="{!! $m->last_eight !!}">{!! $m->meter_make . ' ' . $m->meter_model !!}</option>
												@endforeach
											</select>
										</td>
									</tr>
									<tr>
										<td><b><h3>New replacement meter</h3></b></td>
									</tr>
									<tr>
										<td><b><h4>* primary address</h4></b></td>
									</tr>
									<tr>
										<td><input type="text" maxlength="8" name="primary_meter_num" id="primary_meter_num" placeholder="Primary Meter Number"  value=""></td>
									</tr>
									<tr>
										<td><b><h4>* secondary address</h4></b></td>
									</tr>
									<tr>
										<td><input type="text"  maxlength="16" name="secondary_meter_num" id="secondary_meter_num" placeholder="Secondary Meter Number"  value=""></td>
									</tr>
									<tr>
										<td><b><h4>Test replacement meter</h4></b></td>
									</tr>
									<tr>
										<td><button type="button" class="btn btn-success" id="btn_test_new_meter">Read meter</button></td>
									</tr>
							</table>
							</td>
							<!-- right -->
							<td width="50%" style="vertical-align:top" >
							<table width="100%">
									<tr>
										<td><b><h3>Current meter</h3></b></td>
									</tr>
									@if($meter_type != null)
									<tr>
										<td><b>{!! $meter_type->meter_make !!}<br/>{!! $meter_type->meter_model !!}</b></td>
									</tr>
									@endif
									<tr>
										<td><b><h4>* primary address</h4></b></td>
									</tr>
									<tr>
										<td><input type="text" name="cur_meter_number" placeholder="Current Meter Number" disabled value="{!! (strpos($data['d_meter_number'], '_') == false) ? 'none found' : explode('_', $data['d_meter_number'])[1] !!}"></td>
									</tr>
									<tr>
										<td><b><h4>* secondary address</h4></b></td>
									</tr>
									<tr>
										<td><input type="text" name="cur_meter_number" placeholder="Current Meter Number" disabled value="{!! $data['meter_number_sixteen'] !!}"></td>
									</tr>
							</table>
							</td>
						</tr>
                  </table>
               </form>
            </div>
			
			
			
            <div id="meter-edit" class="tab-pane">
               <br/>
			   	<div class="alert alert-info"> 
				<b>Note: </b> Please always ensure that the 'meter_number' values for both '<b>permanent_meter_data</b>' and '<b>district_heating_meters</b>' are equal.
				</div>
				<script type="text/javascript">
					function check(){
						if($('#p_meter_number').val() !== $('#meter_number').val()) {
							if(!confirm("Are you sure you'd like to save changes? Permanent_meter_data & district_heating_meters must have the same 'meter_number' value!"))
								return false;
							
						}
					}
				</script>
               <form onsubmit="check()" action="{!! URL::to('customer_tabview_controller/save_meter_info', ['customer_id' => $data['id'] ]) !!}" method="POST">
                  <table width="100%" >
                     <tr>
                        <td style="vertical-align:top" width="100%">
                           <table class="table table-striped" onsubmit="check" width="100%">
                              <tr>
                                 <td><b style='font-size:3em;color: #0c68c0;'>district_heating_meters</b></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>meter_ID</b></td>
                                 <td width="80%"><input disabled="true" type="text" name="d_meter_ID" placeholder="DHM ID" value="{!! $data['d_id'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>meter_number</b></td>
                                 <td width="80%"><input type="text" id="meter_number" name="meter_number" placeholder="Meter_number from district_heating_meters e.g nep_12345678" value="{!! $data['d_meter_number'] !!}"></td>
                              </tr>
                              <tr>
                                 <td><b style='font-size:3em;color: #0c68c0;'>permanent_meter_data</b></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>permanent_meter_ID</b></td>
                                 <td width="80%">
                                    <input type="hidden" name="pmd_ID" value="{!! $data['permanent_meter']['ID'] !!}">
                                    <input disabled="true" type="text" name="pmd_ID2" placeholder="PMD ID" value="{!! $data['permanent_meter']['ID'] !!}">
                                 </td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>meter_number</b></td>
                                 <td width="80%"><input type="text" id="p_meter_number" name="p_meter_number" placeholder="Meter_number from permanent_meter_data e.g nep_12345678" value="{!! $data['permanent_meter']['meter_number'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>meter_number2</b></td>
                                 <td width="80%"><input type="text" name="p_meter_number2" placeholder="Meter_number2 from permanent_meter_data e.g nep_12345678" value="{!! $data['permanent_meter']['meter_number2'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>scu_number</b></td>
                                 <td width="80%"><input type="text" name="p_scu_number" placeholder="scu_number from permanent_meter_data e.g 02001234" value="{!! $data['permanent_meter']['scu_number'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>m_bus_relay_id</b></td>
                                 <td width="80%"><input type="text" name="p_m_bus_relay_id" placeholder="Meter_number from permanent_meter_data e.g 02001234" value="{!! $data['permanent_meter']['m_bus_relay_id'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>data_logger_id</b></td>
                                 <td width="80%"><input type="text" name="data_logger_id" placeholder="Data_logger_id from permanent_meter_data e.g 21" value="{!! $data['permanent_meter']['data_logger_id'] !!}"></td>
                              </tr>
							  <tr>
                                 <td style="vertical-align:middle" width="20%"><b>readings_per_day</b></td>
                                 <td width="80%"><input type="text" name="readings_per_day" placeholder="Readings_per_day from permanent_meter_data e.g 12" value="{!! $data['permanent_meter']['readings_per_day'] !!}"></td>
                              </tr>
                              <tr>
                                 <td><b style='font-size:3em;color: #0c68c0;'>customers</b></td>
                                 </td>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>meter_ID</b></td>
                                 <td width="80%"><input type="text" name="c_meter_ID" placeholder="Customer table meter_ID column" value="{!! $data['meter_ID'] !!}"></td>
                              </tr>
                              <tr>
                                 <td><b style='font-size:3em;color: #0c68c0;'>m_address (meter)</b></td>
                                 </td>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>8 digit</b></td>
                                 <td width="80%"><input type="text" maxlength="8" disabled name="mbus_address_translations_meter_8" placeholder="8digit (meter)" value="{!! $data['meter_number'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>16 digit</b></td>
                                 <td width="80%"><input type="text" maxlength="16" disabled name="mbus_address_translations_meter_16" placeholder="16digit (meter)" value="{!! $data['meter_number_sixteen'] !!}"></td>
                              </tr>
                              <tr>
                                 <td><b style='font-size:3em;color: #0c68c0;'>m_address (scu)</b></td>
                                 </td>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>8 digit</b></td>
                                 <td width="80%"><input type="text" maxlength="8" disabled name="mbus_address_translations_scu_8" placeholder="8digit (scu)" value="{!! $data['scu_number'] !!}"></td>
                              </tr>
                              <tr>
                                 <td style="vertical-align:middle" width="20%"><b>16 digit</b></td>
                                 <td width="80%"><input type="text" maxlength="16" disabled name="mbus_address_translations_scu_16" placeholder="16digit (scu)" value="{!! $data['scu_number_sixteen'] !!}"></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                  </table>
                  <hr>
                  <button type="submit" class="btn btn-primary">Save changes</button>
               </form>
            </div>
			
			
		</div>
      </div>
      <!--
         <div class="tab-pane" id="daily-charges">
          
          
          <div >
         <form class="form-inline" action="<?php echo URL::to('customer_tabview_controller/daily_charges_search/'.$data['id']) ?>#daily-charges" method="post">
         	<input type="text" name="on" class="input-small" placeholder="On" id="datepicker1">
         	<button type="submit" class="btn" >Submit</button>
           </form>
              </div>
         
           <table>
                                       <tr><td><dl class="dl-horizontal">
                                           <dt>Date</dt>
                                           <dd>{!! str_replace('_', '-', $date) !!}</dd>
                                       </dl></td></tr>
         						
         						 <tr><td><dl class="dl-horizontal">
                                           <dt>Total taken from customer:</dt>
                                           <dd id="grand_total_billed_space">0</dd>
                                       </dl></td></tr>
         						
         						<tr><td><dl class="dl-horizontal">
                                           <dt>Total kWh billed:</dt>
                                           <dd id="grand_total_billed_normal_space">0</dd>
                                       </dl></td></tr>
         						
         						<tr><td><dl class="dl-horizontal">
                                           <dt>Total SMS billed:</dt>
                                           <dd id="grand_total_billed_sms_space">0</dd>
                                       </dl></td></tr>
         						
         						<tr><td><dl class="dl-horizontal">
                                           <dt>Total kWh:</dt>
                                           <dd id="grand_total_usage_space">0</dd>
                                       </dl></td></tr>
         						
         
         
         
              </table>
           
          <h4> Charges applied to Customer <?php echo $data['id']; ?> on <?php echo date('F jS Y', strtotime("$day.$month.$year")); ?></h4>
         
          
           <div class="" style="margin-top: 20px;">
                                   <table id="sortthistable" class="table table-bordered">
                                   <thead>
                                       <th>#</th>
                                       <th>Usage</th>
                                       <th>Billed</th>
                                       <th>Old Balance</th>
                                       <th>New Balance</th>                            
                                       </thead>
                                   <tbody>
         
                                       <?php
            $total_usage = 0;
            $total_billed = 0;
            $main_key = 0;
            
                                    if ($data['array'] == "")
                                        echo "There are no data to show";
                                    else
                                        foreach ($entries as $key=>$entry):
            		
            		if($entry->type == 'sms')
            			continue;
            		
            		if($entry->type == 'residual_prev_day')
            		{
            			$entry->old_balance = 0;
            			$entry->new_balance = 0;
            			echo $entry->amount;
            		}
            		
            		if($entry->type == 'standing_charge')
            		{
            			$entry->kwh = 'Standing charge';
            			$entry->old_balance = 0;
            			$entry->new_balance = 0;
            		}
            		
            		$total_usage += $entry->kwh;
            		$total_billed += $entry->amount;
            		?>
         								<tr>
         								
         									<td style="width: 70px;"><?php echo $key+1; ?></td>
         									<td style="width: 70px;"><?php echo $entry->kwh; ?> kWh</td>
         									<td style="width: 70px;">{!! $currency !!}<?php echo $entry->amount; ?></td>
         									<td style="width: 70px;">{!! $currency !!}<?php echo $entry->old_balance; ?></td>
         									<td style="width: 70px;">{!! $currency !!}<?php echo $entry->new_balance;?></td>
         								</tr>
         								
         								
         							<?php endforeach; ?>
         							<tr>
         								
         									<td style="width: 70px;"><b>Total</b></td>
         									<td style="width: 70px;"><?php echo $total_usage; ?> kWh</td>
         									<td style="width: 70px;">{!! $currency !!}<?php echo $total_billed; ?></td>
         									<td style="width: 70px;background:rgba(0,0,0,0.2);"></td>
         										<td style="width: 70px;background:rgba(0,0,0,0.2);"></td>
         								</tr>
                                  </tbody>
                               </table>
                               </div>
         
            <h4> Text charges to Customer <?php echo $data['id']; ?> on <?php echo date('F jS Y', strtotime("$day.$month.$year")); ?></h4>
         
           <div class="" style="margin-top: 20px;">
                                   <table id="sortthistable" class="table table-bordered">
                                   <thead>
                                       <th>SMS</th>
                                       <th>Billed</th>                        
                                       </thead>
                                   <tbody>
         
                                       <?php
            $total_sms_billed = 0;
            
                                    if ($data['array'] == "")
                                        echo "There are no data to show";
                                    else
                                        foreach ($entries as $key=>$entry):
            		
            		
            		if($entry->type != 'sms')
            			continue;
            		
            		$total_sms_billed += $entry->amount;
            	
            		?>
         								<tr>
          
          
         								</tr>
         								
         								<tr>				
         									<td style="width: 70px;"><?php echo $entry->message; ?></td>
         									<td style="width: 70px;">{!! $currency !!}<?php echo $entry->amount; ?></td>
         								</tr>
         							<?php endforeach; ?>
         							<tr>
         								<td style="width: 70px;"><b>Total</b></td>
         								<td style="width: 70px;background:rgba(0,0,0,0.2);">{!! $currency !!}<?php echo $total_sms_billed; ?></td>
         							</tr>
                                  </tbody>
                               </table>
                               </div>
         				
         				<?php $grand_total_billed = $total_billed + $total_sms_billed; ?>
         				<input type="hidden" id="grand_total_billed" value="{!!$grand_total_billed!!}">
         				<input type="hidden" id="grand_total_billed_normal" value="{!!$total_billed!!}">
         				<input type="hidden" id="grand_total_billed_sms" value="{!!$total_sms_billed!!}">
         				<input type="hidden" id="grand_total_usage" value="{!!$total_usage!!}">
         							
         				
         				
         </div>
         -->
      <!-- Disabled Advanced Usage -->
      @if(1!=1)
      <div class="tab-pane" id="new-usage-details">
         <div >
            <form class="form-inline" action="<?php echo URL::to('customer_tabview_controller/date_search_action/'.$data['id']) ?>#messages" method="post">
               <input type="text" name="from" class="input-small" placeholder="From" id="datepicker01">
               <input type="text" name="to" class="input-small" placeholder="To" id="datepicker02">
               <button type="submit" class="btn" >Submit</button>
            </form>
         </div>
         <br/>
         <b> ** Other ** </b> respresents kWh usage that the customer was not charged for during the previous day
         <table>
            <tr>
               <td>
                  <table>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Start Date</dt>
                              <dd>{!! $usageTotals->start_date !!}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>End Date</dt>
                              <dd>{!! $usageTotals->end_date !!}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Total Usage:</dt>
                              <dd> {!! $usageTotals->total_usage !!} {!! $abbreviation !!}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Average Daily Usage:</dt>
                              <dd> {!!  number_format((float)$usageTotals->average_daily_usage, 2, '.', '') !!} {!! $abbreviation !!}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Start Reading:</dt>
                              <dd> {!! $usageTotals->start_reading !!} {!! $abbreviation !!}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>End Reading:</dt>
                              <dd> {!! $usageTotals->end_reading !!} {!! $abbreviation !!}</dd>
                           </dl>
                        </td>
                     </tr>
                  </table>
               </td>
               <td>
                  <table>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Total Cost:</dt>
                              <dd>{!! $currency !!}{{ number_format((float)$usageTotals->total_cost, 2, '.', '') }}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Average Daily Cost:</dt>
                              <dd>{!! $currency !!}{{ number_format((float)$usageTotals->average_daily_cost, 2, '.', '') }}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Unit Charge:</dt>
                              <dd>{!! $currency !!}{{ $usageTotals->unit_charge }}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Standing Charge:</dt>
                              <dd>{!! $currency !!}{{ $usageTotals->standing_charge }}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Arrears Repayment:</dt>
                              <dd>{!! $currency !!}{{ $usageTotals->arrears_repayment }}</dd>
                           </dl>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <dl class="dl-horizontal">
                              <dt>Other Charges:</dt>
                              <dd>{!! $currency !!}{{ $usageTotals->other_charges }}</dd>
                           </dl>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
         <div class="" style="margin-top: 20px;">
            <table id="sortthistable" class="table table-bordered">
               <thead>
                  <?php
                     if(isset($_GET['debug']))
                     {
                     	echo "<th>ID</th>";
                     }
                     
                     
                     ?>
                  <th>Date</th>
                  <th>First Reading</th>
                  <th>First Balance</th>
                  <th>Last Reading</th>
                  <th>Last Balance</th>
                  <th>Usage</th>
                  <th>Expected kWh cost</th>
                  <th>Standing charge</th>
                  <th>Other</th>
                  <th>Topup</th>
                  <th>SMS</th>
                  <th>IOU</th>
                  <th>Cost of Day</th>
               </thead>
               <tbody>
                  <?php
                     if ($data['array'] == "")
                         echo "There are no data to show";
                     else
                         foreach ($advancedUsage as $type):
                     
                     
                     ?>
                  <tr @if($type['missed_day']) style='background:rgba(206, 114, 114, 0.3);;' @endif>
                  <?php
                     if(isset($_GET['debug']))
                     {
                     	echo "<th>".$type['id']."</th>";
                     }
                     
                     ?>
                  <td style="width: 70px;"><?php echo $type['date'] ?></td>
                  <td><?php echo $type['first_reading'] ?> {!! $abbreviation !!}</td>
                  <td>{!! $currency !!}<?php echo $type['first_reading_bal'] ?></td>
                  <td><?php echo $type['last_reading'] ?> {!! $abbreviation !!}</td>
                  <td>{!! $currency !!}<?php echo $type['last_reading_bal'] ?></td>
                  <td><?php echo $type['total_kwh_used'] ?> {!! $abbreviation !!}</td>
                  <td>{!! $currency !!}<?php echo round_up($type['expected_kwh_cost'], 5);?></td>
                  <td>{!! $currency !!}<?php echo $type['standing_charge']; ?></td>
                  <td>{!! $currency !!}<?php echo $type['residual_cost']; ?></td>
                  <td>{!! $currency !!}<?php echo $type['total_topup']; ?></td>
                  <td>{!! $currency !!}<?php echo $type['total_sms']; ?></td>
                  <td>{!! $currency !!}<?php echo $type['total_iou']; ?></td>
                  <td>{!! $currency !!}<?php echo $type['total_balance_deducted'] ?></td>
                  </tr>
                  <?php
                     //	echo $otherCharges . "<br/>".$type['cost_of_day']." - ".$type['total_usage']." * ".$data['kWh_usage_tariff']." + ".$type['standing_charge']." + ".$type['arrears_repayment']."<br/><br/>";
                     
                     ?>
                  <?php endforeach; ?>
                  <?php //echo 'Computed cost: ' .  $computed; ?>
                  <?php //echo '<br/>Computed kWh: ' .  $computed_2; ?>
               </tbody>
            </table>
         </div>
      </div>
      @endif
      <div class="tab-pane <?php echo $data['message'] ?>" id="usage_details">
         <ul class="nav nav-pills">
            <li class="active"><a sub-toggle="true" href="#daily_usage" data-toggle="tab">Daily Usage</a></li>
            @if(Auth::user()->isUserTest())
				<li><a sub-toggle="true" href="#daily_readings" data-toggle="tab">Daily Readings</a></li>
			@endif
         </ul>
         <div class="tab-content">
			
            @if (1==1)	
            <div id="daily_readings" class="tab-pane">
               <table>
                  <tr>
                     <td>
                        <table>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Range</dt>
                                    <dd id="total_range">0</dd>
                                 </dl>
                              </td>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Current Reading</dt>
                                    <dd id="current_reading">0</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>From</dt>
                                    <dd id="total_from">0</dd>
                                 </dl>
                              </td>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>To</dt>
                                    <dd id="total_to">0</dd>
                                 </dl>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <div class="" style="margin-top: 20px;">
                  <form class="form-inline" action="<?php echo URL::to('customer_tabview_controller/readings/date_search_action/'.$data['id']) ?>#usage_details" method="post">
                     <input id="readings_from" type="text" name="from" value="{!! $readingsDates['from'] !!}" class="input-small" placeholder="From" >
                     <input id="readings_to" type="text" name="to" value="{!! $readingsDates['to'] !!}" class="input-small" placeholder="To" >
                     <button onclick="getReadingsTable()" type="button" class="btn" >Submit</button>
                  </form>
                  <table id="readings_table" id="sortthistable" class="table table-bordered">
                     <tr>
                        <th>Date</th>
                        <th>Last Reading</th>
                     </tr>
                     <tr id="readings_table_loading">
                        <td>Loading..</td>
                        <td>Loading..</td>
                     </tr>
                  </table>
               </div>
            </div>
            @endif
            <div id="daily_usage" class="tab-pane active">
               <div>
                  <form class="form-inline" action="<?php echo URL::to('customer_tabview_controller/date_search_action/'.$data['id']) ?>#usage_details" method="post">
                     <input type="text" name="from" class="from-usage input-small" placeholder="From" value="{!! Carbon\Carbon::parse($usageTotals->start_date)->format('d-m-Y') !!}" id="datepicker0">
                     <input type="text" name="to" class="to-usage input-small" placeholder="To" value="{!! Carbon\Carbon::parse($usageTotals->end_date)->format('d-m-Y') !!}" id="datepicker2">
                     <button type="submit" class="btn" >Submit</button>
					  @if(Auth::user()->scheme->isBlueScheme)
						<button type="button" data-toggle="modal" data-target="#export-usage" class="btn btn-warning"><i class="fa fa-file-export"></i> Export usage</button>
					  @endif
					  @if(Auth::user()->isUserTest())
						<a style="float:right;margin-right: 9%;" href="{!! URL::to('billing/' . $data['id']) !!}"><button style='display:inline-block' type="button" class="btn btn-primary"><i class="fa fa-coins"></i> Manage Billing</button></a>
					<a style="float:right;margin-right: 9%;" href="{!! URL::to('export/readings/' . $data['id']) !!}"><button style='display:inline-block' type="button" class="btn btn-warning"><i class="fa fa-file-export"></i> Export readings</button></a>
					  @endif
                  </form>
               </div>
               <br/>
               <table>
                  <tr>
                     <td>
                        <table>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Start Date</dt>
                                    <dd>{!! $usageTotals->start_date !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>End Date</dt>
                                    <dd>{!! $usageTotals->end_date !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Total Usage:</dt>
                                    <dd> {!! $usageTotals->total_usage !!} {!! $abbreviation !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Average Daily Usage:</dt>
                                    <dd> {!! $usageTotals->avg_daily_usage !!} {!! $abbreviation !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Start Reading:</dt>
                                    <dd> {!! $usageTotals->start_reading !!} {!! $abbreviation !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>End Reading:</dt>
                                    <dd> {!! $usageTotals->end_reading !!} {!! $abbreviation !!}</dd>
                                 </dl>
                              </td>
                           </tr>
                        </table>
                     </td>
                     <td>
                        <table>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Total Cost:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->total_cost }}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Average Daily Cost:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->avg_daily_cost }}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Unit Charge:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->unit_charge }}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Standing Charge:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->standing_charge }}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Arrears Repayment:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->arrears_repayment }}</dd>
                                 </dl>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <dl class="dl-horizontal">
                                    <dt>Other Charges:</dt>
                                    <dd>{!! $currency !!}{{ $usageTotals->other_charges }}</dd>
                                 </dl>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <div class="" style="margin-top: 20px;">
                  <table id="sortthistable" class="table table-bordered">
                     <thead>
                        <?php
                           if(isset($_GET['debug']))
                           {
                           	echo "<th>ID</th>";
                           }
                           
                           
                           ?>
                        <th>Date</th>
                        <th>Start Reading</th>
                        <th>End Reading</th>
                        <th>Total Usage</th>
                        <th>Unit Charge</th>
                        <th>Standing Charge</th>
                        <th>Arrears Repayment</th>
                        <th>Other Charges</th>
                        <th>Cost of Day</th>
                     </thead>
                     <tbody>
                        <?php
                           $gatheringCharges = false;
                           $gatheredAmount = 0;
                           $computed = 0;
                           $computed_2 = 0;
                           
                           						if ($data['array'] == "")
                           							echo "There are no data to show";
                           						else
                           							foreach ($data['array'] as $type):
                           		
                           		$computed += $type['cost_of_day'];
                           		$computed_2 += $type['total_usage'];
                           		
								if(date('Y-m-d') >= '2019-10-01') {
									$otherCharges = abs($type['cost_of_day'] - (($type['unit_charge']) + ($type['standing_charge']) + ($type['arrears_repayment'])));
								} else {
                           		$otherCharges = abs($type['cost_of_day'] - (($type['total_usage'] * $data['kWh_usage_tariff']) + ($type['standing_charge']) + ($type['arrears_repayment'])));
                           		}
								?>
                        <tr @if($type['missed_day'] && isset($_GET['testing'])) style='background:rgba(206, 114, 114, 0.3);' @endif>
                        <td style="width: 90px;">
							@if(Auth::user()->isUserTest())
								<a href="{!! URL::to('edit_dhu/' . $type['id']) !!}"><i class="fa fa-pencil-alt"></i> <?php echo $type['date']; ?></a>
							@else
								<?php echo $type['date']; ?>
							@endif
						</td>
                        <td><?php echo $type['start_day_reading']; ?> {!! $abbreviation !!}</td>
                        <td><?php echo $type['end_day_reading']; ?> {!! $abbreviation !!}</td>
                        <td><?php echo $type['total_usage']; ?> {!! $abbreviation !!}</td>
                        
						@if(date('Y-m-d') >= '2019-10-01')
							<td>{!! $currency !!}<?php echo $type['unit_charge'] ?></td>
						@else
						<td>{!! $currency !!}<?php echo $type['total_usage'] * $data['kWh_usage_tariff'] ?></td>
						@endif
                        <td>{!! $currency !!}<?php echo $type['standing_charge'] ?></td>
                        <td>{!! $currency !!}<?php echo $type['arrears_repayment']; ?></td>
                        <td>{!! $currency !!}<?php echo number_format($otherCharges, 2, '.', ''); ?></td>
                        <td>{!! $currency !!}<?php echo $type['cost_of_day']; ?></td>
                        </tr>
                        <?php
                           //	echo $otherCharges . "<br/>".$type['cost_of_day']." - ".$type['total_usage']." * ".$data['kWh_usage_tariff']." + ".$type['standing_charge']." + ".$type['arrears_repayment']."<br/><br/>";
                           
                           ?>
                        <?php endforeach; ?>
                        <?php //echo 'Computed cost: ' .  $computed; ?>
                        <?php //echo '<br/>Computed kWh: ' .  $computed_2; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div class="tab-pane" id="arrears">
         <form action="<?php echo URL::to('customer_tabview_controller/addarrears') ?>" method="POST" id="addarrears">
            <p style="font-size: 1.5em;">Add arrears</p>
            <input type="hidden" name="user_id" value="<?php echo $data['id']; ?>">
            <table>
               <tr>
                  <td>Arrears:</td>
                  <td><input type="text" name="addArrears" id="addArrears"></td>
               </tr>
               <tr>
                  <td>Daily Repayment:</td>
                  <td><input type="text" name="addDailyRep" id="addDailyRep"></td>
               </tr>
               <tr>
                  <td>&nbsp;</td>
                  <td>
                     <div class="form-actions">
                        <a href="#myModal" class="btn btn-primary" data-toggle="modal">Add</a>
                     </div>
                  </td>
               </tr>
            </table>
            <div id="myModal" class="modal hide fade" >
               <div class="modal-header">
                  <h3 id="myModalLabel">Add arrears.</h3>
               </div>
               <div class="modal-body">
         <form class="form-horizontal">
         <div class="form-group" role="form">
         <p>Are you sure you would like to add arrears to this customer?</p>
         </div>
         </form>
         </div>
         <div class="modal-footer">
         <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
         <a href="#" class="btn btn-danger" onclick="issue(11)">Yes</a>
         </div>
         </div>
         </form>
         <p>Current Arrears: <?php echo $current_arrears['arrears']; ?></p>
         <p>Current Repayment: <?php echo $current_arrears['arrears_daily_repayment']; ?></p>
         <table class="table table-bordered">
            <tr>
               <th>Date</th>
               <th>Arrears</th>
               <th>Daily Repayment</th>
            </tr>
            <?php foreach($arrears as $item){ ?>
            <tr>
               <td><?php echo $item['date']; ?></td>
               <td><?php echo $item['amount']; ?></td>
               <td><?php echo $item['repayment_amount']; ?></td>
            </tr>
            <?php } ?>
         </table>
      </div>
      <div class="tab-pane" id="topups">
         <ul class="nav nav-pills">
            @if(hasAccess('admin.issued.credit'))<li class="active"><a href="#topups-1" data-toggle="tab">Issue Credit</a></li>@endif
            <li @if(!hasAccess('admin.issued.credit')) class="active" @endif><a href="#topups-4" data-toggle="tab">Deduct credit</a></li>
            <li><a href="#topups-2" data-toggle="tab">Issue Admin IOU</a></li>
            <li><a href="#topups-3" data-toggle="tab">Issue Top-Up Arrears</a></li>
         </ul>
         <div class="tab-content">
			@if(hasAccess('admin.issued.credit'))
            <div id="topups-1" class="tab-pane active">
               <h4>Issue Credit</h4>
               @include('includes.issue_credit_form')
            </div>
			@endif
            <div id="topups-4" class="tab-pane @if(!hasAccess('admin.issued.credit')) active @endif">
               <h4>Deduct Credit</h4>
               @include('includes.deduct_credit_form')
            </div>
            <div id="topups-2" class="tab-pane">
               <h4>Issue Admin IOU</h4>
               @include('includes.topup_form', array('type'=> 'issue_admin_iou', 'modalTitle' => 'Issue Admin IOU', 'addAmountURL' => URL::to('/issue_admin_iou/add_amount/') ))
            </div>
            <div id="topups-3" class="tab-pane">
               <h4>Issue Top-Up Arrears</h4>
               @include('includes.issue_top_up_arrears')
            </div>
         </div>
      </div>
      <div class="tab-pane" id="send-message">
         @if(!isset($data))
         Customer data not found
         @else
		@if(Auth::user()->isUserTest())
		 <ul class="nav nav-pills">
            <li class="active"><a href="#send-sms" data-toggle="tab">Send SMS</a></li>
            <li class=""><a href="#send-notif" data-toggle="tab">Send In-App Notification</a></li>
         </ul>
		@endif
		
		<div class="tab-content">
		
		<div id="send-sms" class="tab-pane active">
		<input type="hidden" id="text_field" value="sms-message"/>
		<input type="hidden" id="email_address_field" value="{!! $data['email_address'] !!}"/>
		<input type="hidden" id="username_field" value="{!! $data['username'] !!}"/>
		<input type="hidden" id="id_field" value="{!! $data['id'] !!}"/>
		<input type="hidden" id="balance_field" value="{!! number_format($data['balance'], 2) !!}"/>
         <form action="{!! URL::to('customer_messaging/send_single_sms') !!}" method="POST" class="well form-horizontal">
            <input type="hidden" name="customer_id" value="{!! $data['id'] !!}"/>
            <fieldset>
				<div class="control-group">
                  <label class="control-label" for="input01">Charge ({!! $currency !!}{{ $data['sms_cost'] }}):</label>
                  <div class="controls">
                     <input style="height: 21px; width: 21px;" type="checkbox" name="sms_charge" class="input-xlarge" id="sms-charge"/>
                  </div>
               </div>
			   @if(Auth::user()->isUserTest())
			   <div class="control-group">
                  <label class="control-label" for="input01">Charge premium ({!! $currency !!}0.50):</label>
                  <div class="controls">
                     <input style="height: 21px; width: 21px;" type="checkbox" name="sms_charge_premium" class="input-xlarge" id="sms-charge-premium"/>
                  </div>
               </div>
			   <hr/>
			   @endif
				<div class="control-group">
                  <label class="control-label" for="input01">Reset password</label>
                  <div class="controls">
                     <input style="height: 21px; width: 21px;" checked="true" type="checkbox" name="reset_pw" id="reset_pw" class="input-xlarge"/>
                  </div>
               </div>
				<div class="control-group">
                  <label class="control-label" for="input01">Include website link</label>
                  <div class="controls">
                     <input style="height: 21px; width: 21px;" checked="true" type="checkbox" name="sms_web" id="sms_web_link" class="input-xlarge"/>
                  </div>
               </div>
			  
					
				<div class="control-group">
                  <div class="controls">
                     <button type="button" name="sms_details" id="sms_details" class="btn btn-primary " />1. Fill details preset</button>
                  </div>
               </div>
			   
			   <hr/>
			   
			   @if(Auth::user()->isUserTest())
			   <div class="control-group">
                  <div class="controls">
				<a href='/settings/sms_presets'><i class='fa fa-pencil-alt'></i>&nbsp;Edit Presets</a><br/><br/>
				<select class='categories' name='categories'>
					<option value='0'> -- Select Preset Category -- </option>
					@foreach(SMSMessagePreset::categories() as $k => $categories)
						<option>{!! $categories->category !!}</option>
					@endforeach
				</select>
				&nbsp;
				<select style='width:70%;display:none;' class='presets' name='presets'>
					
				</select>
				</div>
               </div>
			   @endif
			   
			   <script>
						
					$(function(){
						
						var loaded_presets = [];
		
		$('.categories').on('change', function(){
			
			$('#sms-message').val('');
				
			var category = $(this).val();
			
			if(category.indexOf('Select Preset') != -1) {
				$('.presets').hide();
			} else {
				$('.presets').show();
			}
			
			$.ajax({
				
				url: '/bug/reports/get_presets', 
				type: 'POST',
				data: { category: category, customer_id: {!! $data->id !!}  },
				success: function(data){
					loaded_presets = data.presets;
					generatePresets();
				}
			});
			
		});
		
		$('.presets').on('change', function(){
			
			var val = $(this).val();
			if(val.indexOf('Select Preset') != -1) return;
			
			val = "Hi " + '{!! $data->first_name !!},' + "\n\n" + val;
			val = val + "\n\nThis SMS is NOREPLY - Responses to this SMS will not be delivered.\n\n";
			val = val + "Kind Regards\nSnugZone";
			$('#sms-message').val(val);
			
			
		});
		
		function generatePresets()
		{
			var presets = $('.presets');
			presets.html('');
			var append = '';
			presets.append($('<option>', { 
					value: ' -- Select Preset Response -- ',
					text : ' -- Select Preset Response -- ',
			}));
/// jquerify the DOM object 'o' so we can use the html method
			$.each(loaded_presets, function(k, v){
				var o = new Option(v.body, v.body);
				presets.append($('<option>', { 
					value: v.body,
					text : v.body,
				}));
			});
		
		}
					});
			   </script>
			   
			   <hr/>
			   
               <div class="control-group">
                  <label class="control-label" for="input01">Message:</label>
                  <div class="controls">
                     <textarea name="message" style="width:95%;height:200px;" class="input-xlarge" id="sms-message"></textarea>
                  </div>
               </div>
               <div class="form-actions">
                  <button type="submit" class="btn btn-primary">Send</button>
               </div>
            </fieldset>
         </form>
         @endif
         <h4>SMS List</h4>
         @if (count($smsList))
         <table class="table table-bordered sortthistable">
            <thead>
               <tr>
                  <th class="header">Mobile Number</th>
                  <th class="header">Message</th>
                  <th class="header">Date/Time</th>
                  <th class="header">Charge</th>
                  <th class="header">Sent</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($smsList as $key => $smsInfo)
               <tr>
                  <td>{{ $smsInfo->mobile_number }}</td>
                  <td>{{ $smsInfo->message }}</td>
                  <td>{{ $smsInfo->date_time }}</td>
                  <td>{!! $currencySign !!}{{ $smsInfo->charge }}</td>
                  <td>{{ $smsInfo->message_sent }}</td>
               </tr>
               @endforeach
            </tbody>
         </table>
         @else
         <p>No SMS messages were sent</p>
         @endif
         <div id="SMSModal" class="modal hide fade" >
            <div class="modal-header">
               <h3 id="myModalLabel">Please enter SMS password</h3>
            </div>
            <div class="modal-body">
               <form class="form-horizontal">
                  <div class="form-group" role="form">
                     <label for="inputEmail1" class="control-label">SMS Password: </label>
                     <div>
                        <input type="password" class="form-control" id="sms-password" placeholder="Password">
                     </div>
                  </div>
               </form>
               <div id="sms-alert" class="alert alert-error" style="visibility: hidden;">Wrong Password</div>
            </div>
            <div class="modal-footer">
               <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
               <a href="#" class="btn btn-danger" onclick="sendMessage()">Yes</a>
            </div>
         </div>
		</div>
		
		@if(Auth::user()->isUserTest())
		<div id="send-notif" class="tab-pane">
		<?php
		
		?>
		<form action="<?php echo URL::to('customer_tabview_controller/send_notification/'.$data['id']); ?>" method="POST">
			<table width="100%">
				<tr>
					<td>
						<h4>
							Notification
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" name="title" class="input-large form-control" placeholder="Notification title"/>
					</td>
				</tr>
				<tr>
					<td>
						<h4>
							Dismiss button
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" name="dismiss_txt" class="input-large form-control" placeholder="Dismiss text" value="Okay!"/>
					</td>
				</tr>
				<tr>
					<td>
						<h4>
							Dismiss button URL
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" name="dismiss_txt_url" class="input-large form-control" placeholder="Dismiss text" value="n/a!"/>
					</td>
				</tr>
				<tr>
					<td>
						<h4>
							Notification
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<textarea name="body" placeholder="Type your notification message here"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<button type="submit" class="btn btn-primary">Send notification</button>
					</td>
				</tr>
			</table>
		</form>
		 @if (count($notifications))
         <table class="table table-bordered sortthistable">
            <thead>
               <tr>
                  <th class="header">Title</th>
                  <th class="header">Body</th>
                  <th class="header">Delivered</th>
                  <th class="header">Global</th>
                  <th class="header">Created at</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($notifications as $key => $notif)
               <tr>
                  <td>{!! $notif->title !!}</td>
                  <td>{!! $notif->body !!}</td>
                  <td>{!! ($notif->delivered) ? 'yes' : 'no' !!}</td>
                  <td>{!! ($notif->all_schemes) ? 'yes' : 'no' !!}</td>
                  <td>{!! $notif->created_at !!} ({!! Carbon\Carbon::parse($notif->created_at)->diffForHumans() !!})</td>
               </tr>
               @endforeach
            </tbody>
         </table>
         @else
         <p>No notifications were sent</p>
         @endif
		</div>
		@endif
		
		</div>
		
	  </div>
		
      <div class="tab-pane" id="utility-notes">
         <form id="form1" action="<?php echo URL::to('customer_tabview_controller/add_utility_note/'.$data['id']); ?>" method="POST" class="well form-horizontal">
            <fieldset>
               <div class="control-group">
                  <label for="t_area" class="control-label">Utility Notes: </label>
                  <div class="controls">
                     <input type="hidden" name="formid" value="1">
                     <textarea class="field span5" rows="5" name="t_area" id="t_area"></textarea>
                  </div>
               </div>
               <div class="form-actions">
                  <a href="#"  class="btn btn-primary" onclick="issue(1)">Add</a>
               </div>
            </fieldset>
         </form>
         <h4>Utility Notes List</h4>
         @if (count($utilityNotesList))
         <table id="utlity-notes-table" class="table table-bordered sortthistable">
            <thead>
               <tr>
                  <th class="header">Content</th>
                  <th class="header">Date/Time</th>
                  <th class="noSort"></th>
               </tr>
            </thead>
            <tbody>
               @foreach ($utilityNotesList as $key => $utilityNote)
               <tr>
                  {!! Form::open(['url' => URL::to('customer_tabview_controller/utility_note/' . $data['id']), 'method' => 'delete', 'onSubmit' => 'return confirm("Are you sure you want to delete the selected utility note?")']) !!}
                  {!! Form::hidden('utility_note_id', $utilityNote->id) !!}
                  <td>{{ $utilityNote->notes }}</td>
                  <td>{{ $utilityNote->date_time }}</td>
                  <td>{!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}</td>
                  {!! Form::close() !!}
               </tr>
               @endforeach
            </tbody>
         </table>
         @else
         <p>No utility notes found</p>
         @endif
      </div>
      <div class="tab-pane" id="iou-usage">
         <h4>Utility Notes List</h4>
         @if (count($iouUsageList))
         <table id="iou-usage-table" class="table table-bordered sortthistable">
            <thead>
               <tr>
                  <th class="header">Date/Time</th>
                  <th class="header">Charge</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($iouUsageList as $key => $iouUsage)
               <tr>
                  <td>{{ $iouUsage->time_date }}</td>
                  <td>{!! $currencySign !!} {{ $iouUsage->charge }}</td>
               </tr>
               @endforeach
            </tbody>
         </table>
         @else
         <p>No IOU Usage entries found</p>
         @endif
      </div>
      <div class="tab-pane" id="new-topups">
         <ul class="nav nav-pills">
            <li class="active"><a href="#topup-history-permanent" data-toggle="tab">Top-up History</a></li>
            <!-- <li><a href="#topup-history-temporary" data-toggle="tab">Top-up History (Temporary/Pending)</a></li>-->
            <li><a href="#topup-admin" data-toggle="tab">Admin Top-ups</a></li>
            <li><a href="#deduct-admin" data-toggle="tab">Admin Deductions</a></li>
         </ul>
         <div class="tab-content">
            <form class="form-inline" action="<?php echo URL::to('customer_tabview_controller/topups/date_search_action/'.$data['id']) ?>" method="post">
               <input type="text" name="from" class="input-small" placeholder="From" value="{!! $topupDates['from'] !!}" id="topups_from">
               <input type="text" name="to" class="input-small" placeholder="To" value="{!! $topupDates['to'] !!}" id="topups_to">
               <button type="submit" class="btn" >Submit</button>
            </form>
            <div id="topup-history-permanent" class="tab-pane active">
               <h4>Top-up History 
				<div class='pull-right'>
					<a href="{!! URL::to('customer_tabview_controller/sync_paypal', ['customer_id' => $data->id, 'date' => $topupDates['to']]) !!}">
						<button class="btn btn-primary">
						<i class="fa fa-sync"></i> Sync Paypal ({!! $topupDates['to'] !!})
						</button>
					</a>
				</div>
			   </h4>
			   <hr/>
               @include('includes.topups_list', array('topups'=> $allTopups) )
            </div>
            <!-- <div id="topup-history-temporary" class="tab-pane">
               <h4>Top-up History (Temporary/Pending)</h4>
               @include('includes.topups_list_temp', array('topups'=> $pendingTopups) )
               </div>-->
            <div id="topup-admin" class="tab-pane">
               <h4>Admin Top-ups</h4>
               @if (count($adminTopups))
               <table class="table table-bordered sortthistable">
                  <thead>
                     <tr>
                        <th>Date/Time</th>
                        <th>Admin Name</th>
                        <th>Amount</th>
                        <th>Reason</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($adminTopups as $key => $adminTopup)
                     <tr>
                        <td>{{ $adminTopup->date_time }}</td>
                        <td>{{ $adminTopup->admin_name }}</td>
                        <td>{!! $currencySign !!}{{ $adminTopup->amount }}</td>
                        <td>{{ $adminTopup->reason }}</td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
               @else
               <p>No Entries</p>
               @endif
            </div>
            <div id="deduct-admin" class="tab-pane">
               <h4>Admin Deductions</h4>
               @if (count($adminDeductions))
               <table class="table table-bordered sortthistable">
                  <thead>
                     <tr>
                        <th>Date/Time</th>
                        <th>Admin Name</th>
                        <th>Amount</th>
                        <th>Reason</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($adminDeductions as $key => $adminDeduction)
                     <tr>
                        <td>{{ $adminDeduction->date_time }}</td>
                        <td>{{ $adminDeduction->admin_name }}</td>
                        <td>{!! $currencySign !!}{{ $adminDeduction->amount }}</td>
                        <td>{{ $adminDeduction->reason }}</td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
               @else
               <p>No Entries</p>
               @endif
            </div>
		  
		
		 </div>
		 
      </div>
      <div class="tab-pane" id="diagnostics">
         @include('partials.diagnostics', ['meter_id' => $data->districtHeatingMeter ? ($data->districtHeatingMeter->permanentMeterData ? $data->districtHeatingMeter->permanentMeterData->ID : null) : null])
         @if ($data->balance > 0 && $data->shut_off == 'Device Off' && Auth::user()->isUserTest())
         <div style="clear:both; padding-top: 20px;">
            <a href="{!! URL::to('red_to_green/' . $data['id'])!!}" class="btn btn-primary">Turn green</a>
         </div>
         @endif
      </div>
   </div>
   <!-- end .tab-content -->
</div>
{!!HTML::script('resources/js/util/fill_details_preset.js?21')!!}
@if(Auth::user()->scheme->isBlueScheme)
	
<style>
	input[type=checkbox].opt{
		width: 35px;
		height: 20px;
	}
</style>
	  
<div id="export-usage" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Export Usage: Customer #{!! $data['id'] !!}</h4>
	  </div>
	
	  <div class="modal-body">
			
			<input type="hidden" name="customer_id" value="{!! $data['id'] !!}">
			<div class="row-fluid">
				<div class="span1"><input checked="true" class="opt" id="show_amounts" type="checkbox"></div>
				<div class="span10">Show {!! $currency !!} amount(s)</div>
			</div>
			<div class="row-fluid">
				<div class="span1"><input checked="true" class="opt" id="show_grand_total_amount" type="checkbox"></div>
				<div class="span10">Show Cost of Day</div>
			</div>
			<div class="row-fluid">
				<div class="span1"><input checked="true" class="opt" id="show_usage" type="checkbox"></div>
				<div class="span10">Show kWh Usage</div>
			</div>
			<div class="row-fluid">
				<div class="span1"><input checked="true" class="opt" id="show_readings" type="checkbox"></div>
				<div class="span10">Show Individual Readings</div>
			</div>
			<div class="row-fluid">
				<div class="span1"><input checked="true" class="opt" id="show_tariffs" type="checkbox"></div>
				<div class="span10">Show Tariffs</div>
			</div>
			
			
			
			<hr/>
			
	  </div>
	  
	  <div class="modal-footer">
		<button id="export_submit" type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-download"></i> Download</button>
	  </div>
	  
	  
	</div>

	</div>
	</div>

<script>
	$(function(){
		
		
		
		$('#export_submit').on('click', function(){
			
			var customer_id = $('[name=customer_id]').val();
			var show_amounts = $('#show_amounts').is(":checked");
			var show_gtotal = $('#show_grand_total_amount').is(":checked");
			var show_usage = $('#show_usage').is(":checked");
			var show_readings = $('#show_readings').is(":checked");
			var show_tariffs = $('#show_tariffs').is(":checked");
			var from = $('.from-usage').val();
			var to = $('.to-usage').val();
			
			console.log("\n\n");
			console.log("Show amounts: " + show_amounts);
			console.log("Show grand total: " + show_gtotal);
			console.log("Show usage: " + show_usage);
			console.log("Show readings: " + show_readings);
			console.log("Show Tariffs: " + show_tariffs);
			console.log("Range: " + from + " -> " + to);
			
			
			var url = "http://www.prepago-admin.biz/customer_tabview_controller/export";
			url += "?customer_id=" + customer_id;
			url += "&show_amounts=" + show_amounts;
			url += "&show_gtotal=" + show_gtotal;
			url += "&show_usage=" + show_usage;
			url += "&show_readings=" + show_readings;
			url += "&show_tariffs=" + show_tariffs;
			url += "&from=" + from;
			url += "&to=" + to;
			
			
			window.location.href = url;
			
		});
		
	});
</script>
@endif
<script type="text/javascript"></script>