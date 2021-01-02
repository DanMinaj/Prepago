</div>

<div><br/></div>
<h1>FAQ</h1>

</div>
<div class="cl"></div>
<div class="admin2">
	<div class="alert alert-success success_msg" style="display:none"></div>
	<div class="alert alert-error error_msg" style="display:none"></div>

<a href="#addmodal" role="button" class="btn btn-primary" data-toggle="modal"><i class="fas fa-plus"></i>&nbsp;Create new FAQ</a>
 
<script>
	$(function(){
		
		$('.scheme_check').on('click', function(){
			if($('.scheme_check:checked').length > 0) {
				$('.check_all').text('Uncheck all');
			} else {
				$('.check_all').text('Check all');
			}
		});
		
		$('#faq_form_submit').on('click', function(){
		
			var schemes = [];
			var faq_question = $('.faq_question').val();
			var faq_answer = $('.faq_answer').val();
			$('.scheme_check:checked').each(function(){        
				schemes.push($(this).val());
			});
			
			console.log(schemes)
			
			$.ajax({
				url: "/faq/add_mass_faq",
				method: "POST", 
				data: {schemes: schemes, faq_question: faq_question, faq_answer: faq_answer},
				success: function(data) {
					
					if(data.error) {
						error(data.error);
						return;
					} 
					
					success(data.success);
					$('#addmodal').modal('toggle');
					
				}, error: function(data) { error(data); }
			});
			
		});
		
		$('.check_all').on('click', function(){
			
			if($('.scheme_check:checked').length > 0) {
				$(this).text('Check all');
				$('.scheme_check').prop('checked', false);
			} else {
				$(this).text('Uncheck all');
				$('.scheme_check').prop('checked', true);
			}
		});
		
		$('.editing_existing').on('change', function(){
			
		});
		
		function success(msg)
		{
			$('.error_msg').hide();
			$('.success_msg').html(msg);
			$('.success_msg').show();
		}
		
		function error(msg)
		{
			$('.success_msg').hide();
			$('.error_msg').html(msg);
			$('.error_msg').show();
		}
		
	});
</script>
<div id="addmodal" class="modal fade" role="dialog">

	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Create new FAQ</h4>
	  </div>
		
	  
	  <div class="modal-body">
		<table width="100%">
			<tr>
				<td width="40%" style="vertical-align:top">
					<table width="100%">
					 <tr>
						<td width="100%">
							<button class="check_all btn btn-primary">Check all</button>
						</td>
					 </tr>
					 @foreach($schemes as $k => $s)
						<tr>
							<td width="100%">
								<input style="width: 25px; height: 25px; margin-bottom: 3%;" type="checkbox" class='scheme_check' name="checked_schemes[]" value="{{ $s->scheme_number }}"> {{ $s->scheme_nickname }}
							</td>
						</tr>
					@endforeach
					</table>
				</td>
				<td width="60%"  style="vertical-align:top">
					<table width="100%">
						<tr>
							<td width="100%">
								<div class="alert alert-success success_msg" style="display:none"></div>
								<div class="alert alert-error error_msg" style="display:none"></div>
							</td>
						</tr>
						
						<!--
						<tr>
							<td width="100%">
								<hr/>
								<input class="editing_existing" style="width:25px;height:25px;" type="checkbox"> Editing existing FAQ
								<hr/>
							</td>
						</tr>
						-->
						
						<tr>
							<td width="100%">
								<input type="text" style="padding: 5%;width: 90%;" name="faq_question" class="form-control faq_question" placeholder="FAQ Question">
							</td>
						</tr>
						<tr>
							<td width="100%">
								<textarea class="faq_answer" name="faq_answer" placeholder="FAQ Answer" style="padding: 5%;width:90%;height:200px;"></textarea>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	  </div>
	  
	  <div class="modal-footer">
		<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
		<button type="button"  id="faq_form_submit" class="btn btn-primary" type="submit">Submit</button>
	  </div>
	
	  
	</div>

	</div>

</div>

   <ul class="nav nav-tabs" style="margin: 30px 0">
      @foreach($schemes as $k => $s)
		  <li @if($k == 0) class="active" @endif><a href="#{{ preg_replace("/[^A-Za-z0-9]/", "", $s->scheme_nickname) }}" data-toggle="tab">{{ $s->scheme_nickname }}</a></li>
	  @endforeach
   </ul>
   
   
  <div class="tab-content">
    
	@if(Session::has('successMessage'))
	<div class="alert alert-success" style="padding:2%;font-size:1.2em;">
		{{ Session::get('successMessage') }}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger" style="padding:2%;font-size:1.2em;">
		{{ Session::get('errorMessage') }}
	</div>
	@endif
	
   @foreach($schemes as $k => $s)
		 <div class="tab-pane @if($k == 0) active @endif" id="{{ preg_replace("/[^A-Za-z0-9]/", "", $s->scheme_nickname) }}" style="">
			 <form @if($scheme_id == null) action="{{ URL::to('settings/faq/save_faq') }}" @else action="{{ URL::to('settings/faq/save_faq/' . $scheme_id) }}" @endif method="POST">
			<h4> {{ $s->scheme_nickname }} FAQ's</h4>	
			
			
				<input type="submit" name="save" id="save" class="btn btn-success" style="float:right;margin-bottom:2em;" value="Save Changes">
				<table class="table table-bordered">
					<th>Quesion</th>
					<th>Answer</th>

					<?php
					$counter = 0;
					if ($s->faqs == ""){
						echo "There are no data to show";
					}else{
						foreach ($s->faqs as $faq){
							?>
						<tr>
							<td><textarea name="q<?php echo $counter; ?>" id="q<?php echo $counter; ?>"><?php echo $faq->question; ?></textarea></td>
							<td><textarea name="a<?php echo $counter; ?>" id="q<?php echo $counter; ?>" style="width:700px;"><?php echo $faq->answer; ?></textarea></td>
						</tr>
					<?php 
						$counter++;
					} ?>

					<!--
					<tr id="addfaq">
						<td colspan="2"><a href="#" id="addfaqbtn" class="btn btn-info">Add FAQ</a></td>
					</tr>-->
					<?php } ?>
				</table>
				<input type="hidden" name="scheme_number" id="scheme_number" value="{{ $s->scheme_number }}">
				<input type="hidden" name="faqcounter" id="faqcounter" value="<?php echo $counter; ?>">

				<input type="submit" name="save" id="save" class="btn btn-success" style="float:right;margin-bottom:2em;" value="Save Changes">
			</form>

		 </div>
   @endforeach
   
   
   </div>
   

<script>
    
jQuery(document).ready(function(){

    jQuery('#addfaqbtn').on('click', function(){
        var countervalue = jQuery('#faqcounter').val();
        jQuery('#addfaq').before(
            '<tr><td><textarea name="q'+countervalue+'" id="q'+countervalue+'"></textarea></td>'+
            '<td><textarea name="a'+countervalue+'" id="a'+countervalue+'" style="width:700px;"></textarea></td></tr>');
        jQuery("html, body").animate({ scrollTop: $(document).height() }, 1000);
        jQuery('#faqcounter').val(parseInt(countervalue)+1);
    });

});

</script>

</div>