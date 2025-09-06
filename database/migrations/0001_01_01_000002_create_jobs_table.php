<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up(): void
    {
        // Jobs collection
        $collection = Schema::connection('mongodb')->getCollection('jobs');
        $collection->createIndex(['queue' => 1]);
        $collection->createIndex(['reserved_at' => 1]);
        $collection->createIndex(['available_at' => 1]);
        $collection->createIndex(['created_at' => 1]);

        // Job Batches collection
        Schema::connection('mongodb')->create('failed_jobs', function (Blueprint $table) {
            $table->id(); // This creates _id as ObjectId
            $table->string('uuid')->unique()->index();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('jobs');
        Schema::connection('mongodb')->dropIfExists('job_batches');
        Schema::connection('mongodb')->dropIfExists('failed_jobs');
    }
};
