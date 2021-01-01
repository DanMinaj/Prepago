</div>

<div><br/></div>
<h1>Schemes</h1>

</div>
<div class="cl"></div>
<div class="admin2">

    @include('includes.notifications')

    <a href="{{ URL::to('scheme-setup') }}" role="button" class="btn btn-info" style="float: right;margin-bottom:5px;">Set Up a New Scheme</a>
    <table class="table table-bordered">
        <tr>
            <th>Scheme Number</th>
            <th>Scheme Nickname</th>
            <th>Scheme Description</th>
			<th>Company</th>
            <th width="130px">&nbsp;</th>
        </tr>

        @if (!$schemes)
            <tr><td colspan="4">No schemes found</td></tr>
        @else
            @foreach ($schemes as $scheme)
                <tr>
                    <td>{{{ $scheme->scheme_number }}}</td>
                    <td>{{{ $scheme->scheme_nickname }}}</td>
                    <td>{{{ $scheme->scheme_description }}}</td>
					<td>{{{ $scheme->company_name }}}</td>

                    <td width="10%">
                        <a href="{{ URL::to('schemes/' . $scheme->id) }}" role="button" class="btn btn-success" data-toggle="modal">Edit</a>
                        {{--<a href="#myModal{{{ $scheme->id }}}Delete" role="button" class="btn btn-danger" data-toggle="modal">Delete</a>--}}
                    </td>

                    <div id="myModal{{{ $scheme->id }}}Delete" class="modal hide fade" >
                        <div class="modal-header">

                            <h3 id="myModalLabel">Are you sure you wish to delete this scheme?</h3>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you wish to delete this scheme?</p>
                        </div>
                        <div class="modal-footer">
                            {{ Form::open(['url' => URL::to('schemes/' . $scheme->id), 'method' => 'DELETE']) }}
                                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                                {{ Form::submit('Yes', ['class' => 'btn btn-danger']) }}
                            {{ Form::close() }}
                        </div>
                    </div>
                </tr>
            @endforeach
        @endif
    </table>
</div>