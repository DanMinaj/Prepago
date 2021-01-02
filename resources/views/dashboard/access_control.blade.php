@if ($message = Session::get('successMessage'))
    <div style="color: #468847;background-color: #dff0d8;border-color: #d6e9c6;padding: 14px;margin: 10px 0;">
        {!! $message !!}
    </div>
@endif

@if ($message = Session::get('errorMessage'))
    <div style="color: #b94a48;background-color: #f2dede; border-color: #eed3d7;padding: 14px;margin: 10px 0;">
        {!! $message !!}
    </div>
@endif

{!! HTML::script('resources/js/bootstrap-modal.js') !!}
{!! HTML::style('resources/css/bootstrap.min.css') !!}
<style>
    body {
        font-size: 13px;
    }
    .nav {
        margin-bottom: 0;
    }
    .nav ul {
        margin: 0;
    }
    .modal-body {
        max-height: 420px;
    }
    .modal-body input {
        box-sizing: unset;
    }
</style>


<h1>Access Control</h1>

<div class="units_table">

    <a href="#addModal" role="button" class="btn btn-info" style="float: right;margin-bottom:5px;" data-toggle="modal">Add Account</a>

    @include('partials.users_list')

    <div id="addModal" class="modal hide fade">
        <div class="modal-header">
            <h3 id="myModalLabel">Add user account</h3>
        </div>

        <form action="{!! URL::to('prepago_installer/access_control/add_account_action') !!}" method="POST">
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

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script>
        $(function()
        {
            $('#schemes_select').select2();
        })
    </script>