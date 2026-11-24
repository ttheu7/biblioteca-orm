<?php

declare(strict_types=1);
require dirname(__DIR__) . '/autoload.php';

use Biblioteca\Domain\Autor;
use Biblioteca\Domain\Livro;
use Biblioteca\Infrastructure\AutorRepository;
use Biblioteca\Infrastructure\Conexao;
use Biblioteca\Infrastructure\LivroRepository;
use Biblioteca\Infrastructure\RepositoryInterface;

function secao(string $titulo): void {
    echo PHP_EOL, str_repeat('=', 60), PHP_EOL, $titulo, PHP_EOL, str_repeat('=', 60), PHP_EOL;
}

function mostrarLivro(Livro $l): void {
    echo "  id={$l->id()} | titulo=\"{$l->titulo()}\" | isbn=" . ($l->isbn() ?? 'null')
       . ' | ano=' . ($l->anoPublicacao() ?? 'null') . PHP_EOL;
}

function inspecionar(RepositoryInterface $repo, int $id): void {
    $obj = $repo->buscar($id);
    echo '  ' . get_class($repo) . " -> buscar($id) devolveu "
       . ($obj === null ? 'null' : get_class($obj))
       . ' | listar() devolveu ' . count($repo->listar()) . ' registro(s)' . PHP_EOL;
}

$pdo = Conexao::abrir();
$livros = new LivroRepository($pdo);
$autores = new AutorRepository($pdo);
$tag = bin2hex(random_bytes(3));

secao('1. Cadastro de Livro e Autor (IDs gerados pelo MySQL)');
$autor1 = new Autor('Ana Souza ' . $tag, 'Brasil');
$autor2 = new Autor('Bruno Lima ' . $tag, 'Portugal');
echo '  Antes de salvar: autor1->id() = ' . var_export($autor1->id(), true) . PHP_EOL;
$autores->salvar($autor1);
$autores->salvar($autor2);
$isbn = '978' . str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
$livro = new Livro('Programação Orientada a Objetos ' . $tag, $isbn, 2026);
$livros->salvar($livro);
echo "  Autor 1: id={$autor1->id()} | {$autor1->nome()}" . PHP_EOL;
echo "  Autor 2: id={$autor2->id()} | {$autor2->nome()}" . PHP_EOL;
mostrarLivro($livro);

secao('2. Busca por ID, reconstrução das propriedades e listagem');
$encontrado = $livros->buscar($livro->id());
mostrarLivro($encontrado);
echo '  Mesmo objeto da memória? ' . ($encontrado === $livro ? 'sim' : 'não, é um novo objeto hidratado do banco') . PHP_EOL;
echo '  Tipo de id: ' . get_debug_type($encontrado->id())
   . ' | tipo de ano: ' . get_debug_type($encontrado->anoPublicacao()) . PHP_EOL;
echo '  listar():' . PHP_EOL;
foreach ($livros->listar() as $l) mostrarLivro($l);

secao('3. Atualização e nova consulta');
$encontrado->alterarTitulo('POO com PHP e MySQL ' . $tag);
$livros->salvar($encontrado);
mostrarLivro($livros->buscar($livro->id()));

secao('4. Associação de dois autores e reconstrução da coleção (N:N)');
$livros->associarAutor($livro->id(), $autor1->id());
$livros->associarAutor($livro->id(), $autor2->id());
$completo = $livros->buscarComAutores($livro->id());
foreach ($completo->autores() as $a) {
    echo "  - id={$a->id()} | {$a->nome()} ({$a->nacionalidade()})" . PHP_EOL;
}
$completo->adicionarAutor($autores->buscar($autor1->id()));
echo '  Após tentar adicionar o autor 1 de novo: ' . count($completo->autores()) . ' autor(es)' . PHP_EOL;

secao('5. Polimorfismo: mesmo contrato, dois repositórios');
inspecionar($livros, $livro->id());
inspecionar($autores, $autor1->id());

secao('6. Erro intencional em transação e rollback');
$tituloFalho = 'Livro que não deve existir ' . $tag;
try {
    $livros->salvarComAutores(new Livro($tituloFalho), [$autor1->id(), 999999999]);
    echo '  (inesperado) a transação foi concluída' . PHP_EOL;
} catch (\PDOException $e) {
    echo '  Erro capturado: ' . $e->getMessage() . PHP_EOL;
}
$restantes = array_filter($livros->listar(), fn(Livro $l) => $l->titulo() === $tituloFalho);
echo '  Livros com esse título após o rollback: ' . count($restantes) . PHP_EOL;

secao('7. Exclusão controlada e chave estrangeira');
try {
    $livros->excluir($livro->id());
    echo '  (inesperado) a exclusão foi aceita' . PHP_EOL;
} catch (\PDOException $e) {
    echo '  Exclusão bloqueada (erro MySQL ' . ($e->errorInfo[1] ?? '?')
       . '): o livro ainda é referenciado em livros_autores.' . PHP_EOL;
}
$livros->desassociarAutores($livro->id());
$livros->excluir($livro->id());
echo '  Após remover os vínculos, buscar() devolve: ' . var_export($livros->buscar($livro->id()), true) . PHP_EOL;
$autores->excluir($autor1->id());
$autores->excluir($autor2->id());

echo PHP_EOL . 'Demonstração concluída. Dados temporários removidos.' . PHP_EOL;