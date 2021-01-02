<?php

namespace App\Http\Controllers;

use App\Models\DataLogger;
use App\Models\DataLoggersDiagnostics;
use App\Models\MBusAddressTranslation;
use App\Models\PermanentMeterData;
use App\Models\PermanentMeterDataMeterReadWebsite;
use App\Models\PermanentMeterDataTelegramRelaycheckWebsite;
use App\Models\Scheme;
use App\Models\Simcard;


class DiagnosticsButtonsController extends Controller
{
    public function meterTelegramTest($unitID)
    {
        try {
            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
            $scheme = Scheme::where('scheme_number', '=', $pmd->scheme_number)->get()->first();
            $pmdmrwMeterNumber2 = str_replace($scheme['prefix'], '', $pmd->meter_number2);

            $pmdtrw = new PermanentMeterDataTelegramRelaycheckWebsite();
            $pmdtrw->isTelegramReq = 1;
            $pmdtrw->permanent_meter_id = $unitID;
            $pmdtrw->scheme_number = $pmd->scheme_number;
            $pmdtrw->ICCID = $sim['ICCID'];
            $pmdtrw->data_logger_id = $pmd->data_logger_id;
            $pmdtrw->meter_number = str_replace($scheme['prefix'], '', $pmd->meter_number);
            $pmdtrw->meter_number2 = $pmdmrwMeterNumber2 ?: 'N/A';
            $pmdtrw->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function meterTelegramTestConfirm($unitID)
    {
        $pmdtrw = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', '=', $unitID)->orderBy('time_date', 'desc')->first();

        $dataLogger = DataLogger::where('id', $pmdtrw->data_logger_id)->first();

        if ($pmdtrw->complete == 0 && $pmdtrw->failed == 0) {
            if ($dataLogger) {
                if ($dataLogger->datalogger_active) {
                    return 'Pending..<br/>This SIM is under high load due to the scheme undergoing scheduled automatic meter reading.';
                }
            }

            return 'Pending..';
        }

        if ($pmdtrw['complete'] == 1 && $pmdtrw['telegram'] != 'Error') {
            return $this->formatTemperatures($pmdtrw['telegram']);
        }

        if ($pmdtrw->failed == 1 || $pmdtrw['telegram'] == 'Error') {
            return 'failed';
        }
    }

    public function relayTelegramTest($unitID)
    {
        try {
            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

            $pmdtrw = new PermanentMeterDataTelegramRelaycheckWebsite();
            $pmdtrw->isTelegramReq = 1;
            $pmdtrw->permanent_meter_id = $unitID;
            $pmdtrw->scheme_number = $pmd->scheme_number;
            $pmdtrw->ICCID = $sim['ICCID'];
            $pmdtrw->data_logger_id = $pmd->data_logger_id;
            $pmdtrw->meter_number = $pmd->scu_number;
            $pmdtrw->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function relayTelegramTestConfirm($unitID)
    {
        $pmdtrw = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', '=', $unitID)->orderBy('time_date', 'desc')->first();

        $dataLogger = DataLogger::where('id', $pmdtrw->data_logger_id)->first();

        if ($pmdtrw->complete == 0 && $pmdtrw->failed == 0) {
            if ($dataLogger) {
                if ($dataLogger->datalogger_active) {
                    return 'Pending..<br/>This SIM is under high load due to the scheme undergoing scheduled automatic meter reading.';
                }
            }

            return 'Pending..';
        }

        if ($pmdtrw['complete'] == 1 && $pmdtrw['telegram'] != 'Error') {
            return $this->formatTemperatures($pmdtrw['telegram']);
        }

        if ($pmdtrw->failed == 1 || $pmdtrw['telegram'] == 'Error') {
            return 'failed';
        }
    }

    public function checkValveTest($unitID)
    {
        try {

            /*

            Old checkValveTest - Disabled on 09/11/2018 - Daniel

            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

            $pmdtrw = new PermanentMeterDataTelegramRelaycheckWebsite();
            $pmdtrw->isTelegramReq = 0;
            $pmdtrw->permanent_meter_id = $unitID;
            $pmdtrw->scheme_number = $pmd->scheme_number;
            $pmdtrw->ICCID = $sim['ICCID'];
            $pmdtrw->data_logger_id = $pmd->data_logger_id;
            $pmdtrw->meter_number = $pmd->m_bus_relay_id;
            $pmdtrw->save();

            return 'success';
            */

            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
            $scheme = Scheme::where('scheme_number', '=', $pmd->scheme_number)->get()->first();

            $pmdmrwMeterNumber = str_replace($scheme['prefix'], '', $pmd->meter_number);
            $pmdmrwMeterNumber2 = str_replace($scheme['prefix'], '', $pmd->meter_number2);

            $pmdmrw = new PermanentMeterDataMeterReadWebsite();
            $pmdmrw->permanent_meter_id = $unitID;
            $pmdmrw->scheme_number = $pmd->scheme_number;
            $pmdmrw->ICCID = $sim['ICCID'];
            $pmdmrw->data_logger_id = $pmd->data_logger_id;
            $pmdmrw->meter_number = $pmdmrwMeterNumber;
            $pmdmrw->meter_number2 = $pmdmrwMeterNumber2 ?: 'N/A';
            $pmdmrw->save();

            $mbus16 = MBusAddressTranslation::where('8digit', $pmd->scu_number)->first();
            if ($mbus16) {
                $pmdmrw->scu_number_16 = $mbus16['16digit'];
            }

            $pmdmrw->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function checkValveTestConfirm($unitID)
    {
        /*

            Old checkValveTestConfirm - Disabled on 09/11/2018 - Daniel

        $pmdtrw = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', '=', $unitID)->orderBy('time_date', 'desc')->first();



        if ($pmdtrw['complete'] == 1) {
            return $pmdtrw['relayOnOff'] == 1 ? 'Valve is opened' : 'Valve is closed';
        } else {
            return 'failed';
        }

        */

        $pmdmrw = PermanentMeterDataMeterReadWebsite::where('permanent_meter_id', '=', $unitID)
        ->whereRaw('LENGTH(scu_number_16) > 6')->orderBy('time_date', 'desc')->first();

        $dataLogger = DataLogger::where('id', $pmdmrw->data_logger_id)->first();

        if ($pmdmrw->complete == 0 && $pmdmrw->failed == 0) {
            if ($dataLogger) {
                if ($dataLogger->datalogger_active) {
                    return 'Pending..<br/>This SIM is under high load due to the scheme undergoing scheduled automatic meter reading.';
                }
            }

            return 'Pending..';
        }

        if ($pmdmrw['complete'] == 1 && $pmdmrw->failed == 0) {
            if ($pmdmrw['valve_status'] == 'open') {
                return 'Valve is opened';
            }

            if ($pmdmrw['valve_status'] == 'closed') {
                return 'Valve is closed';
            }

            if ($pmdmrw['valve_status'] == 'unknown') {
                return 'Valve is unknown due to connection failure.';
            }
        }

        if ($pmdmrw['complete'] == 1 && $pmdmrw->failed == 1) {
            return 'failed';
        }
    }

    protected function formatTemperatures($telegram)
    {
        $xmlArr = simplexml_load_string($telegram);

        foreach ($xmlArr->DataRecord as $key => $record) {
            if (isset($record->Unit) && stripos($record->Unit, 'temperature')) {
                $record->Value = number_format((string) $record->Value / 10, 1);
            }
        }

        return $xmlArr->asXML();
    }

    public function dataLoggersTest($scheme_number)
    {
        try {
            $dld = new DataLoggersDiagnostics();
            $dld->scheme_number = $scheme_number;
            $dld->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function dataLoggersTestConfirm($scheme_number)
    {
        $dld = DataLoggersDiagnostics::where('scheme_number', $scheme_number)->orderBy('time_date', 'desc')->first();

        if ($dld['complete'] == 1) {
            if (strpos($dld->response, 'FAILED') !== false) {
                // failed
                return "<font color='red'>".$dld->response.'</font>';
            } else {
                return "<font color='green'>OK!</font>";
            }
        }
    }
}
