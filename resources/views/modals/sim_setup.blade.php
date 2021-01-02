<div id="sim_setup" class="modal fade" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
  <div class="modal-header">
	<h3>Activate SIM - Emnify API</h3>
  </div>
  <div class="modal-body">
  
	<div class="alert alert-success success_msg1" style="display:none;"></div>
	<div class="alert alert-danger error_msg1" style="display:none;"></div>
	<div class="alert alert-info info_msg1" style="display:none;"></div>
	<div class="alert alert-warning warning_msg1" style="display:none;"></div>
	
	<table width="100%" id="grab_table">
		<tr>
			<td width="100%">
				<font style="font-weight:bold;font-size:1.1rem;">* 1. ICCID - (Or ICCID Ending)</font>
			</td>
		</tr>
		<tr>
			<td width="100%">
				<input style="margin-top: 3%;font-size:1.1rem;" class="iccid" type="text" placeholder="ICCID">
			</td>
		</tr>
		<tr>
			<td width="100%">
				<button style="margin-top: 3%;font-size:0.9rem;" class="btn btn-info grab_sim"><i class='fa fa-smog'></i>&nbsp;Grab SIM</button>
			</td>
		</tr>
	</table>
	
	<table width="100%" id="assignment_table" style="display:none;">
		<tr>
			<td width="100%">
				<font style="font-weight:bold;font-size:1.1rem;">* 2. Select a scheme - To assign SIM</font>
			</td>
		</tr>
		<tr>
			<td width="100%">
				<input type='hidden' name='sim_scheme_number' value='{{ Scheme::active(false)[0]->scheme_number  }}'>
				<input type='hidden' name='sim_iccid' value=''>
				<select name='schemes' >
					@foreach(Scheme::active(false) as $k => $s)
						<option value='{{ $s->scheme_number }}'>{{ ucfirst($s->scheme_nickname) }}</option>
					@endforeach
				</select>
			</td>
		</tr>
		<tr>
			<td width="100%">
				<button style="margin-top: 3%;font-size:0.9rem;" class="btn btn-info assign_sim"><i class='fa fa-share'></i>&nbsp;Assign SIM</button>
			</td>
		</tr>
	</table>
	
  </div>
  <div class="modal-footer">
	<button type="button" class="btn" data-dismiss="modal">Dismiss</button>
  </div>
</div>

</div>
</div>


<script>
$(function(){
	
	
	var sim_assigning = null;
	
	$('.grab_sim').on('click', function(){
		
		var iccid = $('.iccid').val();
		
		if(iccid == '') {
			error('<b>Error:</b> Please fill in the ICCID!');
			return;
		}
		
		$.ajax({
			url: "/sim/grab_setup",
			data: {iccid: iccid},
			method: "POST",
			success: function(data){
				if(data.error) {
					error(data.error);
					return;
				}
				
				success(data.success);
				
				sim_assigning = data.sim;
				
				//$("[name='sim_scheme_number']").val();
				$("[name='sim_iccid']").val(sim_assigning.iccid);
				
				$('#grab_table').hide();
				$('#assignment_table').show();
			}
		});
		
	});
	
	$('.assign_sim').on('click', function(){
		
		var scheme_number = $("[name='sim_scheme_number']").val();
		var iccid = $("[name='sim_iccid']").val();
		
		warning('Setting up SIM.. please wait..');
		
		$.ajax({
			url: "/sim/assign_setup",
			data: {scheme_number: scheme_number, iccid: iccid},
			method: "POST",
			success: function(data){
				
				if(data.error) {
					error(data.error);
					return;
				}
				
				success(data.success);
				
				sim_assigning = data.sim;
				
				$('#grab_table').hide();
				$('#assignment_table').show();
			}
		});
	});
	
	$("[name='schemes']").on('change', function(){
		$("[name='sim_scheme_number']").val($(this).val());
	});
	function warning(msg) {
		$('.error_msg1').hide();
		$('.success_msg1').hide();
		$('.info_msg1').hide();
		$('.warning_msg1').fadeIn();
		$('.warning_msg1').html(msg);
	}
	
	function info(msg) {
		$('.warning_msg1').hide();
		$('.error_msg1').hide();
		$('.success_msg1').hide();
		$('.info_msg1').fadeIn();
		$('.info_msg1').html(msg);
	}
	
	function success(msg) {
		$('.warning_msg1').hide();
		$('.error_msg1').hide();
		$('.info_msg1').hide();
		$('.success_msg1').fadeIn();
		$('.success_msg1').html(msg);
	}
	
	function error(msg) {
		$('.warning_msg1').hide();
		$('.success_msg1').hide();
		$('.info_msg1').hide();
		$('.error_msg1').fadeIn();
		$('.error_msg1').html(msg);
	}		
	
	
});
</script>