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
        Schema::create('weekly_goals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('raw_input'); // The original text input from user
            $table->text('parsed_summary')->nullable(); // Generated summary after parsing
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('goal_id')->nullable()->constrained()->onDelete('cascade'); // Link to long-term goal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_goals');
    }
};
