<?php

namespace App\Http\Controllers;

use App\Models\PDF;
use App\Models\ReportSchedule;
use App\Models\Scheme;
use Carbon\Carbon as Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

class AidanReportController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function __construct()
    {
        $this->middleware('canAccessSystemReports', ['except' => 'barcode_reports']);
    }

    public function adviceNotes()
    {
        $scheme_id = Input::get('scheme');
        $start_date = Input::get('start_date');
        $end_date = Input::get('end_date');
        $multiple = false;

        if ($scheme_id == null) {
            $scheme_id = 'all';
        } else {
        }

        if ($scheme_id == null || empty($scheme_id) || $scheme_id == 'all') {
            $multiple = true;

            $scheme = Scheme::active(false);
            foreach ($scheme as $key => $s) {
                if ($s->scheme_number == 6 || $s->scheme_number == 20 || $s->scheme_number == 0) {
                    $scheme->forget($key);
                }
            }
        } else {
            $scheme = Scheme::where('scheme_number', $scheme_id)->get();
        }

        if (empty($start_date)) {
            $start_date = date('Y-m-d', strtotime('first day of this month'));
        }

        if (empty($end_date)) {
            $end_date = date('Y-m-d');
        }

        $parsed_start = Carbon::parse($start_date);
        $parsed_end = Carbon::parse($end_date);
        $days = ($parsed_end->diffInDays($parsed_start)) + 1;

        $vat = Input::get('vat');
        if (empty($vat)) {
            $vat = 13.5;
        }

        $company_name = Input::get('company_name');
        if (empty($company_name)) {
            $company_name = '';
        }

        $payments_charge = Input::get('payments_charge');
        if (empty($payments_charge)) {
            $payments_charge = 7.00;
        }
        $app_charge = Input::get('app_charge');
        if (empty($app_charge)) {
            $app_charge = 3.00;
        }
        $meter_charge = Input::get('meter_charge');
        if (empty($meter_charge)) {
            $meter_charge = 0.04;
        }
        $iou_charge = Input::get('iou_charge');
        if (empty($iou_charge)) {
            $iou_charge = 0;
        }
        $statements_charge = Input::get('statements_charge');
        if (empty($statements_charge)) {
            $statements_charge = 0.25;
        }
        $app_support = Input::get('app_support');
        if (empty($app_support)) {
            $app_support = 0.25;
        }
        $vat_number = Input::get('vat_number');
        if (empty($vat_number)) {
            $vat_number = 'IE9850930S';
        }

        $autotopup_charge = Input::get('autotopup_charge');
        if (empty($autotopup_charge)) {
            $autotopup_charge = 1.95;
        }

        $sms_cost = Input::get('sms_cost');
        if (empty($sms_cost)) {
            $sms_cost = 0.12;
        }

        $premium_sms_cost = Input::get('premium_sms_cost');
        if (empty($premium_sms_cost)) {
            $premium_sms_cost = 0.50;
        }

        $deleted_customers_charge = Input::get('deleted_customers_charge');
        if (empty($deleted_customers_charge)) {
            $deleted_customers_charge = 2.00;
        }

        $blue_accounts_charge = Input::get('blue_accounts_charge');
        if (empty($blue_accounts_charge)) {
            $blue_accounts_charge = 0.06;
        }

        if ($scheme->count() == 1) {
            $company_name = $scheme->first()->company_address;

            if (strlen(Input::get('company_name')) > 1) {
                $sfirst = $scheme->first();
                $sfirst->company_address = Input::get('company_name');
                $sfirst->save();
                $company_name = $sfirst->company_address;
            }
        }

        if (! empty(Input::get('cover_note'))) {
            $schemes = Input::get('schemes');
            $schemes = explode(',', $schemes);
            $schemes = Scheme::whereIn('scheme_number', $schemes)->get();
            $collected = 0;
            $payments_out_total = 0;
            $payments_out_vat = 0;
            $vat_owed = 0;
            $active_autotopups = 0;
            $active_autotopups_ex_vat = 0;
            $active_autotopups_inc_vat = 0;

            foreach ($schemes as $k => $s) {
                Scheme::getReportInformation($s, $start_date, $end_date, $vat, $payments_charge, $app_charge, $meter_charge, $iou_charge, $statements_charge, $app_support, $vat_number, $company_name, $autotopup_charge, $sms_cost, $premium_sms_cost, $deleted_customers_charge, $blue_accounts_charge);
                $s->vat_payment += ($s->autotopup_inc_vat - $s->autotopup_ex_vat);
                $collected += $s->value_of_payments;
                $payments_out_total += $s->scheme_payment;
                $payments_out_vat += $s->invoiced_amount;
                $vat_owed += $s->vat_payment;
                $active_autotopups += $s->autotopup_active;
                $active_autotopups_ex_vat += $s->autotopup_ex_vat;
                $active_autotopups_inc_vat += $s->autotopup_inc_vat;
            }

            $payments_per_total = ($payments_out_vat / $collected);

            if (empty($company_name)) {
                $company_name = '(Unset Company Name)';
            }

            if (Input::get('pdf') == 'true') {
                $pdf = PDF::loadView('report.aidan.cover_note', [
                    'company_name' 			=> $company_name,
                    'schemes' 				=> $schemes,
                    'collected' 			=> $collected,
                    'payments_per_total' 	=> $payments_per_total,
                    'payments_out_total' 	=> $payments_out_total,
                    'payments_out_vat' 		=> $payments_out_vat,
                    'vat_owed' 				=> $vat_owed,
                    'active_autotopups' 		=> $active_autotopups,
                    'active_autotopups_ex_vat' 	=> $active_autotopups_ex_vat,
                    'active_autotopups_inc_vat' => $active_autotopups_inc_vat,
                ]);

                return $pdf->download('CoverNote-'.$schemes[0]->ref_pa.'-'.$company_name.'.pdf');
            } else {
                return $this->layout->page = view('report.aidan.cover_note', [
                    'company_name' 			=> $company_name,
                    'schemes' 				=> $schemes,
                    'collected' 			=> $collected,
                    'payments_per_total' 	=> $payments_per_total,
                    'payments_out_total' 	=> $payments_out_total,
                    'payments_out_vat' 		=> $payments_out_vat,
                    'vat_owed' 				=> $vat_owed,
                    'active_autotopups' 		=> $active_autotopups,
                    'active_autotopups_ex_vat' 	=> $active_autotopups_ex_vat,
                    'active_autotopups_inc_vat' => $active_autotopups_inc_vat,
                ]);
            }
        }

        if (! empty(Input::get('total_report'))) {
            $company_name = Input::get('company_name');
            $schemes = Input::get('schemes');
            $schemes = explode(',', $schemes);
            $schemes = Scheme::whereIn('scheme_number', $schemes)->get();
            $collected = 0;
            $payments_out_total = 0;
            $payments_out_vat = 0;
            $vat_owed = 0;

            foreach ($schemes as $k => $s) {
                Scheme::getReportInformation($s, $start_date, $end_date, $vat, $payments_charge, $app_charge, $meter_charge, $iou_charge, $statements_charge, $app_support, $vat_number, $company_name, $autotopup_charge, $sms_cost, $premium_sms_cost, $deleted_customers_charge, $blue_accounts_charge);
                $collected += $s->value_of_payments;
                $payments_out_total += $s->scheme_payment;
                $payments_out_vat += $s->invoiced_amount;
                $vat_owed += $s->vat_payment;
            }

            $payments_per_total = ($payments_out_vat / $collected);

            return $this->layout->page = view('report.aidan.total_report', [
                'company_name' 			=> $company_name,
                'schemes' 				=> $schemes,
                'collected' 			=> $collected,
                'payments_per_total' 	=> $payments_per_total,
                'payments_out_total' 	=> $payments_out_total,
                'payments_out_vat' 		=> $payments_out_vat,
                'vat_owed' 				=> $vat_owed,
            ]);
        }

        foreach ($scheme as $k => $s) {
            Scheme::getReportInformation($s, $start_date, $end_date, $vat, $payments_charge, $app_charge, $meter_charge, $iou_charge, $statements_charge, $app_support, $vat_number, $company_name, $autotopup_charge, $sms_cost, $premium_sms_cost, $deleted_customers_charge, $blue_accounts_charge);
        }

        if (! empty(Input::get('pdf'))) {
            if (Input::get('mass') == true) {
                $scheme_numbers = Input::get('scheme_number');
                $scheme_numbers = explode(',', $scheme_numbers);

                $start = ReportSchedule::prepare('zip_advice_notes', $scheme_numbers, (object) ['folder' => date('Y-m-d'), 'start_date' => $start_date, 'end_date' => $end_date, 'vat' => $vat, 'payments_charge' => $payments_charge, 'app_charge' => $app_charge, 'meter_charge' => $meter_charge, 'iou_charge' => $iou_charge, 'statements_charge' => $statements_charge, 'app_support' => $app_support, 'vat_number' => $vat_number, 'company_name' => $company_name, 'autotopup_charge' => $autotopup_charge]);

                return Response::json([
                    'successMessage' => 'done',
                    'start'			 => $start,
                 ]);
            } else {
                if (Input::get('multi') == 'true') {
                    ini_set('max_execution_time', '0');
                    $scheme_numbers = Input::get('scheme_number');
                    $scheme_n = explode(',', $scheme_numbers);
                    $scheme = Scheme::whereIn('scheme_number', $scheme_n)->get();
                    foreach ($scheme as $k => $s) {
                        Scheme::getReportInformation($s, $start_date, $end_date, $vat, $payments_charge, $app_charge, $meter_charge, $iou_charge, $statements_charge, $app_support, $vat_number, $company_name, $autotopup_charge, $sms_cost, $premium_sms_cost, $deleted_customers_charge, $blue_accounts_charge);
                    }

                    $pdf = PDF::loadView('report.aidan.advice_notes_pdf_multi', [
                        'schemes' => $scheme,
                        'fullscreen' 	=> false,
                    ]);

                    return $pdf->download('lol.pdf');
                } else {
                    $s = Scheme::where('scheme_number', Input::get('scheme_number'))->first();
                    $s = Scheme::getReportInformation($s, $start_date, $end_date, $vat, $payments_charge, $app_charge, $meter_charge, $iou_charge, $statements_charge, $app_support, $vat_number, $company_name, $autotopup_charge, $sms_cost, $premium_sms_cost, $deleted_customers_charge, $blue_accounts_charge);

                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml(view('report.aidan.advice_notes_pdf', [
                        'company_name'	=> $company_name,
                            's'				=> $s,
                            'fullscreen' 	=> false,
                    ]));
                    $dompdf->set_option('isRemoteEnabled', true);
                    $dompdf->set_option('debugKeepTemp', true);
                    $dompdf->set_option('isHtml5ParserEnabled', true);
                    $dompdf->setPaper('A4', 'portrait');
                    // (Optional) Setup the paper size and orientation
                    //$dompdf->setPaper('A4', 'landscape');

                    // Render the HTML as PDF
                    $dompdf->render();

                    // Output the generated PDF to Browser
                    $dompdf->stream('AdviceNote-'.$s->ref_pa.'_'.$s->scheme_nickname.'_'.$s->month.'.pdf', ['Attachment' => false]);

                    // $pdf = PDF::loadView('report.aidan.advice_notes_pdf', [
                        // 'company_name'	=> $company_name,
                        // 's'				=> $s,
                        // 'fullscreen' 	=> false,
                    // ]);
                    // return $pdf->stream("AdviceNote-" . $s->ref_pa . "_" . $s->scheme_nickname . "_" . $s->month . ".pdf");
                }
            }

            //$pdf->set('isRemoteEnabled',true);
        }

        if (! empty(Input::get('fullscreen'))) {
            $scheme_number = Input::get('scheme_number');
            $s = null;
            foreach ($scheme as $k => $v) {
                if ($v->scheme_number == $scheme_number) {
                    $s = $v;
                }
            }
            $scheme = $s;

            return view('report.aidan.advice_notes_pdf', [
                'start_date' 		=> $start_date,
                'end_date' 			=> $end_date,
                'company_name' 		=> $company_name,
                'days' 				=> $days,
                'scheme_id' 		=> $scheme_number,
                'scheme' 			=> $scheme,
                's' 				=> $s,
                'multiple' 			=> $multiple,
                'vat_number' 		=> $vat_number,
                'vat' 				=> $vat,
                'payments_charge' 	=> $payments_charge,
                'app_charge' 		=> $app_charge,
                'meter_charge' 		=> $meter_charge,
                'iou_charge' 		=> $iou_charge,
                'autotopup_charge' 	=> $autotopup_charge,
                'statements_charge' => $statements_charge,
                'app_support' 		=> $app_support,
                'sms_cost' 			=> $sms_cost,
                'premium_sms_cost' 	=> $premium_sms_cost,
                'blue_accounts_charge' 	=> $blue_accounts_charge,
                'deleted_customers_charge' 			=> $deleted_customers_charge,
                'fullscreen'		=> true,
            ]);

            return;
        }

        $this->layout->page = view('report/aidan/advice_notes', [
            'start_date' 		=> $start_date,
            'end_date' 			=> $end_date,
            'days' 				=> $days,
            'scheme_id' 		=> $scheme_id,
            'scheme' 			=> $scheme,
            'multiple' 			=> $multiple,
            'vat_number' 		=> $vat_number,
            'vat' 				=> $vat,
            'company_name' 		=> $company_name,
            'payments_charge' 	=> $payments_charge,
            'app_charge' 		=> $app_charge,
            'meter_charge' 		=> $meter_charge,
            'iou_charge' 		=> $iou_charge,
            'autotopup_charge' 	=> $autotopup_charge,
            'statements_charge' => $statements_charge,
            'sms_cost' 			=> $sms_cost,
            'premium_sms_cost' 	=> $premium_sms_cost,
            'blue_accounts_charge' 	=> $blue_accounts_charge,
            'deleted_customers_charge' 			=> $deleted_customers_charge,
            'app_support' 		=> $app_support,

        ]);
    }

    public function scheduleGetInfo()
    {
        try {
            $id = Input::get('id');

            $schedule = ReportSchedule::where('id', $id)->first();

            if (! $schedule) {
                throw new Exception("Report schedule #$id does not exist!");
            }

            return $schedule->getProgress();
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
