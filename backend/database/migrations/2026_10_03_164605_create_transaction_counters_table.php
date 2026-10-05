<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('date', 8);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unique(['type', 'date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_counters');
    }
};
