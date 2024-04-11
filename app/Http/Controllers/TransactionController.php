<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

  /**
     * Procesar una compra de monedas virtuales.
     */
    public function buyCoins(Request $request)
    {
        $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'amount_in_dollars' => 'required|numeric|min:0', 
        ]);
    
        $buyer = User::find($request->buyer_id);
        $amount = $request->amount_in_dollars;
    
        // Crear la transacción de compra
        Transaction::create([
            'buyer_id' => $buyer->id,
            'amount' => $amount,
        ]);
    
        // Actualizar el saldo del usuario
        $buyer->saldo += $amount;
        $buyer->save();
    
        return response()->json(['message' => 'Compra realizada con éxito.'], 200);
    }

    public function getBalance(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'buyer_id' => 'required|integer',
            'buyer_name' => 'required|string'
        ]);
    
        // Obtener el comprador
        $buyer = User::where('id', $request->buyer_id)->where('name', $request->buyer_name)->first();
    
        // Verificar si el comprador existe
        if (!$buyer) {
            return response()->json(['message' => 'Buyer not found'], 404);
        }
    
        // Devolver el saldo del comprador
        return response()->json(['saldo' => $buyer->saldo]);
    }
    
    

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
