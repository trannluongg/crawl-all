<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsUkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords_uk', function (Blueprint $table)
        {
            $table->id();
            $table->string('key_name')->nullable();
            $table->mediumText('key_url')->nullable();
            $table->string('key_url_md5')->nullable();
            $table->string('key_site')->nullable();
            $table->tinyInteger('key_type')->default(0);
            $table->tinyInteger('key_status')->default(0);
            $table->text('key_meta')->nullable();
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
        Schema::dropIfExists('keywords_uk');
    }
}
