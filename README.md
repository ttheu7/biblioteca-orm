# BibliotecaORM

Mini-ORM didático no padrão Data Mapper, em PHP puro com PDO e MySQL, desenvolvido para a avaliação prática de Programação II (Ciência da Computação, UNOESC Videira).

**Aluno:** Matheus Gonçalves (450908)
**Professor:** Leandro Otavio Cordova Vieira

## Origem do código

A estrutura base (entidades, interface, classe abstrata, repositórios, conexão e smoke test) parte do artigo/tutorial da disciplina (Aula 11). Sobre essa base, foram implementados:

- `LivroRepository::buscarComAutores()`: reconstrói a coleção de autores dentro do objeto Livro (R8)
- `LivroRepository::salvarComAutores()`: salva livro e vínculos numa transação, com rollback em caso de falha (R9)
- `LivroRepository::desassociarAutores()`: remove os vínculos N:N antes da exclusão, respeitando a chave estrangeira
- Teste automatizado de rollback em `tests/smoke.php`
- Script `tests/demo.php`, que gera as 7 evidências de execução exigidas

## Requisitos

- PHP 8.2 ou superior com a extensão `pdo_mysql` habilitada
- MySQL 8 (InnoDB)
- Não usa Composer nem frameworks

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/ttheu7/biblioteca-orm.git
   cd biblioteca-orm
   ```
2. Crie o banco executando `sql/schema.sql` no MySQL.
3. Crie um usuário dedicado (evita usar credenciais de administrador):
   ```sql
   CREATE USER 'biblioteca'@'localhost' IDENTIFIED BY 'sua_senha_local';
   GRANT ALL PRIVILEGES ON biblioteca_orm.* TO 'biblioteca'@'localhost';
   ```
4. Defina as variáveis de ambiente conforme o `.env.example`. O arquivo é só um modelo e **não** é carregado automaticamente.

   PowerShell (Windows):
   ```powershell
   $env:DB_USER="biblioteca"
   $env:DB_PASS="sua_senha_local"
   ```
   Linux/macOS:
   ```bash
   export DB_USER=biblioteca
   export DB_PASS=sua_senha_local
   ```
   `DB_HOST` e `DB_NAME` têm valores padrão (`127.0.0.1` e `biblioteca_orm`).

## Execução

Teste de integração (CRUD, N:N, polimorfismo e rollback):
```bash
php -d zend.assertions=1 -d assert.exception=1 tests/smoke.php
```

Demonstração das 7 evidências de execução:
```bash
php tests/demo.php
```

Os dois scripts criam dados temporários e os removem ao final. Execute apenas em banco de testes.

## Estrutura

```
sql/schema.sql                         Modelo relacional (PKs, FKs, tabela N:N)
src/Domain/                            Entidades encapsuladas (Livro, Autor), sem SQL
src/Infrastructure/Conexao.php         Criação da conexão PDO
src/Infrastructure/RepositoryInterface.php  Contrato comum dos repositórios
src/Infrastructure/AbstractRepository.php   CRUD reutilizável (herança)
src/Infrastructure/LivroRepository.php      Mapeamento de Livro e relação N:N
src/Infrastructure/AutorRepository.php      Mapeamento de Autor
tests/smoke.php                        Teste de integração com assertions
tests/demo.php                         Demonstração das evidências
autoload.php                           Carregamento automático de classes
```

## Onde cada requisito está implementado

| Requisito | Onde |
|---|---|
| R1 Modelo relacional | `sql/schema.sql` |
| R2 Entidades | `src/Domain/Livro.php`, `src/Domain/Autor.php` |
| R3 Contrato | `RepositoryInterface.php` |
| R4 Herança e abstração | `AbstractRepository.php` estendida pelos dois repositórios |
| R5 Polimorfismo | `inspecionar()` em `tests/demo.php`, `buscarPolimorfico()` em `tests/smoke.php` |
| R6 CRUD | `AbstractRepository.php`, evidências 1, 2, 3 e 7 do demo |
| R7 Hidratação | `hidratar()` nos repositórios, evidência 2 do demo |
| R8 Relacionamento N:N | `associarAutor()`, `autoresDoLivro()`, `buscarComAutores()` |
| R9 Segurança e transações | prepared statements em todo SQL, tabelas e colunas fixas no código, `salvarComAutores()` |
| R10 Organização e testes | este README, `tests/`, `.env.example` sem segredos |

## Limitações conhecidas

- Não há identity map: buscar o mesmo registro duas vezes gera dois objetos diferentes.
- Não há controle de concorrência. O `salvar()` decide entre INSERT e UPDATE apenas pela existência do id.
- Os relacionamentos não são carregados automaticamente. É preciso chamar `buscarComAutores()`.
- Após um rollback, o objeto em memória mantém o id recebido no INSERT desfeito, embora o registro não exista no banco.
- O InnoDB não reaproveita valores de AUTO_INCREMENT de inserções desfeitas, então podem surgir lacunas nos ids.
- Não há exclusão em cascata: os vínculos em `livros_autores` precisam ser removidos antes de excluir um livro.
- Carregar vários livros com seus autores gera uma consulta por livro (problema N+1).
- A camada não é destinada a produção sem testes adicionais.