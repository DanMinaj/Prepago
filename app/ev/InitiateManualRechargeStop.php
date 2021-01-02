<?php

class InitiateManualRechargeStop extends EVRechargeManager {

    public function handle()
    {
        if (!$this->performChecksAndInit())
        {
            return $this->errorResponse();
        }

        $this->districtHeatingMeter->scheduleToShutOff();

        $this->rechargeManualStopProcedure();

        return [
            'ev_recharge_status' => 'off',
            'flag_message' => 0,
            'error' => ''
        ];
    }

    protected function performChecksAndInit()
    {
        if (!parent::performChecksAndInit())
        {
            return false;
        }

        if (!$this->meter->rechargeInProgress())
        {
            $this->errorMsg = 'The EV Meter with RS Code ' . $this->rsCode . ' is currently not in use.';
            return false;
        }

        return true;
    }

}