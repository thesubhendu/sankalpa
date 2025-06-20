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
        Schema::create('problem_solving_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('problem_description');
            
            // SHERLOCK HOLMES template questions
            $table->text('what_doing_wrong')->nullable();
            $table->text('trigger')->nullable();
            $table->boolean('is_daily_pattern')->nullable();
            $table->text('what_to_change_trigger')->nullable();
            $table->text('what_do_when_doing_wrong')->nullable();
            $table->text('long_term_impact')->nullable();
            
            // Solution phase
            $table->text('what_should_do_instead')->nullable();
            $table->text('how_would_benefit')->nullable();
            
            // Problem nature and emotional impact
            $table->enum('problem_nature', ['emotional', 'financial', 'house_abuse', 'spouse', 'work', 'health', 'relationships', 'other'])->nullable();
            $table->integer('emotional_impact_percentage')->nullable(); // 0-100
            $table->text('emotional_strategy')->nullable();
            
            // Power and control
            $table->boolean('have_power_to_solve')->nullable();
            $table->text('what_have_power_to_change')->nullable();
            $table->text('how_get_out_long_term')->nullable();
            
            // Meta fields
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->json('tags')->nullable();
            $table->boolean('needs_follow_up')->default(false);
            $table->date('session_date')->default(now());
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'session_date']);
            $table->index(['user_id', 'problem_nature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_solving_sessions');
    }
};
