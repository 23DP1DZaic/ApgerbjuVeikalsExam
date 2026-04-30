<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
{
    Schema::create('listings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->text('description')->nullable();
        $table->decimal('price', 8, 2);
        $table->string('category');
        $table->string('brand')->nullable();
        $table->string('color')->nullable();
        $table->string('size')->nullable();
        $table->string('condition');
        $table->timestamps();
    });
}
};
