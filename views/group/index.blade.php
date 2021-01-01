</div>
<div class="cl"></div>

<h1>Groups & Permissions</h1>

<div class="admin2">

    @include('includes.notifications')

    @if (!$groups)
        <tr><td>No groups found</td></tr>

    @else

        <div id="accordion">
            @foreach ($groups as $group)
                <h3>{{{ $group['name'] }}}</h3>
                <div>
                    <p><strong>Group Permissions:</strong></p>

                    <table class="table table-bordered">
                        <tr>
                        @foreach ($group['permissions'] as $key => $permission)
                            <td>{{ trans("permissions." . $permission) }}</td>
                            @if ($key != 0 && $key%2 !== 0)
                                </tr><tr>
                            @endif
                        @endforeach
                        </tr>
                    </table>

                    <br />
                    {{ Form::button('Edit Permissions', ['class' => 'btn btn-success', 'id' => 'edit_permissions_' . $group['id']]) }}

                    <div style="display: none; margin-top: 40px;" id="permissions_{{ $group['id'] }}">
                        <p><strong>Available Permissions:</strong></p>
                        {{ Form::open(['url' => URL::to('groups'), 'method' => 'PUT']) }}

                            {{ Form::hidden('group_id', $group['id']) }}

                            <div style="float:left">
                                @foreach (Config::get('permissions.all') as $key => $permission)
                                    @if ($key == 22)
                                    </div><div style="float:right;">
                                    @endif
                                    <label style="display:inline;margin-bottom:0px;font-size:12px" for="{{ "group_" . $group['id'] . "_permission_" . $key }}">
                                        <input type="checkbox" value="{{ $permission }}" name="permissions[]" id="{{ "group_" . $group['id'] . "_permission_" . $key }}" {{ in_array($permission, $group['permissions']) ? 'checked="checked"' : "" }} />
                                        {{ trans("permissions." . $permission) }}
                                    </label>
                                    <br />
                                @endforeach
                            </div>

                            <div style="clear:both"></div>

                            <br />
                            {{ Form::submit('Save Permissions', ['class' => 'btn btn-success pull-right']) }}

                        {{ Form::close() }}
                    </div>
                </div>
            @endforeach
        </div>

    @endif

</div>


<script>
    $(function() {

        $("[id^='edit_permissions_']").each(function()
        {
            $(this).click(function()
            {
                var currentGroupID = $(this).attr('id').replace('edit_permissions_', '');
                $("#permissions_" + currentGroupID).toggle();
            });
        });

        $( "#accordion" ).accordion({
            collapsible: true,
            active : 'none',
            heightStyle: "content"
        });
    });
</script>