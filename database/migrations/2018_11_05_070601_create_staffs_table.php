<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('staffs')) {
            Schema::create('staffs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('firstname');
                $table->string('lastname');
                $table->string('email')->unique();
                $table->string('employeeid')->unique();
                $table->string('phone')->unique()->nullable();
                $table->char('gender',1)->nullable();
                $table->string('postal')->nullable();
                $table->string('address')->nullable();
                $table->string('profileurl')->nullable()->default('defaultprofile.jpg');
                $table->integer('nationality_id')->unsigned()->nullable();
                $table->string('enthnicity', 100)->nullable()->default('text');
                $table->integer('language_id')->unsigned()->nullable();
                $table->integer('marital_status_id')->unsigned()->nullable();
                $table->string('ssn')->unique()->nullable();
                $table->string('tax_number')->unique()->nullable();
                $table->integer('status_id')->unsigned()->default(1);
                $table->string('facebook')->nullable();
                $table->string('twitter')->nullable();
                $table->string('linkedin')->nullable();
                $table->string('instagram')->nullable();
                $table->string('snapchat')->nullable();
                $table->string('filepath')->nullable();
                $table->date('hire_date')->nullable();
                $table->integer('company_id')->unsigned()->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('cascade');
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
                $table->foreign('marital_status_id')->references('id')->on('marital_statuses')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staffs');
    }
}
