<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('full_name');
            // Guardado so com digitos; a mascara e responsabilidade do front.
            $table->char('cpf', 11)->unique();
            $table->string('rg', 20);
            $table->date('birth_date');
            $table->enum('biological_sex', ['male', 'female']);
            $table->char('cellphone', 11);
            $table->string('email')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // A listagem busca por nome e ordena por data de criacao.
            $table->index('full_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
