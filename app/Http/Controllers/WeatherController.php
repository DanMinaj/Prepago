<?php

class WeatherController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function topups()
    {
        $this->layout->page = View::make('weather/topups');
    }

    public function get_topups2()
    {
    }

    public function get_topups()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Longford')->get();

        $data = [];

        foreach ($result as $r) {
            $ps = PaymentStorage::where('time_date', 'LIKE', date_format(date_create($r->date_time), 'Y-m-d').'%');
            $ps_count = $ps->count();
            $amount = 0;
            foreach ($ps->get() as $a) {
                $amount = $amount + $a->amount;
            }
            array_push(
                $data,
                [date_format(date_create($r->date_time), 'd-M-y'),
                    intval($r->currentTemperature),
                    '<div style="padding: 10px; text-align:center; line-height: 2em; font-size: 1.2em;"><span style="font-weight: bold;">'.date_format(date_create($r->date_time), 'd-M-y').'</span><br />'.$r->currentTemperature.'ºC, '.$ps_count.' = €'.$amount.'</div>',
                    intval($ps_count),
                    '<div style="padding: 10px; text-align:center; line-height: 2em; font-size: 1.2em;"><span style="font-weight: bold;">'.date_format(date_create($r->date_time), 'd-M-y').'</span><br />'.$r->currentTemperature.'ºC, '.$ps_count.' = €'.$amount.'</div>',
                ]);
        }

        return $data;
    }

    public function csv_topups()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $from2 = Input::get('from2');
        $to2 = Input::get('to2');

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Longford')->get();

        if ($from2 && $to2) {
            $result2 = Weather::whereBetween('date_time', [$from2, $to2])->where('county', '=', 'Longford')->get();
        }

        $this->layout = '';
        $filename = 'exports/topups.csv';
        $delimiter = ';';

        $f = fopen($filename, 'w');

        fputcsv($f, ['Weather vs Top Ups - Date Range 1'], $delimiter);
        fputcsv($f, ['Date', 'Temperature', 'Top Ups', 'Top Ups Amount', 'Top Up Mode', 'Top Up Mean', 'Top Up Median'], $delimiter);
        foreach ($result as $r) {
            $ps = PaymentStorage::where('time_date', 'LIKE', date_format(date_create($r->date_time), 'Y-m-d').'%');
            $ps_count = $ps->count();

            $amount = 0;
            $count = $ps->count();
            $allAmounts = [];
            foreach ($ps->get() as $a) {
                $amount = $amount + $a->amount;
                array_push($allAmounts, $a->amount);
            }

            if ($count != 0) {
                $mean = $amount / $count;

                sort($allAmounts, SORT_NUMERIC);
                $amountCount = count($allAmounts);
                $middleIndex = floor($amountCount / 2);
                $testMedian = $allAmounts[$middleIndex];
                if ($amountCount % 2 != 0) {
                    $median = $testMedian;
                } else {
                    $median = ($testMedian + $allAmounts[$middleIndex - 1]) / 2;
                }

                $allAmountsInString = [];
                foreach ($allAmounts as $aA) {
                    array_push($allAmountsInString, strval($aA));
                }
                $values = array_count_values($allAmountsInString);
                $mode = array_search(max($values), $values);
            } else {
                $mean = 0;
                $median = 0;
                $mode = 0;
            }

            fputcsv($f, [
                date_format(date_create($r->date_time), 'd-M-y'),
                $r->currentTemperature,
                $ps_count,
                number_format((float) $mode, 2, '.', ''),
                number_format((float) $mean, 2, '.', ''),
                number_format((float) $median, 2, '.', ''),
                ], $delimiter);
        }
        if ($from2 && $to2) {
            fputcsv($f, [''], $delimiter);
            fputcsv($f, ['Weather vs Top Ups - Date Range 2'], $delimiter);
            fputcsv($f, ['Date', 'Temperature', 'Top Ups', 'Top Ups Amount'], $delimiter);
            foreach ($result2 as $r) {
                $ps = PaymentStorage::where('time_date', 'LIKE', date_format(date_create($r->date_time), 'Y-m-d').'%');
                $ps_count = $ps->count();

                $amount = 0;
                $count = $ps->count();
                $allAmounts = [];
                foreach ($ps->get() as $a) {
                    $amount = $amount + $a->amount;
                    array_push($allAmounts, $a->amount);
                }

                if ($count != 0) {
                    $mean = $amount / $count;

                    sort($allAmounts, SORT_NUMERIC);
                    $amountCount = count($allAmounts);
                    $middleIndex = floor($amountCount / 2);
                    $testMedian = $allAmounts[$middleIndex];
                    if ($amountCount % 2 != 0) {
                        $median = $testMedian;
                    } else {
                        $median = ($testMedian + $allAmounts[$middleIndex - 1]) / 2;
                    }

                    $allAmountsInString = [];
                    foreach ($allAmounts as $aA) {
                        array_push($allAmountsInString, strval($aA));
                    }
                    $values = array_count_values($allAmountsInString);
                    $mode = array_search(max($values), $values);
                } else {
                    $mean = 0;
                    $median = 0;
                    $mode = 0;
                }

                fputcsv($f, [
                    date_format(date_create($r->date_time), 'd-M-y'),
                    $r->currentTemperature,
                    $ps_count,
                    number_format((float) $mode, 2, '.', ''),
                    number_format((float) $mean, 2, '.', ''),
                    number_format((float) $median, 2, '.', ''),
                    ], $delimiter);
            }
        }
        fclose($f);

        return URL::to('/').'/'.$filename;
    }

    public function extra_topups()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Longford')->get();

        $amount = 0;
        $count = 0;
        $allAmounts = [];
        foreach ($result as $r) {
            $ps = PaymentStorage::where('time_date', 'LIKE', date_format(date_create($r->date_time), 'Y-m-d').'%');
            $count = $count + $ps->count();
            foreach ($ps->get() as $a) {
                $amount = $amount + $a->amount;
                array_push($allAmounts, $a->amount);
            }
        }

        if ($count != 0) {
            $mean = $amount / $count;

            sort($allAmounts, SORT_NUMERIC);
            $amountCount = count($allAmounts);
            $middleIndex = floor($amountCount / 2);
            $testMedian = $allAmounts[$middleIndex];
            if ($amountCount % 2 != 0) {
                $median = $testMedian;
            } else {
                $median = ($testMedian + $allAmounts[$middleIndex - 1]) / 2;
            }

            $allAmountsInString = [];
            foreach ($allAmounts as $aA) {
                array_push($allAmountsInString, strval($aA));
            }
            $values = array_count_values($allAmountsInString);
            $mode = array_search(max($values), $values);
        } else {
            $mean = 0;
            $median = 0;
            $mode = 0;
        }

        return [
            'mean' => $this->currencySign.number_format((float) $mean, 2, '.', ''),
            'median' => $this->currencySign.$median,
            'mode' => $this->currencySign.$mode,
            ];
    }

    public function heatUsage()
    {
        $this->layout->page = View::make('weather/heatUsage');
    }

    public function get_heatUsage2()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $from = $from.' 00:00:00';
        $to = $to.' 23:59:59';

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Dublin')->get();

        if (! $result) {
            return '';
        }

        $data = [];
        $data['dates'] = [];
        $data['temps'] = [];
        $data['usages'] = [];

        foreach ($result as $r) {
            $ps = DistrictHeatingUsage::where('date', '=', date_format(date_create($r->date_time), 'Y-m-d'))->where('scheme_number', '=', Auth::user()->scheme_number);

            $kWh = 0;

            foreach ($ps->get() as $a) {
                $kWh = $kWh + $a->total_usage;
            }

            array_push($data['usages'], $kWh);
            array_push($data['dates'], date_format(date_create($r->date_time), 'Y-m-d'));
            array_push($data['temps'], $r->currentTemperature);
        }

        return Response::json($data);
    }

    public function get_heatUsage()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $from = $from.' 00:00:00';
        $to = $to.' 23:59:59';

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Dublin')->get();

        if (! $result) {
            return '';
        }

        $data = [];

        foreach ($result as $r) {
            $ps = DistrictHeatingUsage::where('date', '=', date_format(date_create($r->date_time), 'Y-m-d'))->where('scheme_number', '=', Auth::user()->scheme_number);

            $kWh = 0;

            foreach ($ps->get() as $a) {
                $kWh = $kWh + $a->total_usage;
            }
            array_push(
                $data,
                [date_format(date_create($r->date_time), 'd-M-y'),
                    intval($r->currentTemperature),
                    '<div style="padding: 10px; text-align:center; line-height: 2em; font-size: 1.2em;"><span style="font-weight: bold;">'.date_format(date_create($r->date_time), 'd-M-y').'</span><br />'.$r->currentTemperature.'ºC, = '.$kWh.' kWh</div>',
                    intval($kWh),
                    '<div style="padding: 10px; text-align:center; line-height: 2em; font-size: 1.2em;"><span style="font-weight: bold;">'.date_format(date_create($r->date_time), 'd-M-y').'</span><br />'.$r->currentTemperature.'ºC, = '.$kWh.' kWh</div>',
                ]);
        }

        return $data;
    }

    public function extra_heatUsage()
    {
        $result = Weather::where('date_time', 'LIKE', date('Y-m-d').'%')->where('county', '=', 'Longford')->first();

        if (! $result) {
            $result = Weather::where('date_time', 'LIKE', date('Y-m-d', time() - 60 * 60 * 24).'%')->where('county', '=', 'Longford')->first();
        }

        return [
            'day1min' => $result->temperatureDay1Min,
            'day1max' => $result->temperatureDay1Max,
            'day2min' => $result->temperatureDay2Min,
            'day2max' => $result->temperatureDay2Max,
            'day3min' => $result->temperatureDay3Min,
            'day3max' => $result->temperatureDay3Max,
            'day4min' => $result->temperatureDay4Min,
            'day4max' => $result->temperatureDay4Max,
            'day5min' => $result->temperatureDay5Min,
            'day5max' => $result->temperatureDay5Max,
            ];
    }

    public function csv_heatUsage()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $from2 = Input::get('from2');
        $to2 = Input::get('to2');

        $result = Weather::whereBetween('date_time', [$from, $to])->where('county', '=', 'Longford')->get();

        if ($from2 && $to2) {
            $result2 = Weather::whereBetween('date_time', [$from2, $to2])->where('county', '=', 'Longford')->get();
        }

        $this->layout = '';
        $filename = 'exports/heat_usage.csv';
        $delimiter = ';';

        $f = fopen($filename, 'w');

        fputcsv($f, ['Weather vs Heat Usage - Date Range 1'], $delimiter);
        fputcsv($f, ['Date', 'Temperature', 'Total Usage'], $delimiter);
        foreach ($result as $r) {
            $ps = DistrictHeatingUsage::where('date', '=', date_format(date_create($r->date_time), 'Y-m-d'))->where('scheme_number', '=', 0);
            $kWh = 0;
            foreach ($ps->get() as $a) {
                $kWh = $kWh + $a->total_usage;
            }

            fputcsv($f, [
                date_format(date_create($r->date_time), 'd-M-y'),
                $r->currentTemperature,
                $kWh,
                ], $delimiter);
        }
        if ($from2 && $to2) {
            fputcsv($f, [''], $delimiter);
            fputcsv($f, ['Weather vs Heat Usage - Date Range 2'], $delimiter);
            fputcsv($f, ['Date', 'Temperature', 'Total Usage'], $delimiter);
            foreach ($result2 as $r) {
                $ps = DistrictHeatingUsage::where('date', '=', date_format(date_create($r->date_time), 'Y-m-d'))->where('scheme_number', '=', 0);
                $kWh = 0;
                foreach ($ps->get() as $a) {
                    $kWh = $kWh + $a->total_usage;
                }

                fputcsv($f, [
                    date_format(date_create($r->date_time), 'd-M-y'),
                    $r->currentTemperature,
                    $kWh,
                    ], $delimiter);
            }
        }
        fclose($f);

        return URL::to('/').'/'.$filename;
    }
}
