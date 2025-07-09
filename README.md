# SGBD-TDBD2

Este repositório contém o **meu contributo individual** no desenvolvimento do projeto académico da unidade curricular *Sistemas Gestores de Bases de Dados* (SGBD). Cada membro do grupo desenvolveu um número de componentes distintos e aqui encontra-se, exclusivamente, o código das páginas sob a minha responsabilidade.

## Descrição do Projeto

O sistema foi desenvolvido para facilitar a gestão e inserção de dados numa base de dados relacional, integrando autenticação de utilizadores, controlo de permissões via WordPress e lógica de estados.

## Páginas/Componentes Implementados

- **gestao-de-registos.php:** Gestão e registo de novas entidades e respetivos dados.
- **gestao-de-itens.php:** Gestão de tipos de itens e respetivos estados.
- **gestao-de-subitens.php:** Criação e gestão de subitens associados a itens.
- **insercao-de-valores.php:** Interface para inserção dinâmica de valores.
- **common.php:** Funções utilitárias para ligação à base de dados, validações e navegação.
- **CSS personalizado:** Estilos para campos obrigatórios, botões e mensagens de sucesso/erro.

## Funcionalidades Principais

- Validação de permissões específicas para diferentes ações.
- Geração automática de nomes de campos no formulário com base em regras definidas.
- Verificação de campos obrigatórios e formatação.

## Tecnologias Utilizadas

- **PHP** – Lógica do servidor e interação com base de dados.
- **MySQL** – Base de dados relacional.
- **HTML/CSS** – Estrutura e estilo das páginas.
- **WordPress** – Sistema de autenticação e permissões (funções `is_user_logged_in()` e `current_user_can()`).

## Pré-requisitos

- Instância WordPress com permissões administrativas.
- Base de Dados fornecida no contexto académico.
- Ambiente de desenvolvimento compatível (WSL, XAMPP, MAMP, etc.).

## Como Visualizar

O código está organizado para consulta, revisão e demonstração de lógica e estrutura.

Este projeto foi desenvolvido num ambiente local com integração ao WordPress. A execução direta não é viável fora deste contexto, uma vez que depende de ficheiros e configurações internas não incluídos neste repositório.
