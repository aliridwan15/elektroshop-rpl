<?php
include 'koneksi.php';
include "resource/header.php";

// Get the 'jenis' (category) currently being viewed
$currentJenis = isset($_GET['jenis']) ? $_GET['jenis'] : null;

// Ambil daftar semua jenis unik dari tabel FAQ untuk sidebar
$jenisList = [];
$jenisResult = $conn->query("SELECT DISTINCT jenis FROM faq ORDER BY jenis ASC");
while ($row = $jenisResult->fetch_assoc()) {
    $jenisList[] = $row['jenis'];
}

// Determine the default 'jenis' if none is selected
if (!$currentJenis && !empty($jenisList)) {
    $currentJenis = $jenisList[0]; // Set the first jenis as default
}

// Ambil daftar FAQ sesuai dengan 'jenis' yang aktif
$faqsForCurrentJenis = [];
if ($currentJenis) {
    $faqStmt = $conn->prepare("SELECT id, pertanyaan, jawaban FROM faq WHERE jenis = ? ORDER BY id ASC");
    $faqStmt->bind_param('s', $currentJenis);
    $faqStmt->execute();
    $faqResult = $faqStmt->get_result();
    while ($row = $faqResult->fetch_assoc()) {
        $faqsForCurrentJenis[] = $row;
    }
    $faqStmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQ - Electroshop</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffc0e1;
            margin: 0;
            padding:0;
        }

        .container {
            display: flex;
            max-width: 1100px;
            margin: 10px auto;
            gap: 20px;
            padding: 0 10px;
        }

        .sidebar {
            width: 30%;
            min-width: 220px;
            background-color: white;
            border-radius: 6px;
            padding: 20px;
            height: fit-content;
        }

        /* Styling for the category (jenis) links in the sidebar */
        .sidebar a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            color: #333;
            text-decoration: none;
            font-size: 15px;
            border-left: 3px solid transparent;
            padding: 8px 10px;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: normal; /* Default font weight */
        }

        .sidebar a:hover {
            background-color: #f9e4f0;
        }

        .sidebar a.active {
            color: #e91e63;
            font-weight: bold; /* Active category bold */
            border-left: 3px solid #e91e63;
            background-color: #fdf1f7;
        }

        /* Styling for the arrow icon next to category */
        .sidebar a span.arrow {
            margin-left: 10px;
            font-weight: bold;
            color: #ccc;
        }

        .sidebar a.active span.arrow {
            color: #e91e63;
        }

        .content {
            flex: 1;
            background-color: white;
            border: 2px solid #0091ff; /* Blue border matching image */
            border-radius: 6px;
            padding: 25px 30px;
            min-height: 300px;
        }

        .faq-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        /* Styling for individual questions/answers within the content area */
        .content .faq-item {
            margin-bottom: 25px; /* Space between FAQ items */
        }

        .content .faq-question {
            display: block;
            font-size: 16px;
            margin-top: 0; /* Remove top margin, handled by faq-item margin-bottom */
            color: #222;
            font-weight: bold; /* Make question bold */
            padding-bottom: 5px; /* Little space before answer */
        }

        .content .faq-answer {
            font-size: 14px;
            color: #e91e63;
            margin: 0; /* Reset margins */
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <?php foreach ($jenisList as $jenis):
            $activeClass = ($currentJenis === $jenis) ? 'active' : '';
            // Display 'jenis' nicely (e.g., 'checkout' -> 'Checkout')
            $displayJenis = htmlspecialchars(ucwords(str_replace('-', ' ', $jenis)));
            ?>
            <a href="faq.php?jenis=<?= urlencode($jenis) ?>" class="<?= $activeClass ?>">
                <?= $displayJenis ?>
                <span class="arrow">&gt;</span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="content">
        <div class="faq-title">Frequently Asked Questions</div>
        <?php if (!empty($faqsForCurrentJenis)): ?>
            <?php foreach ($faqsForCurrentJenis as $faq): ?>
                <div class="faq-item">
                    <span class="faq-question"><?= htmlspecialchars($faq['pertanyaan']) ?>?</span>
                    <p class="faq-answer"><?= nl2br(htmlspecialchars($faq['jawaban'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No FAQs found for this category.</p>
        <?php endif; ?>
    </div>
</div>

<?php include "resource/footer.php"; ?>
</body>
</html>