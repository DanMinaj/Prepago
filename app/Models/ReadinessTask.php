<?php
use Illuminate\Database\Eloquent\Model;

class ReadinessTask extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'scheduled_readiness_tasks';

    public static function prepare($type, $usernames = [], $extra_data = [])
    {
        $new_task_id = self::orderBy('task_id', 'DESC')->first();
        if (! $new_task_id) {
            $new_task_id = 1;
        } else {
            $new_task_id = $new_task_id->task_id + 1;
        }

        foreach ($usernames as $k => $username) {
            $pmd = PermanentMeterData::where('username', $username)->orderBy('ID', 'DESC')->first();
            if (! $pmd) {
                return;
            }
            $task = new self();
            $task->task_id = $new_task_id;
            $task->order_id = $k + 1;
            $task->type = $type;
            $task->scheme_number = $pmd->scheme_number;
            $task->username = $pmd->username;
            $task->permanent_meter_ID = $pmd->ID;
            $task->step = 1;
            $task->next_execution = (isset($extra_data['next_execution'])) ? $extra_data['next_execution'] : 60;
            $task->log = '';
            $task->extra_data = serialize($extra_data);
            $task->expected_to = null;
            $task->last_execution_at = null;
            $task->completed_at = null;
            $task->started_at = null;
            $task->processing = 0;
            $task->save();
        }
    }

    public function getLog()
    {
        if (empty($this->log)) {
            return $this->log;
        }

        try {
            $log = unserialize($this->log);

            return $log;
        } catch (Exception $e) {
            return '';
        }
    }

    public function schemeProcessing()
    {
        if (self::where('task_id', $this->task_id)
        ->orderBy('order_id', 'ASC')
        ->whereRaw('(completed_at IS NULL)')
        ->where('processing', 1)
        ->first() != null) {
            return true;
        }

        return false;
    }

    public function getFirst()
    {
        return self::where('task_id', $this->task_id)
        ->orderBy('order_id', 'ASC')
        ->whereRaw('(completed_at IS NULL)')
        ->first();
    }

    public function getLast()
    {
        return self::where('task_id', $this->task_id)
        ->orderBy('order_id', 'DESC')
        ->whereRaw('(completed_at IS NULL)')
        ->first();
    }

    public function getNext()
    {
        return self::where('task_id', $this->task_id)
        ->where('order_id', ($this->order_id + 1))
        ->whereRaw('(completed_at IS NULL)')
        ->first();
    }

    public function getPrev()
    {
        return self::where('task_id', $this->task_id)
        ->where('order_id', ($this->order_id - 1))
        ->whereRaw('(completed_at IS NULL)')
        ->first();
    }

    public function isFirst()
    {
        $first = $this->getFirst();

        return $first != null && $first->id == $this->id;
    }

    public function isLast()
    {
        $last = $this->getLast();

        return $last != null && $last->id == $this->id;
    }

    public function getPmdAttribute()
    {
        return PermanentMeterData::where('username', $this->username)->orderBy('ID', 'DESC')->first();
    }

    public function getIterations($completed = false)
    {
        if ($completed) {
            return self::where('task_id', $this->task_id)
            ->whereRaw('(completed_at IS NULL)')
            ->get();
        }

        return self::where('task_id', $this->task_id)
        ->where('id', '!=', $this->id)->get();
    }

    public static function unCompleted()
    {
        $tasks = self::where('order_id', 1)->get();

        foreach ($tasks as $k => $t) {
            if (count($t->getIterations(true)) == 0) {
                $tasks->forget($k);
            }
        }

        return $tasks;
    }
}
