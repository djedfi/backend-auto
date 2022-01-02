<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id');
            $table->char('customer_id',7)->unique();
            $table->string('licence',15)->unique();
            $table->string('first_name',250);
            $table->string('last_name',250);
            $table->string('initial',4)->nullable();
            $table->string('address_p',250);
            $table->string('address_s',150)->nullable();
            $table->string('city',100);
            $table->string('zip',10);
            $table->char('telephone_res',10)->nullable();
            $table->char('telephone_bus',10)->nullable();
            $table->char('cellphone',10);
            $table->string('email',150)->nullable();
            $table->date('birthday');
            $table->string('ssn',150);
            $table->foreign('state_id')->references('id')->on('states')->onUpdate('cascade')->onDelete('restrict');

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
        //
        Schema::dropIfExists('customers');
    }
}