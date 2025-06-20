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
        Schema::create('introspection_journals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->enum('type', ['data_drop', 'learning', 'rule', 'purpose'])
                  ->default('data_drop');
            $table->enum('mood', ['very_low', 'low', 'neutral', 'good', 'very_good'])
                  ->nullable();
            $table->string('trigger')->nullable();
            $table->text('what_happened')->nullable();
            $table->text('feelings')->nullable();
            $table->text('trigger_reason')->nullable();
            $table->json('tags')->nullable();
            $table->integer('intensity_level')->nullable()->default(1); // 1-10 scale
            $table->boolean('is_important')->default(false);
            $table->boolean('needs_review')->default(false);
            $table->date('entry_date')->default(now());
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'entry_date']);
            $table->index(['user_id', 'is_important']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('introspection_journals');
    }
};
