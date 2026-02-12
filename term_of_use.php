<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Use - Electroshop</title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #e91e63;
        }
        ul {
            list-style: disc;
            margin-left: 20px;
        }
        p {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'resource/header.php'; // Assuming you have a header file ?>

    <div class="container">
        <h1>Terms of Use</h1>
        <p>Welcome to Electroshop! These Terms of Use govern your access to and use of the Electroshop website, products, and services (collectively, the "Services"). By accessing or using our Services, you agree to be bound by these Terms of Use and our Privacy Policy.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By using Electroshop, you agree to comply with and be legally bound by these Terms. If you do not agree to these Terms, you may not access or use our Services.</p>

        <h2>2. Changes to Terms</h2>
        <p>We reserve the right to modify these Terms at any time. We will notify you of any changes by posting the new Terms on this page. Your continued use of the Services after any such changes constitutes your acceptance of the new Terms.</p>

        <h2>3. Use of the Services</h2>
        <ul>
            <li>You must be at least 18 years old to use our Services.</li>
            <li>You agree to use the Services only for lawful purposes and in accordance with these Terms.</li>
            <li>You are responsible for maintaining the confidentiality of your account and password and for restricting access to your computer.</li>
            <li>You agree not to reproduce, duplicate, copy, sell, resell, or exploit any portion of the Services without express written permission from us.</li>
        </ul>

        <h2>4. Product Information and Pricing</h2>
        <p>We strive to provide accurate product descriptions and pricing information. However, we do not warrant that product descriptions or other content of this site are accurate, complete, reliable, current, or error-free. Prices are subject to change without notice.</p>

        <h2>5. Orders and Payments</h2>
        <p>All orders placed through the Services are subject to our acceptance. We may refuse or cancel any order for any reason, including limitations on quantities available for purchase, inaccuracies in product or pricing information, or problems identified by our credit and fraud avoidance department. Payment must be received by us prior to our acceptance of an order.</p>

        <h2>6. Intellectual Property</h2>
        <p>All content on this site, such as text, graphics, logos, button icons, images, audio clips, digital downloads, data compilations, and software, is the property of Electroshop or its content suppliers and protected by international copyright laws.</p>

        <h2>7. Limitation of Liability</h2>
        <p>In no event shall Electroshop, its directors, employees, or agents be liable for any direct, indirect, incidental, special, punitive, or consequential damages whatsoever resulting from your use of the Services.</p>

        <h2>8. Governing Law</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of Indonesia, without regard to its conflict of law principles.</p>

        <h2>9. Contact Information</h2>
        <p>If you have any questions about these Terms of Use, please contact us at ElectroshopCS@gmail.com.</p>
    </div>

    <?php include 'resource/footer.php'; // Assuming you have a footer file ?>
</body>
</html>