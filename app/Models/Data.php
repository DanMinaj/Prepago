<?php
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    private static $username = 'root';
    private static $password = 'roslyn1234';

    public static function getFiles($directory)
    {
        ini_set('memory_limit', '-1');

        $files = [];
        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isDot()) {
                continue;
            }
            $files[] = (object) [
                'full_name' => $file->getPathname(),
                'name' => $file->getFilename(),
                'time' => $file->getCTime(),
                'size' => $file->getSize(),
                'size_mb' =>  number_format((($file->getSize()) / (pow(2, 20))), 5),
            ];
        }

        usort($files, function ($a, $b) {
            return strcmp($b->time, $a->time);
        });

        return $files;
    }

    public static function getDbBackupName()
    {
        ini_set('memory_limit', '-1');

        $date = date('d-m-Y');
        $dir = '/var/www/app/storage/backups/main/';

        $file = "prepago_db,$date.sql";
        if (file_exists($dir.$file)) {
            $i = 2;
            while (file_exists($dir.$file)) {
                $file = "prepago_db,$date".".v$i.sql";
                $i++;
            }
        } else {
            return $file;
        }

        return $file;
    }

    public static function getTableBackupName($table_name)
    {
        ini_set('memory_limit', '-1');

        $date = date('d-m-Y');
        $dir = "/var/www/app/storage/backups/individual_tables/$table_name/";

        $file = $table_name.",$date.sql";
        if (file_exists($dir.$file)) {
            $i = 2;
            while (file_exists($dir.$file)) {
                $file = $table_name.",$date".".v$i.sql";
                $i++;
            }
        } else {
            return $file;
        }

        return $file;
    }

    public static function backupDB()
    {
        ini_set('memory_limit', '-1');

        $backupName = self::getDbBackupName();
        $backupDir = "/var/www/app/storage/backups/main/$backupName";
        exec('(mysqldump -u'.self::$username.' -p'.self::$password." prepago > $backupDir) 2>&1", $output, $result);

        return $backupName;
    }

    public static function backupTable($table_name)
    {
        ini_set('memory_limit', '-1');

        $backupName = self::getTableBackupName($table_name);
        $backupDir = "/var/www/app/storage/backups/individual_tables/$table_name/$backupName";
        echo $backupDir;
        exec('(mysqldump -u'.self::$username.' -p'.self::$password." prepago $table_name > $backupDir) 2>&1", $output, $result);

        return $backupName;
    }

    public static function getDatabaseSize()
    {
        ini_set('memory_limit', '-1');

        $databaseSize = DB::select(
        DB::raw('SELECT table_schema "db",
			sum( data_length + index_length ) / 1024 / 1024 "size",
			sum( data_free )/ 1024 / 1024 "free"
		FROM information_schema.TABLES
		WHERE table_schema LIKE "%prepago%"
		GROUP BY table_schema
		ORDER BY size
		DESC LIMIT 1'));

        if ($databaseSize) {
            $databaseSize = $databaseSize[0]->size;
        } else {
            return 1;
        }

        return $databaseSize;
    }

    public static function getTableSize($table_name)
    {
        ini_set('memory_limit', '-1');

        $tableSize = DB::select(
        DB::raw('SELECT 
		table_name AS `table`, 
		round(((data_length + index_length) / 1024 / 1024), 2) `size` 
	FROM information_schema.TABLES 
	WHERE table_schema = "prepago"
		AND table_name = "'.$table_name.'"'));

        if ($tableSize) {
            $tableSize = $tableSize[0]->size;
        } else {
            return 1;
        }

        return $tableSize;
    }

    public static function getDatabaseTables()
    {
        ini_set('memory_limit', '-1');

        $tables = DB::select(DB::raw('show tables'));
        $backups_exist = 0;

        foreach ($tables as $t) {
            $tableDir = '../app/storage/backups/individual_tables/'.$t->Tables_in_prepago;
            if (! is_dir($tableDir)) {
                $oldmask = umask(0);
                mkdir($tableDir, 0777, true);
                umask($oldmask);
            }

            $t->backups = self::getFiles($tableDir);
            $t->size = self::getTableSize($t->Tables_in_prepago);

            if (count($t->backups) > 0) {
                $backups_exist++;
            }
        }

        usort($tables, function ($a, $b) {
            return $b->size > $a->size;
        });

        if ($backups_exist > 0) {
            usort($tables, function ($a, $b) {
                return strcmp(count($b->backups), count($a->backups));
            });
        }

        return $tables;
    }

    public static function removeFile($directory)
    {
        ini_set('memory_limit', '-1');

        if (substr($directory, 0, 22) != '../app/storage/backups' || substr_count($directory, '..') > 1) {
            echo substr($directory, 0, 22);
            die();
        }

        if (file_exists($directory) && substr_count($directory, '..') == 1) {
            unlink($directory);
            echo $directory;
        } else {
            echo 'file doesnt exist';
        }
    }

    public static function restoreDatabase()
    {
        ini_set('memory_limit', '-1');
    }

    public static function restoreTable($directory, $database, $file_info)
    {
        ini_set('memory_limit', '-1');

        if (substr($directory, 0, 22) != '../app/storage/backups' || substr_count($directory, '..') > 1) {
            echo substr($directory, 0, 22);
            die();
        }

        try {
            $table_name = $file_info['table_name'];

            if (file_exists($directory) && substr_count($directory, '..') == 1) {
                try {

                    //exec("(mysql -u" . Data::$username . " -p" . Data::$password . " prepago < $backupDir) 2>&1", $output, $result);

                    if ($database == 'prepago_debug') {
                        exec('(mysql -u'.self::$username.' -p'.self::$password." prepago_debug < $directory)", $output, $result);
                        echo "Restored $table_name to prepago_debug";
                    } else {
                        exec('(mysql -u'.self::$username.' -p'.self::$password." prepago < $directory)", $output, $result);
                        echo "Restored $table_name to prepago";
                    }

                    $log = new BackupRestoreLog();
                    $log->database = $database;
                    $log->table = $table_name;
                    $log->backup_file = $file_info['file_name'];
                    $log->backup_from = Carbon\Carbon::createFromTimestamp(filectime($directory))->format('d-m-Y H:i:s');
                    $log->save();
                } catch (PDOException $e) {
                    DB::unprepared('CREATE database prepago_debug');
                    echo 'Created database prepago_debug, since not exists';
                }
            } else {
                echo 'file doesnt exist';
            }
        } catch (Exception $e) {
            echo 'error: '.$e->getMessage();
        }
    }
}
