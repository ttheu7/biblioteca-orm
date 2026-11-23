<?php
/**
 * INTERFACE — contrato e polimorfismo.
 * Qualquer repositório que implemente este contrato oferece as mesmas
 * operações. Assim, uma função pode receber RepositoryInterface sem saber
 * se o objeto concreto é LivroRepository ou AutorRepository.
 */
namespace Biblioteca\Infrastructure;
interface RepositoryInterface {
    public function buscar(int $id): ?object;
    public function listar(): array;
    public function salvar(object $entidade): void;
    public function excluir(int $id): void;
}
