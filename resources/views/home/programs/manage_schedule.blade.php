
</div>

<div><br/></div>
<h1>System Programs Management</h1>


<div class="admin2">


@if(Session::has('successMessage'))
<div class="alert alert-success">
		
		{!!Session::get('successMessage')!!}

</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger">
		
		{!!Session::get('errorMessage')!!}

</div>
@endif


<div class='admin2'> </div>

<form action='' method='POST'>

<a href="{!! URL::to('admin/specialist') !!}"><button type="button" class="btn btn-primary">&lt;&lt; Go back</button></a>
<br/><br/>
<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Save changes</button>
<br/>
<br/>
<table id="schedule_table" class="table table-bordered">
				
				<tr id="first">
				
					<!--<td width="10%"><b>Switch</b></td>-->
					
					<td width="30%"><b>Time</b></td>
					<td width="10%"><b>Program</b></td>
					<td width="20%"><b>Run-type</b></td>
					<td width="5%"><b>Run on weekends</b></td>
					<td width="15%"><b>Actions</b></td>
					
				</tr>
				
				
				
				@foreach($daily_schedules as $schedule)
					<tr {!! $schedule->rowColour !!} >
							<!--
							<td><i s_id="{!!$schedule->id!!}" class="fa fa-arrow-up switch-up"></i> &nbsp;&nbsp; <i onclick="switchDown(this)" class="fa fa-arrow-down switch-down"></i></td>
							-->
							<td> <input name="input1_{!!$schedule->time!!}" type="text" value='{!!$schedule->time!!}' /></td>
							<td>
								<select name="input2_{!!$schedule->time!!}">
									<option value="{!!$schedule->program!!}">{!!$schedule->program!!}</option>
								</select>
							</td>
							
							<td> <input name="input3_{!!$schedule->time!!}" type='text' value='{!!$schedule->run_type!!}' /> </td>	
							
							<td> <input name="input4_{!!$schedule->time!!}" type='text' value='{!!$schedule->run_on_weekend_and_holiday!!}' /> </td>
							
							<td>
								<i style='color:red;cursor:pointer;' class="fa fa-minus-circle"></i>
							</td>
							
						</tr>
				@endforeach
				
				
				
			
				
</table>
</form>
<button onclick='addNewProgram()' type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Add new </button>
</div>

<script>

	var new_prog_count = 0;
	var program_options = "";
	program_options += "<option value='tc'>Temperature Control</option>";
	program_options += "<option value='b'>Billing Engine</option>";
	
	$('.switch-up').on('click', function(){
		
		alert()
		
	});
	
	function addNewProgram() {
	
		var new_program = "<tr>";
		new_program += "<td> <input name='new_time_" + new_prog_count + "' type='text' value='06:00:00' placeholder='00:00:00'/> </td>";
		new_program += "<td> <select name='new_program_" + new_prog_count + "'> " + program_options + " </select> </td>";
		new_program += "<td> <input name='new_run_type_" + new_prog_count + "' type='text' value='1' placeholder='1'/> </td>";
		new_program += "<td width='5%'> <input name='new_run_on_weekend_and_holiday_" + new_prog_count + "' type='text' placeholder='0 or 1' value='1'/> </td>";
		new_program += "<td>-</td>";
		new_program += "</tr>";
		
		$('#schedule_table').append(new_program);

		new_prog_count++;
	}
	
</script>