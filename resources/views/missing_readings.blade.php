
</div>

<div><br/></div>

<h1>Missing Readings in Scheme # 3</h1>

<h3><a href="<?php echo $csv_url ?>" id="missing_readings_csv">Download CSV</a></h3>

<div id="all_customers">
    <div>
        Click on each checkbox to hide/show the corresponding column<br />
        <ul class="table-options">
            <li><input type="checkbox" checked="checked" id="customer_name"> Customer Name</li>
            <li><input type="checkbox" checked="checked" id="username"> Username</li>
            <li><input type="checkbox" checked="checked" id="email"> Email</li>
            <li><input type="checkbox" checked="checked" id="address"> Address</li>
            <li><input type="checkbox" checked="checked" id="barcode"> Barcode</li>
            <li><input type="checkbox" id="missing_readings">Missing Readings</li>
        </ul>
    </div>
    <table class="table missing-readings-report" style="border: 1px solid #ddd;">
        <thead>
        <tr>
            <th style="width: 15%">Customer Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Address</th>
            <th>Barcode</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->first_name . ' ' . $customer->surname }}</td>
                    <td>{{ $customer->username }}</td>
                    <td>{{ $customer->email_address }}</td>
                    <td>
                        {{ $customer->house_number_name ? $customer->house_number_name . ', ' : '' }}
                        {{ $customer->street1 ? $customer->street1 . ', ' : '' }}
                        {{ $customer->street2 ? $customer->street2 . ', ' : '' }}
                        {{ $customer->town ? $customer->town . ', ' : '' }}
                        {{ $customer->county ? $customer->county . ($customer->country ? ', ' : '') : '' }}
                        {{ $customer->country ? $customer->country : '' }}
                    </td>
                    <td>{{ $customer->barcode }}</td>
                </tr>
                @if (count($customer->missing_readings))
                    <tr id="customer_missing_readings_{!! $customer->id !!}" class="customer_missing_readings" style="background-color: #fff; display:none;">
                        <td class="customer_missing_readings_content">
                            <table width="90%" style="margin: 0 auto;" class="table-bordered customer_missing_readings_table" style="border: 1px solid #ddd">
                                <tr><th colspan="5" style="text-align: center">Missing Readings</th></tr>
                                <tr>
                                    <th>Missing Reading Start Date</th>
                                    <th>Missing Reading Start Reading</th>
                                    <th>Missing Reading End Date</th>
                                    <th>Missing Reading End Reading</th>
                                    <th>Missing Usage</th>
                                </tr>
                                @foreach ($customer->missing_readings as $missingReading)
                                    <tr>
                                        <td>{!! $missingReading['missing_reading_start_date'] !!}</td>
                                        <td>{!! $missingReading['missing_reading_start_value'] !!}</td>
                                        <td>{!! $missingReading['missing_reading_end_date'] !!}</td>
                                        <td>{!! $missingReading['missing_reading_end_value'] !!}</td>
                                        <td{!!$missingReading['missing_reading_end_value'] - $missingReading['missing_reading_start_value'] > 200 ? " style='background:#F9A9A6'" : '' !!}>{!! $missingReading['missing_reading_end_value'] - $missingReading['missing_reading_start_value'] !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
	$("#missing_readings_csv").click(function(){
		$(this).click(function () { return false; });
    });
	
    $(document).ready(function() {

        $(".customer_missing_readings_content").attr('colspan', $('table.missing-readings-report thead tr th').length);

        $("input[type='checkbox']").click(function() {

            var checked = $(this).is(":checked");
            var index = $(this).parent().index();

            if ($(this).attr('id') == 'missing_readings')
            {
                if (checked)
                {
                    $(".customer_missing_readings").show();
                }
                else
                {
                    $(".customer_missing_readings").hide();
                }
            }

            if(checked)
            {
                $('table.missing-readings-report thead tr th:eq(' + index + ')').show();
            }
            else
            {
                $('table.missing-readings-report thead tr th:eq(' + index + ')').hide();
            }
            $('table.missing-readings-report tbody tr').not('.customer_missing_readings').each(function() {
                if (!$(this).parents('.customer_missing_readings').length)
                {
                    if(checked) {
                        $(this).find("td").eq(index).show();
                    } else {
                        $(this).find("td").eq(index).hide();
                    }
                }
            });

            if ($('table.missing-readings-report thead tr th:visible').length == 0)
            {
                $(".customer_missing_readings").hide();
            }
            else
            {
                if ($("#missing_readings").is(':checked'))
                {
                    $(".customer_missing_readings").show();
                }
                $(".customer_missing_readings_content").attr('colspan', $('table.missing-readings-report thead tr th:visible').length);
            }
        });
    });
</script>