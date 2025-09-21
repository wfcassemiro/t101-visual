<aside class="glass-sidebar collapsed" id="sidebar">
  <nav class="sidebar-nav">
    <ul>
    <?php if (function_exists('isLoggedIn') && !isLoggedIn()): ?>
    <li><a href="/"><i class="fa-solid fa-house"></i><span>Início</span></a></li>
    <?php endif; ?>
    <li><a href="/contato.php"><i class="fa-solid fa-envelope"></i><span>Contato</span></a></li>
    <li><a href="/sobre.php"><i class="fa-solid fa-circle-info"></i><span>Sobre</span></a></li>
    <li><a href="/faq.php"><i class="fa-solid fa-circle-question"></i><span>FAQ</span></a></li>

    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
    <li><a href="/dash-t101/"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a></li>
    <li><a href="/perfil.php"><i class="fa-solid fa-user-circle"></i><span>Perfil</span></a></li>
    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <li><a href="/admin/"><i class="fa-solid fa-crown"></i><span>Admin</span></a></li>
    <?php endif; ?>
    <li><a href="/logout_confirm.php"><i class="fa-solid fa-sign-out-alt"></i><span>Sair</span></a></li>
    <?php else: ?>
    <li><a href="/#:~:text=Escolha%20seu%20plano%20e%20comece%20hoje%20mesmo"><i class="fa-solid fa-briefcase"></i><span>Planos</span></a></li>
    <li><a href="/login.php"><i class="fa-solid fa-key"></i><span>Login</span></a></li>
    <li><a href="/registro.php"><i class="fa-solid fa-pen-to-square"></i><span>Cadastro</span></a></li>
    <?php endif; ?>
    </ul>
  </nav>
</aside>