
</div>

<div><br/></div>
<h1>Campaign Edit {{ $campaign->id }}: {{ $campaign->title }}</h1>


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
		<a href="{{ URL::to('campaigns') }}">
		<button class="btn btn-primary"> <i class="fa fa-long-arrow-alt-left"></i> View all campaigns </button>
		</a>
		</div>
		
		<div class="span6 float-right" style="text-align: right">
		<a href="{{ URL::to('campaigns/view', ['id' => $campaign->id]) }}">
						<button class="btn btn-success" type="button"><i class="fa fa-eye"></i> Preview</button>
					</a>
		</div>
		
	</div>
	
	<hr/>
	
	<form action="" method="POST" class="form form-control">
			<div class="row-fluid">
			<div class="span12 pull-right">
				<input type="text" name="show_at" class="form-control" value="{{ $campaign->show_from }}" placeholder="Show at">
				<input type="text" name="stop_show_at" class="form-control" value="{{ $campaign->show_to }}" placeholder="Stop show at">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>Image:</h5>
			</div>
			<div class="span6">
				<input type="text" name="img" value="{{ $campaign->icon_img }}" class="form-control" placeholder="Image">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>* Title:</h5>
			</div>
			<div class="span6">
				<input type="text" name="title" class="form-control" value="{{ $campaign->title }}" placeholder="">
			</div>
			<div class="span6">
				<input type="text" name="teaser" class="form-control" value="{{ $campaign->teaser }}" placeholder="Teaser">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2">
				<h5>* Body:</h5>
			</div>
			<div class="span9">
				<textarea placeholder=""  id="body" name="body" style="width:110%;height:200px;">{{ $campaign->body }}</textarea>
			</div>
		</div>
		<hr/>
		
		<div class="row-fluid">
			<div class="pull-right"> 
				<input type="hidden" name="campaign_id" value="{{ $campaign->id }}"/>
				<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save campaign</button> 
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
<script>
// $( function() {
		
		// if (document.readyState == 'loading') {
  // alert('loading');
  // document.addEventListener('DOMContentLoaded', work);
// } else {
  // alert('done')
// }
</script>
<link rel="stylesheet" href="/resources/js/sceditor/minified/themes/default.min.css" />
<script src="/resources/js/sceditor/minified/sceditor.min.js"></script>
@endsection

