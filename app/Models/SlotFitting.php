<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotFitting extends Model
{
    protected $table = 'slot_fitting';

    protected $fillable = ['tanggal', 'jam_mulai', 'kapasitas'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function reservasi()
    {
        return $this->hasMany(ReservasiFitting::class, 'slot_id');
    }
}