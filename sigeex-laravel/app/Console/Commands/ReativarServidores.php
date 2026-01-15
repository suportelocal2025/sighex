<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Servidor;

class ReativarServidores extends Command
{
    protected $signature = 'servidores:reativar';

    protected $description = 'Reativa servidores cujo período de inatividade expirou';

    public function handle()
    {
        $hoje = now()->toDateString();
        
        $servidores = Servidor::where('ativo', true)
            ->where('inativo_indefinido', false)
            ->whereNotNull('inativo_fim')
            ->where('inativo_fim', '<', $hoje)
            ->get();
        
        $reativados = 0;
        
        foreach ($servidores as $servidor) {
            $servidor->update([
                'inativo_inicio' => null,
                'inativo_fim' => null,
                'motivo_inativo' => null,
            ]);
            $reativados++;
        }
        
        $this->info("Servidores reativados: {$reativados}");
        
        return 0;
    }
}
