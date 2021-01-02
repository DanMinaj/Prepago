<?php

class Cme3100Log extends Eloquent
{
    public static function error($error)
    {
        DB::table('cme3100_error_logs')->insert([
            'log' => $error,
            'time_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getErrors($from = null, $to = null)
    {
        $data = false;

        if ($from == null || $to == null) {
            $data = DB::table('cme3100_error_logs')->get();
        } else {
            $data = DB::table('cme3100_error_logs')->whereRaw("(time_date >= $from AND time_date <= $to)")
            ->get();
        }

        return $data;
    }

    public static function debug($log)
    {
        DB::table('cme3100_debug_logs')->insert([
            'log' => $error,
            'time_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getDebug($from = null, $to = null)
    {
        $data = false;

        if ($from == null || $to == null) {
            $data = DB::table('cme3100_debug_logs')->get();
        } else {
            $data = DB::table('cme3100_debug_logs')->whereRaw("(time_date >= $from AND time_date <= $to)")
            ->get();
        }

        return $data;
    }

    public static function report($log)
    {
        DB::table('cme3100_report_logs')->insert([
            'log' => $error,
            'time_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getReports($from = null, $to = null)
    {
        $data = false;

        if ($from == null || $to == null) {
            $data = DB::table('cme3100_report_logs')->get();
        } else {
            $data = DB::table('cme3100_report_logs')->whereRaw("(time_date >= $from AND time_date <= $to)")
            ->get();
        }

        return $data;
    }
}
