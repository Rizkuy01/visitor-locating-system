<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('cards', function (Blueprint $table) {
      $table->string('rfid_code')->nullable()->unique()->after('code');
    });
  }

  public function down(): void {
    Schema::table('cards', function (Blueprint $table) {
      $table->dropUnique(['rfid_code']);
      $table->dropColumn('rfid_code');
    });
  }
};
