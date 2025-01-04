<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Ganti dengan halaman login Anda
    exit();
}

// Ambil data pengguna dari sesi
$username = $_SESSION['username'] ?? 'Pengguna';

// Daftar URL RSS feed dengan kategori
$rss_feeds = [
    'Olahraga' => 'https://rss.app/feeds/tCnZ6BIHtPxu4wNk.xml',
    'Teknologi' => 'https://rss.app/feeds/tCuepYOi07xcGbXz.xml',
    'Politik' => 'https://rss.app/feeds/tRPeDgTxDgoXRhot.xml',
    'Ekonomi' => 'https://rss.app/feeds/tyKOEFxMek37BCvS.xml',
    'Otomotif' => 'https://rss.app/feeds/tOJvV7meQsPftnNc.xml',
    'Bisnis' => 'https://rss.app/feeds/tGQt146Mzx2MsfaY.xml',
];

$berita = [];
$selected_categories = [];

if (isset($_GET['kategori'])) {
    $selected_categories = $_GET['kategori'];
    foreach ($selected_categories as $category) {
        $feed = $rss_feeds[$category] ?? null;

        if ($feed) {
            $xml = simplexml_load_file($feed, null, LIBXML_NOCDATA);
            if ($xml) {
                foreach ($xml->channel->item as $item) {
                    // Ambil gambar, jika tersedia di media:content atau enclosure
                    $namespaces = $xml->getNamespaces(true);
                    $gambar = '';

                    if (isset($item->children($namespaces['media'])->content)) {
                        $gambar = $item->children($namespaces['media'])->content->attributes()->url;
                    } elseif (isset($item->enclosure)) {
                        $gambar = $item->enclosure->attributes()->url;
                    }

                    $berita[] = [
                        'judul' => htmlspecialchars((string)$item->title),
                        'konten' => strip_tags((string)$item->description),
                        'link' => htmlspecialchars((string)$item->link),
                        'gambar' => htmlspecialchars((string)$gambar),
                        'tanggal' => htmlspecialchars((string)$item->pubDate),
                    ];
                }
            }
        }
    }

    usort($berita, function ($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregator Berita Multi-Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white p-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="m-0">Agregator Berita</h1>
            <div class="d-flex align-items-center">
                <img src="profile.jpg" alt="Profile" class="rounded-circle me-2" width="40" height="40">
                <span class="fw-bold"><?php echo htmlspecialchars($username); ?></span>
                <form method="POST" action="logout.php" class="ms-3">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="container">
        <!-- Form Pemilihan Kategori -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h2 class="mb-3">Pilih Kategori Berita</h2>
                <form method="GET" action="">
                    <div class="row row-cols-2 row-cols-md-3 g-2">
                        <?php foreach ($rss_feeds as $kategori => $feed): ?>
                            <div class="col">
                            <div class="form-check d-flex align-items-center">
                                    <input 
                                        class="form-check-input me-2" 
                                        type="checkbox" 
                                        name="kategori[]" 
                                        value="<?php echo htmlspecialchars($kategori); ?>" 
                                        <?php echo in_array($kategori, $selected_categories) ? 'checked' : ''; ?>
                                    >
                                    <label class="form-check-label mb-0">
                                        <?php echo htmlspecialchars($kategori); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Tampilkan Berita</button>
                </form>
            </div>
        </div>

        <!-- Menampilkan Berita -->
        <div class="mt-4">
            <h2 class="mb-3">Berita Terbaru</h2>
            <?php if (!empty($berita)): ?>
                <div class="row g-4">
                    <?php foreach ($berita as $item): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <?php if (!empty($item['gambar'])): ?>
                                    <img src="<?php echo $item['gambar']; ?>" class="card-img-top news-image" alt="Gambar Berita">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x200" class="card-img-top news-image" alt="Placeholder Image">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?php echo $item['link']; ?>" target="_blank" class="text-decoration-none text-primary">
                                            <?php echo $item['judul']; ?>
                                        </a>
                                    </h5>
                                    <p class="card-text small">
                                        <?php echo $item['konten']; ?>
                                    </p>
                                </div>
                                <div class="card-footer text-muted small">
                                    <?php echo date('d M Y H:i', strtotime($item['tanggal'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada berita yang tersedia untuk kategori ini.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; <?php echo date("Y"); ?> Agregator Berita</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>