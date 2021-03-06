<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Cars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('cars', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('trim_id');
            $table->unsignedBigInteger('style_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('vin',17)->unique();
            $table->string('stock_number',8)->unique();
            $table->year('year');
            $table->decimal('precio',10,2);
            $table->integer('doors')->nullable();
            $table->char('color',7)->nullable();
            $table->integer('mileage')->nullable();
            $table->integer('transmission')->nullable();
            $table->integer('condition_car');
            $table->integer('fuel_type')->nullable();
            $table->integer('estado');
            $table->string('fuel_economy',45)->nullable();
            $table->string('engine',45)->nullable();
            $table->string('drivetrain',45)->nullable();
            $table->string('wheel_size',45)->nullable();
            $table->string('url_info',150)->nullable();

            //llaves foraneas
            $table->foreign('trim_id')->references('id')->on('trims')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('style_id')->references('id')->on('styles')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('restrict');

            $table->timestamps();
        });
        //1=Automatic; 2= CVT; 3= Manual
        DB::statement('ALTER TABLE cars ADD CONSTRAINT chk_transmission CHECK (transmission between 1 and 3);');
        //1=Used; 2= New
        DB::statement('ALTER TABLE cars ADD CONSTRAINT chk_condition_car CHECK (condition_car = 1 or condition_car = 2);');
        //1=Gasoline;2=Diesel;3= Hybrid;4=Electric
        DB::statement('ALTER TABLE cars ADD CONSTRAINT chk_fuel_type CHECK (fuel_type = 1 or fuel_type = 2 or fuel_type = 3 or fuel_type = 4);');
        //1=active;2=Sold;3= Holdon;4=Other
        DB::statement('ALTER TABLE cars ADD CONSTRAINT chk_estado CHECK (estado = 1 or estado = 2 or estado = 3 or estado = 4);');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('cars');
    }
}
