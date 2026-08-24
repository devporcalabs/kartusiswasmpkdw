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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->nullable();
            $table->string('nisn')->unique()->nullable();
            $table->string('nama_lengkap');
            $table->string('nomor_ortu')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('tempat_tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('kelas')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
