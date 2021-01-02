
</div>

<div><br/></div>
<h1>Create notification</h1>


<div class="admin2">

	@if($message = Session::get('successMessage'))
	<div class="alert alert-success">
	{!! $message !!}
	</div>
	@endif
	
	@if($message = Session::get('errorMessage'))
	<div class="alert alert-danger">
	{!! $message !!}
	</div>
	@endif
	
	<a href="{!! URL::to('notifications/all') !!}">
	<button class="btn btn-primary"> <i class="fa fa-bell"></i> All Notifications </button>
	</a>
	
	<hr/>
	
	<form action="" method="POST" class="form form-control">
		<div class="row-fluid">
			<div class="span2">
				<h5>Image:</h5>
			</div>
			<div class="span9">
				<input type="text" style='width:100%;' value="default" name="image" class="form-control" placeholder="Image. Type ' default ' for default image.">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>Additional Button Text:</h5>
			</div>
			<div class="span9">
				<input type="text" style='width:100%;' name="dismiss_txt" class="form-control" placeholder="Additional Button Text e.g Check it out!">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>Additional Button URL:</h5>
			</div>
			<div class="span9">
				<input type="text" style='width:100%;' name="dismiss_txt_url" class="form-control" placeholder="Additional Button URL. The Url the Additional button will open.">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>* Title:</h5>
			</div>
			<div class="span6">
				<input type="text" name="title" class="form-control" placeholder="Title">
			</div>
		</div>

		<div class="row-fluid">
			<div class="span2">
				<h5>* Body:</h5>
			</div>
			<div class="span9">
				<textarea placeholder="Type your announcement body here" id="body" name="body" style="width:110%;height:200px;"></textarea>
			</div>
		</div>
		<hr/>
		
		<div class="row-fluid">
			<div class="pull-right"> 
				<button type="submit" class="btn btn-success"><i class="fa fa-bell"></i> Create notification </button> 
			</div>
		</div> 
	</form>
	
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
<link rel="stylesheet" href="resources/js/sceditor/minified/themes/default.min.css" />
<script src="resources/js/sceditor/minified/sceditor.min.js"></script>
@endsection


