<header class="site-header py-3 my-3 align-top border-bottom">
  <nav class="navbar font-weight-bold form-inline">
    <a class="navbar-brand mr-auto" href="login.php">
      <img class="img-fluid" loading="lazy" src="../public/img/favicon.png" alt="Logo" width="55px" />
    </a>

    <?php
    if (isset($_SESSION["login_user"])) {
    ?>

      <div class="row">
      <?php
        if (isset($_SESSION["admin"])) {
        ?>

        <a class="topRight" href="admin.php">
          <i class="text-primary">Admin</i>
        </a>

        <?php
        }
        ?>

        <a class="nav-link" href="profile.php">
          <i class="text-primary fa fa-user-circle fa-lg"></i>
        </a>

        <a class="nav-link" href="logout.php">
          <i class="text-primary fa fa-sign-out fa-lg"></i>
        </a>
      </div>

    <?php
    }
    ?>

    <a class="topRight" href="#">Policy</a>
    <a class="topRight" href="contact.php">Contact</a>
    <a class="topRight" href="terms.php">Terms of use</a>
  </nav>
</header>
