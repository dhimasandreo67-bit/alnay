<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotFitting extends Model
{
    protected $table = 'slot_fitting';

    /** Daftar jam kedatangan yang tersedia setiap hari (satu sumber untuk pelanggan & admin). */
    public const JAM = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];

    /** Maksimal tamu per jam. */
    public const KAPASITAS_DEFAULT = 2;

    protected $fillable = ['tanggal', 'jam_mulai', 'kapasitas'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    /**
     * Kuota efektif jam ini.
     * kapasitas = 0 berarti jam DITUTUP admin. Nilai lama (mis. 4) tetap dibatasi ke KAPASITAS_DEFAULT.
     */
    public function kapasitasTersedia(): int
    {
        return max(0, min(self::KAPASITAS_DEFAULT, (int) $this->kapasitas));
    }

    public function ditutup(): bool
    {
        return (int) $this->kapasitas <= 0;
    }

    public function reservasi()
    {
        return $this->hasMany(ReservasiFitting::class, 'slot_id');
    }
}