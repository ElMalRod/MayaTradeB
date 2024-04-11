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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'approved' => 'required|boolean',
            'available' => 'required|boolean'
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
        $product->image_path = $imagePath;
        $product->approved = $request->approved;
        $product->available = $request->available;
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

     /**
     * Reportar un producto.
     */
    public function reportProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Verifica si el producto ya ha sido reportado
        if ($product->reported) {
            return response()->json(['error' => 'El producto ya ha sido reportado previamente.'], 400);
        }

        $request->validate([
            'report_reason' => 'required|string|max:255'
        ]);

        $product->update([
            'reported' => true,
            'report_reason' => $request->report_reason
        ]);

        return response()->json(['message' => 'Producto reportado correctamente.', 'product' => $product]);
    }

     /**
     * Display a listing of the reported products.
     *
     * @return \Illuminate\Http\Response
     */
    public function getReportedProducts()
    {
        // Obtiene solo los productos reportados
        $reportedProducts = Product::where('reported', true)->get();

        return response()->json(['reported_products' => $reportedProducts]);
    }

    /**
     * Update the reported status of a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateReportedProductStatus(Request $request, $id)
    {
        $product = Product::findOrFail($id);
    
        // Verifica si el producto ha sido reportado
        if (!$product->reported) {
            return response()->json(['error' => 'El producto no ha sido reportado.'], 400);
        }
    
        $request->validate([
            'action' => 'required|string|in:approve,keep'
        ]);
    
        // Realiza la acción solicitada en base al estado reportado del producto
        if ($request->action === 'approve') {
            $product->reported = false; // Cambia el estado de reportado a no reportado
            $product->save();
            return response()->json(['message' => 'Producto aprobado exitosamente.', 'product' => $product]);
        } elseif ($request->action === 'keep') {
            // Conserva el estado reportado del producto
            return response()->json(['message' => 'Producto conservado.', 'product' => $product]);
        }
    }

    /**
     * Delete a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }
    
    public function approveProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->approved = true;
        $product->save();

        return response()->json(['message' => 'Producto aprobado exitosamente.', 'product' => $product]);
    }

    public function rejectProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->approved = false;
        $product->save();

        return response()->json(['message' => 'Producto rechazado exitosamente.', 'product' => $product]);
    }
}
