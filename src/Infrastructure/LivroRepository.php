<?php
/**
 * DATA MAPPER DE LIVRO — especialização e relacionamento N:N.
 * A classe herdada oferece CRUD; esta classe fornece os metadados e
 * conversões específicos de Livro e consultas da tabela associativa.
 * Atenção: autoresDoLivro() retorna autores, mas NÃO preenche automaticamente
 * a coleção interna de Livro. O carregamento é explícito neste mini-ORM.
 */
namespace Biblioteca\Infrastructure;
use Biblioteca\Domain\Livro;
final class LivroRepository extends AbstractRepository {
    protected function tabela(): string { return 'livros'; }
    protected function colunas(): array { return ['titulo','isbn','ano_publicacao']; }
    protected function hidratar(array $linha): Livro {
        $livro = new Livro($linha['titulo'], $linha['isbn'],
            $linha['ano_publicacao'] === null ? null : (int)$linha['ano_publicacao']);
        $livro->atribuirId((int)$linha['id']);
        return $livro;
    }
    protected function extrair(object $entidade): array {
        if (!$entidade instanceof Livro) throw new \InvalidArgumentException('Esperado Livro');
        return ['titulo' => $entidade->titulo(), 'isbn' => $entidade->isbn(),
                'ano_publicacao' => $entidade->anoPublicacao()];
    }
    protected function identidade(object $entidade): ?int {
        if (!$entidade instanceof Livro) throw new \InvalidArgumentException('Esperado Livro');
        return $entidade->id();
    }
    protected function definirIdentidade(object $entidade, int $id): void {
        if (!$entidade instanceof Livro) throw new \InvalidArgumentException('Esperado Livro');
        $entidade->atribuirId($id);
    }
    // JOIN na tabela associativa: recupera objetos Autor do livro.
    public function autoresDoLivro(int $livroId): array {
        $sql = 'SELECT a.* FROM autores a INNER JOIN livros_autores la
                ON la.autor_id = a.id WHERE la.livro_id = :id ORDER BY a.nome';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $livroId]);
        return array_map(function(array $r) {
            $autor = new \Biblioteca\Domain\Autor($r['nome'], $r['nacionalidade']);
            $autor->atribuirId((int)$r['id']);
            return $autor;
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
    // Persiste a relação N:N; FKs exigem IDs já existentes.
    public function associarAutor(int $livroId, int $autorId): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO livros_autores (livro_id, autor_id) VALUES (:livro, :autor)');
        $stmt->execute(['livro' => $livroId, 'autor' => $autorId]);
    }

    public function buscarComAutores(int $id) : ?Livro {
        $livro = $this->buscar($id);
        if(!$livro instanceof Livro) return null;
        foreach ($this->autoresDoLivro($id) as $autor){
            $livro->adicionarAutor($autor);
        }
        return $livro;
    }

    public function salvarComAutores(Livro $livro, array $autorIds): void {
        $this->pdo->beginTransaction();
        try {
            $this->salvar($livro);
            foreach ($autorIds as $autorId) {
                $this->associarAutor($livro->id(), (int)$autorId);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if($this->pdo->inTransaction()) $this->pdo->rollback();
            throw $e;
        }   
    }

    public function desassociarAutores(int $livroId): void {
        $stmt = $this->pdo->prepare('DELETE FROM livros_autores WHERE livro_id = :id');
        $stmt->execute(['id' => $livroId]);
    }
}
