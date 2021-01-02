<div id="prepago_services" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
	<style>
		#services_list {
			//background: #f9f9f9;
			width: 60%;
			padding: 30px;
			border-radius: 3px;
			font-size:12px;
		}
		.start_btn, .stop_btn, .output_btn, .restart_btn {
			padding: 13px;
			color: white;
			background: #5fbf2c;
			width: 148px;
			margin:0px;
			border-radius: 3px;
			/*float:right;*/
			cursor: pointer;
		}
		.restart_btn{background: #006dcc; !Important;}
		.stop_btn{background: #bf412c !Important;}
		.output_btn{background: #507fbb !Important;}
		.restart_btn.disabled, .start_btn.disabled, .stop_btn.disabled, .output_btn.disabled {
			opacity: 0.1;
			cursor: not-allowed;
		}
		.usage {		
			background:#e67575;
			display:inline-block;
			border-radius:3px;
			height:8px;
			width:0%;
		}
		.usage_container {
			background:white;
			display:inline-block;
			border-radius:3px;
			height:10px;
			line-height:10px;
			width:100px;
			border:1px solid black;
		}
		
		.running_svc, .offline_svc {
			padding-bottom: 5%;
			border-bottom: 1px solid #cccccc38;
			font-size: 15px;
			margin-bottom: 7%;
			text-shadow: 0px 0px 1px #0000003b;
		}
		.running_svc {
			color: green;
		}
		.offline_svc {
			color: red;
		}
		.mini_info {
			margin-left: -1%;
			display: inline-block;
			border-radius: 3px;
			height: 14px;
			text-align: center;
			width: 43%;
			color: white;
			padding: 2%;	
			width: 71px;			
		}
		.running_since{
			background: #000;
			width: 160px;	
		}
		.usage_mem{
			background: #88acff;
		}
		.usage_cpu{
			background: #f88;
		}
	</style>
	
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Manage Prepago Services<div class='pull-right'>
		<button type="button" id="refresh_services" class="btn btn-primary"><i class="fa fa-sync"></i> Refresh</button>
		</div>
      </div></h4>
		
	 
      <div class="modal-body">
        <table width='100%'>
		
		<tr>
			
			<td>
			<div id="services_list_container">
				
			<table id="services_list" width='100%'>
					
			
			</table>
			
			</div>
			</td>
			
			
		</tr>
		
		</table>
		
      </div>
	  
	  
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
	  
	  
    </div>

  </div>
</div>