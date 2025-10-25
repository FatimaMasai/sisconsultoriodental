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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalSales = Sale::sum('total'); //total de ventas
        $totalPurchases = Purchase::sum('total'); //total de compras
        $totalProducts = Product::where('status',1)->count(); //total de productos
         // Depuración para verificar los valores
    //dd($totalSales, $totalPurchases, $totalProducts);


        $totalPatients = Patient::where('status',1)->count(); //total de pacientes
        $totalSuppliers = Supplier::where('status',1)->count(); //total de proveedores
        $totalDoctors = Doctor::where('status',1)->count(); //total de doctores
        $totalSpecialities = Speciality::where('status',1)->count(); //total de especialidades
        $totalServices = Service::where('status',1)->count(); //total de servicios


        // Datos mensuales últimos 12 meses
        $months = [];
        $salesByMonth = [];
        $purchasesByMonth = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $months[] = Carbon::parse($month . '-01')->format('M Y');

            $salesByMonth[] = Sale::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->sum('total');

            $purchasesByMonth[] = Purchase::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->sum('total');
        }


        return view('admin.panel.index', compact('totalSales', 'totalPurchases', 'totalProducts',
            'totalPatients', 'totalSuppliers', 'totalDoctors', 'totalServices', 'totalSpecialities',
        'months','salesByMonth','purchasesByMonth'
        ));
    }


    
}
