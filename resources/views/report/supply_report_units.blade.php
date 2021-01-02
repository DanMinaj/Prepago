</div>

<div><br/></div>
<h1>Supply Report Units</h1>

<div style="float: right">
    
    <form method="post" action="<?php echo URL::to('system_reports/search_supply_report_units_by_date') ?>"class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" name="from" value="{!! $from !!}" type="text">
        <label>To</label>
        <input id="to" name="to"  value="{!! $to !!}" type="text">
        <input type="submit" value="search" class="btn-success"/>

    </form>

</div>

<div class="cl">&nbsp;</div>
<script type="text/javascript">
    $(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
    });

</script>


<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > Supply Report Units
    <h3>Total KWh Usage: <?php echo $total_usage; ?></h3>
    <h4>Total Customers: <?php echo count($customers); ?></h4>
    <h4>Range: <?php echo "$from - $to"; ?></h4>
    <div class="cl"></div>
    <h3><a href="<?php echo $csv_url?>">Download CSV</a></h3>
    
	<div>
        Click on each checkbox to hide/show the corresponding column<br />
        <ul class="table-options">
            <li><input type="checkbox" checked="checked" id="customer_name"> Customer Name</li>
            <li><input type="checkbox" checked="checked" id="barcode"> Barcode</li>
            <li><input type="checkbox" checked="checked" id="total_kwh_use"> Total KWh Use</li>
            <li><input type="checkbox" checked="checked" id="meter_id_number"> Meter ID Number</li>
            <li><input type="checkbox" id="readings"> Readings</li>
        </ul>
    </div>

    <table id="myTable" class="table table-bordered tablesorter sortthistable" style="border: 1px solid #ddd;">
		<thead>
			<tr>
				<th class="header">Customer Name</th>
				<th class="header">Barcode</th>
				<th class="header">Total KWh Use</th>
				<th class="header">Meter ID Number</th>
			</tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><a target="_blank" href="{!! URL::to('customer/' . $customer->id) !!}">({!! $customer->id !!}) <?php echo $customer->first_name." ".$customer->surname; ?></a></td>
                <td><?php echo $customer->barcode; ?></td>
                <td><?php echo $customer->total_usage; ?></td>
				<td><?php echo $customer->permanent_meter_number; ?></td>
            </tr>
			
			@if (count($customer->readings))
                <tr id="customer_readings_{!! $customer->id !!}" class="customer_readings" style="background-color: #fff; display:none;">
                    <td class="customer_readings_content">
                        <table width="90%" style="margin: 0 auto;" class="table-bordered payments_table" style="border: 1px solid #ddd">
                            <tr><th colspan="4">Readings</th></tr>
                            <tr>
                                <td><b>Date</b></td>
                                <td><b>First Reading</b></td>
                                <td><b>Last Reading</b></td>
                                <td><b>Total Usage</b></td>
                            </tr>
                            @foreach ($customer->dhu as $dhu)
                                <tr>
                                    <td>{!! $dhu['date'] !!}</td>
                                    <td>{!! $dhu['start_day_reading'] !!}</td>
                                    <td>{!! $dhu['end_day_reading'] !!}</td>
                                    <td>{!! $dhu['end_day_reading'] - $dhu['start_day_reading'] !!}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            @endif
        <?php endforeach; ?>
    </tbody>
    </table>




</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".customer_readings_content").attr('colspan', $('table.supply-report-units thead tr th').length);

		$(function() {
			$("#myTable").tablesorter();
		});

        $("input[type='checkbox']").click(function() {

            var checked = $(this).is(":checked");
            var index = $(this).parent().index();

            if ($(this).attr('id') == 'readings')
            {
                if (checked)
                {
                    $(".customer_readings").show();
                }
                else
                {
                    $(".customer_readings").hide();
                }
            }

            if(checked)
            {
                $('table.supply-report-units thead tr th:eq(' + index + ')').show();
            }
            else
            {
                $('table.supply-report-units thead tr th:eq(' + index + ')').hide();
            }
            $('table.supply-report-units tbody tr').not('.customer_readings').each(function() {
                if (!$(this).parents('.customer_readings').length)
                {
                    if(checked) {
                        $(this).find("td").eq(index).show();
                    } else {
                        $(this).find("td").eq(index).hide();
                    }
                }
            });

            if ($('table.supply-report-units thead tr th:visible').length == 0)
            {
                $(".customer_readings").hide();
            }
            else
            {
                if ($("#readings").is(':checked'))
                {
                    $(".customer_readings").show();
                }
                $(".customer_readings_content").attr('colspan', $('table.supply-report-units thead tr th:visible').length);
            }
        });
    });
</script>