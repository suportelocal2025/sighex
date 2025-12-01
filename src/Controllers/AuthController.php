<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class AuthController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function showLogin(): void {
        View::render('auth/login');
    }
    
    public function login(): void {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            Session::flash('error', 'Email e senha são obrigatórios');
            View::redirect('/login');
            return;
        }
        
        $user = $this->db->fetch(
            "SELECT * FROM usuarios WHERE email = :email AND ativo = true",
            ['email' => $email]
        );
        
        if (!$user || !password_verify($senha, $user['senha'])) {
            Session::flash('error', 'Email ou senha inválidos');
            View::redirect('/login');
            return;
        }
        
        Session::setUser($user);
        Session::flash('success', 'Bem-vindo, ' . $user['nome'] . '!');
        
        View::redirect('/');
    }
    
    public function logout(): void {
        Session::destroy();
        View::redirect('/login');
    }
}
