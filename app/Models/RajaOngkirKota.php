<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirKota extends Model
{
    protected $table = 'rajaongkir_kota';
    protected $fillable = ['city_id', 'province_id', 'type', 'city_name', 'postal_code'];
}