<?php
// Configurações do Banco de Dados Local (XAMPP)
$host     = 'localhost';
$dbname   = 'tributos_db';
$user     = 'root';
$password = ''; // No XAMPP padrão, a senha do root é vazia

try {
    // Conexão via PDO com charset UTF-8 ativado
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erros
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna dados como arrays associativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa Prepared Statements reais (proteção contra SQL Injection)
    ]);
} catch (PDOException $e) {
    // Interrompe a execução e exibe o erro se a conexão falhar
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}