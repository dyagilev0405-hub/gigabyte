<?php
require __DIR__ . '/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $st = $pdo->prepare('SELECT id, password_hash FROM admins WHERE login = ?');
    $st->execute([$login]);
    $row = $st->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$row['id'];
        header('Location: index.php'); exit;
    }
    $error = 'Неверный логин или пароль';
}
?><!doctype html><meta charset="utf-8"><title>Вход — Гигабайт</title>
<style>body{font-family:system-ui;background:#0B0F0D;color:#E9F2EC;display:grid;place-items:center;height:100vh;margin:0}
form{background:#131C17;border:1px solid #24332B;padding:28px;border-radius:16px;width:320px}
input{width:100%;padding:12px;margin:6px 0 12px;background:#101713;border:1px solid #24332B;border-radius:8px;color:#E9F2EC}
button{width:100%;padding:12px;background:#1AE58A;border:0;border-radius:8px;font-weight:700;cursor:pointer}
.err{color:#FF7A6B;font-size:13px;margin-bottom:10px}</style>
<form method="post">
  <h2>Админка «Гигабайт»</h2>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <input name="login" placeholder="Логин" autocomplete="username" required>
  <input name="password" type="password" placeholder="Пароль" autocomplete="current-password" required>
  <button type="submit">Войти</button>
</form>