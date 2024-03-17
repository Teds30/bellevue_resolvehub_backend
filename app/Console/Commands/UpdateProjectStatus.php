<?php

namespace App\Console\Commands;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateProjectStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-project-status';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates project status';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Get today's date
        $today = Carbon::now();

        // Update projects status based on conditions
        $onGoing = Project::where('schedule', '<=', $today)
            ->where('deadline', '>=', $today)
            ->update(['status' => 2]);

        // $pending = Project::where(function ($query) use ($today) {
        //     $query->where('schedule', '>', $today)
        //         ->orWhere('deadline', '<', $today);
        // })->update(['status' => 1]);
        $pending = Project::where('schedule', '>', $today)
            ->update(['status' => 1]);



        $done = Project::where('deadline', '<', $today)
            ->update(['status' => 4]);

        $this->info('Project statuses updated successfully.');
        $this->info($onGoing);
        $this->info($pending);
        $this->info($done);
        $this->info($today);
    }
}
