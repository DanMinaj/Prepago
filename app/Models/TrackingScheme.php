<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingScheme extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracking_schemes';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function stamp($scheme_number, $status)
    {
        $scheme = Scheme::where('id', $scheme_number)->first();

        if ($scheme) {
            $entry = self::where('date', date('Y-m-d'))->where('scheme_number', $scheme_number)->first();

            if (! $entry) {
                $entry = new self();
                $entry->scheme_number = $scheme_number;
                $entry->customers = Customer::where('scheme_number', $scheme_number)->count();
                $entry->readings = PermanentMeterDataReadingsAll::where('scheme_number', $scheme_number)
                ->whereRaw("(time_date LIKE '%".date('Y-m-d')."%')")
                ->where('reading1', '>', 0)
                ->count();
                $entry->checks = 1;
                $entry->online = $status;

                if ($status == 1) {
                    $entry->online_times++;
                    $entry->uptime_percentage = 100;
                    $entry->last_online = date('Y-m-d H:i:s');
                }

                if ($status == 0) {
                    $entry->offline_times++;
                    $entry->uptime_percentage = 0;
                    $entry->last_offline = date('Y-m-d H:i:s');
                }

                $entry->status_log = serialize([
                    ["$status" => date('Y-m-d H:i:s')],
                ]);

                try {
                    if ($scheme->sim) {
                        if (! empty($scheme->sim->extra)) {
                            $extra = unserialize($scheme->sim->extra);
                            $entry->network_log = serialize([
                                [
                                    "$status" => [
                                        'network' => $extra->last_network,
                                        'last_network_time' => $extra->last_mcc_mnc,
                                        'time' => date('Y-m-d H:i:s'),
                                    ],
                                ],
                            ]);
                        }
                    }
                } catch (Exception $e) {
                }

                $entry->date = date('Y-m-d');
                $entry->last_poll = date('Y-m-d H:i:s');

                $entry->save();
            } else {
                $entry->customers = Customer::where('scheme_number', $scheme_number)->count();
                $entry->readings = PermanentMeterDataReadingsAll::where('scheme_number', $scheme_number)
                ->whereRaw("(time_date LIKE '%".date('Y-m-d')."%')")
                ->where('reading1', '>', 0)
                ->count();
                $entry->checks++;

                if ($status == 1) {
                    $entry->online_times++;
                    $entry->last_online = date('Y-m-d H:i:s');
                }

                if ($status == 0) {
                    $entry->offline_times++;
                    $entry->last_offline = date('Y-m-d H:i:s');
                }

                $log = unserialize($entry->status_log);
                if (! is_array($log)) {
                    $log = ([
                        ["$status" => date('Y-m-d H:i:s')],
                    ]);
                } else {
                    array_push($log, ["$status" => date('Y-m-d H:i:s')]);
                }

                $network_log = unserialize($entry->network_log);
                if (! is_array($network_log)) {
                    //echo 'hi';
                    if ($scheme->sim) {
                        if (! empty($scheme->sim->extra)) {
                            $extra = unserialize($scheme->sim->extra);
                            $network_log = serialize([
                                [
                                    "$status" => [
                                        'network' => $extra->last_network,
                                        'last_network_time' => $extra->last_mcc_mnc,
                                        'time' => date('Y-m-d H:i:s'),
                                    ],
                                ],
                            ]);
                        }
                    }
                } else {
                    $extra = [];
                    if ($scheme->sim) {
                        $extra = $scheme->sim->extra;
                        $extra = unserialize($extra);
                    }

                    array_push($network_log, ["$status" => [
                        'network' => $extra->last_network,
                        'last_network_time' => $extra->last_mcc_mnc,
                        'time' => date('Y-m-d H:i:s'),
                    ]]);
                }

                $entry->status_log = serialize($log);
                $entry->network_log = serialize($network_log);
                $entry->online = $status;
                $entry->last_poll = date('Y-m-d H:i:s');
                $entry->uptime_percentage = ($entry->online_times / $entry->checks) * 100;
                $entry->save();
            }
        }
    }
}
