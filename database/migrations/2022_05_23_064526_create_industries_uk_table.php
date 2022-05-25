<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndustriesUkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industries_uk', function (Blueprint $table)
        {
            $table->id();
            $table->integer('ind_industry_id')->default(0)->index();
            $table->string('ind_name')->nullable();
            $table->mediumText('ind_url')->nullable();
            $table->string('ind_url_md5')->nullable()->index();
            $table->string('ind_site')->nullable()->index();
            $table->tinyInteger('ind_type')->default(0)->index();
            $table->tinyInteger('ind_status')->default(0)->index();
            $table->text('ind_meta')->nullable();
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
        Schema::dropIfExists('industries_uk');
    }
}
