
</div>

<div><br/></div>
<h1>Remote Program Execution</h1>


<div class="admin">

@if(Session::has('successMessage'))
<div class="alert alert-success">
		
		{{Session::get('successMessage')}}

</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger">
		
		{{Session::get('errorMessage')}}

</div>
@endif

<ul class="nav nav-tabs" style="margin: 30px 0">
	<li class="active"><a href="#view_remote_executions" data-toggle="tab">View Remote Executions</a></li>
	<li><a href="#create_remote_execution" data-toggle="tab">Create Remote Execution</a></li>
</ul>

</div>

<div class="admin2">
	<div class="tab-content">
   
	
			 
	
		 <div class="tab-pane active" id="view_remote_executions">
				
			<h4> Remote Program Executions currently running </h4>
			
			<table class="table table-bordered">
				
				
				<tr>
					<td width="30%"><b>Program</b></td>
					<td width="10%"><b>Run type</b></td>
					<td width="20%"><b>Run after</b></td>
					<td width="20%"><b>Run for</b></td>
					<td width="10%"><b>Processed</b></td>
					<td width="10%"><center><b><i class="fa fa-cogs"></i></b></center></td>
				</tr>
				
				@foreach($calendar_remote_executions as $execution)
					
					<tr><form action="{{ URL::to('system_programs/remote_execution/stop') }}" method="POST">
						<td valign="middle">{{$execution->program}}</td>
						<td valign="middle">{{$execution->run_type}}</td>
						<td valign="middle">{{$execution->run_after}}</td>
						<td valign="middle">{{$execution->running_for}}</td>
						<td valign="middle">{{$execution->processed}}</td>
						<td>
						
							<input type='hidden'  value='{{$execution->id}}' name='remote_id'>
							<center><button type="submit" class="btn btn-danger"> Stop</button></center>
						
						</td>
						</form>
					</tr>	
				
				@endforeach
				
			</table>
			
						
		 </div>	
		 
	<div class="tab-pane" id="create_remote_execution">
				
	<table width="100%">
		
		<tr>		
			<td valign="top">	
				<table width="100%">
					<tr>
						<td><h3>Select program to run</h3></td>
					</tr>
					<tr>
					<form action="{{ URL::to('system_programs/remote_execution') }}" method="POST">
						<td class="program_choice">
							<input type="radio" name="program_to_run" checked="true" value="Billing Engine" id="p1"/><label for="p1">Billing Engine</label>
						</td>
					</tr>
					<tr>
						<td class="program_choice">
							<input type="radio" name="program_to_run" value="Shut-Off Engine" id="p2"/><label for="p2">Shut-Off Engine</label>
						</td>
					</tr>
					<tr>
					
					</tr>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td class="program_choice">
							<h3>Delay execution</h3>
						</td>
					</tr>
					<tr>
						<td class="program_choice">
							<input type="text" name="program_execute_delay" value="0" placeholder="x seconds"/>
						</td>
					</tr>
					<tr>
						<td>
							<br/><br/>
						
						<button type="submit" class="btn btn-primary" style="padding:3%"><i class="fa fa-play"></i> Execute Program</button>

						</td>
					</tr>
				</table>	
			</td>
			
			<td valign="top">
					
				<table width="100%">
					<tr>
						<td><h3>Run Type</h3></td>
					</tr>
					<tr>
						<td class="program_choice">
							<input type="radio" name="program_runtype" checked="true" value="1" id="r1"/><label for="r1">1</label><br/>
							<input type="radio" name="program_runtype" value="2" id="r2"/><label for="r2">2</label>	
						</td>
					</tr>
				</table>	
					
			</td>
			
				<td valign="top">
					
				<table width="100%">
					<tr>
						<td><h3><center>Run For</center></h3></td>
					</tr>
					<tr>
						<td class="program_choice">
							<input type="radio" name="program_runfor" checked="true" value="all" class="run_for" id="f1"/><label for="f1">All</label><br/>
							<input type="radio" name="program_runfor" value="customer" class="run_for" id="f2"/><label for="f2">Customer</label><br/>	
							<input type="radio" name="program_runfor" value="scheme" class="run_for" id="f3"/><label for="f3">Scheme</label>	
						</td>
					</tr>
					<tr>
						<td>
						<br/>
						<center><select style="" name="program_scheme">
						<option>
							-- Select a Scheme --
						</option>
							@foreach($schemes as $scheme)
								<option value="{{$scheme->id}}">
									{{ $scheme->company_name }}
								</option>
							@endforeach
						</select>
						<hr>
						
						<input type="text" name="program_customer" placeholder="Customer ID"/>
					</form>
						</center>
						</td>
						
					</tr>
				</table>	
					
			</td>
		</tr>
	</table>
	 </div>
	
	 
	 </div>	
</div>

<style>
	.program_choice{
		font-size:1.2em;
	}
	input[type=radio]{
		transform: scale(1);
		margin-right: 10px;
	}
	
	input[type='radio'] {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        outline: none;
        border: 3px solid gray;
    }

    input[type='radio']:before {
        content: '';
        display: block;
        width: 60%;
        height: 60%;
        margin: 20% auto;
        border-radius: 50%;
    }

 input[type="radio"]:checked:before {
        background: green;
        
    }
	label{
		display: inline-block;
		font-size:1em;
	}
</style>

