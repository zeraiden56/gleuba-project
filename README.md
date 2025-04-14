
# 📊 CDT | Controle e Administração de Glebas

Sistema de gestão territorial que permite visualizar, consultar e comparar dados socioeconômicos dos municípios brasileiros.

> Backend em **Laravel 12**, Frontend em **Angular 17 + TailwindCSS**, com suporte completo a **API REST**, **Docker**, importação de dados, cálculo de indicadores e exibição paginada.

---

## 🧱 Estrutura do Projeto

```bash
gleuba-project/
├── backend/           # API Laravel + comandos de importação + Cálculo de Indicadores
├── frontend/          # Aplicação Angular com visual moderno
└── README.md
```

---

## 🚀 Backend (Laravel + Docker)

### 📦 Requisitos

- Docker + Docker Compose
- PHP 8.3 (caso rode localmente)
- Composer

### ▶️ Subindo com Docker

```bash
cd backend
cp .env.example .env
docker-compose up -d --build
```

### ⚙️ Configuração inicial

```bash
docker exec -it backend-app bash

# Dentro do container
composer install
php artisan migrate
php artisan l5-swagger:generate
```

### 📥 Importar os dados

Execute os comandos abaixo para importar e calcular os dados:

```bash
php artisan import:municipios
php artisan import:admissoes
php artisan import:demissoes
php artisan import:remuneracao
php artisan import:violencia
php artisan import:empresas-abertas
php artisan import:empresas-fechadas
php artisan import:calcular-indice
```

---

## 💻 Frontend (Angular + TailwindCSS)

### ⚙️ Instalação

```bash
cd frontend
npm install
```

### ▶️ Rodando

```bash
npm start
# ou
ng serve
```

Acesse: [http://localhost:4200](http://localhost:4200)

---

## 📡 API Endpoints

| Método | Rota                | Descrição                         |
|--------|---------------------|-----------------------------------|
| GET    | /api/municipios     | Lista todos os municípios         |
| GET    | /api/municipios/{id}| Detalha um município específico  |

> A documentação da API Swagger estará disponível em `/api/documentation`

---

## 📈 Funcionalidades

- Importação de dados por planilha (admissões, demissões, remunerações, violência etc)
- Cálculo automático de indicadores populacionais e econômicos
- Índice de ranqueamento geral
- Dashboard visual para consulta
- Paginação, busca e ordenação

---

## 🌐 Hospedagem (opcional)

Sugestão de produção:

- Subir backend via Docker + Nginx (porta customizada via reverse proxy)
- Frontend pode ser hospedado via GitHub Pages, Vercel ou Nginx

---

## 👨‍💻 Autor

Arthur / [zeraiden56](https://github.com/zeraiden56)

---

## 🧪 Em breve

- Filtros avançados por UF, curva, população
- Painel administrativo
- Exportação de dados CSV
