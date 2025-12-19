<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class AdminController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function unidades(): void {
        $unidades = $this->db->fetchAll("
            SELECT u.*, 
                   us.nome as responsavel_nome, us.email as responsavel_email,
                   (SELECT COUNT(*) FROM equipes WHERE unidade_id = u.id) as total_equipes,
                   (SELECT COUNT(*) FROM modulos WHERE unidade_id = u.id) as total_modulos,
                   (SELECT COUNT(*) FROM servidores WHERE unidade_id = u.id) as total_servidores
            FROM unidades u
            LEFT JOIN usuarios us ON u.responsavel_id = us.id
            ORDER BY u.nome
        ");
        
        View::layout('main', 'administrativo/unidades', [
            'titulo' => 'Gestão de Unidades',
            'unidades' => $unidades
        ]);
    }
    
    public function novaUnidade(): void {
        View::layout('main', 'administrativo/form-unidade', [
            'titulo' => 'Nova Unidade',
            'unidade' => null,
            'responsavel' => null
        ]);
    }
    
    public function salvarUnidade(): void {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $local = trim($_POST['local'] ?? '');
        $responsavelNome = trim($_POST['responsavel_nome'] ?? '');
        $responsavelEmail = trim($_POST['responsavel_email'] ?? '');
        $responsavelSenha = $_POST['responsavel_senha'] ?? '';
        
        if (empty($nome)) {
            Session::flash('error', 'Nome da unidade é obrigatório');
            View::redirect($id ? "/admin/unidades/{$id}/editar" : '/admin/unidades/nova');
            return;
        }
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            if ($id > 0) {
                $this->db->update('unidades', [
                    'nome' => $nome,
                    'local' => $local,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $id]);
                
                $unidade = $this->db->fetch("SELECT responsavel_id FROM unidades WHERE id = :id", ['id' => $id]);
                
                if ($unidade['responsavel_id'] && !empty($responsavelNome)) {
                    $updateData = [
                        'nome' => $responsavelNome,
                        'email' => $responsavelEmail,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    if (!empty($responsavelSenha)) {
                        $updateData['senha'] = password_hash($responsavelSenha, PASSWORD_DEFAULT);
                    }
                    $this->db->update('usuarios', $updateData, 'id = :id', ['id' => $unidade['responsavel_id']]);
                }
                
                Session::flash('success', 'Unidade atualizada com sucesso!');
            } else {
                $responsavelId = null;
                if (!empty($responsavelNome) && !empty($responsavelEmail) && !empty($responsavelSenha)) {
                    $existingUser = $this->db->fetch("SELECT id FROM usuarios WHERE email = :email", ['email' => $responsavelEmail]);
                    if ($existingUser) {
                        throw new \Exception('Email já cadastrado no sistema');
                    }
                    
                    $responsavelId = $this->db->insert('usuarios', [
                        'nome' => $responsavelNome,
                        'email' => $responsavelEmail,
                        'senha' => password_hash($responsavelSenha, PASSWORD_DEFAULT),
                        'papel' => 'diretor'
                    ]);
                }
                
                $unidadeId = $this->db->insert('unidades', [
                    'nome' => $nome,
                    'local' => $local,
                    'responsavel_id' => $responsavelId
                ]);
                
                if ($responsavelId) {
                    $this->db->update('usuarios', ['unidade_id' => $unidadeId], 'id = :id', ['id' => $responsavelId]);
                }
                
                foreach (['A', 'B', 'C', 'D'] as $equipe) {
                    $this->db->query(
                        "INSERT INTO equipes (unidade_id, nome) VALUES (:uid, :nome)",
                        ['uid' => $unidadeId, 'nome' => "Equipe {$equipe}"]
                    );
                }
                
                Session::flash('success', 'Unidade criada com sucesso!');
            }
            
            $this->db->getConnection()->commit();
            View::redirect('/admin/unidades');
            
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            Session::flash('error', 'Erro ao salvar: ' . $e->getMessage());
            View::redirect($id ? "/admin/unidades/{$id}/editar" : '/admin/unidades/nova');
        }
    }
    
    public function editarUnidade(string $id): void {
        $unidade = $this->db->fetch("SELECT * FROM unidades WHERE id = :id", ['id' => $id]);
        
        if (!$unidade) {
            Session::flash('error', 'Unidade não encontrada');
            View::redirect('/admin/unidades');
            return;
        }
        
        $responsavel = null;
        if ($unidade['responsavel_id']) {
            $responsavel = $this->db->fetch("SELECT * FROM usuarios WHERE id = :id", ['id' => $unidade['responsavel_id']]);
        }
        
        $modulos = $this->db->fetchAll("SELECT * FROM modulos WHERE unidade_id = :uid ORDER BY nome", ['uid' => $id]);
        $equipes = $this->db->fetchAll("SELECT * FROM equipes WHERE unidade_id = :uid ORDER BY nome", ['uid' => $id]);
        
        View::layout('main', 'administrativo/form-unidade', [
            'titulo' => 'Editar Unidade',
            'unidade' => $unidade,
            'responsavel' => $responsavel,
            'modulos' => $modulos,
            'equipes' => $equipes
        ]);
    }
    
    public function excluirUnidade(string $id): void {
        $this->db->delete('unidades', 'id = :id', ['id' => $id]);
        Session::flash('success', 'Unidade excluída com sucesso!');
        View::redirect('/admin/unidades');
    }
    
    public function adicionarModulo(): void {
        $unidadeId = (int)($_POST['unidade_id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        
        if (empty($nome)) {
            View::json(['success' => false, 'message' => 'Nome é obrigatório']);
            return;
        }
        
        $id = $this->db->insert('modulos', [
            'unidade_id' => $unidadeId,
            'nome' => $nome
        ]);
        
        View::json(['success' => true, 'id' => $id, 'nome' => $nome]);
    }
    
    public function removerModulo(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->db->delete('modulos', 'id = :id', ['id' => $id]);
        View::json(['success' => true]);
    }
    
    public function servidores(): void {
        $unidades = $this->db->fetchAll("SELECT id, nome FROM unidades ORDER BY nome");
        
        $filtroUnidade = $_GET['unidade_id'] ?? '';
        $filtroAtivo = $_GET['ativo'] ?? '';
        
        $where = '1=1';
        $params = [];
        
        if ($filtroUnidade) {
            $where .= ' AND s.unidade_id = :uid';
            $params['uid'] = (int)$filtroUnidade;
        }
        
        if ($filtroAtivo !== '') {
            $where .= ' AND s.ativo_extra = :ativo';
            $params['ativo'] = $filtroAtivo === '1';
        }
        
        $servidores = $this->db->fetchAll("
            SELECT s.*, u.nome as unidade_nome
            FROM servidores s
            LEFT JOIN unidades u ON s.unidade_id = u.id
            WHERE {$where}
            ORDER BY s.nome
        ", $params);
        
        View::layout('main', 'administrativo/servidores', [
            'titulo' => 'Gestão de Servidores',
            'servidores' => $servidores,
            'unidades' => $unidades,
            'filtroUnidade' => $filtroUnidade,
            'filtroAtivo' => $filtroAtivo
        ]);
    }
    
    public function salvarServidor(): void {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $unidadeId = (int)($_POST['unidade_id'] ?? 0);
        $ativoExtra = isset($_POST['ativo_extra']) && $_POST['ativo_extra'] == '1';
        
        if (empty($nome) || empty($matricula)) {
            View::json(['success' => false, 'message' => 'Nome e matrícula são obrigatórios']);
            return;
        }
        
        try {
            if ($id > 0) {
                $this->db->update('servidores', [
                    'nome' => $nome,
                    'matricula' => $matricula,
                    'unidade_id' => $unidadeId ?: null,
                    'ativo_extra' => $ativoExtra,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $id]);
                View::json(['success' => true, 'message' => 'Servidor atualizado!']);
            } else {
                $existing = $this->db->fetch("SELECT id FROM servidores WHERE matricula = :mat", ['mat' => $matricula]);
                if ($existing) {
                    View::json(['success' => false, 'message' => 'Matrícula já cadastrada']);
                    return;
                }
                
                $id = $this->db->insert('servidores', [
                    'nome' => $nome,
                    'matricula' => $matricula,
                    'unidade_id' => $unidadeId ?: null,
                    'ativo_extra' => $ativoExtra
                ]);
                View::json(['success' => true, 'message' => 'Servidor cadastrado!', 'id' => $id]);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
    
    public function excluirServidor(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->db->delete('servidores', 'id = :id', ['id' => $id]);
        View::json(['success' => true]);
    }
    
    public function importarUnidades(): void {
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Erro no upload do arquivo');
            View::redirect('/admin/unidades');
            return;
        }
        
        $file = fopen($_FILES['arquivo']['tmp_name'], 'r');
        $header = fgetcsv($file, 0, ';');
        
        $count = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $nome = trim($row[0] ?? '');
            $local = trim($row[1] ?? '');
            
            if (!empty($nome)) {
                $existing = $this->db->fetch("SELECT id FROM unidades WHERE nome = :nome", ['nome' => $nome]);
                if (!$existing) {
                    $unidadeId = $this->db->insert('unidades', ['nome' => $nome, 'local' => $local]);
                    foreach (['A', 'B', 'C', 'D'] as $equipe) {
                        $this->db->query("INSERT INTO equipes (unidade_id, nome) VALUES (:uid, :nome)", 
                            ['uid' => $unidadeId, 'nome' => "Equipe {$equipe}"]);
                    }
                    $count++;
                }
            }
        }
        
        fclose($file);
        Session::flash('success', "{$count} unidade(s) importada(s) com sucesso!");
        View::redirect('/admin/unidades');
    }
    
    public function importarServidores(): void {
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Erro no upload do arquivo');
            View::redirect('/admin/servidores');
            return;
        }
        
        $file = fopen($_FILES['arquivo']['tmp_name'], 'r');
        $header = fgetcsv($file, 0, ';');
        
        $count = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $nome = trim($row[0] ?? '');
            $matricula = trim($row[1] ?? '');
            $unidadeNome = trim($row[2] ?? '');
            $ativo = strtolower(trim($row[3] ?? 'sim')) === 'sim';
            
            if (!empty($nome) && !empty($matricula)) {
                $existing = $this->db->fetch("SELECT id FROM servidores WHERE matricula = :mat", ['mat' => $matricula]);
                if (!$existing) {
                    $unidadeId = null;
                    if (!empty($unidadeNome)) {
                        $unidade = $this->db->fetch("SELECT id FROM unidades WHERE nome ILIKE :nome", ['nome' => "%{$unidadeNome}%"]);
                        $unidadeId = $unidade['id'] ?? null;
                    }
                    
                    $this->db->insert('servidores', [
                        'nome' => $nome,
                        'matricula' => $matricula,
                        'unidade_id' => $unidadeId,
                        'ativo_extra' => $ativo
                    ]);
                    $count++;
                }
            }
        }
        
        fclose($file);
        Session::flash('success', "{$count} servidor(es) importado(s) com sucesso!");
        View::redirect('/admin/servidores');
    }
    
    public function usuarios(): void {
        $usuarios = $this->db->fetchAll("
            SELECT u.id, u.nome, u.email, u.papel, u.unidade_id, u.ativo, u.created_at, u.updated_at,
                   un.nome as unidade_nome
            FROM usuarios u
            LEFT JOIN unidades un ON u.unidade_id = un.id
            ORDER BY u.papel, u.nome
        ");
        
        $unidades = $this->db->fetchAll("SELECT id, nome FROM unidades ORDER BY nome");
        
        View::layout('main', 'administrativo/usuarios', [
            'titulo' => 'Gestão de Usuários',
            'usuarios' => $usuarios,
            'unidades' => $unidades
        ]);
    }
    
    public function salvarUsuario(): void {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $papel = $_POST['papel'] ?? '';
        $unidadeId = !empty($_POST['unidade_id']) ? (int)$_POST['unidade_id'] : null;
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        if (empty($nome) || empty($email) || empty($papel)) {
            Session::flash('error', 'Nome, email e perfil são obrigatórios');
            View::redirect('/admin/usuarios');
            return;
        }
        
        if ($id === 0 && empty($senha)) {
            Session::flash('error', 'Senha é obrigatória para novos usuários');
            View::redirect('/admin/usuarios');
            return;
        }
        
        if ($papel === 'diretor' && empty($unidadeId)) {
            Session::flash('error', 'Diretores devem estar vinculados a uma unidade');
            View::redirect('/admin/usuarios');
            return;
        }
        
        try {
            $existingEmail = $this->db->fetch(
                "SELECT id FROM usuarios WHERE email = :email AND id != :id",
                ['email' => $email, 'id' => $id]
            );
            
            if ($existingEmail) {
                Session::flash('error', 'Este email já está em uso por outro usuário');
                View::redirect('/admin/usuarios');
                return;
            }
            
            if ($id > 0) {
                $updateData = [
                    'nome' => $nome,
                    'email' => $email,
                    'papel' => $papel,
                    'unidade_id' => $unidadeId,
                    'ativo' => $this->db->boolValue($ativo),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (!empty($senha)) {
                    $updateData['senha'] = password_hash($senha, PASSWORD_DEFAULT);
                }
                
                $this->db->update('usuarios', $updateData, 'id = :id', ['id' => $id]);
                
                if ($papel === 'diretor' && $unidadeId) {
                    $this->db->query(
                        "UPDATE unidades SET responsavel_id = :uid WHERE id = :unid",
                        ['uid' => $id, 'unid' => $unidadeId]
                    );
                }
                
                Session::flash('success', 'Usuário atualizado com sucesso!');
            } else {
                $novoId = $this->db->insert('usuarios', [
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => password_hash($senha, PASSWORD_DEFAULT),
                    'papel' => $papel,
                    'unidade_id' => $unidadeId,
                    'ativo' => $this->db->boolValue($ativo)
                ]);
                
                if ($papel === 'diretor' && $unidadeId) {
                    $this->db->query(
                        "UPDATE unidades SET responsavel_id = :uid WHERE id = :unid",
                        ['uid' => $novoId, 'unid' => $unidadeId]
                    );
                }
                
                Session::flash('success', 'Usuário criado com sucesso!');
            }
            
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao salvar usuário: ' . $e->getMessage());
        }
        
        View::redirect('/admin/usuarios');
    }
    
    public function resetarSenhaUsuario(): void {
        $id = (int)($_POST['id'] ?? 0);
        $novaSenha = $_POST['nova_senha'] ?? '';
        
        if (empty($novaSenha) || strlen($novaSenha) < 6) {
            Session::flash('error', 'A nova senha deve ter no mínimo 6 caracteres');
            View::redirect('/admin/usuarios');
            return;
        }
        
        $this->db->update('usuarios', [
            'senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $id]);
        
        Session::flash('success', 'Senha resetada com sucesso!');
        View::redirect('/admin/usuarios');
    }
    
    public function excluirUsuario(): void {
        $id = (int)($_POST['id'] ?? 0);
        $usuarioLogado = Session::getUser();
        
        if ($id === (int)$usuarioLogado['id']) {
            Session::flash('error', 'Você não pode excluir seu próprio usuário');
            View::redirect('/admin/usuarios');
            return;
        }
        
        $usuario = $this->db->fetch("SELECT * FROM usuarios WHERE id = :id", ['id' => $id]);
        
        if (!$usuario) {
            Session::flash('error', 'Usuário não encontrado');
            View::redirect('/admin/usuarios');
            return;
        }
        
        if ($usuario['unidade_id']) {
            $this->db->query(
                "UPDATE unidades SET responsavel_id = NULL WHERE responsavel_id = :id",
                ['id' => $id]
            );
        }
        
        $this->db->query("DELETE FROM usuarios WHERE id = :id", ['id' => $id]);
        
        Session::flash('success', 'Usuário excluído com sucesso!');
        View::redirect('/admin/usuarios');
    }
}
