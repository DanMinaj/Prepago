<?php

use Carbon\Carbon;

class ReportsRepository
{
    private $fromDate;
    private $toDate;
    private $csvURL;

    /* SETTERS */
    public function setCsvURL($url)
    {
        $this->csvURL = URL::to($url);
    }

    public function setFromDate($date, $appendTime = false)
    {
        if ($appendTime) {
            $date = Carbon::createFromFormat('d-m-Y', $date)->startOfDay();
        }
        $this->fromDate = $date;
    }

    public function setToDate($date, $appendTime = false)
    {
        if ($appendTime) {
            $date = Carbon::createFromFormat('d-m-Y', $date)->endOfDay();
        }
        $this->toDate = $date;
    }

    public function setDefaultFromDate()
    {
        $from = Carbon::now()->startOfMonth();
        $this->setFromDate($from);
    }

    public function setDefaultToDate()
    {
        $to = Carbon::now()->endOfDay();
        $this->setToDate($to);
    }

    /* GETTERS */
    public function getCsvURL()
    {
        if ($this->getDate('from') && $this->getDate('to')) {
            return $this->csvURL.'/'.$this->getDate('from').'/'.$this->getDate('to');
        }

        return $this->csvURL;
    }

    public function getDate($date, $withTime = false, $asCarbonObj = false)
    {
        //convert the date string to Carbon
        $carbonDate = $this->getDateAsCarbon($date);
        if ($carbonDate) {
            if ($asCarbonObj) {
                return $carbonDate;
            }

            if ($withTime) {
                return $carbonDate->toDateTimeString();
            }

            return $carbonDate->toDateString();
        }

        return null;
    }

    protected function getDateAsCarbon($date)
    {
        if ($this->{$date.'Date'}) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->{$date.'Date'});
        }

        return null;
    }

    public function validateDatepickerDate($date)
    {
        //format should be dd-mm-yyyy
        if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/', $date)) {
            return true;
        }

        return false;
    }
}
