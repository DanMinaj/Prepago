<?php

class EVRechargeManager
{
    protected $rsCode;
    protected $customerID;
    protected $customer;
    protected $meterModel;
    protected $meter;
    protected $districtHeatingMeter;
    protected $errorMsg;

    public function __construct($rsCode, $customerID, PermanentMeterData $meterModel)
    {
        $this->rsCode = $rsCode;
        $this->customerID = $customerID;
        $this->meterModel = $meterModel;
    }

    protected function performChecksAndInit()
    {
        if (! $this->meter = $this->findEVMeterByRSCode($this->rsCode)) {
            return false;
        }

        if (! $this->districtHeatingMeter = $this->findDistrictHeatingMeterAssociatedWithEVMeter()) {
            $this->errorMsg = 'There is no district heating meter associated with the EV meter with RS code '.$this->rsCode;

            return false;
        }

        if (! $this->customer = Customer::find($this->customerID)) {
            $this->errorMsg = 'Customer does not exist';

            return false;
        }

        return true;
    }

    protected function findDistrictHeatingMeterAssociatedWithEVMeter()
    {
        return $this->meter->districtHeatingMeters->first();
    }

    protected function findEVMeterByRSCode()
    {
        if (! $meter = $this->meterModel->IsEV()->withRSCode($this->rsCode)->first()) {
            $this->errorMsg = 'An EV meter with RS Code '.$this->rsCode.' does not exist';

            return false;
        }

        return $meter;
    }

    protected function findEVUsage()
    {
        $ev_usage = EVUsage::where('customer_id', $this->customer->id)
        ->where('ev_meter_ID', $this->findDistrictHeatingMeterAssociatedWithEVMeter()->meter_ID)
        ->orderBy('id', 'DESC')
        ->first();

        return $ev_usage;
    }

    protected function rechargeManualStopProcedure()
    {
        //$this->meter->unblock();

        //$this->disassociateDistrictHeatingMeterFromCustomer();

        $this->meter->closeValve($this->customer->scheme_number);
    }

    protected function disassociateDistrictHeatingMeterFromCustomer()
    {
        $this->customer->disassociateDistrictHeatingMeter();
    }

    protected function getChargeFee()
    {
        if (! $meterReading = $this->districtHeatingMeter->getEVLatestReading()) {
            $this->errorMsg = 'There is no reading for the provided meter.';

            return false;
        }

        $tariff = Tariff::where('scheme_number', '=', $this->customer->scheme_number)->first();
        if (! $tariff) {
            $this->errorMsg = 'There is no tariff assigned to the customer.';

            return false;
        }

        return $meterReading->total_usage * $tariff->tariff_3;
    }

    protected function errorResponse()
    {
        return [
            'ev_recharge_status' => '',
            'flag_message' => 1,
            'error' => $this->errorMsg,
        ];
    }
}
