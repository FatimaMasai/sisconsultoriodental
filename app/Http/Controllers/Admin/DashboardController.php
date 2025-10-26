<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\History;
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

    public function dashboard2()
    {
        $newPatientsThisMonth = Patient::where('status', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $servicesToday = History::whereDate('date', $today)->count();
        $servicesThisWeek = History::whereBetween('date', [$startOfWeek, $today])->count();
        $servicesThisMonth = History::whereBetween('date', [$startOfMonth, $today])->count();

        $totalSales = Sale::sum('total');
        $totalPurchases = Purchase::sum('total');
        $totalPatients = Patient::where('status', 1)->count();
        $totalDoctors = Doctor::where('status', 1)->count();
        $totalSuppliers = Supplier::where('status', 1)->count();
        $totalServices = Service::where('status', 1)->count();
        $totalProducts = Product::where('status', 1)->count();

        $doctorsBySpecialty = Doctor::with('speciality')
            ->where('status', 1)
            ->selectRaw('speciality_id, COUNT(*) as count')
            ->groupBy('speciality_id')
            ->get()
            ->map(function ($doctor) {
                return [
                    'name' => $doctor->speciality->name ?? 'Sin especialidad',
                    'count' => $doctor->count
                ];
            });

        $topSuppliers = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->join('people', 'suppliers.person_id', '=', 'people.id')
            ->selectRaw('suppliers.id, suppliers.company, people.name, SUM(purchases.total) as total_purchases, COUNT(purchases.id) as total_orders')
            ->where('suppliers.status', 1)
            ->groupBy('suppliers.id', 'suppliers.company', 'people.name')
            ->orderBy('total_purchases', 'DESC')
            ->limit(10)
            ->get()
            ->map(function ($supplier) {
                return [
                    'name' => $supplier->company ?? $supplier->name ?? 'N/A',
                    'total_purchases' => $supplier->total_purchases,
                    'total_orders' => $supplier->total_orders
                ];
            });

        $topPatients = History::selectRaw('patient_id, COUNT(*) as visits')
            ->groupBy('patient_id')
            ->orderBy('visits', 'DESC')
            ->limit(10)
            ->get()
            ->map(function ($history) {
                $patient = Patient::with('person')->find($history->patient_id);
                return [
                    'name' => $patient ? ($patient->person->name . ' ' . $patient->person->last_name_father) : 'N/A',
                    'visits' => $history->visits
                ];
            });

        $quarterlyMonths = [];
        $salesByMonthQ = [];
        $purchasesByMonthQ = [];

        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $quarterlyMonths[] = $date->format('M Y');

            $startOfPeriod = $date->copy()->startOfMonth();
            $endOfPeriod = $date->copy()->endOfMonth();

            $salesByMonthQ[] = Sale::whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
                ->sum('total');

            $purchasesByMonthQ[] = Purchase::whereBetween('date', [$startOfPeriod, $endOfPeriod])
                ->sum('total');
        }

        $servicesByDay = [];
        $serviceLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $servicesByDay[] = History::whereDate('date', $date)->count();
            $serviceLabels[] = $date->format('d/m');
        }

        return view('admin.panel.index2', compact(
            'newPatientsThisMonth',
            'servicesToday',
            'servicesThisWeek',
            'servicesThisMonth',
            'totalSales',
            'totalPurchases',
            'totalPatients',
            'totalDoctors',
            'totalSuppliers',
            'totalServices',
            'totalProducts',
            'doctorsBySpecialty',
            'topSuppliers',
            'topPatients',
            'quarterlyMonths',
            'salesByMonthQ',
            'purchasesByMonthQ',
            'servicesByDay',
            'serviceLabels'
        ));
    }

    
}
