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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang', 255);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->string('gambar')->nullable();          // path relatif dari storage
            $table->boolean('is_encoded')->default(false); // apakah sudah di-encode LSB
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
