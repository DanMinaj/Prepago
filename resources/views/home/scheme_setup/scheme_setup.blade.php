</div>
<div class="cl"></div>

<h1>Scheme Set Up</h1>

<div class="admin2">

    <div style="width: 500px; margin: 0 auto"><h3>Step 2 - Set Up the New Scheme</h3></div>

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

    {!! Form::model($scheme, ['URL' => URL::to('scheme-setup/scheme-setup'), 'method' => 'POST']) !!}

        {!! Form::hidden ('user_id', $user_id) !!}

        @include('partials.scheme_setup', ['action' => 'create'])

        <div class="clearfix">&nbsp;</div>
        <div style="margin-top: 20px; width: 35%; float: right">{!! Form::submit('Add Scheme', array('class' => 'btn btn-success')) !!}</div>
        <div class="clearfix">&nbsp;</div>

    {!! Form::close() !!}

    <script type="text/javascript">
        var fieldsVersions = {!!json_encode($fieldsVersions)!!};
        $(function()
        {
            $("#country").change(function()
            {
                var countryVal = $(this).val();
                setFieldVersions(countryVal);
            });
        });

        function setFieldVersions(countryVal)
        {
            $("#vat_rate").val(fieldsVersions[countryVal].vat_rate);
            $("#currency_code").val(fieldsVersions[countryVal].currency_code);
            $("#currency_sign").val(fieldsVersions[countryVal].currency_sign);
            $("#IOU_text").html(fieldsVersions[countryVal].IOU_text);
            $("#IOU_extra_text").html(fieldsVersions[countryVal].IOU_extra_text);
        }
    </script>

</div>