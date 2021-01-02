<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CacheUsage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cache:usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache customers district_heating_usage for easy access';

    /**
     * Set the log file & log file name.
     */
    private function setLog($name = 'default')
    {
        $this->log = new Logger($name);
        $this->log->pushHandler(new StreamHandler(__DIR__.'/CacheUsage/'.date('Y-m-d').'.log', Logger::INFO));
    }

    /**
     * Create a log entry in the log file.
     */
    public function log($msg, $print = true)
    {
        $this->log->info($msg);

        if ($print) {
            $this->info($msg);
        }
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        try {
            $time_start = microtime(true);

            ini_set('memory_limit', '-1');

            $this->setLog();

            DB::table('customers')->update([
                'reset_password_resent' => 0,
            ]);

            $customers = Customer::where('status', 1)->get();

            foreach ($customers as $c) {
                $dhu = DistrictHeatingUsage::where('customer_id', $c->id)->orderBy('id', 'DESC')
                ->where('start_day_reading', '>', 0)
                ->where('end_day_reading', '>', 0)->get();

                $data = [];

                foreach ($dhu as $k => $v) {
                    if ($v->start_day_reading > 0 && $v->end_day_reading <= 0) {
                        $v->end_day_reading = $v->start_day_reading;
                        $v->total_usage = 0;
                        $v->unit_charge = 0;
                    }

                    if ($v->end_day_reading > 0 && $v->start_day_reading <= 0) {
                        $v->start_day_reading = $v->end_day_reading;
                        $v->total_usage = 0;
                        $v->unit_charge = 0;
                    }

                    $pack = (object) [
                        'date' => $v->date,
                        'start_day_reading' => $v->start_day_reading,
                        'end_day_reading' => $v->end_day_reading,
                        'total_usage' => $v->total_usage,
                    ];

                    array_push($data, $pack);
                }

                $insert = serialize($data);

                $entry = CachedDistrictHeatingUsage::where('customer_id', $c->id)->first();
                if (! $entry) {
                    if (count($dhu) == 0) {
                    } else {
                        $entry = new CachedDistrictHeatingUsage();
                        $entry->customer_id = $c->id;
                        $entry->end_date = $dhu->first()->date;
                        $entry->start_date = $dhu[count($dhu) - 1]->date;
                        $entry->data = $insert;
                        $entry->save();
                    }
                } else {
                    $entry->data = $insert;
                    $entry->save();
                }
            }

            $this->info('Took '.number_format((microtime(true) - $time_start), 0).' seconds to complete');
        } catch (Exception $e) {
            $this->info($e->getMessage());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
