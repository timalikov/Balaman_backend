<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factor;

class FactorController extends Controller
{
    public function index()
    {
        $factors = Factor::select('factor_id', 'name')->get();

        return response()->json($factors);

    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'string'
        ]);
        
        $factor = Factor::create($validatedData);

        return response()->json($factor, 201);
    }

    public function destroy(string $id)
    {
        $factor = Factor::where('factor_id', $id)->first();

        if (!$factor) {
            return response()->json(['message' => 'Factor not found.'], 404);
        }

        try {
            $factor->delete();
            return response()->json(['message' => 'Factor deleted successfully.'], 200); 
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the factor. It may be in use.'], 500);
        }
    }
}
