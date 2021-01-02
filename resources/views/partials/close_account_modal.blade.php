<div id="myModal<?php echo $customer->id?>" class="modal hide fade" >
    {!! Form::open(['url' => 'close_account', 'onsubmit' => 'return checkLandlord()']) !!}
    {!! Form::hidden('swap_from_id', $customer->id) !!}
    <div class="modal-header">
        <h3 id="myModalLabel">Close Account</h3>
    </div>
    <div class="modal-body">
        <p>
            <a href="{!! URL::to('open_account/swap/' . $customer->id) !!}">Create a new normal user or landlord</a>
        </p>
        @if ($customer->role == 'normal' && $landlords[$customer->id]->count())
            <select name="swap_to_id" id="landlords">
                <option value="">Select landlord</option>
                @foreach ($landlords[$customer->id] as $key => $val)
                    <option value="{!! $val->id !!}">{!! $val->username !!}</option>
                @endforeach
            </select>
            <div>
                <label>{!! Form::checkbox('swap_credit', 1, true, ['id' => 'swap_credit', 'onclick' => 'javascript: swapCredit()']) !!} Would you like to swap credit? ({!! $currencySign !!}{{ $customer->balance }})</label>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <input class="btn btn-danger" type="submit" value="Delete" id="submit">
    </div>
    {!! Form::close() !!}
</div>