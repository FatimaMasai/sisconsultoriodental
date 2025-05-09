<?php

// namespace App\Http\Controllers;

// abstract class Controller
// {
    
// }
 
//agregue esta linea para que funcione el controlador de usuario los middleware
// si ingreso con rol de doctor no me deja entrar a la vista de usuarios http://127.0.0.1:8000/admin/users que era el problema q tenia

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}

