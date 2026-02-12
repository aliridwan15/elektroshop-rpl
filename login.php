<?php
session_start();
require_once 'koneksi.php';

$error = "";
$showModal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $login = $_POST['email']; // Email atau no_hp
  $password = md5($_POST['password']); // Hash MD5

  $query = $conn->prepare("SELECT * FROM pengguna WHERE (email = ? OR no_hp = ?) AND password = ?");
  $query->bind_param("sss", $login, $login, $password);
  $query->execute();

  $result = $query->get_result();
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['id'] = $user['id'];
    $_SESSION['nama'] = $user['nama_pengguna'];
    $_SESSION['role'] = $user['role'];
    $showModal = true;
  } else {
    $error = "Email/Nomor HP atau password salah.";
  }
}

include 'resource/header.php';
?>

<style>
  /* Layout login form */
  body {
    font-family: Arial, sans-serif;
    background-color: #ffcce5;
    margin: 0;
    padding: 0;
  }
  .login-container {
    display: flex;
    min-height: 100vh;
    padding: 40px;
    gap: 40px;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
  }
  .login-image img {
    max-width: 800px;
    height: 600px;
    object-fit: contain;
    transform: translateX(-80px);
  }
  .login-form {
    background: #ffcce5;
    padding: 30px;
    border-radius: 12px;
    flex: 1;
    max-width: 400px;
  }
  .login-form h2 {
    margin-bottom: 20px;
    font-size: 28px;
    color: #b71c4a;
  }
  .login-form p {
    margin-bottom: 20px;
    font-size: 14px;
    color: #555;
  }
  .login-form input {
    width: 100%;
    padding: 12px 10px;
    margin-bottom: 15px;
    border: none;
    border-bottom: 2px solid #ccc;
    background: transparent;
    color: #111;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s ease;
  }
  .login-form input:focus {
    border-bottom-color: #e91e63;
  }
  .login-form button {
    width: 100%;
    background: #e91e63;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
  }
  .login-form button:hover {
    background: #c2185b;
  }
  .login-form a {
    color: #b71c4a;
    text-decoration: underline;
    font-size: 14px;
  }
  .alert {
    background-color: #f44336;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 15px;
  }

  /* Modal styling */
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
  }
  .modal-backdrop.show {
    display: flex;
  }
  .modal {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 5px 15px rgba(0,0,0,.3);
    animation: slideDown 0.3s ease forwards;
  }
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    font-weight: bold;
    font-size: 18px;
    color: white;
  }
  .modal-header.success {
    background-color: #4CAF50;
  }
  .modal-header.error {
    background-color: #f44336;
  }
  .modal-body {
    padding: 20px;
    font-size: 16px;
    color: #333;
  }
  .modal-footer {
    padding: 10px 20px 20px 20px;
    text-align: right;
  }
  .btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: background-color 0.3s ease;
  }
  .btn-success {
    background-color: #4CAF50;
    color: white;
  }
  .btn-success:hover {
    background-color: #388E3C;
  }
  .btn-danger {
    background-color: #f44336;
    color: white;
  }
  .btn-danger:hover {
    background-color: #d32f2f;
  }
</style>

<div class="login-container">
  <div class="login-image">
    <img src="img/logologin.png" alt="Login" />
  </div>

  <form class="login-form" method="POST" novalidate>
    <h2>Welcome Back!</h2>
    <p>Enter your credentials to log in</p>

    <?php if (!empty($error) && !$showModal): ?>
      <div class="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <input type="text" name="email" placeholder="Email or Phone Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Log In</button>

    <p style="margin-top:10px;">Don't have an account? <a href="register.php">Create Account</a></p>
  </form>
</div>

<?php include 'resource/footer.php'; ?>

<!-- Modal HTML -->
<?php if ($showModal): ?>
  <div class="modal-backdrop show" id="loginModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="loginModalLabel">
      <div class="modal-header success" id="loginModalLabel">
        Login Successful
      </div>
      <div class="modal-body">
        <?php if ($_SESSION['role'] === 'admin'): ?>
          Welcome, Admin <?= htmlspecialchars($_SESSION['nama']); ?>. Redirecting to admin dashboard...
        <?php else: ?>
          Welcome, <?= htmlspecialchars($_SESSION['nama']); ?>. Redirecting to homepage...
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" id="redirectButton">Continue</button>
      </div>
    </div>
  </div>

  <script>
    const role = <?= json_encode($_SESSION['role']) ?>;
    const redirectURL = role === 'admin' ? 'admin/loginadmin.php' : 'index.php';

    document.getElementById('redirectButton').addEventListener('click', () => {
      window.location.href = redirectURL;
    });

    // Auto redirect after 3 seconds
    setTimeout(() => {
      window.location.href = redirectURL;
    }, 3000);
  </script>
<?php endif; ?>

<?php if (!empty($error) && !$showModal): ?>
  <div class="modal-backdrop show" id="errorModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="errorModalLabel">
      <div class="modal-header error" id="errorModalLabel">
        Login Failed
      </div>
      <div class="modal-body">
        <?= htmlspecialchars($error) ?>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" id="closeErrorBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    const errorModal = document.getElementById('errorModal');
    document.getElementById('closeErrorBtn').addEventListener('click', () => {
      errorModal.classList.remove('show');
    });
  </script>
<?php endif; ?>
