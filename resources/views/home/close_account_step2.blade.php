</div>
<div class="cl"></div>

<h1>Close Account Procedure</h1>

<div class="admin2" style="width: 500px">

    <h3>Step 2 - Meter Readings Information</h3>

    <input type="hidden" name="total_usage" id="total_usage" value="{!! $data['total_usage'] !!}" />

    @include('partials.close_account_modal', ['customer' => $data['customer'], 'landlords' => $data['landlords']])

    <table width="80%">
        <tr style="border-bottom: 1px solid black; line-height: 25px;">
            <td>Meter Reading at last billing</td>
            <td align="right">{{ $data['dhm']->latest_reading }}</td>
        </tr>
        <tr style="border-bottom: 1px solid black; line-height: 25px;">
            <td>Current Reading</td>
            <td align="right">{{ $data['dhm']->sudo_reading }}</td>
        </tr>
        <tr style="line-height: 25px;">
            <td><strong>Total Usage</strong></th>
            <td align="right"><strong>{{ $data['total_usage'] }}</strong></td>
        </tr>
    </table>

    <div style="clear:both; margin: 20px 0; float:right;">
        <a href="javascript: void(0);" onclick="javascript: return continueToNextStep()" class="btn btn-info" id="next-btn">{!! $data['total_usage'] !== floatval(0) ? 'Next' : 'Close Account' !!}</a>
    </div>

</div>

<script>
    function continueToNextStep()
    {
        if ($("#total_usage").val() == 0)
        {
            $("#next-btn").attr({"href" : "#myModal{!! $data['customer']->id !!}", "role" : "button", "data-toggle" : "modal"});
        }
        else
        {
            $("#next-btn").attr('href', "{!! URL::to('close_account/'. $data['customer']->id . '/step3') !!}");
        }
    }
</script>