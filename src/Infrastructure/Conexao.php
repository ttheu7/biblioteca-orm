<?php
/**
 * CONEXÃO PDO — separa a infraestrutura de banco das entidades.
 * PDO fornece uma interface para conexão e consultas SQL parametrizadas.
 * As variáveis DB_* podem ser definidas no ambiente; .env.example é apenas
 * um modelo e NÃO é carregado automaticamente por este projeto.
 * Em produção, não inclua senhas reais no repositório.
 */
namespace Biblioteca\Infrastructure;
final class Conexao {
    public static function abrir(): \PDO {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $nome = getenv('DB_NAME') ?: 'biblioteca_orm';
        $usuario = getenv('DB_USER') ?: 'biblioteca';
        $senha = getenv('DB_PASS') ?: '';
        // ERRMODE_EXCEPTION transforma falhas SQL em exceções tratáveis.
        // EMULATE_PREPARES=false solicita prepared statements nativos.
        return new \PDO("mysql:host=$host;dbname=$nome;charset=utf8mb4", $usuario, $senha, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
}
