<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PaypointController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $payments = PaymentStorage::where('time_date', '>', '2018-07-18')->orderBy('time_date', 'DESC')->get(['customer_id']);

        $most_recent_paypoint = [];

        $total_paypoint_no = 0;
        $total_paypoint_amt = 0;

        foreach ($payments as $key=>$p) {
            $customer = Customer::where('id', $p->customer_id)->first();

            if (! $customer) {
                continue;
            }

            // Get last topup from July 18th onwards
            $last_topup = PaymentStorage::where('customer_id', $customer->id)->orderBy('time_date', 'DESC')->where('time_date', '>=', '2018-07-18')->first();

            if (! $last_topup) {
                continue;
            }

            if (substr($last_topup->ref_number, 0, 3) != 'PPR') {
                continue;
            }

            $total_paypoint_no += $customer->countPayments('paypoint', '2018-07-18');
            $total_paypoint_amt += $customer->sumPayments('paypoint', '2018-07-18');

            if (isset($most_recent_paypoint[$p->customer_id])) {
                continue;
            }

            $most_recent_paypoint[$p->customer_id] = [
                'customer' => $customer,
                'last_topup' => $last_topup,
            ];
        }

        $this->layout->page = View::make('report/paypoint/index_paypoint_reports',
            [
                'most_recent_paypoint' => $most_recent_paypoint,
                'total_paypoint_no' => $total_paypoint_no,
                'total_paypoint_amt' => $total_paypoint_amt,
            ]
        );
    }
}
