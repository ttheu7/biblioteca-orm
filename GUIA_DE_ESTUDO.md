# BibliotecaORM — Guia de leitura dos fontes comentados

**UNOESC · Ciência da Computação · Programação II**  
**Professor:** Leandro Otavio Cordova Vieira

## Objetivo
Estudar como construir um **mini-ORM Data Mapper** em PHP puro com PDO e MySQL. 

## Ordem sugerida de leitura
1. `sql/schema.sql`: entidades relacionais, PK, FK e tabela N:N `livros_autores`.
2. `src/Domain/Autor.php` e `Livro.php`: **encapsulamento**, validação e identidade.
3. `src/Infrastructure/RepositoryInterface.php`: **polimorfismo** por contrato.
4. `src/Infrastructure/AbstractRepository.php`: **herança** e métodos abstratos para CRUD reutilizável.
5. `AutorRepository.php` e `LivroRepository.php`: hidratação, extração, mapeamento e relacionamento.
6. `Conexao.php` e `autoload.php`: PDO, namespaces e infraestrutura.
7. `tests/smoke.php`: execução integrada do fluxo de persistência.

## Três pilares de POO — localize no código
- **Encapsulamento:** propriedades `private`, métodos de acesso e validações nas entidades.
- **Herança:** `LivroRepository` e `AutorRepository` estendem `AbstractRepository`.
- **Polimorfismo:** ambos são utilizáveis como `RepositoryInterface`.

## Questões para orientar o estudo
1. Por que `Livro` não executa comandos SQL?
2. Como o método `salvar()` decide entre INSERT e UPDATE?
3. O que muda entre `extrair()` e `hidratar()`?
4. Por que `LivroRepository` implementa os métodos abstratos?
5. Como `RepositoryInterface` reduz o acoplamento?
6. Por que `livros_autores` é necessária no MySQL, mas a classe Livro possui `array $autores`?
7. O que acontece ao excluir um livro ainda referenciado por `livros_autores`?
8. Quais operações deveriam utilizar transações?

## Execução
Requisitos: PHP 8.2+, extensão `pdo_mysql`, MySQL 8. Execute `sql/schema.sql` em banco de testes, configure `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` como variáveis de ambiente e rode:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/smoke.php
```

O arquivo `.env.example` **não é carregado automaticamente**. O teste cria e remove dados temporários e precisa de MySQL ativo.

## Desafios de evolução (não implementados nesta versão)
- Implementar `Exemplar` e `ExemplarRepository`.
- Usar transações ao salvar livro e autores associados.
- Adicionar tratamento de erros e testes de falha de chave estrangeira.
- Implementar carregamento explícito de autores na coleção do Livro.
- Criar um teste automatizado de UPDATE, DELETE e rollback.
