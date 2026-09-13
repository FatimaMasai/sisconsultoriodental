<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    // Opciones fijas para el campo Estado Civil (select en el formulario de
    // paciente). Se centralizan acá para no repetirlas entre la validación
    // del controlador y las vistas de crear/editar paciente.
    public const CIVIL_STATUSES = ['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)', 'Unión libre'];

    protected $fillable = [
        'name',
        'last_name_father',
        'last_name_mother', 
        'identity_card',
        'birth_date',
        'gender',
        'civil_status',
        'phone',
        'email',
        'address',
        'status',
        'user_id',
    ];

    //uno a uno inversa
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //uno a muchos
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    /**
     * Número de teléfono listo para armar un enlace de WhatsApp (wa.me),
     * con el código de país de Bolivia (591) agregado si hace falta.
     * Devuelve null si la persona no tiene teléfono registrado.
     */
    public function getWhatsappPhoneAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '591') ? $digits : '591' . $digits;
    }

}
