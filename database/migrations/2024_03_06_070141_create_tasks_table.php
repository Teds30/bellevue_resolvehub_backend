<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('room');
            $table->string('issue');
            $table->text('details')->nullable();
            $table->unsignedBigInteger('requestor_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('pending_marker_id')->nullable();
            $table->unsignedBigInteger('completed_marker_id')->nullable();
            $table->text('pending_reason')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->unsignedBigInteger('assignor_id')->nullable();
            $table->timestamp('assigned_timestamp')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 -> requested | 1 -> active | 2 -> pending | 3 -> cancelled | 4 -> done');
            $table->tinyInteger('priority')->nullable();
            $table->timestamp('schedule')->nullable();
            $table->tinyInteger('d_status')->default(1);

            $table->timestamps();

            // $table->foreign('issue_id')->references('id')->on('issues');
            $table->foreign('requestor_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('no action');
            $table->foreign('pending_marker_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_marker_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assignor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
