<?php
   $current_page = $_SERVER['REQUEST_URI'];
   if(strpos($current_page, 'customer_tabview') !== false)
   	$current_page = 'customer_tabview';
   ?>
   <div id="guarddog" class="modal fade" role="dialog">
      <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
            <div class="modal-header" style="padding: 9px 15px 0px 15px;  border-bottom: 0px solid #eee;">
               <button type="button" class="close" data-dismiss="modal">&times;</button>
			   <h3> Guard Dog <span class='gd_title'></span></h3>
            </div>
            <div class="modal-body">
			<ul class="nav nav-tabs" style="">
				  <li class="active"><a href="#topups" data-toggle="tab">Topups</a></li>
				  <li><a href="#log" data-toggle="tab">Log</a></li>
			  </ul>
			  <div class="tab-content">
				<div class="tab-pane active gd_topups" id="topups" style="">
					
				</div>
				
				<div class="tab-pane gd_log" id="log" style="">
			
				</div>
				
			  </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-danger gd_stop">Stop</button>
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
