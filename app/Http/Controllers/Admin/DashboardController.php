<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Speciality;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalSales = Sale::sum('total'); //total de ventas
        $totalPurchases = Purchase::sum('total'); //total de compras
        $totalProducts = Product::count(); //total de productos
         // Depuración para verificar los valores
    //dd($totalSales, $totalPurchases, $totalProducts);


        $totalPatients = Patient::count(); //total de pacientes
        $totalSuppliers = Supplier::count(); //total de proveedores
        $totalDoctors = Doctor::count(); //total de doctores
        $totalSpecialities = Speciality::count(); //total de especialidades
        $totalServices = Service::count(); //total de servicios


        return view('admin.dashboard', compact('totalSales', 'totalPurchases', 'totalProducts',
            'totalPatients', 'totalSuppliers', 'totalDoctors', 'totalServices', 'totalSpecialities'));
    }
}
