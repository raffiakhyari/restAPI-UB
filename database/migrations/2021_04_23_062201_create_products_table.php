<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('produk_id');
            $table->string('deskripsi_produk')->nullable();
            $table->string('name_produk')->nullable();
            $table->integer('id_satuan')->nullable();
            $table->integer('harga')->nullable();
            $table->integer('kategori_id')->nullable();
            $table->integer('id_supplier')->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('products');
    }
}
