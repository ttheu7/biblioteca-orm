<?php
/**
 * TESTE DE INTEGRAÇÃO — exercita o ciclo CRUD real contra o MySQL.
 * Atenção: este script insere e apaga registros temporários.
 * Execute somente em banco de testes. Assertions devem estar habilitadas:
 * php -d zend.assertions=1 -d assert.exception=1 tests/smoke.php
 */
declare(strict_types=1);
require dirname(__DIR__) . '/autoload.php';
use Biblioteca\Domain\Livro;
use Biblioteca\Domain\Autor;
use Biblioteca\Infrastructure\Conexao;
use Biblioteca\Infrastructure\LivroRepository;
use Biblioteca\Infrastructure\AutorRepository;
use Biblioteca\Infrastructure\RepositoryInterface;
function buscarPolimorfico(RepositoryInterface $repo, int $id): ?object { return $repo->buscar($id); }
// Injeta a MESMA conexão nos dois repositórios.
$pdo = Conexao::abrir();
$livros = new LivroRepository($pdo);
$autores = new AutorRepository($pdo);
$tag = bin2hex(random_bytes(4));
// Primeiro persiste o Autor e o Livro, obtendo seus IDs.
$autor = new Autor('Autor Teste '.$tag, 'Brasil');
$autores->salvar($autor);
$livro = new Livro('Livro Teste '.$tag, null, 2026);
$livros->salvar($livro);
assert($livro->id() !== null);
// Agora a tabela associativa pode referenciar ambos os registros.
$livros->associarAutor($livro->id(), $autor->id());
// Demonstra despacho polimórfico pelo contrato RepositoryInterface.
$recuperado = buscarPolimorfico($livros, $livro->id());
assert($recuperado instanceof Livro);
assert(count($livros->autoresDoLivro($livro->id())) === 1);
$recuperado->alterarTitulo('Livro Atualizado '.$tag);
$livros->salvar($recuperado);
assert($livros->buscar($livro->id())->titulo() === 'Livro Atualizado '.$tag);
assert(buscarPolimorfico($autores, $autor->id()) instanceof Autor);
// Remoção da associação antes da exclusão para respeitar a FK.
$pdo->prepare('DELETE FROM livros_autores WHERE livro_id = :id')->execute(['id' => $livro->id()]);
$livros->excluir($livro->id());
$autores->excluir($autor->id());
echo "Smoke test concluído com sucesso\n";
