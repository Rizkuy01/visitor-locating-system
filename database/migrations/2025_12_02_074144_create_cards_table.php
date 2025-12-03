<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // "1".."20" (atau kode RFID)
            $table->enum('status', ['available', 'in_use'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cards');
    }
};
