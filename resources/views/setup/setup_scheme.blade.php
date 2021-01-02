</div>
<div class="cl"></div>
<h1>
@if($scheme_data->mode == 'edit') 
	Edit {!! $scheme_data->scheme->scheme_nickname !!}
@else	
	Create a new scheme
@endif
</h1>

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
	
	<a href="{!! URL::to('setup/choose') !!}">
		<button class="btn btn-primary"> <i class="fa fa-chevron-left"></i> Change setup type </button>
	</a>
	
	<form action="" method="POST" style="display:inline;margin:0px !important;">
		<a class='pull-right' href="#">
			<button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save changes </button>
		</a>
		
	   <ul class="nav nav-tabs" style="margin: 30px 0">
		
		
			<li class="active">
				<a href="#s1"  data-toggle="tab">
				@if($step1_complete) 
					<font color='#4caf50'>Step 1. Scheme Info <i class='fa fa-check-circle'></i></font>
				@else  
					<font color=''>Step 1. Scheme Info <i class='fa fa-ellipsis-h'></i></font>
				@endif 
				</a>
			</li>
			
			<li>
				<a href="#s2" data-toggle="tab">
				@if($step2_complete) 
					<font color='#4caf50'>Step 2. Manage SIM <i class='fa fa-check-circle'></i></font>
				@else  
					<font color=''>Step 2. Manage SIM <i class='fa fa-ellipsis-h'></i></font>
				@endif 
				</a>
			</li>
			
			<li>
				<a href="#s3" data-toggle="tab">Step 3. Manage SCU's</a>
			</li>
			
			<li>
				<a href="#s4" data-toggle="tab">Step 4. Manage Meters</a>
			</li>
			
			<li>
				<a href="#s5" data-toggle="tab">Step 5. Get certificate</a>
			</li>
			  
	   </ul>
	   
		 
		<div class="tab-content">
			
			<!-- Setup Scheme -->
			<div class="tab-pane active" id="s1" style="text-align: left">
				
				<table width="100%">
					<tr>
						
						<!-- Left -->
						<td style="vertical-align:top">
							<table width="100%">

								<tr><td><b> <h4>Scheme name</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="scheme_nickname" placeholder="Scheme name e.g Charlotte" 
								value="{!! $scheme_data->scheme->scheme_nickname !!}">
								</td></tr>
								
								<tr><td><b> <h4>Scheme prefix</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="prefix" placeholder="Prefix e.g char_" 
								value="{!! $scheme_data->scheme->prefix !!}">
								</td></tr>
															
								<tr><td><b> <h4>Currency sign</h4> </b></td></tr>
								<tr><td>
								<input type="hidden" name="currency_code" value="{!! $scheme_data->scheme->currency_code !!}">
								<input type="text" name="currency_sign" placeholder="Currency sign e.g €" 
								value="{!! $scheme_data->scheme->currency_sign !!}">
								</td></tr>
								
								<tr><td><b> <h4>VAT rate</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="vat_rate" placeholder="Vat rate" 
								value="{!! (empty($scheme_data->scheme->vat_rate)) ? '0.135' : $scheme_data->scheme->vat_rate  !!}">
								</td></tr>
								
							</table>
						</td>
						
						<!-- Center -->
						<td style="vertical-align:top">
							<table width="100%">
								<tr><td><b> <h4>Street</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="street2" placeholder="Street name" 
								value="{!! $scheme_data->scheme->street2 !!}">
								</td></tr>
								<tr><td><b> <h4>Town/Village</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="street2" placeholder="Town/village name e.g Dún Laoghaire" 
								value="{!! $scheme_data->scheme->street2 !!}">
								</td></tr>
								<tr><td><b> <h4>County</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="county" placeholder="County name e.g Dublin" 
								value="{!! $scheme_data->scheme->county !!}">
								</td></tr>
								<tr><td><b> <h4>Post code</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="post_code" placeholder="Post code e.g A94 XXXX" 
								value="{!! $scheme_data->scheme->post_code !!}">
								</td></tr>
								<tr><td><b> <h4>Country</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="country" placeholder="Country e.g Ireland" 
								value="{!! $scheme_data->scheme->country !!}">
								</td></tr>
							</table>
						</td>
						
						
						<!-- Right -->
						<td style="vertical-align:top">
							<table width="100%">
								<tr><td><b> <h4>Company name</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="company_name" placeholder="Company name e.g Cosgraves" 
								value="{!! $scheme_data->scheme->company_name !!}">
								</td></tr>
								<tr><td><b> <h4>Company address</h4> </b></td></tr>
								<tr><td>
								<input type="text" name="company_name" placeholder="Company address e.g Blackrock" 
								value="{!! $scheme_data->scheme->company_name !!}">
								</td></tr>
							</table>
						</td>
						
					</tr>
				</table>
				
			</div>
			
			<!-- Setup SIM -->
			<div class="tab-pane" id="s2" style="text-align: left">
				
				<table width="100%">
					<tr>
					
					</tr>
				</table>
				
			</div>
			
		</div>
	</form>

</div>