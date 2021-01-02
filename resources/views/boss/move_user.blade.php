</div>

<div><br/></div>
<h1>Move "{{ $user->username }}"</h1>

</div>
<div class="cl"></div>
<div class="admin2">

    <div style="margin-bottom: 10px;"><a href="{!! URL::to('boss') !!}">BOSS</a> &raquo; Move "{!! $user->username !!}"</div>

    @include('includes.notifications')

    {!! Form::open(['url' => URL::to('boss/' . $user->id . '/reassign'), 'role' => 'form', 'method' => 'POST']) !!}

    <h3>Users:</h3>

    @foreach ($users as $matchingUser)
        <label style="display:inline;margin-bottom:0px;font-size:12px" for="{!! "user_" . $matchingUser['id'] !!}">
            <input type="radio" value="{!! $matchingUser['id'] !!}" name="users" id="{!! "user_" . $matchingUser['id'] !!}" />
            <strong>{{ $matchingUser['username'] }}</strong> ({{ $matchingUser['level_name'] }})
        </label>
        <br />
    @endforeach

    <br />
    {!! Form::submit('Reassign', ['class' => 'btn btn-success']) !!}

    {!! Form::close() !!}
</div>

</div>