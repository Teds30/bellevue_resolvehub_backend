<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TasksExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    // Declare the property
    private $tasks;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }
    public function headings(): array
    {
        return [
            '#',
            'Issue',
            'Concerned Department',
            'Requestor',
            'Assignee',
            'Assignor',
            'Schedule',
            'Completion Date',
            'Status',
            // 'url'
        ];
    }

    public function map($task): array
    {

        $status = Task::getStatus($task);


        return [
            $task->id,
            $task->issue,
            "department" => $task->department ? $task->department->name : '',
            "requestor" => $task->requestor ? $task->requestor->first_name . ' ' . $task->requestor->last_name : '',
            "assignee" => $task->assignee ? $task->assignee->first_name . ' ' . $task->assignee->last_name : '',
            "assignor" => $task->assignor ? $task->assignor->first_name . ' ' . $task->assignor->last_name : '',
            $task->schedule,
            $task->updated_at,
            "status" => $status,
        ];
    }

    public function query()
    {
        return $this->tasks;
        // return Task::query()->with(['department', 'assignor', 'assignee'])->whereYear('created_at', 2024);
    }
}
