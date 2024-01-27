<?php

namespace App\Http\Controllers;

use App\Models\Nutrient; 
use Illuminate\Http\Request;

class NutrientController extends Controller
{
    /**
     * Display a listing of the nutrients.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nutrients = Nutrient::all(); // Retrieve all nutrients
        return response()->json($nutrients); // Return a JSON response with all nutrients
    }
}
