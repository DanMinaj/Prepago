</div>
<div class="cl"></div>

<h1>Choose setup type</h1>

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
	
	 <style>
	.setup-choose{
		margin: auto;
		width: 50%;
		//border: 3px solid green;
		padding-top: 10%;
	}
	.setup-option{
		padding-bottom: 14px;
		text-align: center;
	}
	.opt-btn{
		padding: 17px;
		font-size: 1rem;
	}
	</style>
	

	<div class="setup-choose">
		
		<form action="" method="POST">
		<table width="100%">
			<tr>
				<td width="100%" class="setup-option">
					<button type="submit" value='edit_{!! Auth::user()->scheme->scheme_number !!}' name="option" style="min-width:38%;" class="btn btn-primary opt-btn">
						<i class="fa fa-edit"></I> Edit {!! Auth::user()->scheme->scheme_nickname !!}
					</button>
				</td>
			</tr>
			<tr>
				<td width="100%"  class="setup-option">
					<button type="submit" value='create' name="option" style="min-width:38%;" class="btn btn-success opt-btn">
						<i class="fa fa-plus"></i> Create a Scheme
					</button>
				</td>
			</tr>
		</table>
		</form>
		
	</div>

</div>