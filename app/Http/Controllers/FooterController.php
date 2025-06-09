<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    function politica_privacidad() {
        $ciudades = Ciudad::all();

        return view('footer.politica_privacidad', compact('ciudades'));
    }

    function terminos_y_condiciones() {
        $ciudades = Ciudad::all();

        return view('footer.terminos_y_condiciones', compact('ciudades'));
    }

    function aviso_legal() {
        $ciudades = Ciudad::all();

        return view('footer.aviso_legal', compact('ciudades'));
    }

    function politica_de_cookies() {
        $ciudades = Ciudad::all();

        return view('footer.politica_de_cookies', compact('ciudades'));
    }

    function preguntas_frecuentes() {
        $ciudades = Ciudad::all();

        return view('footer.preguntas_frecuentes', compact('ciudades'));
    }

    function contacto() {
        $ciudades = Ciudad::all();

        return view('footer.contacto', compact('ciudades'));
    }
}
