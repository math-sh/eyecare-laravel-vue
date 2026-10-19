# EyeCare — Cadastro de Pacientes

CRUD completo de pacientes: API RESTful em **PHP 8.4 / Laravel 13** e painel em **Vue 2.7**, com
MySQL e aplicação conteinerizados via Docker Compose.

| | |
|---|---|
| Painel | http://localhost:5173 |
| API | http://localhost:8000/api |
| Health check | http://localhost:8000/up |
| Coleção Postman | [`docs/eyecare.postman_collection.json`](docs/eyecare.postman_collection.json) |

---

## Como executar

Pré-requisito: Docker com Compose v2. Nada mais precisa estar instalado — PHP, Composer, Node e
MySQL rodam dentro dos containers.

```bash
git clone <url-do-repositorio> eyecare
cd eyecare

cp api/.env.example api/.env
cp painel/.env.example painel/.env

docker compose up -d --build
```

Na primeira execução o container da API roda `composer install` e as migrations sozinho, e o do
painel roda `npm install`. Isso leva alguns minutos; acompanhe com `docker compose logs -f`.

Com a stack no ar, popule o banco com dados de exemplo (35 pacientes, com CPFs válidos):

```bash
docker compose exec api php artisan db:seed
```

Pronto: abra **http://localhost:5173**.

### Comandos do dia a dia

```bash
docker compose exec api php artisan test              # suíte de testes
docker compose exec api php artisan migrate:fresh --seed   # recria e repopula o banco
docker compose exec api ./vendor/bin/pint --test      # checagem de estilo (Laravel Pint)
docker compose exec mysql mysql -uroot -proot eyecare # console do MySQL
docker compose logs -f api                            # logs da API
docker compose down -v                                # derruba tudo e apaga os volumes
```

### Rodando o painel fora do Docker

Se preferir desenvolver o front na máquina (hot reload mais rápido no Windows):

```bash
docker compose up -d mysql api   # só o back
cd painel && npm install && npm run dev
```

---

## A API

Base: `http://localhost:8000/api`

| Método | Rota | Resposta |
|---|---|---|
| `GET` | `/patients?search=&page=1&per_page=10` | `200` — lista paginada |
| `POST` | `/patients` | `201` · `422` inválido · `409` CPF duplicado |
| `GET` | `/patients/{id}` | `200` · `404` |
| `PUT` | `/patients/{id}` | `200` · `404` · `409` |
| `DELETE` | `/patients/{id}` | `204` (soft delete) · `404` |

`search` casa com **nome do paciente ou CPF** e aceita o CPF mascarado (`529.982.247-25`).




### Formato de erro

Erro de validação (`422`) segue o padrão do Laravel, com as mensagens em português:

```json
{
  "message": "O campo nome completo é obrigatório. (and 5 more errors)",
  "errors": { "full_name": ["O campo nome completo é obrigatório."] }
}
```

Erro de regra de negócio traz o nome da exceção, útil para o cliente tratar sem depender do texto:

```json
{ "error": "CpfAlreadyInUseException", "message": "Este CPF já está cadastrado." }
```

### Postman

Importe [`docs/eyecare.postman_collection.json`](docs/eyecare.postman_collection.json): são 6
requisições e 12 exemplos de resposta salvos, incluindo os casos de erro. Ao rodar "Criar paciente",
o `id` da resposta é guardado na variável de coleção `patient_id` e reaproveitado pelas requisições
seguintes, então dá para executar a coleção inteira de cima para baixo.

---

## Arquitetura

Monólito modular: um módulo por domínio de negócio, quatro camadas por módulo, dependências sempre
apontando para dentro.

```
Presentation → Application → Domain ← Infrastructure
```

```
api/app/
├── Shared/                                  # transversal: não conhece nenhum módulo
│   ├── Domain/Exceptions/DomainException.php
│   ├── Domain/Pagination/PaginatedResult.php
│   └── Infrastructure/Exceptions/DomainExceptionRenderer.php
└── Modules/Patients/
    ├── PatientsServiceProvider.php          # liga contrato → implementação e carrega as rotas
    ├── Domain/
    │   ├── Entities/Patient.php             # o model Eloquent É a entidade
    │   ├── Enums/BiologicalSex.php
    │   ├── ValueObjects/Cpf.php             # valida dígito verificador; imutável
    │   ├── Rules/ValidCpf.php
    │   ├── Repositories/PatientRepository.php   # contrato (interface)
    │   └── Exceptions/
    ├── Application/
    │   ├── DTOs/PatientData.php
    │   └── Services/PatientService.php      # os casos de uso
    ├── Infrastructure/Repositories/EloquentPatientRepository.php
    └── Presentation/
        ├── Controllers/PatientController.php
        ├── Requests/   Resources/   routes.php
```

