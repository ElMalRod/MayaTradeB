<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volunteering', function (Blueprint $table) {
            $table->id();
            $table->string('userName')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('compensation_type', ['currency', 'product', 'credit']);
            $table->decimal('compensation_value', 10, 2)->default(0); 
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('approved')->default(true);
            $table->boolean('available')->default(true);  // Indica si está disponible para participar
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('volunteering');
    }
};
