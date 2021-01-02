<?php


class CSVBoilerReportController extends CSVBaseController {

    public function __construct(BoilerReportRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index($from = null, $to = null)
    {
        if ($from && $to)
        {
            //convert "from" and "to" dates from Y-m-d to d-m-Y
            $fromFormatted = $this->convertDateToFormat('d-m-Y', $from);
            $toFormatted = $this->convertDateToFormat('d-m-Y', $to);

            $this->repo->setFromDate($fromFormatted, true);
            $this->repo->setToDate($toFormatted, true);
        }

        $csvData = "";
        $data = $this->repo->getReportData();

        $csvData .= 'Meter Number,';
        $csvData .= 'Date,';
        $csvData .= 'Reading,';
        $csvData .= "\n";

        foreach ($data as $meter)
        {
            if ($meter->latestReadings && $meter->latestReadings->count())
            {
                foreach ($meter->latestReadings as $meterReading)
                {
                    $csvData .= $meter->meter_number . ',';
                    $csvData .= $meterReading->time_date . ',';
                    $csvData .= $meterReading->reading1 . ',';
                    $csvData .= "\n";
                }
            }
        }

        $csvFilename = 'boiler_report';

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $csvFilename .".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $csvData;
    }

}