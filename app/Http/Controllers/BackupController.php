<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class BackupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function database()
    {
        $databaseBackupDirectory = '../app/storage/backups/main';
        $tablesBackupDirectorys = '../app/storage/backups/individual_tables';

        $databaseBackups = Data::getFiles($databaseBackupDirectory);
        $databaseSize = Data::getDatabaseSize();
        $tables = Data::getDatabaseTables();
        $estimatedTime = $databaseSize / 46;

        $this->layout->page = view('backup.database', [
            'databaseBackups' => $databaseBackups,
            'databaseSize' => $databaseSize,
            'estimatedTime' => $estimatedTime,
            'tables' => $tables,
        ]);
    }

    public function databaseSubmit()
    {
        Data::backupDB();

        return 'Done.';
    }

    public function databaseSubmitTable()
    {
        $table = Input::get('table');

        Data::backupTable($table);

        return 'Done.';
    }

    public function databaseRemoveFile()
    {
        $file = Input::get('file');

        Data::removeFile($file);

        return 'Done.';
    }

    public function databaseRestore()
    {
    }

    public function databaseRestoreTable()
    {
        $file = Input::get('file');
        $database = Input::get('database');
        $file_info = Input::get('file_info');

        Data::restoreTable($file, $database, $file_info);

        return 'Done.';
    }
}
