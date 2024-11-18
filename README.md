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

### Dusk

Para rodar os teste de frontend, serão necessários 2 processos rodando em separado com os seguintes comandos:

- Processo para subir a aplicação:

```shell
  php artisan serve
```

- Processo para executar o Dusk

```shell
  php artisan dusk
```

Caso queira visualizar o Dusk executando os testes no navegador, execute o comando com o options `--browse`

```shell
  php artisan dusk --browse
```

#### Algumas soluções de erros que podem ocorrer

Caso aconteça o erro SessionNotCreatedException, execute:

```shell
php artisan dusk:chrome-driver --detect
```

Garanta que a variável de ambiente `APP_URL` tenha o valor que você usa para acessar localmente, por exemplo: `http://127.0.0.1:8000`
