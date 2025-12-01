<?php
namespace App\Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy(): void {
        self::start();
        session_destroy();
        $_SESSION = [];
    }
    
    public static function flash(string $key, mixed $value): void {
        self::set('_flash_' . $key, $value);
    }
    
    public static function getFlash(string $key, mixed $default = null): mixed {
        $value = self::get('_flash_' . $key, $default);
        self::remove('_flash_' . $key);
        return $value;
    }
    
    public static function isLoggedIn(): bool {
        return self::has('user_id');
    }
    
    public static function getUser(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => self::get('user_id'),
            'nome' => self::get('user_nome'),
            'email' => self::get('user_email'),
            'papel' => self::get('user_papel'),
            'unidade_id' => self::get('user_unidade_id')
        ];
    }
    
    public static function setUser(array $user): void {
        self::set('user_id', $user['id']);
        self::set('user_nome', $user['nome']);
        self::set('user_email', $user['email']);
        self::set('user_papel', $user['papel']);
        self::set('user_unidade_id', $user['unidade_id'] ?? null);
    }
    
    public static function getUserPapel(): ?string {
        return self::get('user_papel');
    }
    
    public static function getUserUnidadeId(): ?int {
        return self::get('user_unidade_id');
    }
}
