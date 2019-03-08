# Como executar o projeto

### 1. Baixar a última versão do PHP
https://windows.php.net/download/

Baixe o ZIP e extraia em uma pasta no seu computador.

### 2. Instalar o composer
https://getcomposer.org/

Durante a instalação do **composer** será necessário apontar a pasta onde o PHP está. Depois que o composer estiver instalado, rode o comando `composer` no terminal, se aparecer as dicas do **composer** quer dizer que funcionou.

### 3. Instalar dependências
Abra um terminal qualquer e acesse o diretório do projeto. Rode o comando `composer install`

### 4. Configurar variáveis de ambiente
Rode o comando `cp .env.example .env` para copiar o arquivo de exemplo e colar com o nome **.env**. Nesse arquivo criado, configure todas as variáveis de ambiente com os dados corretos.

### 5. Gerar chave de acesso
Rode o comando `php artisan key:generate` para gerar uma nova de acesso e salvá-la no arquivo **.env**.

### Executar o servidor
Não é necessário utilizar nenhuma forma de servidor PHP como *Xampp*, *Vagrant* e *Nginx*. Basta rodar o comando `php artisan serve` que a aplicação já estará disponível na url http://localhost:8000. Para testar acesse http://localhost:8000/info.