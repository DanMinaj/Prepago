</div>

<div><br/></div>
<h1>Customer Duplicates</h1>

</div>
<div class="cl"></div>
<div class="admin2">

    @include('includes.notifications')

    
    
	@foreach ($customerduplicates as $dups)   
		
                @include('customerduplicates.duplicate_customers', array('info'=> $dups ))
        
    @endforeach
	
	
</div>