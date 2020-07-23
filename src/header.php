<header class="site-header py-3 my-3 align-top border-bottom">
  <nav class="navbar navbar-expand-lg font-weight-bold navbar-light bg-light">
    <a class="navbar-brand" href="login.php">
      <img class="d-inline-flex" loading="lazy" width="55px" src="../public/img/favicon.png" alt="Logo" />
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggle" aria-controls="navbarToggle" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon border-dark-1"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarToggle">
      <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
        <!-- Navbar Right Side -->
        <?php
        if (isset($_SESSION["login_user"])) {
        ?>

          <?php
          if (isset($_SESSION["admin"])) {
          ?>
            <li class="nav-item active">
              <a class="topRight" href="admin.php">
                <i class="text-primary">Admin</i>
              </a>
            </li>

          <?php
          }
          ?>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <i class="text-primary fa fa-user-circle fa-lg"></i>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="text-primary fa fa-sign-out fa-lg"></i>
            </a>
          </li>
        <?php
        }
        ?>
        <li class="nav-item">
          <a class="topRight" href="#">Policy</a>
        </li>
        <li class="nav-item">
          <a class="topRight" href="contact.php">Contact</a>
        </li>
        <li class="nav-item">
          <a class="topRight" href="terms.php">Terms of use</a>
        </li>
      </ul>
    </div>
  </nav>
</header>