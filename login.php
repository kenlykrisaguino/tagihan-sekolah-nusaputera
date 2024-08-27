<?php
require_once './config/app.php';

if(isset($_SESSION['level'])){
  returnError();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <!-- Sertakan Bootstrap CSS dari CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" />
</head>

<body class="main-bg">
    <!-- Login Form -->
    <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="card shadow">
            <div class="card-title text-center border-bottom pb-3">
              <h2 class="px-3 pt-3">Login</h2>
            </div>
            <div class="card-body pt-3">
                <form action="auth.php" method="post">
                <div class="mb-4">
                  <label for="username" class="form-label">Virtual Account / Username</label>
                  <input type="text" class="form-control" id="username" name="username"/>
                  <?php if (isset($_SESSION['error_message'])){
                    echo '<small class="text-danger">'.$_SESSION['error_message'].'</small>';
                    unset($_SESSION['error_message']);
                } ?>
                </div>
                <div class="mb-4">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password"/>
                </div>
                <div class="d-grid mt-5">
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
  </body>
</html>
