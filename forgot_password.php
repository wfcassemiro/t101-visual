<?php
require_once __DIR__ . '/config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
                ->execute([$user['id'], $token, $expires_at]);

            $reset_link = "http://yourdomain.com/reset_password.php?token=" . urlencode($token);

            // exemplo com mail()
            $subject = "Redefinição de senha - Translators101";
            $body = "Olá! Clique no link para redefinir sua senha: $reset_link\nEsse link expira em 1 hora.";
            mail($email, $subject, $body, "From: suporte@yourdomain.com");

            $message = "Um link de redefinição foi enviado para seu email.";
        } else {
            $message = "Email não encontrado.";
        }
    } else {
        $message = "Preencha seu email.";
    }
}
?>

<?php include __DIR__ . '/vision/includes/head.php'; ?>
<?php include __DIR__ . '/vision/includes/header.php'; ?>

<div class="main-content">
    <div class="video-card" style="max-width: 500px; margin: 0 auto;">
        <h2><i class="fas fa-unlock-alt"></i> Esqueci minha senha</h2>

        <?php if ($message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="vision-form">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Informe seu email
                </label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="cta-btn">
                    <i class="fas fa-paper-plane"></i> Enviar link
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/vision/includes/footer.php'; ?>