
</div>

<div><br/></div>
<h1><a href="/campaigns">Campaigns</a> &gt; Create</h1>


<div class="admin2">
	
	
	@if(Session::has('successMessage'))
	<div class="alert alert-success">
	{!! Session::get('successMessage') !!}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger">
	{!! Session::get('errorMessage') !!}
	</div>
	@endif
	
	
	<style>
		input, textarea{
			padding: 1% 3% 1% 3% !important;
			font-size:1.4rem !important;
			width: 70%;
		}
	</style>
	
	<table width="100%">
		<form action="" method="POST">
		<tr>
			
			<td width="50%" style="vertical-align:top;">
				<table width="100%">
					<tr>
						<td colspan='2'><button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Create</button>
						<br/></br></td>
					</tr>
					<tr>
						<td colspan='2'><font style="font-weight:bold;font-size:0.9rem;">Schedule</font></td>
					</tr>
					<tr>
						<td width="50%"><input  type="text" id="from" style="font-size:0.9rem !important;" name="from"></td>
						<td width="50%"><input type="text" id="to" style="font-size:0.9rem !important;" name="to"></td>
					</tr>
					<tr>
						<td colspan='2'><font style="font-weight:bold;font-size:1.5rem;">Title</font></td>
					</tr>
					<tr>
						<td colspan='2'><input type="text" name="title"></td>
					</tr>
					<tr>
						<td colspan='2'><font style="font-weight:bold;font-size:0.9rem;">Teaser</font></td>
					</tr>
					<tr>
						<td colspan='2'><input type="text" style="font-size:0.9rem !important;" name="teaser"></td>
					</tr>
					<tr>
						<td colspan='2'><font style="font-weight:bold;font-size:0.9rem;">Teaser image (.png, .jpg/.jpeg or .gif)</font></td>
					</tr>
					<tr>
						<td colspan='2'><input type="text" style="font-size:0.9rem !important;" name="img"></td>
					</tr>
					<tr>
						<td colspan='2'><hr/></td>
					</tr>
					<tr>
						<td colspan='2'>
						
						<input name="create_notif" checked style="width:30px;height:30px;" type="checkbox">
							<font style="margin-left:5%;line-height:3px;font-weight:bold;font-size:1rem;">
							Create an in-app notification<br/><br/>
							</font>
						</td>
					</tr>
					<tr>
						<td colspan='2'><font style="font-weight:bold;font-size:0.9rem;">Button text</font></td>
					</tr>
					<tr>
						<td colspan='2'><input value="Read more" type="text" style="font-size:0.9rem !important;" name="notif_btn_txt"></td>
					</tr>
					
				</table>
			</td>
			<td width="50%" style="vertical-align:top;">
				<table width="100%">
					<tr>
						<td><font style="font-weight:bold;font-size:1.5rem;">Body</font></td>
					</tr>
					<tr>
						<td><textarea placeholder=""  id="body" name="body" style="width:100%;height:40rem;"></textarea></td>
					</tr>
				</table>
			</td>
		</tr>
		</font>
	</table>
	
</div>


<script src="resources/js/sceditor/minified/formats/xhtml.js"></script>
<script type="text/javascript">
// Replace the textarea #example with SCEditor
$(function(){
	//
	var textarea = document.getElementById('body');
	sceditor.create(textarea, {
		format: 'xhtml',
		style: 'resources/js/sceditor/minified/themes/content/default.min.css'
	});
});
</script>
<style>
.ui-datepicker {
    margin-left: 161px;
    margin-top: -16%;
}
</style>
<script>
	$( function() {
		
		var dateFormat = "yy-mm-dd",
		from = $( "#from" )
		.datepicker({
		  defaultDate: "+1w",
		  changeMonth: true,
		  numberOfMonths: 1,
		  dateFormat: dateFormat,
		})
		.on( "change", function() {
		  to.datepicker( "option", "minDate", getDate( this ) );
		}),
		to = $( "#to" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat: dateFormat,
		})
		.on( "change", function() {
		from.datepicker( "option", "maxDate", getDate( this ) );
		});
		
		function getDate( element ) {
		var date;
		try {
		date = $.datepicker.parseDate( dateFormat, element.value );
		} catch( error ) {
		date = null;
		}

		return date;
		}
	} );
</script>
@section('extra_scripts')
<link rel="stylesheet" href="/resources/js/sceditor/minified/themes/default.min.css" />
<script src="/resources/js/sceditor/minified/sceditor.min.js"></script>
@endsection
