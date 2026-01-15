<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unidade;
use App\Models\Escala;
use App\Models\AlertaDiretor;
use App\Models\User;
use Carbon\Carbon;

class VerificarPrazoEnvioEscala extends Command
{
    protected $signature = 'escalas:verificar-prazo-envio';
    protected $description = 'Verifica prazo de envio de escalas e gera alertas para diretores';

    public function handle()
    {
        $hoje = Carbon::now();
        $proximoMes = $hoje->copy()->addMonth();
        $mesAlvo = $proximoMes->month;
        $anoAlvo = $proximoMes->year;
        
        $prazoLimite = Carbon::create($anoAlvo, $mesAlvo, 1, 0, 0, 0);
        $diasRestantes = $hoje->diffInDays($prazoLimite, false);
        
        $unidades = Unidade::all();
        $alertasCriados = 0;
        
        foreach ($unidades as $unidade) {
            $escalaEnviada = Escala::where('unidade_id', $unidade->id)
                ->where('mes', $mesAlvo)
                ->where('ano', $anoAlvo)
                ->whereIn('status', ['pendente', 'aprovada', 'executada'])
                ->exists();
            
            if ($escalaEnviada) {
                continue;
            }
            
            if ($diasRestantes <= 10 && $diasRestantes > 5) {
                $alertaExistente = AlertaDiretor::where('unidade_id', $unidade->id)
                    ->where('tipo', 'prazo_envio_10dias')
                    ->where('mes', $mesAlvo)
                    ->where('ano', $anoAlvo)
                    ->exists();
                
                if (!$alertaExistente) {
                    AlertaDiretor::create([
                        'unidade_id' => $unidade->id,
                        'tipo' => 'prazo_envio_10dias',
                        'titulo' => 'Prazo de Envio - 10 dias',
                        'mensagem' => "Faltam {$diasRestantes} dias para o prazo de envio da escala de " . $this->getNomeMes($mesAlvo) . "/{$anoAlvo}. Envie a escala até o dia 01/{$mesAlvo}/{$anoAlvo}.",
                        'mes' => $mesAlvo,
                        'ano' => $anoAlvo,
                        'prazo_limite' => $prazoLimite,
                    ]);
                    $alertasCriados++;
                    $this->enviarEmailDiretores($unidade, 'prazo_envio_10dias', $diasRestantes, $mesAlvo, $anoAlvo);
                }
            }
            
            if ($diasRestantes <= 5 && $diasRestantes >= 0) {
                $alertaExistente = AlertaDiretor::where('unidade_id', $unidade->id)
                    ->where('tipo', 'prazo_envio_5dias')
                    ->where('mes', $mesAlvo)
                    ->where('ano', $anoAlvo)
                    ->exists();
                
                if (!$alertaExistente) {
                    AlertaDiretor::create([
                        'unidade_id' => $unidade->id,
                        'tipo' => 'prazo_envio_5dias',
                        'titulo' => 'URGENTE: Prazo de Envio - 5 dias',
                        'mensagem' => "URGENTE: Faltam apenas {$diasRestantes} dias para o prazo de envio da escala de " . $this->getNomeMes($mesAlvo) . "/{$anoAlvo}. Envie a escala imediatamente!",
                        'mes' => $mesAlvo,
                        'ano' => $anoAlvo,
                        'prazo_limite' => $prazoLimite,
                    ]);
                    $alertasCriados++;
                    $this->enviarEmailDiretores($unidade, 'prazo_envio_5dias', $diasRestantes, $mesAlvo, $anoAlvo);
                }
            }
        }
        
        $this->info("Alertas de prazo de envio criados: {$alertasCriados}");
        return Command::SUCCESS;
    }
    
    private function getNomeMes($mes): string
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        return $meses[$mes] ?? '';
    }
    
    private function enviarEmailDiretores(Unidade $unidade, string $tipo, int $diasRestantes, int $mes, int $ano): void
    {
        $diretores = User::where('unidade_id', $unidade->id)
            ->where('perfil', 'diretor')
            ->where('ativo', true)
            ->whereNotNull('email')
            ->get();
        
        foreach ($diretores as $diretor) {
            $this->info("Enviando email para {$diretor->email} sobre {$tipo}");
        }
    }
}
