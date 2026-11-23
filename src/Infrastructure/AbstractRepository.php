<?php
/**
 * CLASSE ABSTRATA — herança, abstração e método-template.
 * Reutiliza CRUD com PDO e delega o mapeamento específico aos métodos
 * abstratos implementados pelos repositórios concretos.
 * Os nomes de tabelas e colunas são constantes definidas no código;
 * NUNCA devem vir diretamente de entrada do usuário, pois PDO não permite
 * parametrizar identificadores SQL.
 * Limite didático: não há identity map, controle de concorrência nem
 * validação genérica de todas as entidades.
 */
namespace Biblioteca\Infrastructure;
abstract class AbstractRepository implements RepositoryInterface {
    public function __construct(protected \PDO $pdo) {}
    // Cada subclasse informa a tabela fixa que será utilizada.
    abstract protected function tabela(): string;
    abstract protected function colunas(): array;
    // HIDRATAR: linha do MySQL -> objeto do domínio.
    abstract protected function hidratar(array $linha): object;
    // EXTRAIR: objeto -> array de parâmetros para SQL.
    abstract protected function extrair(object $entidade): array;
    abstract protected function identidade(object $entidade): ?int;
    abstract protected function definirIdentidade(object $entidade, int $id): void;
    // READ: prepared statement vincula o valor de :id.
    public function buscar(int $id): ?object {
        $stmt = $this->pdo->prepare('SELECT * FROM '.$this->tabela().' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $linha = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $linha === false ? null : $this->hidratar($linha);
    }
    public function listar(): array {
        $stmt = $this->pdo->query('SELECT * FROM '.$this->tabela().' ORDER BY id');
        return array_map(fn(array $linha) => $this->hidratar($linha),
            $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
    // CREATE ou UPDATE conforme o objeto tenha ou não identidade.
    public function salvar(object $entidade): void {
        $dados = $this->extrair($entidade);
        $colunas = $this->colunas();
        // Confere a ordem e os nomes das colunas esperadas no mapeamento.
        if (array_keys($dados) !== $colunas) throw new \LogicException('Mapeamento inconsistente');
        $id = $this->identidade($entidade);
        // Sem ID: INSERT e recuperação do AUTO_INCREMENT gerado.
        if ($id === null) {
            $nomes = implode(', ', $colunas);
            $marcadores = implode(', ', array_map(fn($c) => ':'.$c, $colunas));
            $stmt = $this->pdo->prepare('INSERT INTO '.$this->tabela()." ($nomes) VALUES ($marcadores)");
            $stmt->execute($dados);
            $this->definirIdentidade($entidade, (int)$this->pdo->lastInsertId());
        } else {
            // Com ID: UPDATE apenas do registro identificado.
            $atribuicoes = implode(', ', array_map(fn($c) => "$c = :$c", $colunas));
            $stmt = $this->pdo->prepare('UPDATE '.$this->tabela()." SET $atribuicoes WHERE id = :id");
            $stmt->execute($dados + ['id' => $id]);
        }
    }
    public function excluir(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM '.$this->tabela().' WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
