
</div>

<div><br/></div>
<h1>Advanced search</h1>


<div class="admin2">

	<form action="" method="POST">
	
	@if($results)
	<table width="100%" class="">
		<tr>
			<td>
				<h3>{{$results->count()}} Results found</h3>
				@if($searched_by) <h5>Searched by: {{ $searched_by }}</h3> @endif
			</td>
		</tr>
		@foreach($results as $r) 
		<tr>
			<td>
				<h4><a href="{{ URL::to('customer_tabview_controller/show', ['customer_id' => $r->id]) }}"> Customer {{ $r->id }} - {{ $r->username }} </a></h4>
			</td>
		</tr>
		@endforeach
	</table>
	<hr/>
	@endif
	
	<table width="100%" class="table table-bordered">
	
		
		<tr>
			<td>
				<b>Meter ID</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="meter_ID" value="" placeholder="Meter ID">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Permanent Meter ID</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="pmd_ID" value="" placeholder="Permanent Meter ID">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Username</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="username" value="" placeholder="Username e.g aidan62">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Barcode</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="barcode" value="" placeholder="Barcode e.g 9826004400000000025">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Mobile number</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="mobile_number" value="{{ Input::old('mobile_number') }}" placeholder="Mobile number e.g 0867267392">
			</td>
		</tr>
		
		
		<tr>
			<td>
				<b>Email</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="email" value="{{ Input::old('email') }}" placeholder="Email address">
			</td>
		</tr>
		
	</table>
	
	<table width="100%"  class="table table-bordered">
		
		<tr>
			<td>
				<b>Firstname</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="first_name" value="{{ Input::old('first_name') }}" placeholder="Firstname e.g John">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Surname</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="surname" value="{{ Input::old('surname') }}" placeholder="Surname e.g Doe">
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Custom search</b>
			</td>	
		</tr>
		<tr>
			<td>
				<input style='width:98%' type="text" name="custom" value="{{ Input::old('custom') }}" placeholder="e.g 'email_address = test@gmail.com'">
			</td>
		</tr>
		
		
	</table>
	
	<table width="100%">
		<tr>
			<td>
				<button style='width:100%' type="submit" class="btn btn-primary">Search</button>
			</td>
		</tr>
	</table>
	
	</form>
	
</div>