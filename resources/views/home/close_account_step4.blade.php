</div>
<div class="cl"></div>

<h1>Close Account Procedure</h1>

<div class="admin2" style="width: 500px">

    <h3>Step 4 - Customer Balance Information</h3>

    <div>
        <strong>New Balance: </strong> {!! $currencySign !!}{{ $data['customer']->balance }}
    </div>

    <div style="clear:both; margin: 20px 0; float:right;">
        <a href="#myModal{!! $data['customer']->id !!}" role="button" data-toggle="modal" class="btn btn-info" id="next-btn">Close Account</a>
    </div>

    @include('partials.close_account_modal', ['customer' => $data['customer'], 'landlords' => $data['landlords']])

</div>