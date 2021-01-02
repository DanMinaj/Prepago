<?php

namespace App\Repositories;

use App\Models\IOUStorage;
use App\Models\PaymentStorage;
use App\Models\PermanentMeterData;
use App\Models\RegisteredPhonesWithApps;
use App\Models\Scheme;
use App\Models\SMSMessage;
use App\Models\Tariff;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;



class PayoutReportRepository extends ReportsRepository
{
    public function __construct()
    {
        $this->log = new Logger('Temporary Payments Logs');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/temporary_payments_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    public function setSchemeNumber($schemeNumber)
    {
        $this->scheme_number = $schemeNumber;
    }

    public function calculateDaysDiff()
    {
        $fromCarbonDate = $this->getDate('from', false, true); // format is Y-m-d 00:00:00
        $toCarbonDate = $this->getDate('to', false, true)->addSecond(); // add 1 sec here so that we get the correct days diff (format is Y-m-d 23:59:59)

        $daysDiff = $toCarbonDate->diffInDays($fromCarbonDate);

        return $daysDiff;
    }

    public function getPaymentsInfo($param = null)
    {
        $payments = PaymentStorage::inScheme($this->scheme_number)
                    ->whereBetween('time_date', [$this->getDate('from', true), $this->getDate('to', true)]);

        if ($param == 'count') {
            return $payments->count();
        }
        if ($param == 'sum') {
            return $payments->sum('amount');
        }

        return $payments->get();
    }

    public function getNumberOfSMS()
    {
        return  SMSMessage::charged()
                ->inScheme($this->scheme_number)
                ->whereBetween('date_time', [$this->getDate('from', true), $this->getDate('to', true)])
                ->count();
    }

    public function getAppsInstalled()
    {
        if ($this->getDate('from', true) >= '2020-01-01') {
            return RegisteredPhonesWithApps::getNewApps($this->scheme_number, $this->getDate('from', true), $this->getDate('to', true));
        }

        return RegisteredPhonesWithApps::inScheme($this->scheme_number)
                ->whereBetween('date_added', [$this->getDate('from', true), $this->getDate('to', true)])
                ->count();
    }

    public function getIOUChargeable()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->pluck('IOU_chargeable');
    }

    public function getIOUChargeableInfo($param = null)
    {
        $iou = IOUStorage::inScheme($this->scheme_number)
                ->whereBetween('time_date', [$this->getDate('from', true), $this->getDate('to', true)]);

        if ($param == 'count') {
            return $iou->count();
        }
        if ($param == 'sum') {
            return $iou->sum('amount');
        }

        return $iou->get();
    }

    public function getMetersInfo($param = null, $all = false)
    {
        $meterCharge = $this->getMeterCharge();

        $pmd = PermanentMeterData::inScheme($this->scheme_number);
        if (! $all) {
            $pmd = $pmd->whereBetween('install_date', [$this->getDate('from', true), $this->getDate('to', true)]);
        }

        if ($param == 'count') {
            return $pmd->count();
        }
        if ($param == 'total') {
            return $meterCharge * $pmd->count() * $this->calculateDaysDiff();
        }

        return $pmd->get();
    }

    public function getMeterCharge()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->pluck('daily_customer_charge');
    }

    public function getSchemeTotalUsage()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->first()->totalUsage($this->getDate('from'), $this->getDate('to'));
    }

    public function getSchemeAvgUsage()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->first()->avgDailyUsage($this->getDate('from'), $this->getDate('to'));
    }

    public function getSchemeAvgCost()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->first()->avgDailyCost($this->getDate('from'), $this->getDate('to'));
    }

    public function getT1()
    {
        return $tariff = Tariff::where('scheme_number', $this->scheme_number)->first()->tariff_1;
    }

    public function getT2()
    {
        return $tariff = Tariff::where('scheme_number', $this->scheme_number)->first()->tariff_2;
    }
}
