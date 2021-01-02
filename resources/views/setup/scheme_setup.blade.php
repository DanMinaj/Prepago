</div>
<div class="cl"></div>

<h1>Scheme Set Up</h1>

<div class="admin2">

    
    @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
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

   <ul class="nav nav-tabs" style="margin: 30px 0">
    
	
		<li class="active">
			<a href="#s1" data-toggle="tab">Step 1. Manage Scheme</a>
		</li>
		
		<li>
			<a href="#s2" data-toggle="tab">Step 2. Manage SCU's</a>
		</li>
		
		<li>
			<a href="#s3" data-toggle="tab">Step 3. Manage Meters</a>
		</li>
		
		<li>
			<a href="#s4" data-toggle="tab">Step 4. Get certificate</a>
		</li>
		  
   </ul>
   
     
	<div class="tab-content">
		
		<!-- Setup Scheme -->
		<div class="tab-pane active" id="s1" style="text-align: left">
			
		</div>
		
		<div class="tab-pane" id="s2" style="text-align: left">
			
		</div>
		
    </div>
   

</div>