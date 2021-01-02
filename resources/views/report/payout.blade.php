</div>

<div><br/></div>
<h1>Payout Reports</h1>

<div style="float: right">

    <form method="post" action="<?php echo URL::to('system_reports/payout_reports') ?>"class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" name="from" type="text">
        <label>To</label>
        <input id="to" name="to" type="text">
        <input type="submit" value="search" class="btn-success"/>

    </form>
	
	<input type="hidden" name="start_date" id="start_date" value="{!! $data['start_date'] !!}" />
    <input type="hidden" name="end_date" id="end_date" value="{!! $data['end_date'] !!}" />

</div>


<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > Payout Reports
    <div class="cl"></div>
	
    @include('report.payout_content')

    @foreach($schemes as $schemeNumber => $schemeName)
        <div style="padding: 15px">
            <a href="" class="generate-payout-report" id="generate-payout-report-{!!$schemeNumber!!}" onclick="return generatePayoutReport({!!$schemeNumber!!})">
                Show payout report for scheme {!! $schemeName !!} (Scheme Number {!! $schemeNumber !!})
            </a>
            <div id="payout_content_{!! $schemeNumber !!}"></div>
        </div>
    @endforeach
</div>


<script type="text/javascript">

	$(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
    });	

    function generatePayoutReport(schemeNumber) {
        var url = "<?php echo URL::to('system_reports/payout_reports'); ?>";
        url = url + "/" + schemeNumber;
        if ($("#start_date").val() && $("#end_date").val()) {
            url = url + "?from=" + $("#start_date").val() + "&to=" + $("#end_date").val();
        }

        $("#generate-payout-report-" + schemeNumber).html('<b>Loading...</b>');

        $.ajax({
            type: 'GET',
            url: url,
            data: 'html',
            success: function(html, textStatus) {
                $("#generate-payout-report-" + schemeNumber).hide();
                $("#payout_content_" + schemeNumber).html(html);
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('An error occurred! ' + textStatus);
            }
        });

        return false;
    }
</script>