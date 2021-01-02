<?php

class MeterController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function installed_meters()
    {
        $meters = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)->where('installation_confirmed', '=', 1)->orderby('in_use', 'asc')->get();
        $meterReadingsQuery = PermanentMeterDataReadings::with('permanentMeter')->where('scheme_number', '=', Auth::user()->scheme_number);
        $csv_url = URL::to('create_csv/meter_readings');

        if (Request::isMethod('post') || Input::get('from')) {
            $toWithoutTime = Input::get('to') ? date('Y-m-d', strtotime(Input::get('to'))) : date('Y-m-d');
            $to = $toWithoutTime.' 23:59:59';
            $from = date('Y-m-d', strtotime(Input::get('from')));
            $meterReadingsQuery = $meterReadingsQuery->where('time_date', '>=', $from)->where('time_date', '<=', $to);
            $csv_url = URL::to('create_csv/meter_readings/'.$to.'/'.$from);

            if (Request::isMethod('post')) {
                return Redirect::to('installed_meters?from='.$from.'&to='.$toWithoutTime);
            }
        }

        $meterReadings = $meterReadingsQuery->orderBy('time_date', 'desc')->paginate(500);

        $this->layout->page = View::make('meter/list_meters', [
            'meters' 		=> $meters,
            'meterReadings' => $meterReadings,
            'csv_url'		=> $csv_url,
        ]);
    }

    public function search_installed_meters()
    {
        $meters = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)
            ->where('installation_confirmed', '=', 1)
            ->where(function ($query) {
                $query->orWhere('meter_number', 'like', '%'.Input::get('search_box').'%')
                      ->orWhere('house_name_number', 'like', '%'.Input::get('search_box').'%')
                      ->orWhere('street1', 'like', '%'.Input::get('search_box').'%');
            })
            ->orderby('in_use', 'asc')
            ->get();

        $this->layout->page = View::make('meter/list_meters', ['meters' => $meters]);
    }

    public function meter_data($meter_id)
    {
        $meter = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)->where('ID', '=', $meter_id)->get()->first();
        $readings = PermanentMeterDataReadings::where('scheme_number', '=', Auth::user()->scheme_number)->where('permanent_meter_id', '=', $meter_id)->get();

        $abb = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();

        $this->layout->page = View::make('meter/meter_data', ['meter' => $meter, 'readings' => $readings, 'abb' => $abb['unit_abbreviation']]);
    }

    public function meter_data_search()
    {
        $meter_id = Input::get('meter_id');
        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));

        $meter = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)->where('ID', '=', $meter_id)->get()->first();
        $readings = PermanentMeterDataReadings::where('scheme_number', '=', Auth::user()->scheme_number)
                    ->where('permanent_meter_id', '=', $meter_id)
                    ->where('time_date', '>=', $from)
                    ->where('time_date', '<=', $to)
                    ->get();

        $abb = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();

        $this->layout->page = View::make('meter/meter_data', ['meter' => $meter, 'readings' => $readings, 'abb' => $abb['unit_abbreviation']]);
    }

    public function meterLookup()
    {
        $meter_lookup = MeterLookup::all();

        $this->layout->page = View::make('settings/meter_lookup', [
            'meter_lookup' => $meter_lookup,
        ]);
    }

    public function meterLookupSubmit()
    {
        $meters = [];

        foreach (Input::all() as $key => $m) {
            if (strpos($key, '|') === false) {
                continue;
            }

            $parts = explode('|', $key);
            $id = $parts[0];
            $input_name = $parts[1];

            $meters[$id][$input_name] = $m;
        }

        foreach ($meters as $key => $m) {
            $lookup_id = $key;
            $ml = MeterLookup::find($lookup_id);
            if ($ml) {
                foreach ($m as $name => $value) {
                    $ml->$name = $value;
                }
            }
            $ml->save();
        }

        return Redirect::back()->with(['successMessage' => 'Successfully saved changes']);
    }

    public function meterLookupEdit($id)
    {
        $meter = MeterLookup::find($id);

        if (! $meter) {
            $this->layout->page = View::make('settings/meter_lookup_edit', [
                'meter' => null,
                'id' => $id,
            ]);
        }

        $this->layout->page = View::make('settings/meter_lookup_edit', [
            'meter' => $meter,
            'id' => $id,
        ]);
    }

    public function meterLookupEditSubmit($id)
    {
        $meter = MeterLookup::find($id);

        if (! $meter) {
            return Redirect::back()->with(['errorMessage' => 'Meter lookup ID '.$id.' not found!']);
        }

        foreach (Input::all() as $key => $value) {
            $meter->$key = $value;
        }

        $meter->save();

        return Redirect::back()->with(['successMessage' => 'Successfully saved changes']);
    }

    public function bulkMeterSetupSubmit2()
    {
        try {
            $action = strtolower(Input::get('action'));
            $scheme = Input::get('scheme');
            $input = Input::get('input');
            $street = Input::get('street');
            $scheme = Scheme::where('scheme_nickname', $scheme)->first();

            if (! $scheme) {
                throw new Exception('That scheme is invalid..');
            }
            if (empty($input)) {
                throw new Exception('Please fill in the input');
            }
            $lines = explode(PHP_EOL, $input);
            $invalid_lines = [];
            $inserted = [];
            $updated = [];

            foreach ($lines as $k => $v) {
                try {
                    $line = str_replace(',', '', $v);
                    $parts = explode(' ', $line);

                    if (count($parts) < 3) {
                        $invalid_lines[] = [
                            'value' => $v,
                            'line' => ($k + 1),
                            'reason' => 'parts < 3',
                        ];
                        continue;
                    }

                    $house_name_number = preg_replace('/[^A-Za-z0-9]/', '', $parts[0]);
                    $username = $house_name_number.strtolower($scheme->scheme_nickname);

                    if ((preg_match('/[a-z]/i', $parts[0]))) {
                        $house_name_number = preg_replace('/[^0-9]/', '', $parts[0]);
                        $username = $parts[0];
                    }

                    $scu = preg_replace('/[^A-Za-z0-9]/', '', $parts[1]);
                    $meter = preg_replace('/[^A-Za-z0-9]/', '', $parts[2]);
                    $meter2 = 'N/A';

                    if (count($parts) > 3) {
                        $meter2 = preg_replace('/[^A-Za-z0-9]/', '', $parts[3]);
                    }

                    $pmd = PermanentMeterData::where('username', $username)->first();

                    if ($pmd) {
                        $new_pmd = PermanentMeterData::createPMD($scheme->scheme_number,
                            $meter, $scu, $house_name_number,
                            $action, $username, $street, $meter2);

                        if (isset($new_pmd['error'])) {
                            throw new Exception($new_pmd['error']);
                        }

                        if (! isset($new_pmd['error'])) {
                            $updated[] = [
                                'value' => $v,
                                'line' => ($k + 1),
                                'id' => $new_pmd->ID,
                            ];
                        }
                    } else {
                        $new_pmd = PermanentMeterData::createPMD($scheme->scheme_number,
                            $meter, $scu, $house_name_number,
                            $action, $username, $street, $meter2);

                        if (isset($new_pmd['error'])) {
                            throw new Exception($new_pmd['error']);
                        }
                        if (! isset($new_pmd['error'])) {
                            $inserted[] = [
                                'value' => $v,
                                'line' => ($k + 1),
                                'id' => $new_pmd->ID,
                            ];
                        }
                    }

                    //echo count($parts) . '<br/>';
                } catch (Exception $e) {
                    $invalid_lines[] = [
                            'value' => $v,
                            'line' => ($k + 1),
                            'reason' => $e->getMessage().' ('.$e->getLine().')',
                    ];
                    continue;
                }
            }

            if ($action == 'insert') {
                if (count($invalid_lines) > 0) {
                    return Redirect::back()->with([
                        'errorMessage' => 'Failed to insert '.count($invalid_lines).' records.',
                        'successMessage' => 'Successfully inserted '.count($inserted).' records and updated '.count($updated).' records',
                        'failed' => $invalid_lines,
                        'success' => $inserted,
                        'updated' => $updated,
                    ]);
                } else {
                    return Redirect::back()->with([
                        'successMessage' => 'Successfully inserted '.count($inserted).' records and updated '.count($updated).' records',
                        'success' => $inserted,
                        'updated' => $updated,
                    ]);
                }
            } else {
                return Redirect::back()->with([
                    'warningMessage' => 'Previewing records ',
                ]);
            }
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'An error occured: '.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function bulkMeterSetup()
    {
        $lookup = MeterLookup::where('applied_schemes', 'like', '%'.Auth::user()->scheme_number.'%')->first();
        $scheme = Scheme::find(Auth::user()->scheme_number);

        if (! $lookup) {
            $lookup = MeterLookup::first();
        }

        $this->layout->page = View::make('settings/bulk_meter_setup', [
            'lookup' => $lookup,
            'scheme' => $scheme,
        ]);
    }

    public function bulkMeterSetupSubmit()
    {
        try {
            $scheme = Scheme::find(Auth::user()->scheme_number);

            $action = Input::get('action');

            $meter_last_8 = Input::get('last_8');
            $scu_last_8 = Input::get('scu_last_8');

            $data_format = Input::get('format');

            $data = Input::get('data');

            $street = Input::get('street1');

            $lines = explode(PHP_EOL, $data);

            $count = 0;

            $returned_pmds = [];

            foreach ($lines as $line) {
                $parts = explode(' ', $line);

                if (count($parts) < 3) {
                    continue;
                }

                // House number is 1st part
                $house_no = trim($parts[0]);

                // Username is 2nd part
                $username = trim($parts[1]);

                // SCU number is 3rd part
                $scu_no = trim($parts[2]);

                // Meter number is 3rd part
                $meter_no = $scheme->prefix;
                if (count($parts) > 3) {
                    $meter_no = trim($parts[3]);
                }

                if (PermanentMeterData::where('username', $username)->first()) {
                    $new_pmd = PermanentMeterData::createPMD(Auth::user()->scheme_number, $meter_no, $scu_no, $house_no, $action, $username, $street);
                } else {
                    $new_pmd = PermanentMeterData::createPMD(Auth::user()->scheme_number, $meter_no, $scu_no, $house_no, $action, $username, $street);
                }

                array_push($returned_pmds, $new_pmd);

                $count++;
            }

            if ($action == 'preview') {
                return Redirect::back()->with([
                    'warningMessage' => "Previewing <b>$count</b> records ",
                    'returned_pmds' => $returned_pmds,
                ]);
            } else {
                foreach ($returned_pmds as $r) {
                    if (isset($r['error'])) {
                        return Redirect::back()->with([
                            'errorMessage' => 'Error: '.$r['error'],
                            'returned_pmds' => $returned_pmds,
                        ]);

                        return;
                    }
                }

                return Redirect::back()->with([
                    'successMessage' => "Successfully inserted <b>$count</b> records ",
                    'returned_pmds' => $returned_pmds,
                ]);
            }
        } catch (Exception $e) {
            echo 'Error: '.$e->getMessage();
            die();
        }
    }

    public function scanSetup()
    {
        $meterLookup = Auth::user()->scheme->lookup;

        $this->layout->page = View::make('settings.scan_setup', [
            'meterLookup' => $meterLookup,
        ]);
    }

    public function scanSetupSubmit()
    {
        try {
            $existing = [];
            $inserted = [];
            $errors = [];
            $total = [];

            $input = str_replace('>', '', str_replace('<', '', Input::get('scan')));

            $lines = preg_split("/\r\n|\n|\r/", $input);

            foreach ($lines as $key => $l) {
                if (strpos($l, 'MBusData') === false) {
                    continue;
                }

                $id2 = explode('"', explode('id2="', $l)[1])[0];
                $id3 = explode('"', explode('id3="', $l)[1])[0];

                $mbus_address_exists = MBusAddressTranslation::where('8digit', $id2)->first();

                if ($mbus_address_exists) {
                    if ($mbus_address_exists['16digit'] != $id3) {
                        array_push($errors, $mbus_address_exists);
                        // fix the error
                        $mbus_address_exists['16digit'] = $id3;
                        $mbus_address_exists->save();
                    }

                    array_push($existing, $mbus_address_exists);
                } else {
                    $mbus_address_exists = new MBusAddressTranslation();
                    $mbus_address_exists['8digit'] = $id2;
                    $mbus_address_exists['16digit'] = $id3;
                    $mbus_address_exists->save();
                    array_push($inserted, $mbus_address_exists);
                }

                array_push($total, $mbus_address_exists);
            }

            usort($existing, function ($first, $second) {
                return strlen($first->permanentMeter) > strlen($second->permanentMeter);
            });

            return Redirect::back()->with([
                'successMessage' => 'Success',
                'input' => $input,
                'existing' => $existing,
                'inserted' => $inserted,
                'total' => $total,
                'errors' => $errors,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
        Cme3100 New Functions for management & collection of data
        Created by Daniel Nwabueze 22/05/19

     **/
    public function incomingCme3100Meter()
    {
        try {
            $device_address = Input::get('secondary_address');
            $pmd = PermanentMeterData::whereRaw("(meter_number LIKE '%$device_address%')")->first();

            if (! $pmd) {
                Cme3100Log::error("Couldn't find a corresponding permanent_meter_data for device $device_address.");

                return;
            }

            $data = Input::get('data');
            $reading = 0;

            // Use ths section to process the data and grab reading
            //$reading = sksksk;

            // Timestamp cold possibly come from the actually device request
            //$time_date = Input::get('timestmap');

            $time_date = date('Y-m-d H:i:s');

            // Save to permanent_meter_data_readings_all
            $new_reading = new PermanentMeterDataReadingsAll();
            $new_reading->time_date = $time_date;
            $new_reading->scheme_number = $pmd->scheme_number;
            $new_reading->permanent_meter_id = $pmd->ID;
            $new_reading->reading1 = $reading;
            //$new_reading->save();
        } catch (Exception $e) {
            Cme3100Log::error('Error in incomingCme3100Meter(): '.$e->getMessage());
        }
    }

    public function incomingCme3100Report()
    {
        try {
        } catch (Exception $e) {
            Cme3100Log::error('Error in incomingCme3100Report(): '.$e->getMessage());
        }
    }

    public function outgoingCme3100Action($action)
    {
        try {
            switch ($action) {

                default:
                    return Response::json([
                        'error' => 'Action not found!',
                    ]);
                break;

            }
        } catch (Exception $e) {
            Cme3100Log::error('Error in outgoingCme3100Action(): '.$e->getMessage());
        }
    }
}
