
</div>

<div><br/></div>
<h1>Bulk meter setup</h1>

	
<div class="admin2">

@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif


	<ul class="nav nav-tabs" style="margin: 30px 0">
      <li class="active"><a href="#v1" data-toggle="tab">Method 1.</a></li>
      <li><a href="#v2" data-toggle="tab">Method 2.</a></li>
   </ul>

	
	<div class="tab-content">
     
	<div class="tab-pane active" id="v1" style="">
		
	<div class="alert alert-info alert-block">
		<b>Note:</b> Please note that you are inserting this into the scheme 
		<?php $scheme = Scheme::find(Auth::user()->scheme_number); ?>
		@if($scheme)	
		<b>'{{ $scheme->scheme_nickname }}'</b>
		@else
		'<b>Scheme not found</b>'
		@endif
	</div>
	
		@if($returned_pmds = Session::get('returned_pmds'))
		<b> Records inserted </b>
		<hr/>
		<textarea style='width:100%'>
		@foreach($returned_pmds as $r)
		@if(isset($r['error']))
			error
		@else
		{{$r}}
		---------------------------------------------------------------------------------------------------------------
		@endif
		@endforeach
		</textarea>
		<hr/>
		@endif

		<form action="" method="POST">
		
			
			<table class='table-bordered table' width="100%">
			
				<tr>
					<td> <b> <h3><input type="checkbox" id="insert_meter" checked>Meter</h3> </b> </td>
					<td> <b> <h3>SCU</h3> </b> </td>
					<td> <b> <h3>Street (optional)</h3> </b> </td>
				</tr>
				<tr>
			
					
					<td> 
					<font style='font-size:13px' >{{ $lookup->meter_make }} {{ $lookup->meter_model }}</font> 
					<br/></br>
					</td>
					
					
					<td> 
					<font style='font-size:13px' >{{ $lookup->scu_make }} {{ $lookup->scu_model }}</font> 
					<br/></br>
					</td>
					
					<td> 
					Address street name for this block
					<br/></br>
					</td>
					
					
				</tr>
				
			
				
				<tr>
					
					<td>
						<input type="text" name="last_8" value="{{ $lookup->last_eight }}" placeholder="Meter last 8 digits">
					</td>
					
					
					<td>
						<input type="text" name="scu_last_8" value="{{ $lookup->scu_last_eight }}" placeholder="SCU last 8 digits">
					</td>
				
					@if($scheme)
					<td>
						<input type="text" name="street1" value="{{$scheme->scheme_nickname}}" placeholder="Street"><br/>
					</td>
					@else
					<td>
						<input type="text" name="street1" value="scheme_not_found" placeholder="Street"><br/>
					</td>
					@endif
				
				
				</tr>
				
				<tr>	
				
					<td>
					
						<b>Format: </b> <br/><br/><textarea name="format" disabled style='width:191%;height:20px;' placeholder='Bulk Format'></textarea>
				
					
					</td>
					
				</tr>
				
				<tr>
					
					<td width="100%">
						
						<textarea name="data" style='width:97%;height:200px;' placeholder=''></textarea>
					
				
					</td>
					
				</tr>
			
			<tr>
				<td>
					<button type="submit" id="preview" class="btn btn-warning" style="width:194%">Preview</button>
				</td>
			</tr>
			
			<tr>
				<td>
					<input type="hidden" name="action" value="submit">
					<button type="submit" id="submit" class="btn btn-primary" style="width:194%">Insert records</button>
				</td>
			</tr>
		
			</table>
		
		
		
		</form>

	</div>

	<div class="tab-pane" id="v2" style="">
	
	
		<div class="alert alert-info alert-block">
			<b>Note:</b> Please note that you are inserting this into the scheme 
			<?php $scheme = Scheme::find(Auth::user()->scheme_number); ?>
			@if($scheme)	
			<b>'<span class='scheme'>{{ $scheme->scheme_nickname }}</span>'</b>
			@else
			'<b>Scheme not found</b>'
			@endif
		</div>
		
		
		@if(Session::has('failed') && $failed = Session::get('failed'))
			<table width="100%" class="table table-bordered">
			<tr>
				<td>
					<b>  Failed to insert {{ count($failed) }} records. </b>
				</td>
			</tr>
			@foreach($failed as $k => $v)
				<tr>
					<td>
					{{ $v['value'] }}: {{ $v['reason'] }}
					</td>
				</tr>
			@endforeach
			</table>
		@endif
		
		
		@if(Session::has('success') && $success = Session::get('success'))
			<table width="100%" class="table table-bordered">
			<tr>
				<td>
					<b> Successfullly inserted records {{ count($success) }}. </b>
				</td>
			</tr>
			@foreach($success as $k => $v)
				<tr>
					<td>
					{{ $v['value'] }}: PMD#{{ $v['id'] }}
					</td>
				</tr>
			@endforeach
			</table>
		@endif
	
	
		<form action="{{ URL::to('settings/meter_setup2') }}" method="POST">
			
		<div class="row-fluid">
			<div class="span6">
				<b> Scheme: </b>
			</div>
		</div>
		<script>
			$(function(){
				$('.scheme').text($('#sel_scheme').val());
				$('#sel_scheme').on('change', function(){
					$('.scheme').text($(this).val());
				});
			});
		</script>
		<div class="row-fluid">
			<div class="span6">
				<input type="text" name="street" placeholder="Address Street/Town">
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<select id="sel_scheme" class="form-control" name="scheme">
					<option>
						{{ Auth::user()->scheme->scheme_nickname }}
					</option>	
					@foreach(Scheme::active() as $k => $v)
						@if($v->scheme_nickname != Auth::user()->scheme->scheme_nickname)
						<option>
							{{ $v->scheme_nickname }}
						</option>
						@endif
					@endforeach
				</select>
			</div>
		</div>
		
			
		<div class="row-fluid">
		
			<div class="span12">
				<textarea placeholder="HouseNumber SCU Meter <Optional:Meter2> Example: 1 02009383 01939382" style="margin: 0px 0px 9px; width: 972px; height: 131px;" name="input"></textarea>
			</div>
			
		</div>
		
			<div class="row-fluid">
		
			<div class="span12">
				<input type="submit" name="action" value="Preview" class="btn btn-warning">
				<input type="submit" name="action" value="Insert" class="btn btn-primary">
			</div>
			
		</div>
		
		</form>
		
	</div>
	
	<script>
	$(function(){
		
		var insert_meter = true;
		
		$('#submit').on('click', function(){
			$('input[name=action]').val('submit');
		});
		
		$('#preview').on('click', function(){
			$('input[name=action]').val('preview');
		});
		
		$('input[id=insert_meter]').on('change', function(){
			if($(this).prop('checked')) {
				// just checked
				insert_meter = true;
			} else {
				// just unchecked
				insert_meter = false;
			}
			
			generateExampleInput();
			generateFormat();
		});
		
		function generateExampleInput()
		{
			var textarea = $('textarea[name=data]');

			if(insert_meter)
				textarea.attr('placeholder', "[**Example Input(s):**]\nWF1 #username 02002525 15065977\nWF2 #username 02002511 15066009\nWF3 #username 02002494 15066008\nWF4 #username 02002501 15066010\nWF5 #username 02002477 15066007\nWF6 #username 02002614 15065984\nNF1 #username 02002440 15066006\nNF2 #username 02002615 15065981\nNF3 #username 02003881 15065982\nNF4 #username 02002436 15065985\nNF5 #username 02002476 15065976\nNF6 #username 02002514 15065983");
			else
				textarea.attr('placeholder', "[**Example Input(s):**]\nWF1 #username 02002525\nWF2 #username 02002511\nWF3 #username 02002494\nWF4 #username 02002501\nWF5 #username 02002477\nWF6 #username 02002614\nNF1 #username 02002440\nNF2 #username 02002615\nNF3 #username 02003881\nNF4 #username 02002436\nNF5 #username 02002476\nNF6 #username 02002514");
		
		}
		
		function generateFormat()
		{
			var textarea = $('textarea[name=format]');
			
			if(insert_meter)
				textarea.val('HouseID Username SCU Meter');
			else
				textarea.val('HouseID Username SCU');
			
		}
		
		generateExampleInput();
		generateFormat();
		
	});
	</script>
	
</div>