<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BackupCustomers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backup:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup customers table to dropbox';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Customer Backup Log');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/BackupCustomers/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        try {
            ini_set('memory_limit', '1056M');

            $customers = Customer::orderby('id', 'ASC')->get();
            $customerfoldersdirectory = '/var/www/app/storage/backups/customers';
            $directories = glob($customerfoldersdirectory.'/*', GLOB_ONLYDIR);

            $this->log->addInfo('Backing up customers at  '.date('d-m-Y H:i:s'));
            $this->info('Backing up customers at  '.date('d-m-Y H:i:s'));

            foreach ($customers as $customer) {
                $dir = '/var/www/app/storage/backups/customers/Customer '.$customer->id;

                if (! is_dir($dir)) {
                    mkdir($dir, 755, true);
                    echo "Created $dir <br/>";
                }

                if (is_dir($dir)) {
                    $information_dir = '/var/www/app/storage/backups/customers/Customer '.$customer->id.'/info';

                    if (! is_dir($information_dir)) {
                        mkdir($information_dir, 755, true);
                    }

                    if (is_dir($information_dir)) {
                        $information_file = '/var/www/app/storage/backups/customers/Customer '.$customer->id.'/info/info_'.date('d_m_Y').'.txt';
                        if (1 == 1) {

                            //Some simple example content.
                            $contents = 'This is data is generated as of '.date('d-m-Y H:i:s')."\nCustomer ID: ".$customer->id."\nCustomer Username: ".$customer->username."\nCustomer Barcode: ".$customer->barcode."\n";
                            //Save our content to the file.
                            file_put_contents($information_file, $contents);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->log->addInfo('Error occured while backing up customers at  '.date('d-m-Y H:i:s').' : '.$e->getMessage());
            $this->info('Error occured  '.$e->getMessage());
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
