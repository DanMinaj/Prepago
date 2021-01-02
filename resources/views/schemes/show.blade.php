</div>

<div><br/></div>
<h1>Edit Scheme</h1>

</div>
<div class="cl"></div>
<div class="admin2">

    <div style="margin-bottom: 10px;"><a href="{{ URL::to('schemes') }}">Schemes List</a> &raquo; Edit Scheme</div>

        @if ($message = Session::get('successMessage'))
            <div class="alert alert-success alert-block">
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

        {{ Form::model($scheme, ['URL' => URL::to('schemes'), 'method' => 'PUT']) }}

            @include('partials.scheme_setup', ['action' => 'edit'])

            <div class="clearfix">&nbsp;</div>
            <div style="margin-top: 20px; width: 35%; float: right">{{ Form::submit('Edit Scheme', array('class' => 'btn btn-success')) }}</div>
            <div class="clearfix">&nbsp;</div>

        {{ Form::close() }}

</div>