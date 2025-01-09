<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('user_id'); // Assuming student/user ID's
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};
