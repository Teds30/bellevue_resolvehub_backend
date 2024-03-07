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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('location')->nullable();
            $table->string('coordinates')->nullable();
            $table->timestamp('schedule')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->string('type')->default(0)->comment('0 -> minor | 1 -> major'); //minor
            $table->unsignedBigInteger('requestor_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('incharge_id')->nullable();
            $table->unsignedBigInteger('pending_marker_id')->nullable();
            $table->unsignedBigInteger('completed_marker_id')->nullable();
            $table->text('pending_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 -> requested | 1 -> pending | 2 -> on-going -> 3 -> cancelled');
            $table->tinyInteger('d_status')->default(1);

            $table->timestamps();

            $table->foreign('requestor_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('no action');
            $table->foreign('incharge_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('pending_marker_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('completed_marker_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
