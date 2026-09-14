# Vendor Back - API do Portal do Vendedor

Esta é a API do Vendor Portal, o backend que trata de tudo o que acontece por trás do painel do vendedor. É uma API REST em Laravel, onde cada vendedor tem a sua conta e só vê e mexe nos seus próprios produtos, inventário e encomendas. É consumida pelo frontend em Vue, o projeto irmão Vendor-front.

## Links

API em produção: http://169.58.26.205:8080

As rotas ficam todas sob o prefixo `/api`, por exemplo `http://169.58.26.205:8080/api/login`.

## Tecnologias

Foi feito em Laravel com PHP 8.3, e a base de dados é MySQL com o motor InnoDB. A autenticação usa o Laravel Sanctum em modo de token. O envio de emails, para a recuperação de senha e as notificações, é feito por SMTP. Não há muito mais, é uma stack propositadamente simples e conhecida.

## Como correr

Precisas de PHP, Composer e um MySQL a correr. Os passos são os habituais.

```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

A API fica em `http://localhost:8000/api`. O comando de seed cria um vendedor de demonstração com o email `demo@vp.mz` e a palavra-passe `password`, já com produtos, categorias e clientes para explorar.

Antes de correr as migrations, confirma no `.env` os dados de ligação à base de dados, e no `config/database.php` que o motor está definido como InnoDB, por causa da razão que explico a seguir.

## O desafio da concorrência

O enunciado apresenta um cenário concreto. Um produto tem 10 unidades em stock. Dois clientes fazem encomendas ao mesmo tempo, um pede 7 e o outro pede 5. O sistema tem de impedir que se confirmem mais unidades do que as que existem.

A minha solução tem duas partes. A primeira é o momento em que o stock é descontado. Uma encomenda criada fica apenas pendente e não toca no stock. O desconto só acontece quando a encomenda é confirmada. É por isso que as duas encomendas podem existir pendentes ao mesmo tempo sobre as mesmas 10 unidades, o confronto só existe na confirmação.

A segunda parte é como essa confirmação é feita. Toda a operação corre dentro de uma transação de base de dados, e antes de ler o stock a linha do produto é bloqueada. Isto obriga qualquer outra confirmação sobre o mesmo produto a esperar que a primeira termine. A primeira confirma as 7 unidades e o stock fica em 3. Só então a segunda avança, lê o valor real de 3, percebe que não chega para as 5 pedidas e é recusada com um erro claro. Como está tudo dentro da transação, a encomenda recusada não consome nada.

Sem este bloqueio, as duas confirmações podiam ler 10 ao mesmo tempo e ambas concluir que havia stock, o que levaria a vender 12 unidades de 10. É exatamente isso que o bloqueio impede. Este comportamento está coberto por um teste automático que reproduz o cenário do enunciado.

## Como está organizado

A lógica de negócio mais pesada não vive nos controladores, vive em classes de serviço. Os controladores são finos, só recebem o pedido, chamam quem trabalha e devolvem a resposta. A validação da entrada acontece em classes próprias antes de o pedido chegar ao controlador, e a forma como os dados saem é moldada por resources, que mantêm as respostas consistentes e escondem o que é sensível.

Há três ideias que gosto de destacar. A primeira é o isolamento por vendedor, em que todas as consultas partem do vendedor autenticado, por isso ninguém consegue ver ou tocar nos dados de outro. A segunda é o inventário como livro-razão, em que cada alteração de stock fica registada como um movimento, com o tipo, a quantidade e o total resultante, o que dá um histórico completo em vez de apenas guardar o número atual. A terceira são as cópias que cada linha de encomenda guarda do nome e do preço do produto no momento da compra, para que uma alteração futura ao catálogo não distorça o histórico de vendas.

## Dificuldades

A parte que me deu mais trabalho foi o deploy no servidor. Duas coisas em concreto. Primeiro, o MySQL estava a criar as tabelas no motor MyISAM, que não suporta transações nem bloqueio de linhas, ou seja, exatamente aquilo de que a solução de concorrência precisa. Tive de forçar o InnoDB na configuração para tudo funcionar. Depois, o servidor tinha uma versão do PHP mais antiga do que a da minha máquina, o que fez o Composer recusar instalar, e tive de acertar as versões para o projecto correr.

## Melhorias futuras
Pretendo passar o envio de emails para uma fila, para não atrasar a resposta ao utilizador. E daria à API um endereço seguro próprio, com https, para o frontend poder falar com ela de forma direta em vez de depender de um proxy.
