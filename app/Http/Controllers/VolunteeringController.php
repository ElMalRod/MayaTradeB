<?php

namespace App\Http\Controllers;

use App\Models\Volunteering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class VolunteeringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $volunteering = Volunteering::where('active', true)->get();
        return response()->json(['volunteering' => $volunteering]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userName' => 'required|string|exists:users,name',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'compensation_type' => 'required|in:currency,product,credit',
            'compensation_value' => 'required|numeric',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'  // Optional image field
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $volunteering = new Volunteering($request->all());

        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->getClientOriginalName();
            $imagePath = $request->file('image')->storeAs('images/volunteering', $imageName, 'public');
            $volunteering->image_path = $imagePath;
        }

        $volunteering->save();

        return response()->json(['message' => 'Volunteering posted successfully', 'volunteering' => $volunteering]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Volunteering  $volunteering
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $volunteering = Volunteering::findOrFail($id);
        return response()->json(['volunteering' => $volunteering]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Volunteering  $volunteering
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $volunteering = Volunteering::findOrFail($id);
        $volunteering->update($request->all());

        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->getClientOriginalName();
            $imagePath = $request->file('image')->storeAs('images/volunteering', $imageName, 'public');
            $volunteering->image_path = $imagePath;
        }

        $volunteering->save();

        return response()->json(['message' => 'Volunteering updated successfully', 'volunteering' => $volunteering]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Volunteering  $volunteering
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $volunteering = Volunteering::findOrFail($id);
        $volunteering->delete();

        return response()->json(['message' => 'Volunteering deleted successfully']);
    }


    public function participate(Request $request, $id)
    {
        $volunteering = Volunteering::findOrFail($id);

        if (!$volunteering->available) {
            return response()->json(['error' => 'This volunteering opportunity is not available.'], 400);
        }

        if (!$volunteering->approved) {
            return response()->json(['error' => 'This volunteering opportunity is not approved for participation.'], 400);
        }

        $user = User::findOrFail($request->user_id);

        // Simulate transaction logic depending on compensation type
        switch ($volunteering->compensation_type) {
            case 'currency':
                $user->balance += $volunteering->compensation_value; // or however you want to handle currency
                break;
            case 'product':
                // Handle product compensation logic
                break;
            case 'credit':
                $user->saldo += $volunteering->compensation_value; // or similar field for credits
                break;
        }

        $user->save();
        $volunteering->available = false;  // Mark the opportunity as unavailable
        $volunteering->save();

        return response()->json([
            'message' => 'Participation successful, compensation processed.',
            'volunteering' => $volunteering,
            'user' => $user
        ]);
    }

}
