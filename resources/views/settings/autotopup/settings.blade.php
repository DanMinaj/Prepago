
</div>

<div><br/></div>
<h1>Autotopup Settings</h1>

<div class="admin2">

	
<br/><br/>
@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

<b> Auto topup scheduler ran at: </b> 
{!! (strlen(SystemStat::get('last_autotopup_schedule')) > 2) ? SystemStat::get('last_autotopup_schedule') : '' !!}
&horbar; ({!! (strlen(SystemStat::get('last_autotopup_schedule')) > 2) ? Carbon\Carbon::parse(SystemStat::get('last_autotopup_schedule'))->diffForHumans() : '' !!})

		<ul class="nav nav-tabs" style="margin: 30px 0">
		<li class="active"><a href="#signup_material" data-toggle="tab">Signup</a></li>
		<li><a href="#terms" data-toggle="tab">Terms</a></li>
        <li><a href="#sub_variables" data-toggle="tab">Subscription variables</a></li>
		</ul>
		
	     <div class="tab-content">   
			<div class="tab-pane active" id="signup_material" style="text-align: left">	
				<form action="{!! URL::to('settings/autotopup/edit_signup') !!}" method="POST">
					<div class="row-fluid">
						<div class="span12">
							<input type="text" name="title" value="{!! $autotopup_title !!}" style="width:90%" placeholder="Title">
						</div>
					</div>
					<div class="row-fluid">
						<div class="span8">
							<input type="text" name="subtitle" value="{!! $autotopup_subtitle !!}" style="width:90%" placeholder="Subtitle">
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<textarea id="body" name="body" style="width:90%;height:200px;">{!! $autotopup_body !!}</textarea>
						</div>
					</div>
					<hr/>
					<div class="row-fluid">
						<div class="span12">
							<button type="submit" class="btn btn-success">Save changes</button>
						</div>
					</div>
				</form>
			</div>
			
			<div class="tab-pane" id="terms" style="text-align: left">
				<form action="{!! URL::to('settings/autotopup/edit_terms') !!}" method="POST">
					<div class="row-fluid">
						<div class="span12">
							<textarea id="terms_body" name="terms" style="width:90%;height:200px;">{!! $autotopup_terms !!}</textarea>
						</div>
					</div>
					<hr/>
					<div class="row-fluid">
						<div class="span12">
							<button type="submit" class="btn btn-success">Save changes</button>
						</div>
					</div>
				</form>
			</div>
			
			<div class="tab-pane" id="sub_variables" style="text-align: left">	
				
				<form action="{!! URL::to('settings/autotopup/edit_vars') !!}" method="POST">
				<table width="100%" class="table table-bordered">
				
					@foreach($vars as $v)
						<tr>
							<td width="20%" style="vertical-align: middle;"><b>{!! $v->name !!}</b></td>
							<td with="80%">
								<input type="text" name="{!! $v->name !!}" value="{!! $v->value !!}" style="width:90%"/>
							</td>
						</tr>
					@endforeach
					
				</table>
				
				<table width="100%" class="table table-bordered">
					<tr>
						<td colspan="2">
							Add new variable
						</td>
					</tr>
					<tr>
						<td width="20%">
							<input type="text" name="new_var_name"/>
						</td>
						<td width="80%">
							<input type="text" name="new_var_value" style="width:90%"/>
						</td>
					</tr>
				</table>
				<div class="row-fluid">
						<div class="span12">
							<button type="submit" class="btn btn-success">Save changes</button>
						</div>
				</div>
					
				</form>
			</div>
		</div>


</div>

<script src="{!! URL::to('resources/js/sceditor/minified/formats/xhtml.js') !!}"></script>
<script type="text/javascript">
// Replace the textarea #example with SCEditor
$(function(){
	//
	var textarea = document.getElementById('body');
	var textarea2 = document.getElementById('terms_body');
	sceditor.create(textarea, {
		format: 'xhtml',
		emoticonsEnabled: false,
		style: "{!! URL::to('resources/js/sceditor/minified/themes/content/default.min.css') !!}"
	});
	sceditor.create(textarea2, {
		format: 'xhtml',
		emoticonsEnabled: false,
		style: "{!! URL::to('resources/js/sceditor/minified/themes/content/default.min.css') !!}"
	});
});
</script>
@section('extra_scripts')
<link rel="stylesheet" href="{!! URL::to('resources/js/sceditor/minified/themes/default.min.css') !!}" />
<script src="{!! URL::to('resources/js/sceditor/minified/sceditor.min.js') !!}"></script>
@endsection
