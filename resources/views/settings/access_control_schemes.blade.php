</div>

<div><br/></div>
<h1>Manage Schemes for user "{!! $user->username !!}"</h1>

</div>
<div class="cl"></div>
<div class="admin2">
    <div style="margin-bottom: 10px;"><a href="{!! $breadcrumbsURL !!}">{!! $breadcrumbsLinkText !!}</a> &raquo; Manage Schemes for user "{{ $user->username }}"</div>

    @include('includes.notifications')

    {!! Form::open(['url' => $formURL, 'role' => 'form', 'method' => 'POST']) !!}

        <h3>Schemes:</h3>

        @include('partials.schemes_list')

        <br />
        {!! Form::submit('Save Schemes', ['class' => 'btn btn-success']) !!}

    {!! Form::close() !!}
</div>

</div>