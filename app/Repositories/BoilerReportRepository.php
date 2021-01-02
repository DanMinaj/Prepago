<?php

namespace App\Repositories;

use App\Models\PermanentMeterData;
use Illuminate\Support\Facades\Auth;


class BoilerReportRepository extends ReportsRepository
{
    public function getReportData()
    {
        $from = $this->getDate('from', true);
        $to = $this->getDate('to', true);

        if ($from && $to) {
            $meters = PermanentMeterData::with(['latestReadings' => function ($query) use ($from, $to) {
                $query->where('time_date', '>=', $from)
                    ->where('time_date', '<=', $to)
                    ->orderBy('time_date');
            }, ])->where('is_boiler_room_meter', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->get();
        } else {
            $meters = PermanentMeterData::with(['latestReadings' => function ($query) {
                $query->orderBy('time_date')->paginate(11);
            }, ])->where('is_boiler_room_meter', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->get();
        }

        return $meters;
    }
}
