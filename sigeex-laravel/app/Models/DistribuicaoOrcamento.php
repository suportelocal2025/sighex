<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribuicaoOrcamento extends Model
{
    protected $table = 'distribuicao_orcamento';
    
    protected $fillable = [
        'unidade_id',
        'ano',
        'valor_distribuido',
        'valor_gasto',
        'margin_percentual',
    ];

    protected function casts(): array
    {
        return [
            'valor_distribuido' => 'decimal:2',
            'valor_gasto' => 'decimal:2',
            'margin_percentual' => 'decimal:2',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function getSaldoAttribute(): float
    {
        return $this->valor_distribuido - $this->valor_gasto;
    }
}
