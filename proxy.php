<?php
// Pastikan URL diberikan melalui parameter GET
if (isset($_GET['url'])) {
    // Ambil URL yang diberikan langsung dari parameter query
    $imageUrl = $_GET['url'];

    // Dekode URL untuk memastikan karakter seperti spasi, tanda kurung, dll., ditangani dengan benar
    $decodedUrl = urldecode($imageUrl);

    // Validasi apakah URL yang diberikan benar
    if (filter_var($decodedUrl, FILTER_VALIDATE_URL) === false) {
        echo 'URL tidak valid!';
        exit;
    }

    // Validasi host agar hanya URL dari domain tertentu yang diproses
    $parsedUrl = parse_url($decodedUrl);
    if ($parsedUrl['host'] !== 'owner.kostku.web.id') {
        echo 'Akses ditolak. Hanya URL dari owner.kostku.web.id yang diperbolehkan.';
        exit;
    }

    // Inisialisasi cURL untuk mengambil gambar
    $ch = curl_init($decodedUrl);
    
    // Set opsi cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengambil respons sebagai string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect jika ada
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Nonaktifkan verifikasi SSL (jika diperlukan)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Nonaktifkan verifikasi SSL (jika diperlukan)
    
    // Eksekusi cURL untuk mengambil gambar
    $imageContent = curl_exec($ch);

    // Cek apakah ada kesalahan saat mengambil gambar
    if (curl_errno($ch)) {
        echo "cURL error: " . curl_error($ch);  // Menampilkan error cURL
        exit;
    }

    // Dapatkan kode HTTP respons
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        echo "HTTP error code: " . $httpCode;  // Menampilkan error HTTP
        exit;
    }

    // Tutup sesi cURL
    curl_close($ch);

    // Jika gambar berhasil diambil
    if ($imageContent !== false) {
        // Tentukan header yang tepat untuk tipe konten gambar
        $fileInfo = pathinfo($decodedUrl);
        $extension = strtolower($fileInfo['extension']);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }

        // Output gambar ke browser
        echo $imageContent;
    } else {
        echo 'Gambar tidak ditemukan atau gagal mengambil gambar.';
    }
} else {
    echo 'URL tidak diberikan.';
}
?>
