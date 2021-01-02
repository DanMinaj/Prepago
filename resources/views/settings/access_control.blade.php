</div>

<div><br/></div>
<h1>Access Control</h1>

</div>
<div class="cl"></div>
<div class="admin2">

@include('includes.notifications')

<a href="#addModal" role="button" class="btn btn-info" style="float: right;margin-bottom:5px;" data-toggle="modal">Add Account</a>

@include('partials.users_list')

<div id="addModal" class="modal hide fade" >
    <div class="modal-header">

        <h3 id="myModalLabel">Add user account</h3>
    </div>
    <form action="{!! URL::to('settings/access_control/add_account_action') !!}" method="POST">
    <div class="modal-body">
		@include('partials.user_setup')
		
		@if ($schemes)
			{!! Form::label('schemes', 'Schemes:') !!}
			{!! Form::select('schemes[]', $schemes, null, ['id' => 'schemes_select', 'multiple' => 'multiple', 'style' => 'width: 80%']) !!}
		@endif
	</div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <input type="submit" class="btn btn-info" value="Add Account">
    </div>
    </form>
</div>

</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script>
    $(function()
    {
        $('#schemes_select').select2();
    })
</script>