# 🏛️ Sistema de Arrecadação Tributária Municipal

Sistema web para gestão tributária municipal, emissão de **Documentos de Arrecadação Municipal (DAM)**, certidões fiscais e controle de contribuintes. Desenvolvido para otimizar os processos da Secretaria Municipal da Fazenda Pública de **Centro do Guilherme - MA**.

---

## 🚀 Funcionalidades

### 👤 Gestão de Contribuintes
* **Cadastro e Edição:** Registro de Pessoas Físicas (PF) e Jurídicas (PJ).
* **Armazenamento de Dados Fiscais:** CPF/CNPJ, RG, Inscrição Municipal e Ramo de Atividade.
* **Ações Rápidas:** Edição e exclusão diretamente no painel principal.

### 🧾 Emissão e Gestão de DAM
* **Cálculo Automatizado:** Aplicação da fórmula `Base de Cálculo × Alíquota (%) = Imposto`.
* **Cálculo Fixo/Manual:** Suporte para tributos de valor fixo ou sem alíquota percentual.
* **Tributos Dinâmicos:** Seleção de tributos cadastrados no banco de dados (ISSQN, IPTU, ITBI, Alvará, Taxa de Expediente, etc.).
* **Controle Financeiro:** Gerenciamento de status do pagamento (`PENDENTE` / `PAGO`), juros, multas e descontos.
* **Layout Oficial:** Inclusão de logo municipal, dados bancários (Banco Bradesco), endereço completo e observações para pagamento.
* **Edição de DAM:** Possibilidade de alterar valores e status de documentos já emitidos sem precisar recadastrar.

### 📜 Emissão de Certidões Fiscais
* **Certidão Negativa de Débitos (CND)**.
* **Certidão Positiva com Efeito de Negativa**.
* **Comprovante de Inscrição Municipal**.
* **Formatação Oficial:** Layout contínuo em texto corrido (sem quebras indevidas), fundamentação legal conforme o Código Tributário Municipal (CTM), código de validação único e brasão do município.

### ⚙️ Gestão de Tributos
* Cadastro dinâmico de novos tributos e taxas municipais com alíquotas padrão pré-definidas.

---

## 🛠️ Tecnologias Utilizadas

* **Linguagem Backend:** PHP 8.x
* **Banco de Dados:** MySQL / MariaDB (com extensão PDO)
* **Frontend:** HTML5, CSS3, JavaScript
* **Framework CSS:** Bootstrap 5.3
* **Servidor Web Recomendado:** Apache (XAMPP / WAMP / Lamp)

---

## 📂 Estrutura do Projeto

```text
/
├── img.jpeg                      # Brasão / Logo oficial do município
├── db.php                        # Conexão PDO com o banco de dados
├── schema.sql                    # Script de criação do banco de dados e tabelas
├── index.php                     # Painel principal (Listagem e controle geral)
├── cadastrar_contribuinte.php    # Formulário de criação e edição de contribuintes
├── excluir_contribuinte.php      # Processamento de exclusão de contribuintes
├── tributos.php                  # Gerenciador de tributos e taxas
├── gerar_dam.php                 # Emissão, edição e impressão de DAM
└── emitir_certidao.php           # Emissão e impressão de certidões municipais
