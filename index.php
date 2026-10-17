<?php
session_start();

// Gunakan session baru agar daftar tugas lama tidak digunakan
if (!isset($_SESSION['home_tasks'])) {
    $_SESSION['home_tasks'] = [
        ['judul' => 'Bangun tidur saat Subuh', 'selesai' => false],
        ['judul' => 'Melaksanakan sholat 5 waktu', 'selesai' => false],
        ['judul' => 'Membereskan tempat tidur', 'selesai' => false],
        ['judul' => 'Menyapu dan merapikan rumah', 'selesai' => false],
        ['judul' => 'Mencuci piring setelah makan', 'selesai' => false],
        ['judul' => 'Merapikan pakaian', 'selesai' => false],
    ];
}

function aman($teks) {
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

// Memproses tindakan pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($aksi === 'tambah') {
        $judul = trim($_POST['judul'] ?? '');

        if ($judul !== '') {
            $_SESSION['home_tasks'][] = [
                'judul' => $judul,
                'selesai' => false
            ];
        }
    } elseif (
        ($aksi === 'status' || $aksi === 'hapus') &&
        $id !== false && $id !== null &&
        isset($_SESSION['home_tasks'][$id])
    ) {
        if ($aksi === 'status') {
            $_SESSION['home_tasks'][$id]['selesai'] =
                !$_SESSION['home_tasks'][$id]['selesai'];
        } else {
            unset($_SESSION['home_tasks'][$id]);
            $_SESSION['home_tasks'] =
                array_values($_SESSION['home_tasks']);
        }
    }

    header('Location: index.php');
    exit;
}

// Menghitung statistik (dipakai untuk progres)
$tugas = $_SESSION['home_tasks'];
$total = count($tugas);

$selesai = count(array_filter(
    $tugas,
    fn($item) => $item['selesai']
));

$filter = $_GET['filter'] ?? 'semua';

if (!in_array($filter, ['semua', 'proses', 'selesai'], true)) {
    $filter = 'semua';
}

$daftarTugas = array_filter($tugas, function ($item) use ($filter) {
    if ($filter === 'proses') return !$item['selesai'];
    if ($filter === 'selesai') return $item['selesai'];
    return true;
});

$persentase = $total > 0
    ? round(($selesai / $total) * 100)
    : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Daily Tasks</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        :root {
            --pink-bg: #fff4f8;
            --pink-soft: #ffe1ed;
            --pink-line: #fbd5e4;
            --pink-main: #ec5b94;
            --pink-deep: #c93a74;
            --ink: #4a2336;
            --ink-soft: #9a6b80;
        }

        body {
            background: var(--pink-bg);
            font-family: 'Nunito', sans-serif;
            color: var(--ink);
        }

        .container-main {
            max-width: 900px;
        }

        .card-custom {
            background: #fff;
            border: 1px solid var(--pink-line);
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 10px 30px #ec5b9418;
        }

        .card-custom .text-secondary {
            color: var(--ink-soft) !important;
        }

        .form-control {
            border-color: var(--pink-line);
            border-radius: 12px;
            padding: 12px 14px;
            background: #fffafc;
            color: var(--ink);
        }

        .form-control::placeholder {
            color: #c9a1b3;
        }

        .form-control:focus {
            border-color: var(--pink-main);
            box-shadow: 0 0 0 4px #ec5b9422;
            background: #fff;
        }

        .btn-pink {
            background: var(--pink-main);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 22px;
            font-weight: 700;
            white-space: nowrap;
            transition: background .15s;
        }

        .btn-pink:hover {
            background: var(--pink-deep);
            color: #fff;
        }

        .btn-pink:focus-visible,
        .filter-link:focus-visible,
        .btn-delete:focus-visible {
            outline: 3px solid #ec5b9455;
            outline-offset: 2px;
        }

        .filter-link {
            text-decoration: none;
            color: var(--ink-soft);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
        }

        .filter-link:hover {
            background: var(--pink-bg);
            color: var(--pink-deep);
        }

        .filter-link.active {
            background: var(--pink-soft);
            color: var(--pink-deep);
        }

        .progress {
            height: 10px;
            background: var(--pink-soft);
            border-radius: 20px;
        }

        .progress-bar {
            background: linear-gradient(90deg, #f58ab4, var(--pink-main));
            border-radius: 20px;
            transition: width .4s ease;
        }

        .task-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 5px;
            border-bottom: 1px solid #fde8f0;
        }

        .task-row:last-child {
            border-bottom: none;
        }

        .task-check {
            width: 20px;
            height: 20px;
            accent-color: var(--pink-main);
            cursor: pointer;
        }

        .task-name {
            flex: 1;
            overflow-wrap: anywhere;
            font-weight: 600;
        }

        .task-done {
            color: #c4a5b3;
            text-decoration: line-through;
        }

        .badge-status {
            font-size: 11px;
            font-weight: 700;
            border-radius: 20px;
            padding: 6px 11px;
            background: var(--pink-soft);
            color: var(--pink-deep);
        }

        .badge-done {
            background: #e2f6eb;
            color: #28865a;
        }

        .btn-delete {
            background: #fff0f3;
            border: none;
            color: #d9506a;
            border-radius: 9px;
            padding: 6px 11px;
        }

        .btn-delete:hover {
            background: #ffdfe6;
        }

        .empty-state {
            text-align: center;
            padding: 30px 10px;
            color: var(--ink-soft);
        }

        @media (max-width: 576px) {
            .card-custom { padding: 18px; }
            .badge-status { display: none; }
            .filter-link { padding: 7px 10px; }
        }
    </style>
