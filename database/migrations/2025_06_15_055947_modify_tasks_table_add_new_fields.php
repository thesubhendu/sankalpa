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
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('energy_level', ['low', 'medium', 'high'])->default('medium')->after('points');
            $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes')->after('energy_level');
            $table->text('motivation_note')->nullable()->comment('Why this task is important')->after('estimated_duration');
            $table->integer('priority')->default(1)->comment('1=low, 2=medium, 3=high')->after('motivation_note');
            $table->timestamp('started_at')->nullable()->after('priority');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->foreignId('weekly_goal_id')->nullable()->constrained()->onDelete('cascade')->after('goal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'energy_level',
                'estimated_duration', 
                'motivation_note',
                'priority',
                'started_at',
                'completed_at',
                'weekly_goal_id'
            ]);
        });
    }
};
