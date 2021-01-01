</div>
<div class="cl"></div>

<h1>Scheme Set Up</h1>

<div class="admin2" style="width: 500px">

    @include('includes.notifications')

    <h3>Step 3 - Set Up a New Tariff</h3>

    {{ Form::open(['URL' => URL::to('scheme-setup/tariff-setup'), 'method' => 'POST']) }}

        {{ Form::hidden ('scheme_id', $scheme_id) }}

        {{ Form::label('tariff_1', 'Tariff 1') }}
        {{ Form::text('tariff_1') }}

        {{ Form::label('tariff_1_name', 'Tariff 1 Name') }}
        {{ Form::text('tariff_1_name') }}

        {{ Form::label('tariff_2', 'Tariff 2') }}
        {{ Form::text('tariff_2') }}

        {{ Form::label('tariff_2_name', 'Tariff 2 Name') }}
        {{ Form::text('tariff_2_name') }}

        {{ Form::label('tariff_3', 'Tariff 3') }}
        {{ Form::text('tariff_3') }}

        {{ Form::label('tariff_3_name', 'Tariff 3 Name') }}
        {{ Form::text('tariff_3_name') }}

        {{ Form::label('tariff_4', 'Tariff 4') }}
        {{ Form::text('tariff_4') }}

        {{ Form::label('tariff_4_name', 'Tariff 4 Name') }}
        {{ Form::text('tariff_4_name') }}

        {{ Form::label('tariff_5', 'Tariff 5') }}
        {{ Form::text('tariff_5') }}

        {{ Form::label('tariff_5_name', 'Tariff 5 Name') }}
        {{ Form::text('tariff_5_name') }}

        <br /><br />

        {{ Form::submit('Add Tariff', array('class' => 'btn btn-success')) }}

    {{ Form::close() }}

</div>