</head>

<body>

<main class="container container-main py-4 py-md-5">

    <!-- Tambah kegiatan -->
    <section class="card-custom mb-4">
        <h2 class="h5 fw-bold">Tambah Kegiatan Baru ✨</h2>
        <p class="text-secondary small">
            Tulis kegiatan rumah atau ibadah yang ingin kamu lakukan.
        </p>

        <form method="POST"
              class="d-flex flex-column flex-sm-row gap-2">
            <input type="hidden" name="aksi" value="tambah">

            <input type="text"
                   name="judul"
                   class="form-control"
                   placeholder="Contoh: Menyiram tanaman..."
                   maxlength="150"
                   required>

            <button type="submit" class="btn-pink">+ Tambah</button>
        </form>
    </section>

    <!-- Daftar kegiatan -->
    <section class="card-custom">
        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Daftar Kegiatan Harian</h2>
                <p class="small text-secondary mb-0">
                    Progres penyelesaian <?= $persentase ?>%
                </p>
            </div>

            <div class="d-flex gap-1">
                <a href="?filter=semua"
                   class="filter-link <?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
                <a href="?filter=proses"
                   class="filter-link <?= $filter === 'proses' ? 'active' : '' ?>">Proses</a>
                <a href="?filter=selesai"
                   class="filter-link <?= $filter === 'selesai' ? 'active' : '' ?>">Selesai</a>
            </div>
        </div>

        <div class="progress mb-3">
            <div class="progress-bar"
                 style="width:<?= $persentase ?>%"></div>
        </div>

        <?php if (empty($daftarTugas)): ?>
            <div class="empty-state">
                <div style="font-size:38px">📝</div>
                <h3 class="h6 fw-bold mt-2">Belum ada kegiatan</h3>
                <p class="small mb-0">Tambahkan kegiatan baru di atas.</p>
            </div>
        <?php else: ?>

            <?php foreach ($daftarTugas as $id => $item): ?>
                <div class="task-row">

                    <form method="POST" class="d-flex">
                        <input type="hidden" name="aksi" value="status">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="checkbox"
                               class="task-check"
                               onchange="this.form.submit()"
                               aria-label="Ubah status kegiatan"
                               <?= $item['selesai'] ? 'checked' : '' ?>>
                    </form>

                    <span class="task-name <?= $item['selesai'] ? 'task-done' : '' ?>">
                        <?= aman($item['judul']) ?>
                    </span>

                    <span class="badge-status <?= $item['selesai'] ? 'badge-done' : '' ?>">
                        <?= $item['selesai'] ? 'Selesai' : 'Proses' ?>
                    </span>

                    <form method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit"
                                class="btn-delete"
                                aria-label="Hapus kegiatan">✕</button>
                    </form>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

</main>
</body>
</html>