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
        Schema::create('pedido_outros', function (Blueprint $table) {
            $table->integer('id_pedido')->primary();
            $table->text('descricao');

            $table->foreign('id_pedido')
                ->references('id_pedido')
                ->on('pedido')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_outros');
    }
};
