<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;
    // Declare the property
    private $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }


    public function headings(): array
    {
        return [
            '#',
            'Project',
            'Location',
            'Type',
            'Department',
            'Requestor',
            'In-Charge',
            'Start Date',
            'End Date',
            'Completion Date',
            'Status',
            // 'url'
        ];
    }

    public function map($task): array
    {

        $status = Project::getStatus($task);

        if (intVal($task->type) == 1) {
            $type = 'Major';
        } else {
            $type = 'Minor';
        }
        return [
            $task->id,
            $task->title,
            $task->location,
            "type" => $type,
            "department" => $task->department ? $task->department->name : '',
            "requestor" => $task->requestor ? $task->requestor->first_name . ' ' . $task->requestor->last_name : '',
            "incharge" => $task->incharge ? $task->incharge->first_name . ' ' . $task->incharge->last_name : '',
            $task->schedule,
            $task->deadline,
            "completion_date" => ($status == 'done' || $status == 'cancelled' || $status == 'rejected') ? $task->updated_at : '',
            "status" => $status,
        ];
    }

    public function query()
    {
        return $this->projects;
        // return Task::query()->with(['department', 'assignor', 'assignee'])->whereYear('created_at', 2024);
    }
}
