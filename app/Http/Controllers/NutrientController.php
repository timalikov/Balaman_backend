<?php

namespace App\Http\Controllers;

use App\Models\Nutrient; 
use Illuminate\Http\Request;

class NutrientController extends Controller
{
    public function index()
    {
        $nutrients = Nutrient::all(); 
        return response()->json($nutrients);
    }
}
