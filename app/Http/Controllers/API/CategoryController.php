<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function all (Request $request)
    {
        $category_id = $request->input('category_id');
        $cateogry_name = $request->input('category_name');
    }
}