O painel é organizado **por tipo de arquivo** (camadas), com o código específico de cada tela
colocado junto da própria página:

```
painel/src/
├── pages/patients/   # telas (index/form/detail) + includes/ (tabela, dialog e composables da tela)
├── components/       # layout/ (shell, breadcrumb) e ui/ (reutilizáveis)
├── hooks/            # composables transversais (ex.: use-toast)
├── services/         # http.ts e patients.service.ts — único ponto que conhece as rotas HTTP
├── lib/              # formatters e a regra do CPF
├── types/            # tipos do domínio (patient.ts)
└── styles/           # css global
```

### Regras de dependência

| Camada | Pode | Não pode |
|---|---|---|
| `Domain` | `Shared/Domain` | conhecer HTTP, Eloquent ou as outras camadas |
| `Application` | o **contrato** do repositório | tocar no Eloquent |
| `Infrastructure` | Eloquent, o contrato | conter regra de negócio |
| `Presentation` | o service, os DTOs | acessar repositório ou conter regra |

O `PatientsServiceProvider` é o **único** arquivo que conhece ao mesmo tempo a interface e a
implementação. Trocar a persistência é mexer em `Infrastructure/` e numa linha do provider.

### SOLID, onde exatamente

| Princípio | Onde |
|---|---|
| **S**RP | `PatientService` tem os casos de uso; `EloquentPatientRepository` só persiste; o controller só traduz HTTP |
| **O**CP | Um novo critério de busca entra em `PatientFilters` + repositório, sem tocar no service |
| **L**SP | `InMemoryPatientRepository` (testes) e `EloquentPatientRepository` são intercambiáveis sob o mesmo contrato |
| **I**SP | `PatientRepository` expõe cinco métodos com nome de negócio, não um `findBy(array $where)` genérico |
| **D**IP | `PatientService` depende da interface; quem escolhe a implementação é o provider |

### Decisões que valem explicar

**O model Eloquent é a entidade de domínio.** Separar entidade pura de modelo de persistência
custaria um mapper e a duplicação de cada campo, e o ganho — trocar de ORM — não se paga num CRUD.
Em troca, a entidade **nunca** sai crua pela API: quem decide o que vai para o cliente é o
`PatientResource`. Sem isso, qualquer coluna nova passaria a vazar sem ninguém decidir.

**A validação mora no `FormRequest`, não no DTO.** Em Laravel o idiomático é o `FormRequest`
validar antes do controller e devolver `422` sozinho. O DTO (`PatientData`) ficou como
`readonly class` de transporte, recebendo `Cpf` e `BiologicalSex` já como tipos do domínio.

**Um `FormRequest` e um DTO para cadastro e edição.** Os campos e as regras são idênticos nas duas
telas; dois arquivos seriam duplicação. A unicidade do CPF não é checada por regra de validação e
sim no `PatientService`, que sabe ignorar o próprio paciente e enxergar os excluídos.

**Exceção de domínio não conhece HTTP.** O domínio lança `PatientNotFoundException`; o
`DomainExceptionRenderer` traduz o **nome da classe** em status (404/409/422). Ele guarda o nome como
string justamente para a camada `Shared` não precisar importar nada de dentro de um módulo.


## Stack

**Back-end** — PHP 8.4, Laravel 13, MySQL 8, PHPUnit 12, Laravel Pint.
**Front-end** — Vue 2.7 (Composition API com `<script setup>`), TypeScript, Vite 5, Vuetify 2.7,
vue-router 3, axios.

O Vuetify entra com o CSS pré-compilado, sem tree-shaking: é o caminho de menor risco para combinar
Vuetify 2 com Vite, ao custo de um bundle maior — aceitável para um painel interno.
