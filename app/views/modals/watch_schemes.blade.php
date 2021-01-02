<div id="watch-schemes" class="modal fade" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">
	<ul class="nav nav-tabs" style="margin: 30px 0">
	  <li class="active"><a href="#watch" data-toggle="tab">Watch</a></li>
	  <li><a href="#settings" data-toggle="tab">Settings</a></li>
	  <li><a href="#history" data-toggle="tab">History</a></li>
	</ul>
	</h4>
  </div>
  <div class="modal-body">
	
	<div class="alert alert-success success_msg" style="display:none;"></div>
	<div class="alert alert-danger error_msg" style="display:none;"></div>
			
	<div class="tab-content">
		<div class="tab-pane active" id="watch" style="">
			<div class="row-fluid">
				<div class="span12">
					@foreach($all_schemes as $s) 
						<label class="check">
							<div style='border:1px solid #ccc;border-radius:3px;margin-bottom:10px;padding:5px;' value="{{ $s->scheme_number }}">
								<div class="row-fluid">
									<div class="span4">
										&nbsp;{{ ucfirst($s->scheme_nickname) }}
									</div>
									<div class="span4">
										@if(strpos($s->status, "Online") !== false)
											<i style="color:green;" class="fa fa-check-circle"></i>&nbsp;Online
										@endif
										@if(strpos($s->status, "Offline") !== false)
											<i style="color:red;" class="fa fa-times-circle"></i>&nbsp;Offline
										@endif
										@if(strpos($s->status, "Reboot") !== false)
											<i style="color:orange;" class="fa fa-sync"></i>&nbsp;Rebooting..
										@endif
									</div>
									<div class="span4">
										@if($s->watch)
											<button class="watch" scheme="{{ $s->scheme_number }}"><i class="fa fa-eye-slash"></i> Stop Watch</button>
										@else
											<button class="btn btn-primary watch" scheme="{{ $s->scheme_number }}"><i class="fa fa-eye"></i> Watch</button>
										@endif
									</div>
								</div>
							</div>
						</label>
					@endforeach
				</div>
			</div>
		</div>
		<div class="tab-pane" id="settings" style="">
			<?php $setting = SystemSetting::get('var_scheme_watch_emails'); ?>
			<div class="row-fluid"><div class="span12"><font style="font-size:1.2rem;font-weight:bold;">Email Subject:</font></div></div>
			<div class="row-fluid">
				<div class="span12"> 
					<input name="var_scheme_watch_subject" value="{{ SystemSetting::get('var_scheme_watch_subject') }}" type="text" style="width:95%;border-radius: 5px;padding: 2%;margin-top: 2%;margin-bottom: 2%;">
				</div>
			</div>
			<div class="row-fluid"><div class="span12"><font style="font-size:1.2rem;font-weight:bold;">Email To:</font></div></div>
			<div class="row-fluid">
				<div class="span12"> 
					<textarea name="var_scheme_watch_emails" style="width:95%;border-radius: 5px;padding: 2%;margin-top: 2%;margin-bottom: 2%;">{{ SystemSetting::get('var_scheme_watch_emails') }}</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12"> 
				<hr/>
					<button style='float:right' type="submit" class="btn btn-primary save-changes-settings">Save changes</button>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="history" style="">
			
		</div>
	</div>
  </div>
  <div class="modal-footer">
	<button type="button" class="btn btn-primary" data-dismiss="modal">Done</button>
  </div>
</div>

</div>
</div>


<script>
	$(function(){
		
		$('.save-changes-settings').on('click', function(){
			
			var var_scheme_watch_subject = $("[name='var_scheme_watch_subject']").val();
			var var_scheme_watch_emails = $("[name='var_scheme_watch_emails']").val();
			
			$.ajax({
				url: "/system_reports/sim/track_scheme/settings",
				method: "POST",
				data: {
					var_scheme_watch_subject: var_scheme_watch_subject,
					var_scheme_watch_emails: var_scheme_watch_emails,
				},
				success: function(data){
					
					if(data.error) {
						error(data.error);
						return;
					}
					
					success(data.success);
					
					
				},
			});
		});
		
		$('.watch').on('click', function(){
			
			var scheme = $(this).attr('scheme');
			var t = $(this);
			
			$.ajax({
				url: "/system_reports/sim/track_scheme",
				data: {scheme_number:scheme},
				method: "POST",
				success: function(data){
					
					if(data.error) {
						error(data.error);
						return;
					}
					
					success(data.success);
					
					t.html(data.btn_content);
					
					if(data.btn_content.indexOf('slash') !== -1) {
						t.removeClass('btn-primary');
					} else {
						t.addClass('btn-primary');
					}
					
				},
			});
			
		});
		
		function success(msg) {
			$('.error_msg').hide();
			$('.success_msg').fadeIn();
			$('.success_msg').html(msg);
		}
	
		function error(msg) {
			$('.success_msg').hide();
			$('.error_msg').fadeIn();
			$('.error_msg').html(msg);
		}
	
		
	});
</script>

	