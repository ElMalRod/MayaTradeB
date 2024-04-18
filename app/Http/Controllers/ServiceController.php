<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\User; 

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all();
        return response()->json(['services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userName' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',  
            'active' => 'required|boolean',
            'approved' => 'required|boolean'
        ]);

        $userName = $request->userName;
        $imageName = $request->file('image')->getClientOriginalName();
        $imagePath = $request->file('image')->storeAs('images', $imageName, 'public');

        $service = new Service();
        $service->userName = $userName;
        $service->name = $request->name;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->image_path = $imagePath;  
        $service->active = $request->active;
        $service->approved = $request->approved;
        $service->save();

        return response()->json(['message' => 'Service published successfully', 'service' => $service]);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json(['service' => $service]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $service->update($request->all());
        return response()->json(['message' => 'Service updated successfully', 'service' => $service]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getServices()
    {
        $services = Service::all();
        return response()->json(['services' => $services]);
    }

     /**
     * Reportar un servicio.
     */
    public function reportService(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        // Verifica si el servicio ya ha sido reportado
        if ($service->reported) {
            return response()->json(['error' => 'El servicio ya ha sido reportado previamente.'], 400);
        }

        $request->validate([
            'report_reason' => 'required|string|max:255'
        ]);

        $service->update([
            'reported' => true,
            'report_reason' => $request->report_reason
        ]);

        return response()->json(['message' => 'Servicio reportado correctamente.', 'service' => $service]);
    }

      /**
     * Display a listing of the reported services.
     *
     * @return \Illuminate\Http\Response
     */
    public function getReportedServices()
    {
        // Obtiene solo los servicios reportados
        $reportedServices = Service::where('reported', true)->get();

        return response()->json(['reported_services' => $reportedServices]);
    }

    /**
     * Update the reported status of a service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateReportedServiceStatus(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        // Verifica si el servicio ha sido reportado
        if (!$service->reported) {
            return response()->json(['error' => 'El servicio no ha sido reportado.'], 400);
        }

        $request->validate([
            'action' => 'required|string|in:approve,keep'
        ]);

        // Realiza la acción solicitada en base al estado reportado del servicio
        if ($request->action === 'approve') {
            $service->reported = false; // Cambia el estado de reportado a no reportado
            $service->save();
            return response()->json(['message' => 'Servicio aprobado exitosamente.', 'service' => $service]);
        } elseif ($request->action === 'keep') {
            // Conserva el estado reportado del servicio
            return response()->json(['message' => 'Servicio conservado.', 'service' => $service]);
        }
    }

    /**
     * Delete a service.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente.']);
    }

    public function approveService($id)
    {
    $service = Service::findOrFail($id);
    $service->approved = true;
    $service->save();

    return response()->json(['message' => 'Servicio aprobado exitosamente.', 'service' => $service]);
    }

public function rejectService($id)
    {
    $service = Service::findOrFail($id);
    $service->approved = false;
    $service->save();

    return response()->json(['message' => 'Servicio rechazado exitosamente.', 'service' => $service]);
    } 
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserServices($userName)
    {
        $services = Service::where('userName', $userName)->get();

        return response()->json(['services' => $services]);
    }

      /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateService(Request $request, $id)
    {
        // Busca el servicio por su ID
        $service = Service::findOrFail($id);

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'description' => 'string',
            'price' => 'numeric|min:0'
            // Aquí puedes agregar más reglas de validación según tus necesidades
        ]);

        // Si la validación falla, retorna un mensaje de error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Actualiza los campos solo si se proporcionan en la solicitud
        if ($request->has('name')) {
            $service->name = $request->name;
        }
        if ($request->has('description')) {
            $service->description = $request->description;
        }
        if ($request->has('price')) {
            $service->price = $request->price;
        }
        // Aquí puedes agregar más campos para actualizar según tus necesidades

        // Guarda los cambios en la base de datos
        $service->save();

        // Retorna una respuesta con el servicio actualizado
        return response()->json(['message' => 'Service updated successfully.', 'service' => $service]);
    }

    /**
 * Adquirir un servicio.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id  El ID del servicio a adquirir
 * @return \Illuminate\Http\Response
 */
public function acquireService(Request $request, $id)
{
    $userName = $request->input('userName'); // Obtiene el nombre de usuario del solicitante
    $user = User::where('name', $userName)->first(); // Encuentra al usuario solicitante

    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado.'], 404);
    }

    $service = Service::findOrFail($id); // Encuentra el servicio por ID

    // Verifica si el usuario es el mismo que ofrece el servicio
    if ($service->userName == $userName) {
        return response()->json(['error' => 'No puedes adquirir tu propio servicio.'], 403);
    }

    // Verifica si el usuario tiene saldo suficiente para adquirir el servicio
    if ($user->saldo < $service->price) {
        return response()->json(['error' => 'Saldo insuficiente para adquirir el servicio.'], 400);
    }

    // Encuentra al usuario que ofrece el servicio
    $provider = User::where('name', $service->userName)->first();

    // Realiza las operaciones de la transacción
    $user->saldo -= $service->price; // Descuenta el saldo del usuario solicitante
    $provider->saldo += $service->price; // Incrementa el saldo del usuario proveedor del servicio

    $user->save();
    $provider->save();

    // Actualiza el estado del servicio a no disponible, si es necesario
    // $service->active = false;
    // $service->save();

    return response()->json([
        'message' => 'Servicio adquirido exitosamente.',
        'service' => $service,
        'user' => $user,
        'provider' => $provider
    ]);
}


        
}
