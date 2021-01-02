</div>

<div><br/></div>
<h1>BOSS - {!! $user->username !!}</h1>

</div>
<div class="cl"></div>
<div class="admin2">
    @if ($userID != Auth::user()->id)
        <div style="margin-bottom: 10px;"><a href="{!! URL::to('boss' . ($user->parent_id != Auth::user()->id ? '/' . $user->parent_id : '')) !!}">BOSS</a> &raquo; {{ $user->username }}</div>
    @endif

    @include('includes.notifications')

    <h3>Assigned Schemes</h3>
    <!-- Display Assigned Schemes -->
    {!! Form::open(['url' => URL::to('boss/' . $userID . '/schemes'), 'method' => 'GET']) !!}
        <table class="table table-bordered">
            <tr><th>Schemes</th></tr>
            @if ($schemes->count())
                @foreach ($schemes as $scheme)
					<tr>
                        <td>
                            {{ $scheme->scheme_nickname ? : $scheme->company_name }}
                            <a class="btn btn-info pull-right" style="margin-right: 10px" href="{!! URL::to('boss/' . $userID . '/schemes/' . $scheme->scheme_number . '/rs-codes') !!}">RS Codes</a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td>No schemes assigned</td></tr>
            @endif
        </table>
    {!! Form::close() !!}
    <a href="{!! URL::to('boss/' . $userID . '/schemes') !!}" class="btn btn-success pull-right">Edit Schemes</a>
    <div class="clearfix"></div>

    <br /><br />

    @if ($userBossLevel < 3)
        <!-- If the current user is not assigned to anyone and is not an admin -> make sure to add to the BOSS hierarchy first before assigning users -->
        @if ($userBossLevel == 0 && $userID != getAdminID())
            <h4>This user is not assigned to anyone yet. Please include in the BOSS hierarchy before being able to manage assigned users</h4>
        @else

            <!-- Display Assigned Users -->
            <h3>Assigned {{ $bossLevelDownUser }}s</h3>

            @if ($userBossLevel < 3)
                <a href="{!! URL::to('boss/' . $userID . '/assign') !!}" role="button" class="btn btn-info" style="float: right;margin-bottom:5px;">Assign New {{ $bossLevelDownUser }}</a>
            @endif

            <table class="table table-bordered">
                <tr>
                    <th>Username</th>
                </tr>
                @if ($users->count())
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <a href="{!! URL::to('boss/' . $user->id) !!}">{{ $user->username }}</a>
                                <div class="pull-right">
                                    {!! Form::open(['url' => URL::to('boss/' . $user->id . '/unassign'), 'method' => 'POST', 'style' => 'margin: 0']) !!}
                                        {!! Form::submit('Unassign', ['class' => 'btn btn-success']) !!}
                                    {!! Form::close() !!}
                                </div>
                                <div class="pull-right" style="margin-right: 10px">
                                    <a class="btn btn-info" href="{!! URL::to('boss/' . $user->id . '/reassign') !!}">Reassign</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="2">No {{ $bossLevelDownUser }}s assigned</td></tr>
                @endif
            </table>

         @endif
    @endif

</div>