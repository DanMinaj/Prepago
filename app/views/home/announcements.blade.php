
</div>

<div><br/></div>
<h1>Announcement</h1>


<div class="admin2">

	@if(Session::has('successMessage'))
	<div class="alert alert-success">
	{{ Session::get('successMessage') }}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger">
	{{ Session::get('errorMessage') }}
	</div>
	@endif
	
	
	<a href="{{ URL::to('announcements/all') }}">
	<button class="btn btn-primary"> <i class="fa fa-long-arrow-alt-left"></i> View all announcements </button>
	</a>
	
	<hr/>
	
	<form action="" method="POST" class="form form-control">
		<div class="row-fluid">
			<div class="span2">
				<h5>Show from</h5>
			</div>
			<div class="span4">
				<input type="text" name="show_at" id="from" class="form-control" placeholder="2019-08-12">
			</div>
			<div class="span2">
				<h5>Show to</h5>
			</div>
			<div class="span4">
				<input type="text" name="stop_show_at" id="to" class="form-control" placeholder="2019-08-13">
			</div>
		</div>
		<hr/>
		<div class="row-fluid">
			<div class="span2">
				<h5>Image:</h5>
			</div>
			<div class="span6">
				<input type="text" value="default" name="image" class="form-control" placeholder="Image. Type ' default ' for default image.">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>Teaser:</h5>
			</div>
			<div class="span6">
				<input type="text" name="teaser" class="form-control" placeholder="Teaser">
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
				<button type="submit" class="btn btn-success"><i class="fa fa-bell"></i> Create announcement</button> 
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


<hr/>

<table width="100%">
	<tr>
		<td width='50%' style="vertical-align: top">
			<table width="100%">
				<tr>
					<td width='100%'>
						<h3 style='font-size:3rem;'> Aidan O'Neill </h3>
					</td>
				</tr>
			</table>
		</td>
		<td width='50%' style="vertical-align: top">
			ss
		</td>
	</tr>
</table>

