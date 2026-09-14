Teste de Conexao";

try {
    $host = 'sql202.infinityfree.com';
    $dbname = 'if0_42842841_tributos';
    $user = 'if0_42842841';
    $pass = 'e2Y6tcXDeKdIfC';
    $charset = 'utf8mb4';

    \(dsn = "mysql:host=" .\)host . ";dbname=" . \(dbname . ";charset=" .\)charset;

    \(pdo = new PDO(\)dsn, \(user,\)pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    echo " 
    getMessage();
}