<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('institution');
            $table->foreignId('card_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('check_in_at')->useCurrent();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamps();

            $table->index(['card_id', 'check_out_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('visitors');
    }
};
