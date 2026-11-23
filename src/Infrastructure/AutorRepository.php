<?php
/**
 * DATA MAPPER DE AUTOR — especialização da classe abstrata.
 * Herança: extends AbstractRepository.
 * Polimorfismo: implementa indiretamente RepositoryInterface.
 * Hidratação: transforma uma linha SQL em objeto Autor.
 * Extração: transforma o objeto em dados para INSERT/UPDATE.
 */
namespace Biblioteca\Infrastructure;
use Biblioteca\Domain\Autor;
final class AutorRepository extends AbstractRepository {
    protected function tabela(): string { return 'autores'; }
    protected function colunas(): array { return ['nome','nacionalidade']; }
    protected function hidratar(array $r): Autor {
        $a = new Autor($r['nome'], $r['nacionalidade']);
        $a->atribuirId((int)$r['id']); return $a;
    }
    protected function extrair(object $e): array {
        if (!$e instanceof Autor) throw new \InvalidArgumentException('Esperado Autor');
        return ['nome'=>$e->nome(), 'nacionalidade'=>$e->nacionalidade()];
    }
    protected function identidade(object $e): ?int {
        if (!$e instanceof Autor) throw new \InvalidArgumentException('Esperado Autor');
        return $e->id();
    }
    protected function definirIdentidade(object $e, int $id): void {
        if (!$e instanceof Autor) throw new \InvalidArgumentException('Esperado Autor');
        $e->atribuirId($id);
    }
}
function consultarPorContrato(RepositoryInterface $repo, int $id): ?object {
    return $repo->buscar($id);
}
