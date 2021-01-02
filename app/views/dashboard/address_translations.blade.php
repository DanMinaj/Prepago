@if ($message = Session::get('successMessage'))
	<div style="color: #468847;background-color: #dff0d8;border-color: #d6e9c6;padding: 14px;margin: 10px 0;">
		{{ $message }}
	</div>
@endif

@if ($message = Session::get('errorMessage'))
	<div style="color: #b94a48;background-color: #f2dede; border-color: #eed3d7;padding: 14px;margin: 10px 0;">
		{{ $message }}
	</div>
@endif

@if( Auth::user() )
<div class="search">
	{{ Form::open(array('url' => 'prepago_installer/address_translations/search')) }}
	{{ Form::text('search') }}
	{{ Form::submit('', array('class' => 'btn_search')) }}
	{{ Form::close() }}
</div>
@endif

<div class="title"><a href="{{ URL::to('prepago_installer/address_translations') }}" title="MBUS Address Translations"><div class="ico_units"></div></a>
<h1>MBUS Address Translations</h1>
<h2> @if($search) {{count($scu_translations) + count($meter_translations)}} Results found in search for ' {{ $searching }} ' @else Total: {{count($scu_translations) + count($meter_translations)}} address translations @endif</h2></div>

<div class="units_table">

	<div class="unit_title_row">
		<div class="unit_title_cell unit1">8 DIGIT</div>
		<div class="unit_title_cell unit2">16 DIGIT</div>
		<div class="unit_title_cell unit3">Tools</div>
	</div>

<div class="unit_row">
	<div class="unit_cell unit_number">
	{{ Form::open(array('url' => 'prepago_installer/address_translations/add-new')) }}
	{{ Form::text('8digit', '', array('placeholder'=>'8 digit')) }}
	</div>
	<div class="unit_cell unit_type">
	{{ Form::text('16digit', '', array('placeholder'=>'16 digit')) }}
	</div>
	<div class="unit_cell unit_options">
	{{ Form::submit('Add new address translation', array('class' => 'btn-primary')) }}
	{{ Form::close() }}
	</div>
</div>

@if(count($scu_translations) > 0)
@foreach($scu_translations as $addresstranslation)

	@if(1!=1)
		<div class="unit_row warn-red">
	@else
		<div class="unit_row">
	@endif
		<div class="unit_cell unit1">
			{{$addresstranslation->eight}}
		</div>

		<div class="unit_cell unit2">
			{{$addresstranslation->sixteen}}
		</div>

			<div class="unit_cell unit3">
			
			<a href="{{URL::to('prepago_installer/edit-address-translation/'.$addresstranslation->eight)}}" title="Edit" style="float: left; padding-right: 5px;">
			<div class="ico_edit"></div></a>
			
			{{ Form::open(['url' => URL::to('prepago_installer/delete-address-translation/'.$addresstranslation->eight), 'method' => 'delete', 'id' => 'delete-address-' . $addresstranslation->eight, 'onSubmit' => 'return confirm("Are you sure you want to delete the selected MBus Address Translation?")']) }}
				<a href="javascript: void(0);" title="Delete" style="float: left" onclick="$('#delete-address-{{ $addresstranslation->eight }}').submit()">
				<div class="ico_delete"></div></a>
				{{ Form::close() }}
				
			<!--<span style="background-color: yellow;padding: 0 3px;margin-left:1px;">S</span>-->
			
		</div>
	</div>
@endforeach

@else
    <div class="unit_row">
		<div class="unit_cell unit_type">No Address Translations Found</div>
		<div class="unit_cell unit_type">&nbsp;</div>		
	</div>
@endif

@foreach($meter_translations as $addresstranslation)

	@if(1!=1)
		<div class="unit_row warn-red">
	@else
		<div class="unit_row">
	@endif
		<div class="unit_cell unit1">
			{{$addresstranslation->eight}}
		</div>

		<div class="unit_cell unit2">
			{{$addresstranslation->sixteen}}
		</div>

			<div class="unit_cell unit3">
			
			<a href="{{URL::to('prepago_installer/edit-address-translation/'.$addresstranslation->eight)}}" title="Edit" style="float: left; padding-right: 5px;">
			<div class="ico_edit"></div></a>
			
			{{ Form::open(['url' => URL::to('prepago_installer/delete-address-translation/'.$addresstranslation->eight), 'method' => 'delete', 'id' => 'delete-address-' . $addresstranslation->eight, 'onSubmit' => 'return confirm("Are you sure you want to delete the selected MBus Address Translation?")']) }}
				<a href="javascript: void(0);" title="Delete" style="float: left" onclick="$('#delete-address-{{ $addresstranslation->eight }}').submit()">
				<div class="ico_delete"></div></a>
				{{ Form::close() }}
				
			<!--<span style="background-color: yellow;padding: 0 3px;margin-left:1px;">S</span>-->
			
		</div>
	</div>
@endforeach

</div>
