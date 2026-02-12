<?php
include 'resource/header.php';
?>

<style>
  main.content {
    display: flex;
    flex-direction: column;
    justify-content: center; /* Vertikal center */
    align-items: center;     /* Horizontal center */
    height: 100vh;           /* Tinggi penuh viewport agar vertikal benar-benar center */
    text-align: center;
    padding: 40px 20px;
  }

  h1 {
    font-size: 96px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #e91e63;
  }

  p {
    font-size: 18px;
    margin-bottom: 30px;
  }

  .btn-home {
    background-color: #e91e63;
    color: white;
    padding: 12px 28px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }

  .btn-home:hover {
    background-color: #b7154a;
  }
</style>

<main class="col-md-9 content">
  <h1>404 Not Found</h1>
  <p>Your visited page not found. You may go home page.</p>
  <a href="index.php" class="btn-home">Back to Homepage</a>
</main>

<?php
include 'resource/footer.php';
?>
