<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOmegasActualGradeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('omegas', function(Blueprint $table) {
            $table->decimal('actual_grade', 3, 2)->nullable()->after('final_grade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('omegas', function (Blueprint $table) {
            $table->dropColumn('actual_grade');
        });
    }
}
