<?php
namespace App\Core;

class Middleware {
    public static function auth(): callable {
        return function() {
            if (!Session::isLoggedIn()) {
                View::redirect('/login');
                return false;
            }
            return true;
        };
    }
    
    public static function guest(): callable {
        return function() {
            if (Session::isLoggedIn()) {
                View::redirect('/');
                return false;
            }
            return true;
        };
    }
    
    public static function role(string|array $roles): callable {
        return function() use ($roles) {
            if (!Session::isLoggedIn()) {
                View::redirect('/login');
                return false;
            }
            
            $userPapel = Session::getUserPapel();
            $allowedRoles = is_array($roles) ? $roles : [$roles];
            
            if (!in_array($userPapel, $allowedRoles)) {
                http_response_code(403);
                echo "Acesso negado. Você não tem permissão para acessar esta página.";
                return false;
            }
            
            return true;
        };
    }
    
    public static function superintendente(): callable {
        return self::role('superintendente');
    }
    
    public static function diretor(): callable {
        return self::role('diretor');
    }
    
    public static function rh(): callable {
        return self::role('rh');
    }
    
    public static function administrativo(): callable {
        return self::role('administrativo');
    }
}
