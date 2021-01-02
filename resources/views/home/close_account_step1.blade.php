
</div>
<div class="cl"></div>

<h1>Close Account Procedure</h1>

<div class="admin2" style="width: 500px">

    <h3>Step 1 - Take last meter reading</h3>

	
	<div id="meter_read_test" class="iniSetup-container">

		<div class="iniSetupTitle">Meter read test</div>

		<div class="test_msg"></div>

		<button class="btn_setup" onclick="meter_read_test()">Test</button>

		<div class="clear"></div>
	</div>
	
					
    @include('partials.diagnostics', ['meter_id' => $data['meter_id'], 'meter_read_only' => 1])

	
    <div style="clear:both; margin: 20px 0; float:right;">
        <a href="{!! URL::to('customer_tabview_controller/show/'. $data['customer_id'] . '/step2') !!}" onclick="javascript: return continueToNextStep()" class="btn btn-info">Next</a>
    </div>
</div>

<script type="text/javascript">
    function continueToNextStep()
    {
        if ($("#meter_read_test_success").val() == '0')
        {
            alert('A successful meter reading is required to continue to the next step.');
            return false;
        }
        else
        {
            //call to insert the last meter reading in the corresponding tables
            $.ajax({
                type: 'POST',
                url: '{!! URL::to('close_account/' . $data['customer_id'] . '/step1') !!}',
                data: { 'unit_id' : {!! $data['meter_id'] !!} },
                success: function (resp, textStatus) {
                    if (resp.success)
                    {
                        window.location = "{!! URL::to('close_account/' . $data['customer_id'] . '/step2') !!}";
                    }
                    else if (resp.error)
                    {
                        alert(resp.error);
                    }
                    else
                    {
                        alert('An error occured');
                    }
                }
            });
        }

        //return true;
        return false;
    }
</script>
{!! HTML::script('resources/js/installer.js?98877') !!}