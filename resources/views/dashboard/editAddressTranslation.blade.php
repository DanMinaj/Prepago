@if ($message = Session::get('errorMessage'))
	<div style="color: #A94442; margin-top: 15px; padding: 10px; background-color: #F2DEDE; font-weight: bold">{{ $message }}</div>
@endif

@if ($message = Session::get('successMessage'))
	<div style="color: #69763D; margin-top: 15px; padding: 10px; background-color: #DFF0D8; font-weight: bold">{{ $message }}</div>
@endif

<div class="title"><div class="ico_units"></div><h1>Edit Address Translation: {{$eight}} - {{$sixteen}} </h1></div>

<!--
<div class="iniSetup-container">

	<div class="iniSetupTitle">Installation:</div>
	
	<div class="clear"></div>
</div>

<div id="meter_read_test" class="iniSetup-container">

	<div class="iniSetupTitle">Meter read test</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup" onclick="meter_read_test()">Test</button>

	<div class="clear"></div>

	
</div>

-->

<div class="clear"></div>

<div id="mbusmbus" class="max-container" style="margin-top: 1em;">
	
	<div class="iniSetup-container">
			
			<div class="edit-unit">
				
				{{ Form::open(array('method' => 'POST', 'url' => '/prepago_installer/edit-address-translation/' . $eight)) }}
					8digit
					<input placeholder='8 digit' type="text" name="eight" value="{{ $eight }}" />
					<br/>
					16digit
					<input placeholder='16 digit' type="text" name="sixteen" value="{{ $sixteen }}" />
					<p>{{ Form::submit('Save changes', array('class' => 'btn_setup')) }}</p>
				{{ Form::close() }}
			</div>
	
	</div>
	
	<div class="clear"></div>
	
	<div><a href="{{ URL::to('prepago_installer/address_translations') }}" class="btn btn-primary">&laquo; Back</a></div>

</div>


{{ HTML::script('resources/js/installer.js') }}
<script>
	jQuery(document).ready(function(){
		jQuery('#completeInstall').hide();
	});
</script>