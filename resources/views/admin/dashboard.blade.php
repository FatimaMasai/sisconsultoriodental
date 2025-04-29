<x-admin-layout>
     <x-label class="text-black text-xl font-semibold mb-4">
         Dashboard
     </x-label>
 
     <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
         <!-- Total de Ventas -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Ventas Totales</h2>
             <p class="text-3xl font-bold text-blue-500">{{ $totalSales }} Bs.</p>
         </div>
 
         <!-- Total de Compras -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Compras Totales</h2>
             <p class="text-3xl font-bold text-green-500">{{ $totalPurchases }} Bs.</p>
         </div>
 
         <!-- Total de Productos -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Productos Registrados</h2>
             <p class="text-3xl font-bold text-yellow-500">{{ $totalProducts }}</p>
         </div>
 
         <!-- Total de Pacientes -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Pacientes Registrados</h2>
             <p class="text-3xl font-bold text-teal-500">{{ $totalPatients }}</p>
         </div>
     </div>
 
     <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
         <!-- Total de Proveedores -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Proveedores Registrados</h2>
             <p class="text-3xl font-bold text-orange-500">{{ $totalSuppliers }}</p>
         </div>
 
         <!-- Total de Doctores -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Doctores Registrados</h2>
             <p class="text-3xl font-bold text-purple-500">{{ $totalDoctors }}</p>
         </div>
         <!-- Total de Doctores -->
         <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-2">Especialidades Registrados</h2>
          <p class="text-3xl font-bold text-purple-500">{{ $totalSpecialities }}</p>
      </div>
 
         <!-- Total de Servicios -->
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-semibold mb-2">Servicios Registrados</h2>
             <p class="text-3xl font-bold text-red-500">{{ $totalServices }}</p>
         </div>
     </div>
  
 </x-admin-layout>
 
  
 