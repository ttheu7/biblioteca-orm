<?php
/**
 * AUTOLOAD: demonstra o carregamento automático de classes (sem Composer).
 * O namespace Biblioteca\Domain\Livro corresponde ao arquivo
 * src/Domain/Livro.php. A convenção elimina require_once repetitivos.
 * Conceitos: namespaces, funções anônimas, organização e reutilização.
 */
declare(strict_types=1);
spl_autoload_register(function (string $class): void {
    $prefix = 'Biblioteca\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) require $path;
});
