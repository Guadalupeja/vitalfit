<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TratamientoController extends Controller
{
    public function index()
    {
        return view('tratamientos.index');
    }
}
