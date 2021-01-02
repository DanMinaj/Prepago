<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;



class Timer
{
    public $elapsed;
    public $started_at;
    public $stopped_at;

    public function __construct()
    {
        $this->started_at = microtime(true);
    }

    public static function start()
    {
        return new self();
    }

    public function elapsed()
    {
        $this->elapsed = microtime(true) - $this->started_at;

        return $this->elapsed;
    }

    public function stop()
    {
        $this->stopped_at = microtime(true);
        $this->elapsed = $this->stopped_at - $this->started_at;
    }
}
