</div>
<div><br/></div>
<h1>Cronjobs
</h1>
<div class="admin">

   @include('includes.notifications')
   
   <form action="" method="POST">
   <button type="submit" class="btn btn-success">Save changes</button>
   <br/><br/>

   

	@foreach($cronjobs as $c) 
	<table class="table table-bordered" width="100%">
		<tr>
			<td style="vertical-align:middle;" width="30%">
				<input style='text-align:center;font-weight:bold;' type="text" name="existing_name_{{$c->name}}" placeholder="Name e.g Example" value="{{ $c->name }}"/>
			</td>
			<td style="vertical-align:middle;" width="30%">{{ $c->artisan_description }}</td>
			<td style="vertical-align:middle;" width="20%"><b>{{ $c->artisan_command }}</b></td>
			<td style="vertical-align:middle;" width="20%">
				<a href="{{ URL::to('settings/system_programs/run_cronjob/' . $c->name) }}" class="btn btn-primary"><i class="fa fa-play"></i></a>
			</td>
		</tr>
		@foreach($c->getTimes() as $key => $t) 
		<tr style='{{ $t->ran_today_style }}'>
			<td colspan='4'><input type="text" name="existing_time_{{ $t->name }}|{{ $t->time }}" placeholder="Time #{{ $key+1 }}" value="{{ $t->time }}"/></td>
		</tr>
		@endforeach
			
	</table>
	@endforeach

	<hr/>
	
	<table class="table table-bordered" width="100%">
	<tr>
		<td style="vertical-align:middle;" width="30%"><input type="text" name="new_name" placeholder="Name" value="{{ Input::old('new_name') }}"></td>
		<td style="vertical-align:middle;" width="30%"> <textarea name="new_artisan_description" placeholder="Description">{{ Input::old('new_artisan_description') }}</textarea></td>
		<td style="vertical-align:middle;" width="40%"><input type="text" name="new_artisan_command" placeholder="Artisan command" value="{{ Input::old('new_artisan_command') }}"></td>
	</tr>
	<tr style=''>
		<td colspan='2'><input type="text" name="new_time" placeholder="Time e.g 00:00:00" value="{{ Input::old('new_time') }}"/></td>
		<td>
			<input type="submit" class="btn btn-primary" value="Create">
		</td>
	</tr>
		
	</table>
	
   </form>
   
 </div>
