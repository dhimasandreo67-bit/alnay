<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirProvinsi extends Model
{
    protected $table = 'rajaongkir_provinsi';
    protected $fillable = ['province_id', 'province'];
}