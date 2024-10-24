# Game Log App

## Objetivo

Projeto pessoal público para demonstrar uma aplicação de um sistema de catalogo de jogos

## Bibliotecas utilizadas até o momento

- [Laravel 11](https://laravel.com/docs/11.x)
- [Orchid](https://orchid.software/en/docs/installation/)
- [Xdebug](https://xdebug.org/)

## Instruções

### Laravel

#### Instalando o projeto

Para instar o projeto, via linha de comando, na raiz do projeto, execute o seguinte comando:

```shell
composer install
```

### Orchid

#### Criar usuário administrador

Para criar um usuário do tipo administrador, execute na linha de comando o seguinte comando:

```shell
php artisan orchid:admin
```

## Testes

### Phpunit

Para rodar os testes unitários e de funcionalidades, execute na linha de comando o seguinte comando:

```shell
php artisan test
```

Para rodar os testes com cobertura de testes, execute na linha de comando o seguinte comando:

```shell
php artisan test --coverage-html coverage-report
```

Caso apareça a mensagem `no code coverage driver available` verifique dois pontos:

- Se foi instalado o xdebug
  - Pode ser instalado por exemplo `sudo apt install php-xdebug` ou segindo a versão do PHP instalado
- Se foi adicionado a configuração `xdebug.mode=coverage` no arquivo `php.ini`