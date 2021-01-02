<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReportScheduleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'report:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled reports';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Report Schedule Command');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/ReportScheduleCommand/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $schedules = ReportSchedule::where('is_first', 1)->where('all_completed', 0)->get();

        if (count($schedules) == 0) {
            echo 'There are no reports schedule..';
        }

        foreach ($schedules as $k => $schedule) {
            $iteration = $schedule->nextIteration();

            if ($iteration == null) {
                //echo "Completed!";
                $schedule->markComplete();

                return;
            }

            $run_data = unserialize($iteration->run_data);

            $path = '/var/www/html/reports/'.$run_data->folder;

            if ($iteration->is_first) {
                if (file_exists($path)) {
                    array_map('unlink', glob("$path/*.*"));
                    rmdir($path);
                }
            }

            if (! file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $scheme = Scheme::where('scheme_number', $iteration->scheme_number)->first();
            Scheme::getReportInformation($scheme, $run_data->start_date, $run_data->end_date, $run_data->vat, $run_data->payments_charge, $run_data->app_charge, $run_data->meter_charge, $run_data->iou_charge, $run_data->statements_charge, $run_data->app_support, $run_data->vat_number, $run_data->company_name, $run_data->autotopup_charge);

            if ($iteration->type == 'zip_advice_notes') {

                //echo "\nIteration #" . $iteration->id . ": SchemeID: " . $iteration->scheme_number . "\n";

                // instantiate and use the dompdf class

                $dompdf = new Dompdf\Dompdf();
                $dompdf->loadHtml(view('report.aidan.advice_notes_pdf', [
                    'company_name'	=> $scheme->company_name,
                    's'				=> $scheme,
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
                $output = $dompdf->output();

                // $pdf = PDF::loadView('report.aidan.advice_notes_pdf', [
                // 'company_name'	=> $scheme->company_name,
                // 's'				=> $scheme,
                // 'fullscreen' 	=> false,
                // ]);

                // $pdf->getDomPDF()->get_option('enable_html5_parser');
                // $pdf->save();
                // }

                $file = $path.'/AdviceNote-'.$scheme->scheme_number.'_'.$scheme->scheme_nickname.'_'.$scheme->month.'.pdf';
                try {

                //echo "saving.. to " . $file . "\n";

                    $f = file_put_contents($file, $output);

                    //if ($f) print 1;
                //else print 0;
                } catch (Exception $e) {
                    //echo "\n\nerror: ". $e->getMessage() . "\n" . $e->getLine();
                }
                //die();
            }

            $iteration->it_completed = 1;
            $iteration->save();

            if ($schedule->isComplete()) {
                //echo "Completed!";
                $schedule->markComplete();
            } else {
                //echo count($schedule->iterations(true)) . "/" . count($schedule->iterations());
            }
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
