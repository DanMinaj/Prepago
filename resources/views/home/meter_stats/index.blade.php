	
@section('extra_scripts')

	{!! HTML::script('resources/js/datatable/datatables.min.js') !!}
	{!! HTML::style('resources/js/datatable/datatables.min.css') !!}
	{!! HTML::script('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js') !!}
	
@stop


</div>
<div><br/></div>
<h1>Meter Graphical Statistics [ Under Construction ] </span>
</h1>
<div class="admin">
  
   @include('includes.notifications')
   
   
	<ul class="nav nav-tabs" style="margin: 30px 0">
		<li class="active"><a href="#meter_readings" data-toggle="tab">Successful readings</a></li>
		<li><a href="#rtu_commands" data-toggle="tab">RTU Commands</a></li>
	</ul>

   
   
</div>

<script>

</script>
