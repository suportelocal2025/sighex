<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoGlobal extends Model
{
    protected $table = 'orcamento_global';
    
    protected $fillable = [
        'ano',
        'valor_total',
        'reserva_tecnica_percentual',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'decimal:2',
            'reserva_tecnica_percentual' => 'decimal:2',
        ];
    }

    public function getReservaTecnicaAttribute(): float
    {
        return $this->valor_total * ($this->reserva_tecnica_percentual / 100);
    }

    public function getValorDisponivelAttribute(): float
    {
        return $this->valor_total - $this->reserva_tecnica;
    }
}
