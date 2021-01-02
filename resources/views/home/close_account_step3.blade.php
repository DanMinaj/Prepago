
</div>
<div class="cl"></div>

<h1>Close Account Procedure</h1>

<div class="admin2" style="width: 500px">

    <h3>Step 3 - Calculate charges</h3>

    @include('includes.notifications')

    {!! Form::open(['url' => URL::to('close_account/'. $data['customer_id'] . '/step3'), 'method' => 'POST']) !!}

        <div>
            <strong>Select a tariff:</strong>
        </div>

        <div>
            @if (!$data['tariffs'])
                There are no data to show
            @else
                <select name="tariff_type" id="tariff_type">
                    <option value="">-- SELECT --</option>
                    @for ($i = 1; $i < 6; $i++)
                        <?php $tariffName = "tariff_" . $i . "_name"; ?>
                        <?php $tariffIndex = "tariff_" . $i; ?>
                        @if ($data['tariffs']->{$tariffName})
                            <option value="tariff_{{ $i }}">{{ $data['tariffs']->{$tariffName} }}</option>
                        @endif
                    @endfor
                </select>
            @endif
        </div>

        <div style="clear:both; margin: 20px 0; float:right;">
            <button type="submit" onclick="javascript: return continueToNextStep()" class="btn btn-info">Next</button>
        </div>

    {!! Form::close() !!}
</div>

<script type="text/javascript">
    function continueToNextStep()
    {
        if (!$("#tariff_type").val())
        {
            alert('You have to select a tariff to continue to the next step');
            return false;
        }

        return true;
    }
</script>