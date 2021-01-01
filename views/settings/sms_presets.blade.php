<br />
<div class="cl"></div>
<h1><i class='fa fa-sms'></i> SMS Presets - Settings</h1>

<div class="admin">

@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('successMessage')}}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('errorMessage')}}
</div>
@endif


	 
	   <ul class="nav nav-tabs" style="margin: 30px 0">
		
		   @foreach($categories as $k => $c)
		   <li @if($k == 0) class="active" @endif><a href="#{{ strtolower(str_replace(' ', '', $c->category)) }}" data-toggle="tab"><i class='fa fa-list'></i> {{ $c->category }}</a></li>
		  @endforeach
		  
	   </ul>
	  
		<div class="tab-content">

		
		@foreach($categories as $k => $c)
		
			
			
				<div class="tab-pane @if($k == 0) active @endif" id="{{ strtolower(str_replace(' ', '', $c->category)) }}" style="text-align: left">	

				
				<table class="table table-bordered">
		
				<tr>
					<th colspan="4">
						<h4><i class='fa fa-list'></i> Category - {{ $c->category }}</h4>
					</th>
				</tr>
				<tr>
					<th width='5%'><b>Category</b></th>
					<th width='25%'><b>Name</b></th>
					<th width='50%'><b>Body</b></th>
					<th width='20%'><b>Edit</b></th>
				</tr>
				
			@foreach($c->getPresets() as $s)
			<tr>
			
			<form action="{{URL::to('settings/sms_presets/save', $s->id)}}" method="POST">
				<td>
				
				
					<select name='category'>
						<option>{{ $s->category }}</option>
						@foreach($categories as $k => $c)
							@if($c->category == $s->category)
								
							@else
								<option>{{ $c->category }}</option>
							@endif
						@endforeach
					</select>
						
					<!--
					<input name="category" type="text" value="{{$s->category}}" placeholder="Category">
					-->
				
					
				</td>
				<td>
					<input type='text' name="name" style="width:70%" value='{{$s->name}}' placeholder="Preset Name">
				</td>
				<td>
					<textarea name="body" style="width:90%;height:7rem;" placeholder="Preset Body">{{$s->body}}</textarea>
				</td>
				<td>
					
					<a href="{{URL::to('settings/sms_presets/remove', $s->id)}}">
						<button type="button" class="btn btn-danger">Delete</button>
					</a>
					
					<button type="submit" class="btn btn-success">Save</button>
					
				
				</td>
			</form>
			
			
			</tr>
			
		@endforeach
			
		<tr>
			<td colspan='4'><b style='color:#08c;'> <i class='fa fa-plus-square'></i> Add new preset to: &nbsp; <i class='fa fa-list'></i> {{ $s->category }}</b> </td>
		</tr>	
		<tr>
			
			<form action="{{ URL::to('settings/sms_presets/add') }}" method="POST">
				
				<input name="category" type="hidden" value="{{$s->category}}" placeholder="Category">
				
				
				<td style='vertical-align:middle;text-align:center;' width="20%">
					<input name="name" type='text' style="width:70%" placeholder="Preset Name"></textarea>
				</td>
				<td style='vertical-align:middle;text-align:center;' width="60%" colspan='2'>
					<br/><textarea name="body" style="width:97%" placeholder="Preset Body"></textarea>
				</td>
				<td style='vertical-align:middle;text-align:center;' width="20%" style='vertical-align:middle;'>
					
				<center><button type="submit" class="btn btn-success"><i class='fa fa-plus'></i></button></center>
				
				</td>
			</form>
		
		</tr>
			
				</table>
				
				
				</div>
				
		@endforeach
		
		</div>
		
		<hr/>
		
		<table width="100%" class="table table-bordered">
			
			<tr>
			<td colspan='4'><b style='color:#00cc9d;'> <i class='fa fa-plus-square'></i> Create a <u>new</u> category with it's own presets </b> &horbar; <i style='color:#666'>If your preset matches any categories above, consider creating it under one of them instead.</i> </td>
		</tr>	
		<tr>
			
			<form action="{{ URL::to('settings/sms_presets/add') }}" method="POST">
				
				<td style='vertical-align:middle;text-align:center;'width="30%">
					<input name="category" type='text' style="width:70%" placeholder="Category Name"></textarea>
				</td>
				<td style='vertical-align:middle;text-align:center;'width="20%">
					<input name="name" type='text' style="width:70%" placeholder="First Preset Name"></textarea>
				</td>
				<td style='vertical-align:middle;text-align:center;' width="50%" colspan='1'>
					<br/><textarea name="body" style="width:97%" placeholder="First Preset Body"></textarea>
				</td>
				<td width="10%" style='vertical-align:middle;text-align:center;'>
					
					<center><button type="submit" class="btn btn-success"><i class='fa fa-plus'></i></button></center>
				
				</td>
			</form>
		
		</tr>
		
		</table>
	

<div class="admin2">
	
</div>
