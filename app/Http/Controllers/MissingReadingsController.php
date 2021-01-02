<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);

class MissingReadingsController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $customerMissingReadings = new \Illuminate\Support\Collection();

        foreach (Customer::inScheme(3)->get() as $customer) {
            $readingToCompareTo = [
                'date' => null,
                'start_day_reading' => null,
            ];

            $customerInfo = $this->extractRelevantCustomerInfo($customer);

            foreach ($customer->validDistrictHeatingUsage()->remember(1440)->get() as $reading) {
                if (! $reading->end_day_reading >= $reading->start_day_reading) {
                    $readingToCompareTo['date'] = $reading->date;
                    $readingToCompareTo['start_day_reading'] = $reading->start_day_reading;
                    continue;
                }

                if (! is_null($readingToCompareTo['start_day_reading']) && $reading->start_day_reading > $readingToCompareTo['start_day_reading']) {
                    $customerInfo->missing_readings[] = [
                        'missing_reading_start_date' => $readingToCompareTo['date'],
                        'missing_reading_start_value' => $readingToCompareTo['start_day_reading'],
                        'missing_reading_end_date' => $reading->date,
                        'missing_reading_end_value' => $reading->start_day_reading,
                    ];

                    $readingToCompareTo['date'] = null;
                    $readingToCompareTo['start_day_reading'] = null;
                }
            }

            $customerMissingReadings->push($customerInfo);
        }

        $this->layout->page = View::make('missing_readings', [
            'csv_url' => URL::to('create_csv/missing_readings_reports'),
            'customers' => $customerMissingReadings->filter(function ($item) {
                return count($item->missing_readings);
            }),
        ]);
    }

    protected function extractRelevantCustomerInfo($customer)
    {
        $customerInfo = new stdClass();

        $customerInfo->id = $customer->id;
        $customerInfo->first_name = $customer->first_name;
        $customerInfo->surname = $customer->surname;
        $customerInfo->username = $customer->username;
        $customerInfo->email_address = $customer->email_address;

        $customerInfo->house_number_name = $customer->house_number_name;
        $customerInfo->street1 = $customer->street1;
        $customerInfo->street2 = $customer->street2;
        $customerInfo->town = $customer->town;
        $customerInfo->county = $customer->county;
        $customerInfo->country = $customer->country;

        $customerInfo->barcode = $customer->barcode;

        $customerInfo->missing_readings = [];

        return $customerInfo;
    }
}
