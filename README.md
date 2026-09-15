# BiblioTech Simples — Versão Inicial para os Alunos

Projeto didático de biblioteca escolar em PHP + MySQL + HTML/CSS/JavaScript.

## Ambiente
- XAMPP: Apache + MySQL
- PHP 8.x
- MySQL
- VS Code
- Git/GitHub

## Instalação
1. Copie a pasta `bibliotech_simples_inicial` para `C:\xampp\htdocs\bibliotech_simples`.
2. Inicie Apache e MySQL no XAMPP.
3. Abra `http://localhost/phpmyadmin`.
4. Importe `database/bibliotech_simples.sql`.
5. Confira `database/conexao.php`.
6. Acesse `http://localhost/bibliotech_simples/`.

## Conta inicial
E-mail: admin@bibliotech.local
Senha: 123456

A senha é criada com `password_hash()` pelo arquivo `database/criar_admin.php`.
Execute esse arquivo uma única vez e depois remova ou proteja o arquivo.

## O que os alunos devem completar
Os arquivos já possuem a estrutura inicial, consultas básicas e comentários `TODO`.
As partes marcadas como TODO são pontos de desenvolvimento das aulas:
1. Ambiente e arquitetura
2. Banco de dados
3. Conexão
4. Interface
5. CRUD de usuários
6. Login/sessão
7. Permissões
8. CRUD de livros
9. Empréstimos
10. Devoluções
11. Dashboard
12. Testes e correções
