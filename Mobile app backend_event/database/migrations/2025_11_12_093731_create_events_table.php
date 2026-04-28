<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécution de la migration.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('picture')->nullable(); // URL ou chemin de l’image
            $table->string('type')->nullable();    // Type d’évènement
            $table->string('title');               // Titre de l’évènement
            $table->date('date');                  // Date de l’évènement
            $table->string('localisation');        // Lieu
            $table->string('business');        // Entreprise organisatrice
            $table->string('categorie');        // Entreprise organisatrice
            $table->string('description');        // Entreprise organisatrice
            $table->float('rating')->nullable();    // Entreprise organisatrice
            $table->decimal('price', 8, 2)->nullable(); // Prix (ex: 999999.99)
            $table->timestamps();                  // created_at et updated_at
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
