<?php
include "resource/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Contact - Electroshop</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: #ffc0e1;
      margin: 0;
      padding: 0;
    }
    .container {
  max-width: 600px;
  margin: 20px auto;
  background-color: white;
  border: 2px solid #0091ff;
  border-radius: 6px;
  padding: 30px;
  color: #222;
  flex-grow: 1; /* container will grow to fill space */
}
    .section {
      margin-bottom: 25px;
    }
    .title {
      font-size: 18px;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #e91e63;
      margin-bottom: 8px;
    }
    .title span.icon {
      font-size: 22px;
      cursor: default;
    }
    .text-small {
      font-size: 14px;
      color: #555;
      margin-bottom: 5px;
    }
    .text-phone, .text-email {
      font-weight: 600;
      color: #e91e63;
      font-size: 15px;
    }
    .divider {
      border-top: 1px solid #ccc;
      margin: 15px 0;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="section call-us">
    <div class="title" title="call to us">
      <span class="icon">📞</span> Call to Us
    </div>
    <div class="text-small">We are available 24/7, 7 days a week.</div>
    <div class="text-phone">Phone: +62 823-3727-1130</div>
  </div>

  <div class="divider"></div>

  <div class="section write-us">
    <div class="title" title="write to us">
      <span class="icon">✉️</span> Write to Us
    </div>
    <div class="text-small">Fill out our form and we will contact you within 24 hours.</div>
    <div class="text-email">Emails: ElectroshopCS@gmail.com</div>
  </div>
</div>

<?php
include "resource/footer.php";
?>
</body>
</html>
