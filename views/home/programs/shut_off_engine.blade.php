
</div>

<div><br/></div>
<h1>Shut-off Engine Manager</h1>


<div class="admin2">
	
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

	<form action="" method="POST">
	<input id="holiday_days" name="holiday_days" type="hidden" value={{json_encode($holiday_days)}}>
	<table width="100%">
		<tr>
		
			<td style="vertical-align:top" width="60%">
				<h4> Holiday Mode </h4> Prevent shut off on 'holiday' classified days
				<br/>
				<input name="holiday_enabled" type="checkbox" @if($holiday_enabled) checked='true' @else @endif data-toggle="toggle" data-onstyle="primary">
			</td>
			<td style="vertical-align:top" width="40%">
				<h4> Holiday Days </h4>
				<table id="holiday_table" class="table table-bordered" width="100%">
					<tr>
						<th> Name </th>
						<th> Date </th>
						<th> <i class="fa fa-pencil-alt"></i> </th>
					</tr>
					@if(empty($holiday_days))
						<tr>
							<td colspan="3"><center>Currently none</center></td>
						</tr>
					@else
						@foreach($holiday_days as $key => $day)
						<tr class="day" day_name="{{ str_replace(' ', '', ($day->name)) }}">
							<td> {{ $day->name }} </td>
							<td> {{ $day->date }} </td>
							<td> <i day_name="{{ str_replace(' ', '', ($day->name)) }}" style='color:red;cursor:pointer;' class="fa fa-minus-circle holiday_day_remove"></i> </td>
						</tr>
						@endforeach
					@endif
				</table>
				<table class="table table-bordered" width="100%">
					<tr>
						<td> <input id="new_day_name" type="text"> </td>
						<td> <input id="new_day_date" type="text"> </td>
						<td> <i id="holiday_day_add" style='color:green;cursor:pointer;' class="fa fa-plus-circle"></i> </td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<script>
		$(function(){
			
			var days = 0;
			var holiday_days_arr = [];
			
			// Add day ID attribute to each day table row
			$('.day').each(function(key, val){			
				
				var new_day_obj = {
					name: ($(this).children('td').first().text()).split(' ').join(''),
					date: ($(this).children('td').first().next().text()).split(' ').join(''),
				};	
				holiday_days_arr.push(new_day_obj);
				//console.log(holiday_days_arr)
			});
			
			$('#holiday_day_add').on('click', function(){
				
				days++;
				
				var new_day_name_div = $('#new_day_name');
				var new_day_date_div = $('#new_day_date');
				
				
				var new_day_obj = {
					name: (new_day_name_div.val()).split(' ').join(''),
					date: (new_day_date_div.val()).split(' ').join(''),
				};
				
				var new_entry = "";
				new_entry += "<tr class='day' day_name='" + new_day_obj.name + "'>";
				new_entry += "<td>" + new_day_obj.name + "</td>";
				new_entry += "<td>" + new_day_obj.date + "</td>";
				new_entry += "<td><i day_name='" + new_day_obj.name + "' style='color:red;cursor:pointer;' class='fa fa-minus-circle holiday_day_remove'></i></td>";
				new_entry += "</tr>";
				
				new_day_name_div.val('');
				new_day_date_div.val('');
				
				holiday_days_arr.push(new_day_obj);
				
				$('#holiday_days').val(JSON.stringify(holiday_days_arr));
				
				//console.log(holiday_days_arr)
				
				$('#holiday_table').append(new_entry);
			});
			
			$('.holiday_day_remove').on('click', function(){
				
				var day_name = ($(this).attr('day_name'));
				var day_row = $('tr[day_name=' + (day_name).split(' ').join('') + ']');
				var key_remove = 0;
				
				$.each(holiday_days_arr, function(key, val){
					var n = (val.name).split(' ').join('');
					var d = (day_name).split(' ').join('');
					//console.log(n)
					
					if(n == d)
					{
						key_remove = key;
					}
				});
				
				
				holiday_days_arr.splice(key_remove, 1)
				
				day_row.hide();
				
				$('#holiday_days').val(JSON.stringify(holiday_days_arr));
			});
		});
	</script>
	
	<hr>
	<table width="100%">
		<tr>
			<td>
				<button type="submit" class="btn btn-success">Save changes</button>
			</td>
		</tr>
	</table>
	</form>
	
</div>
