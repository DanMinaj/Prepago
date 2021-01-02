<?php

$current_page = $_SERVER['REQUEST_URI'];
if(strpos($current_page, 'customer_tabview') !== false)
	$current_page = 'customer_tabview';

?>

<style>
.slider {
    -webkit-appearance: none;
    width: 100%;
    height: 25px;
    background: #d3d3d3;
    outline: none;
    opacity: 0.7;
    -webkit-transition: .2s;
    transition: opacity .2s;
}

.slider:hover {
    opacity: 1;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 25px;
    height: 25px;
    background: #4CAF50;
    cursor: pointer;
}

.slider::-moz-range-thumb {
    width: 25px;
    height: 25px;
    background: #4CAF50;
    cursor: pointer;
}
</style>

<form action="{!! URL::to('changelog/add') !!}" method="POST">
<div id="changelog" class="modal fade" role="dialog">

	  
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Create new change set</h4>
	  </div>
	
	  <div class="modal-body">
		
			
			<table width="100%">
			
					<tr>		
						<td>
							<input name="title" type="text" placeholder="Summary/Title of change">
						</td>
					</tr>
					
					
					<tr>
						<td>
							<textarea name="details" placeholder="Details of change" style="width:80%;height:90px;"></textarea>
						</td>
					</tr>
					
					<tr>
						<td>
						<hr>
						
							
							<table width="100%">
								<tr>
									<td width="100%" colspan="2">
										<b>Receive completion progress update via specified email</b><br/><br/>
									</td>
								</tr>
								<tr>
									<td width="5%" style="vertical-align:top"><input name="track_progress" type="checkbox" data-toggle="toggle" data-onstyle="primary"></td>
									<td width="95%" style="vertical-align:top"><input name="email" value="{!! Auth::user()->email_address !!}" type="email" placeholder="Email address"></td>
								</tr>
							</table>
							
							
							
						</td>
					</tr>
					
					
			</table>
			
			
			
	  </div>
	  
	  <div class="modal-footer">
		<!--<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>-->
		<button type="submit" class="btn btn-primary" style="padding:2%">Submit</button>
		</form>
	  </div>
	  
	  
	</div>

	</div>
	</div>

</form>

{!! HTML::style('resources/js/bootstrap-toggle-master/css/bootstrap2-toggle.min.css') !!}
{!! HTML::script('resources/js/bootstrap-toggle-master/js/bootstrap2-toggle.min.js') !!}
