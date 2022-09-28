<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
        } catch (Exception $e) {
            return $this->failed_response($e);
        }

        return $this->successful_response($products);
    }

    public function show($id)
    {
        try {
            $products = Product::find($id);
        } catch (Exception $e) {
            return $this->failed_response($e);
        }

        return $this->successful_response($products);
    }

    public function store(Request $request)
    {
        try {
            $products = Product::create($request->all());
        } catch (Exception $e) {
            return $this->failed_response($e);
        }

        return $this->successful_response($products);
    }

    public function update(Request $request, $id)
    {
        try {
            $products = Product::find($id)
                        ->update($request->all());
        } catch (Exception $e) {
            return $this->failed_response($e);
        }

        return $this->successful_response($products);
    }

    public function destroy($id)
    {
        try {
            $products = Product::destroy($id);
        } catch (Exception $e) {
            return $this->failed_response($e);
        }

        return $this->successful_response($products);
    }

    function successful_response($data) 
    {
        return response()->json([
            'data' => $data,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    function failed_response(Exception $e) 
    {
        return response()->json([
            'data' => [],
            'message'=>$e->getMessage()
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}