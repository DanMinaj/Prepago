	
<div id="permissions-help" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Permissions groups information</h4>
	  </div>
	 
	  <div class="modal-body">
				
		
	  <div class="well">
		This is a list of all the groups & their corresponding permissions.
	  </div>
	
			@foreach(Group::all() as $group) 
			
				<?php 
					$permissions = $group->permissions;
				?>
				
				<h4> {!! $group->name !!} permissions: </h4>
				<table width="100%">
					
					@if(count($permissions) == 0)
						<tr>
							<td>None / not-applicable for this group.</td>
						</tr>
					@endif
					
					@foreach($permissions as $p)
						<tr>
							<td>{!! $p !!}</td>
						</tr>
					@endforeach					
				
				</table>
			
			@endforeach
	
			
	  </div>
	  
	  <!--
	  <div class="modal-footer">
		<button type="submit" class="btn btn-primary">Submit</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	  -->
	  
	</div>

</div>
</div>