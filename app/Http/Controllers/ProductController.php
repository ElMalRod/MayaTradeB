<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class ProductController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userName' => 'required|string', // Cambiar a userName
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación para la imagen
        ]);

        // Obtiene el nombre de usuario del request
        $userName = $request->userName;

        $imageName = $request->file('image')->getClientOriginalName(); // Obtiene el nombre original de la imagen
        $imagePath = $request->file('image')->storeAs('images', $imageName, 'public'); // Guarda la imagen en la carpeta 'public/images'

        $product = new Product();
        $product->userName = $userName; // Asigna el nombre de usuario al producto
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->image_path = $imagePath; // Guarda la ruta de la imagen en la base de datos
        $product->save();

        return response()->json(['message' => 'Product published successfully.', 'product' => $product]);
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductos()
    {
        // Obtiene todos los productos publicados
        $products = Product::all();

        return response()->json(['products' => $products]);
    }
}
