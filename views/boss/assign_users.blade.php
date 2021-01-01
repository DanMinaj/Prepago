</div>

<div><br/></div>
<h1>Assign {{{ $bossLevelDownUser }}} for user "{{{ $user->username }}}"</h1>

</div>
<div class="cl"></div>
<div class="admin2">

    <div style="margin-bottom: 10px;"><a href="{{ URL::to('boss' . ($user->id != Auth::user()->id ? '/' . $user->id : '')) }}">BOSS</a> &raquo; Assign {{{ $bossLevelDownUser }}}s for user "{{ $user->username }}"</div>

    @include('includes.notifications')

    {{ Form::open(['url' => URL::to('boss/' . $user->id . '/assign'), 'role' => 'form', 'method' => 'POST']) }}

        <h3>{{{ $bossLevelDownUser }}}s:</h3>

        @foreach ($users as $availableUser)
            <label style="display:inline;margin-bottom:0px;font-size:12px" for="{{ "user_" . $availableUser->id }}">
                <input type="checkbox" value="{{ $availableUser->id }}" name="users[]" id="{{ "user_" . $availableUser->id }}" />
                <strong>{{{ $availableUser->username }}}</strong>
            </label>
            <br />
        @endforeach

        <br />
        {{ Form::submit('Assign ' . $bossLevelDownUser . 's', ['class' => 'btn btn-success']) }}

    {{ Form::close() }}
</div>

</div>