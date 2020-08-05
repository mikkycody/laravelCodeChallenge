<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use JWTAuth;

class ProductController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index()
    {
        return $this->user
            ->products()
            ->get(['name', 'price', 'quantity'])
            ->toArray();
    }
    public function show($id)
    {
        $product = $this->user->products()->find($id);
    
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found'
            ], 400);
        }
    
        return $product;
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);

            $product->image = $image;
        }
        if ($this->user->products()->save($product))
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product could not be created'
            ], 500);
    }
}
