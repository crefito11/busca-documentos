# Busca de Documentos

Sistema de indexação e busca de documentos (PDF, etc.) utilizando **Elasticsearch**, **Apache Tika** e **PHP**, executando em containers Docker.

O projeto permite indexar arquivos de uma pasta e realizar buscas rápidas no conteúdo dos documentos.

---

# Requisitos

Antes de iniciar, instale:

* Docker
* Docker Compose (já incluído no Docker Desktop)

Verifique a instalação:

```bash
docker --version
docker compose version
```

---

# Estrutura do Projeto

```
BUSCA-DOCUMENTOS
 ├ backup
 │   └ esdata-backup.tar.gz
 ├ php
 │   └ Dockerfile
 ├ scripts
 │   ├ buscar.php
 │   ├ buscar_caminhos.php
 │   └ indexador.php
 ├ docker-compose.yml
 └ README.md
```

Descrição:

* **backup/** → contém o backup do índice do Elasticsearch
* **php/** → imagem PHP usada pelo sistema
* **scripts/** → scripts de indexação e busca
* **docker-compose.yml** → configuração dos containers
* **README.md** → documentação do projeto

---

# Restaurando o Projeto em uma Nova Máquina

Siga os passos abaixo para subir o sistema em outro computador sem precisar reindexar os documentos.

---

## 1. Clonar ou copiar o projeto

```bash
git clone SEU_REPOSITORIO
cd BUSCA-DOCUMENTOS
```

Ou simplesmente copie a pasta do projeto.

---

## 2. Criar o volume do Elasticsearch

Crie o volume que armazenará os dados do Elasticsearch:

```bash
docker volume create busca-documentos_esdata
```

---

## 3. Restaurar o backup do índice

Execute o comando abaixo no Powershell com Administrador:

```bash
docker run --rm -v busca-documentos_esdata:/data -v $(pwd)/backup:/backup alpine tar xzf /backup/esdata-backup.tar.gz -C /data
```
Exemplo:
$(pwd) -> "C:\users\HOME\Documents\workspace\crefito11\busca-documentos/backup"

Esse comando irá restaurar o índice previamente salvo no volume do Elasticsearch.

---

## 4. Iniciar os containers

Suba o ambiente com:

```bash
docker compose up -d --build
```

Isso iniciará os serviços:

* Elasticsearch
* Apache Tika
* PHP

---

## 5. Verificar se o Elasticsearch está funcionando

Abra no navegador:

```
http://localhost:9200
```

Se estiver funcionando, verá uma resposta JSON semelhante a:

```json
{
  "name": "elasticsearch",
  "cluster_name": "docker-cluster"
}
```

---

# Montando a pasta `/documentos` para arquivos externos

Após o projeto estar rodando, é necessário montar a pasta onde ficam os arquivos que serão indexados.

Essa pasta será montada dentro do container `php-api`.

---

## 1. Acessar o container

```bash
docker exec -it php-api bash
```

---

## 2. Criar a pasta `/documentos`

Dentro do container execute:

```bash
mkdir /documentos
```

---

## 3. Montar o compartilhamento de rede

Execute o comando abaixo para montar o compartilhamento SMB:

```bash
mount -t cifs //192.168.15.100/arquivo /documentos \
-o username=user,password=password,vers=3.0
```

Parâmetros utilizados:

* `//192.168.15.100/arquivo` → caminho do compartilhamento de rede
* `/documentos` → pasta dentro do container onde o compartilhamento será montado
* `username` → usuário com acesso ao compartilhamento
* `password` → senha do usuário
* `vers=3.0` → versão do protocolo SMB

---

## 4. Verificar se a montagem funcionou

```bash
ls /documentos
```

Se a montagem estiver correta, os arquivos do servidor aparecerão nesse diretório.

---

## Observação

Essa montagem é **temporária** e será perdida caso o container seja reiniciado.

Caso precise tornar a montagem permanente, recomenda-se configurar o mount diretamente no `docker-compose` ou em um script de inicialização do container.

---

## 6. Testar a busca

Exemplo de busca:

```
http://localhost:8080/buscar.php?q=teste
```

Ou para retornar apenas os caminhos dos arquivos:

```
http://localhost:8080/buscar_caminhos.php?q=teste
```

---

# Criando Backup do Elasticsearch (PowerShell - Administrador)

Para salvar um backup do índice do Elasticsearch, execute o **PowerShell como Administrador** e rode o comando abaixo:

```bash
docker run --rm -v busca-documentos_esdata:/data -v /c/docker-backup:/backup alpine tar czf /backup/esdata-backup.tar.gz -C /data .
```

Esse comando irá:

1. Acessar o volume `busca-documentos_esdata`
2. Compactar os dados do Elasticsearch
3. Gerar o arquivo de backup

O backup será salvo em:

```
C:\docker-backup\esdata-backup.tar.gz
```

---

# Restaurando um Backup

Para restaurar o backup em outra máquina:

1. Crie o volume:

```bash
docker volume create busca-documentos_esdata
```

2. Execute a restauração:

```bash
docker run --rm -v busca-documentos_esdata:/data -v $(pwd)/backup:/backup alpine tar xzf /backup/esdata-backup.tar.gz -C /data
```

3. Suba o ambiente:

```bash
docker compose up -d
```

---

# Observações

* O backup contém apenas o **índice do Elasticsearch**, não os arquivos originais.
* Os documentos devem continuar acessíveis no caminho configurado no sistema.
* Recomenda-se manter backups periódicos do volume do Elasticsearch.

---

# Licença

Projeto interno.
