<?php

namespace App\Http\Controllers;

use App\Models\Micro; 
use Illuminate\Http\Request;

class MicroController extends Controller
{
    /**
     * Display a listing of the micros.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $micros = Micro::all(); // Retrieve all micros
        return response()->json($micros); // Return a JSON response with all micros
    }
}
