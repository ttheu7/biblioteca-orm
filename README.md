# BibliotecaORM — PHP puro + MySQL

Implementação didática de um Data Mapper simplificado, conforme o artigo/tutorial da disciplina de Programação II (UNOESC).

## Requisitos
PHP 8.2+, extensão `pdo_mysql`, MySQL 8 com InnoDB.

## Configuração
1. Execute `sql/schema.sql` no MySQL (cria `biblioteca_orm`).
2. Configure as variáveis de ambiente `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` (veja `.env.example`).
3. Execute `php -d zend.assertions=1 -d assert.exception=1 tests/smoke.php`.

Não requer Composer nem frameworks. Os testes de integração precisam de MySQL ativo.

## Estrutura
- `src/Domain`: entidades encapsuladas.
- `src/Infrastructure`: conexão PDO, contrato, classe abstrata e repositórios.
- `tests/smoke.php`: teste de integração com dados temporários.
- `sql/schema.sql`: esquema relacional.

## Limitações didáticas
Sem carregamento automático de relacionamentos, identity map, controle de concorrência, migrations ou query builder. A relação N:N é manipulada explicitamente. A camada não é destinada a produção sem testes adicionais.
