<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('jogadores', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->unique();
            $table->integer('moedas')->default(0);
            $table->integer('xp')->default(0);
            $table->json('conquistas')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('jogadores');
    }
};
