<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use App\Models\User; 


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

     
        $userName = $request->userName;
        $imageName = $request->file('image')->getClientOriginalName();
        $imagePath = $request->file('image')->storeAs('images', $imageName, 'public'); 

        $product = new Product();
        $product->userName = $userName;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserProducts($userName)
    {
        $products = Product::where('userName', $userName)->get();

        return response()->json(['products' => $products]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(Request $request, $id)
    {
        // Busca el producto por su ID
        $product = Product::findOrFail($id);

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'description' => 'string',
            'price' => 'numeric|min:0'
        ]);

        // Si la validación falla, retorna un mensaje de error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Actualiza los campos solo si se proporcionan en la solicitud
        if ($request->has('name')) {
            $product->name = $request->name;
        }
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        if ($request->has('price')) {
            $product->price = $request->price;
        }

        // Guarda los cambios en la base de datos
        $product->save();

        // Retorna una respuesta con el producto actualizado
        return response()->json(['message' => 'Product updated successfully.', 'product' => $product]);
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

    /**
     * Buy a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function buyProduct(Request $request, $id)
    {
        $userName = $request->input('userName'); // Get userName from the request
        $buyer = User::where('name', $userName)->first(); // Find the buyer by userName
    
        if (!$buyer) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    
        $product = Product::findOrFail($id);
    
        if (!$product->available) {
            return response()->json(['error' => 'Este producto ya fue comprado.'], 400);
        }
    
        if (!$product->approved) {
            return response()->json(['error' => 'Este producto no está aprobado para la venta.'], 400);
        }
    
        if ($buyer->saldo < $product->price) {
            return response()->json(['error' => 'Saldo insuficiente para completar la compra.'], 400);
        }
    
        // Retrieve the seller's user record
        $seller = User::where('name', $product->userName)->first();
        if (!$seller) {
            return response()->json(['error' => 'Vendedor no encontrado.'], 404);
        }
    
        // Transaction operations
        $buyer->saldo -= $product->price;  // Subtract the price from the buyer's balance
        $seller->saldo += $product->price; // Add the price to the seller's balance
    
        $buyer->save();
        $seller->save();
    
        $product->available = false;  // Mark the product as unavailable
        $product->save();
    
        return response()->json([
            'message' => 'Producto comprado exitosamente.',
            'product' => $product,
            'buyer' => $buyer,
            'seller' => $seller
        ]);
    }
    
}
