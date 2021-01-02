</div>

<div><br/></div>
<h1>Carlinn Report</h1>

<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > Carlinn Report
    <div class="cl"></div>
    <!--<h3><a href="<?php echo $csv_url ?>">Download CSV</a></h3>-->
    <br /><br />
    <div id="all_customers">
        <div>
            Click on each checkbox to hide/show the corresponding column<br />
            <ul class="table-options">
                <li><input type="checkbox" checked="checked" id="customer_name"> Customer Name</li>
                <li><input type="checkbox" checked="checked" id="username"> Username</li>
                <li><input type="checkbox" checked="checked" id="email"> Email</li>
                <li><input type="checkbox" checked="checked" id="address"> Address</li>
                <li><input type="checkbox" checked="checked" id="barcode"> Barcode</li>
                <li><input type="checkbox" id="readings"> Readings</li>
            </ul>
        </div>
        <table id="sortthistable" class="table carlinn-report" style="border: 1px solid #ddd;">
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
                @if (count($customer->readings))
                    <tr id="customer_readings_{!! $customer->id !!}" class="customer_readings" style="background-color: #fff; display:none;">
                        <td class="customer_readings_content">
                            <table width="90%" style="margin: 0 auto;" class="table-bordered readings_table" style="border: 1px solid #ddd">
                                <tr><th colspan="8">Readings</th></tr>
                                <tr>
                                    <td>Date</td>
                                    <td>Cost Of Day</td>
                                    <td>Start Day Reading</td>
                                    <td>End Day Reading</td>
                                    <td>Total Usage</td>
                                    <td>Standing Charge</td>
                                    <td>Unit Charge</td>
                                    <td>Arrears Repayment</td>
                                </tr>
                                @foreach ($customer->readings as $customerReading)
                                    <tr>
                                        <td>{!! $customerReading->date !!}</td>
                                        <td>{!! $customerReading->cost_of_day !!}</td>
                                        <td>{!! $customerReading->start_day_reading !!}</td>
                                        <td>{!! $customerReading->end_day_reading !!}</td>
                                        <td>{!! $customerReading->total_usage !!}</td>
                                        <td>{!! $customerReading->standing_charge !!}</td>
                                        <td>{!! $customerReading->unit_charge !!}</td>
                                        <td>{!! $customerReading->arrears_repayment !!}</td>
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
</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {

        $(".customer_readings_content").attr('colspan', $('table.carlinn-report thead tr th').length);

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
                $('table.carlinn-report thead tr th:eq(' + index + ')').show();
            }
            else
            {
                $('table.carlinn-report thead tr th:eq(' + index + ')').hide();
            }
            $('table.carlinn-report tbody tr').not('.customer_readings').each(function() {
                if (!$(this).parents('.customer_readings').length)
                {
                    if(checked) {
                        $(this).find("td").eq(index).show();
                    } else {
                        $(this).find("td").eq(index).hide();
                    }
                }
            });

            if ($('table.carlinn-report thead tr th:visible').length == 0)
            {
                $(".customer_readings").hide();
            }
            else
            {
                if ($("#readings").is(':checked'))
                {
                    $(".customer_readings").show();
                }
                $(".customer_readings_content").attr('colspan', $('table.carlinn-report thead tr th:visible').length);
            }
        });
    });
</script>