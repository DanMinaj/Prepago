</div>
<div class="cl"></div>

<h1>Scheme Set Up</h1>

<div class="admin2" style="width: 500px">

    @include('includes.notifications')

    <h3>Step 1 - Set Up a New User Account</h3>

    <form action="{!! URL::to('scheme-setup/user-setup') !!}" method="POST">

        @include('partials.user_setup')

        <br /><br />

        <input type="submit" class="btn btn-info" value="Add Account">
    </form>

</div>