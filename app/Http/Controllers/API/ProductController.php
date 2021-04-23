<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
       // $id = $request->input('id');
        $limit = $request->input('limit', 6);

        $produk_id = $request->input('product_id');
        $deskripsi_produk = $request->input('deskripsi_produk');
        $nama_produk = $request->input('nama_produk');

        $id_satuan = $request->input('id_satuan');
        $harga = $request->input('harga');
        $kategori_id = $request->input('kategori_id');
        $id_supplier = $request->input('id_supplier');


        if($produk_id)
        {
            $product = Product::find($produk_id);

            if($product){
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $product= Product::query();


        if($deskripsi_produk){
            $product->where('deskripsi_produk', 'like', '%' . $deskripsi_produk . '%');
        }

        if($nama_produk){
            $product->where('nama_produk', 'like', '%' . $nama_produk . '%');
        }
       
        if($id_satuan){
            $product->where('id_satuan', 'like', '%' . $id_satuan . '%');
        }

        if($harga){
            $product->where('harga', 'like', '%' . $harga . '%');
        }

        if($kategori_id){
            $product->where('kategori_id', 'like', '%' . $kategori_id . '%');
        }

        if($id_supplier){
            $product->where('id_supplier', 'like', '%' . $id_supplier . '%');
        }
       
        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }
}
