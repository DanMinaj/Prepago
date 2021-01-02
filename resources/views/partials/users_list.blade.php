<table class="table table-bordered">
    <th>Employee Name</th>
    <th>Username</th>
    <th>Group</th>
    <th>isInstaller</th>

    @if (!isset($is_installer))
        <th width="25%">&nbsp;</th>
    @endif
	
    @if ($customers == "")
        <tr><td colspan="4">There are no data to show</td></tr>
    @else
        @foreach ($customers as $type)
            <tr>
                <td><?php echo $type['employee_name'] ?></td>
                <td><?php echo $type['username'] ?></td>

                <td>{!! $type->group->name !!}</td>

                <td><?php echo $type['isInstaller'] === 1 ? 'yes' : 'no' ?></td>

				@if (!isset($is_installer))
					<td>
						<a href="#editModal<?php echo $type['id']?>" role="button" class="btn btn-info" data-toggle="modal">Edit</a>
						<a href="#myModal<?php echo $type['id']?>" role="button" class="btn btn-danger" data-toggle="modal">Delete</a>
						<a href="{!! $baseURL . '/' . $type['id'] . '/schemes' !!}" class="btn btn-success">Schemes</a>
					</td>
				@endif	

                <div id="myModal<?php echo $type['id']?>" class="modal hide fade" >
                    <div class="modal-header">

                        <h3 id="myModalLabel">Are you sure you wish to close this account?</h3>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you wish to close this account?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                        <a href="{!! $baseURL . '/close_account_action/' . $type['id'] !!}" class="btn btn-danger">Yes</a>
                    </div>
                </div>
				
				<div id="editModal<?php echo $type['id']?>" class="modal hide fade">
                    <div class="modal-header">
                        <h3 id="editModalLabel">Edit user account</h3>
                    </div>

                    {!! Form::open(['url' => URL::to($baseURL . '/' . $type['id']), 'method' => 'PUT']) !!}
                        <div class="modal-body">
                            {!! Form::label('employee_name', 'Employee Name:') !!}
                            {!! Form::text('employee_name', $type['employee_name']) !!}

                            {!! Form::label('username', 'Username:') !!}
                            {!! Form::text('username', $type['username']) !!}

                            {!! Form::label('group', 'Group:') !!}
                            {!! Form::select('group', groups(), $type->group->id) !!}
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <input type="submit" class="btn btn-info" value="Edit Account">
                        </div>
                    {!! Form::close() !!}
                </div>
            </tr>
        @endforeach
    @endif
</table>