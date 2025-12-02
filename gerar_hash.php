<?php
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "<h1>Hash para senha 'admin123'</h1>";
echo "<p>Hash gerado: <code>{$hash}</code></p>";

echo "<h2>Verificando...</h2>";
if (password_verify($senha, $hash)) {
    echo "<p style='color:green'>Verificação OK!</p>";
} else {
    echo "<p style='color:red'>Falha na verificação!</p>";
}

echo "<h2>SQL para atualizar no phpMyAdmin:</h2>";
echo "<textarea rows='10' cols='100'>";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'super@sistema.gov.br';\n";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'diretor@sistema.gov.br';\n";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'rh@sistema.gov.br';\n";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'admin@sistema.gov.br';\n";
echo "</textarea>";
