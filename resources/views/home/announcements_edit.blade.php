
</div>

<div><br/></div>
<h1>Announcement Edit {{ $announcement->id }}: {{ $announcement->title }}</h1>


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
	
	
	<div class="row-fluid">
		
		<div class="span6">
		<a href="{{ URL::to('announcements/all') }}">
		<button class="btn btn-primary"> <i class="fa fa-long-arrow-alt-left"></i> View all announcements </button>
		</a>
		</div>
		
		<div class="span6 float-right" style="text-align: right">
		<a href="{{ URL::to('announcements/view', ['id' => $announcement->id]) }}">
						<button class="btn btn-success" type="button"><i class="fa fa-eye"></i> Preview</button>
					</a>
		</div>
		
	</div>
	
	<hr/>
	
	<form action="" method="POST" class="form form-control">
			<div class="row-fluid">
			<div class="span12 pull-right">
				<input type="text" name="show_at" class="form-control" value="{{ $announcement->show_at }}" placeholder="Show at">
				<input type="text" name="stop_show_at" class="form-control" value="{{ $announcement->stop_show_at }}" placeholder="Stop show at">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>Image:</h5>
			</div>
			<div class="span6">
				<input type="text" name="img" value="{{ $announcement->img }}" class="form-control" placeholder="Image">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>* Title:</h5>
			</div>
			<div class="span6">
				<input type="text" name="title" class="form-control" value="{{ $announcement->title }}" placeholder="">
			</div>
			<div class="span6">
				<input type="text" name="teaser" class="form-control" value="{{ $announcement->teaser }}" placeholder="Teaser">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>* Body:</h5>
			</div>
			<div class="span9">
				<textarea placeholder=""  id="body" name="body" style="width:110%;height:200px;">{{ $announcement->body }}</textarea>
			</div>
		</div>
		<hr/>
		
		<div class="row-fluid">
			<div class="pull-right"> 
				<input type="hidden" name="announcement_id" value="{{ $announcement->id }}"/>
				<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save announcement</button> 
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
<link rel="stylesheet" href="/resources/js/sceditor/minified/themes/default.min.css" />
<script src="/resources/js/sceditor/minified/sceditor.min.js"></script>
@endsection

