</div>
<div><br/></div>
<h1>Valve Control Test</h1>
<div class="admin">
   @include('includes.notifications')
  
   <table width="100%">
	<tr>
		<td>
		<br/>
		<b>Running tests:</b> {{ count($running_tasks) }}
		</td>
	</tr>
   </table>
   
   <ul class="nav nav-tabs" style="margin: 30px 0">
      <li class="active"><a href="#1" data-toggle="tab">Running Tests ({{ count($running_tasks) }})</a></li>
   </ul>
   <div class="tab-content">
     
	 <div class="tab-pane active" id="1" style="">
		 @foreach($running_tasks as $k => $tasks)
		 <h3> {{ Scheme::find($tasks[0]->scheme_number)->scheme_nickname }} Test </h3>
			<table width="100%" class="table table-bordered">
				<thead>
					<tr>
						<th><b>Username</b></th>
						<th><b>Stage</b></th>
						<th><b>Waiting for..</b></th>
						<th><b>Log</b></th>
					</tr>
				</thead>
				<tbody>
				@foreach($tasks as $k => $t) 
					<tr>
					
						<td>
						{{ $t->username }}
						</td>
						<td>
						{{ $t->step }}
						</td>
						<td>
						{{ $t->expected_to }}
						</td>
						<td>
						@if(!empty($t->getLog()))
						@foreach($t->getLog() as $k => $log) 
						{{ $log }} <br/>
						@endforeach
						@else
							-
						@endif
						</td>
						
					</tr>
				@endforeach
				</tbody>
			</table>
		 @endforeach
	 </div>

   
   </div>
</div>
<input type="hidden" id="baseInstallerURL" value="{{ URL::to('prepago_installer') }}">
{{ HTML::script('resources/js/util/remote_control_panel.js?jsjsjs') }}