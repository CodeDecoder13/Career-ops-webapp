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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('num')->unique();
            $table->string('company');
            $table->string('role');
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status')->nullable();
            $table->date('date')->nullable();
            $table->string('report_link')->nullable();
            $table->string('pdf_link')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
