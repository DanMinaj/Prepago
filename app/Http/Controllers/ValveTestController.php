<?php

namespace App\Http\Controllers;

use App\Models\ReadinessTask;
use Illuminate\Support\Collection;

class ValveTestController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $running_tasks = [];

        $tasks = ReadinessTask::where('order_id', 1)->get();

        foreach ($tasks as $k => $t) {
            $running_iterations = ReadinessTask::where('task_id', $t->task_id)
            ->whereRaw('(completed_at IS NULL)')->get();
            foreach ($running_iterations as $j => $i) {
                if (! isset($running_tasks[$i->scheme_number])) {
                    $running_tasks[$i->scheme_number] = [];
                }
                $running_tasks[$i->scheme_number][] = $i;
            }
        }

        $this->layout->page = view('home/programs/valve_control',
        [
            'running_tasks' => $running_tasks,
        ]);
    }
}
