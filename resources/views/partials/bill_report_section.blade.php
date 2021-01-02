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
    <td>{{ $customer->total_usage }}</td>
    <td>{{ $customer->barcode }}</td>
    <td>{!! $currencySign !!} {{ $customer->paymentsTotal }}</td>
</tr>
@if (count($customer->payments))
    <tr id="customer_payments_{!! $customer->id !!}" class="customer_payments" style="background-color: #fff; display:none;">
        <td class="customer_payments_content">
            <table width="90%" style="margin: 0 auto;" class="table-bordered payments_table" style="border: 1px solid #ddd">
                <tr><th colspan="2">Payments</th></tr>
                <tr>
                    <td>Time</td>
                    <td>Amount</td>
                </tr>
                @foreach ($customer->payments as $customerPayment)
                    <tr>
                        <td>{!! $customerPayment->time_date !!}</td>
                        <td>
                            {!! $currencySign !!}
                            {{ $customerPayment->amount }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
@endif