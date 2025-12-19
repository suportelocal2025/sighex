<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala;
use App\Models\Servidor;
use App\Models\Equipe;
use App\Models\Modulo;
use App\Models\Alocacao;
use App\Models\EscalaEquipeServidor;
use App\Models\DistribuicaoOrcamento;
use Illuminate\Support\Facades\Auth;

class DiretorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $ano = date('Y');
        $mes = date('n');

        $distribuicao = DistribuicaoOrcamento::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->first();

        $orcamento = $distribuicao?->valor_distribuido ?? 0;
        $gasto = $distribuicao?->valor_gasto ?? 0;
        $disponivel = $orcamento - $gasto;

        $horasExecutadas = Escala::where('unidade_id', $unidadeId)
            ->where('status', 'executada')
            ->where('ano', $ano)
            ->join('alocacoes', 'escalas.id', '=', 'alocacoes.escala_id')
            ->sum('alocacoes.horas');

        $escalas = Escala::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->orderBy('mes', 'desc')
            ->get();

        $escalasRejeitadas = $escalas->where('status', 'rejeitada')->count();
        $escalasAprovadas = $escalas->where('status', 'aprovada')->count();
        $escalasPendentes = $escalas->where('status', 'pendente')->count();

        return view('diretor.dashboard', compact(
            'orcamento',
            'gasto',
            'disponivel',
            'horasExecutadas',
            'escalas',
            'escalasRejeitadas',
            'escalasAprovadas',
            'escalasPendentes',
            'ano',
            'mes'
        ));
    }

    public function escalaMensal(Request $request)
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $mes = $request->get('mes', date('n'));
        $ano = $request->get('ano', date('Y'));

        $escala = Escala::where('unidade_id', $unidadeId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->first();

        if (!$escala) {
            $escala = Escala::create([
                'unidade_id' => $unidadeId,
                'mes' => $mes,
                'ano' => $ano,
                'status' => 'rascunho',
                'criado_por' => Auth::id(),
            ]);
        }

        $equipes = Equipe::where('unidade_id', $unidadeId)->get();
        $modulos = Modulo::where('unidade_id', $unidadeId)->where('ativo', true)->get();
        $servidores = Servidor::where('unidade_id', $unidadeId)
            ->where('ativo', true)
            ->where('apto_escala_extra', true)
            ->get();

        $escalaServidores = EscalaEquipeServidor::with(['servidor', 'equipe', 'modulo'])
            ->where('escala_id', $escala->id)
            ->get();

        $alocacoes = Alocacao::where('escala_id', $escala->id)->get();

        return view('diretor.escala-mensal', compact(
            'escala',
            'equipes',
            'modulos',
            'servidores',
            'escalaServidores',
            'alocacoes',
            'mes',
            'ano'
        ));
    }

    public function adicionarServidor(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'equipe_id' => 'required|exists:equipes,id',
            'servidor_id' => 'required|exists:servidores,id',
            'modulo_id' => 'nullable|exists:modulos,id',
        ]);

        $existe = EscalaEquipeServidor::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->exists();

        if ($existe) {
            return back()->withErrors(['servidor' => 'Servidor já está na escala']);
        }

        EscalaEquipeServidor::create([
            'escala_id' => $request->escala_id,
            'equipe_id' => $request->equipe_id,
            'servidor_id' => $request->servidor_id,
            'modulo_id' => $request->modulo_id,
            'lider' => $request->has('lider'),
        ]);

        return back()->with('success', 'Servidor adicionado!');
    }

    public function removerServidor(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        EscalaEquipeServidor::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->delete();

        Alocacao::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->delete();

        return back()->with('success', 'Servidor removido!');
    }

    public function alocarDia(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'servidor_id' => 'required|exists:servidores,id',
            'data' => 'required|date',
        ]);

        $alocacao = Alocacao::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->where('data', $request->data)
            ->first();

        if ($alocacao) {
            $alocacao->delete();
            return response()->json(['removed' => true]);
        }

        Alocacao::create([
            'escala_id' => $request->escala_id,
            'servidor_id' => $request->servidor_id,
            'data' => $request->data,
            'horas' => 12,
        ]);

        return response()->json(['added' => true]);
    }

    public function enviarAprovacao(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        $escala->update([
            'status' => 'pendente',
            'data_envio' => now(),
        ]);

        return redirect('/diretor')->with('success', 'Escala enviada para aprovação!');
    }

    public function servidores()
    {
        $user = Auth::user();
        $servidores = Servidor::where('unidade_id', $user->unidade_id)->get();
        return view('diretor.servidores', compact('servidores'));
    }
}
