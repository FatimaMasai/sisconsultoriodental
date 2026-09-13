<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{

    protected $fillable = [
        'name',
        'status',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    // Todas las versiones de la plantilla de historial de esta especialidad
    // (activas, de baja, borradores), más recientes primero.
    public function formTemplates()
    {
        return $this->hasMany(FormTemplate::class)->orderByDesc('version');
    }

    // La plantilla que se usa hoy para llenar consultas nuevas de esta
    // especialidad. Puede no haber ninguna todavía. OJO: si la especialidad
    // tiene más de una plantilla activa (ver activeFormTemplates), esto
    // devuelve cualquiera de ellas de forma ambigua; para especialidades con
    // varios "tipos" de documento (ej. Medicina Ortomolecular) hay que usar
    // activeFormTemplates().
    public function activeFormTemplate()
    {
        return $this->hasOne(FormTemplate::class)->where('status', true)->orderByDesc('version');
    }

    // Todas las plantillas activas de la especialidad hoy. Normalmente hay
    // una sola, pero una especialidad puede tener más de un "tipo" de
    // documento a la vez (ej. Medicina Ortomolecular: "Historia Clínica
    // General" + "Perfil Neurofuncional"), cada uno como su propia
    // plantilla activa e independiente. Cuando hay más de una, quien
    // registra la consulta elige cuál completar.
    public function activeFormTemplates()
    {
        return $this->hasMany(FormTemplate::class)->where('status', true)->orderBy('name');
    }
}
