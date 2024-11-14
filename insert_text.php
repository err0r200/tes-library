<?php
require_once('vendor/autoload.php'); // Pastikan ini mengarah ke autoload.php dari FPDI dan FPDF

use setasign\Fpdi\Fpdi;

$data = json_decode(file_get_contents('php://input'), true);
$x = $data['x'];
$y = $data['y'];
$page = $data['page'];
$text = $data['text'];
$pdfWidth = $data['pdf_width'];
$pdfHeight = $data['pdf_height'];

// Path ke file PDF asli
$filePath = 'contoh_upload.pdf';

// Membuat objek FPDI
$pdf = new Fpdi();

// Memuat file PDF yang ada
$pageCount = $pdf->setSourceFile($filePath);

// Memeriksa apakah halaman yang diminta valid
if ($page < 1 || $page > $pageCount) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Halaman tidak valid.']);
    exit();
}

// Mengimport halaman yang ditentukan
$templateId = $pdf->importPage($page);

// Mendapatkan ukuran halaman dari template PDF
$size = $pdf->getTemplateSize($templateId);
$pdfWidthOriginal = $size['width'];
$pdfHeightOriginal = $size['height'];

// Menambahkan halaman baru dengan ukuran yang sama
$pdf->AddPage($pdfWidthOriginal > $pdfHeightOriginal ? 'L' : 'P', [$pdfWidthOriginal, $pdfHeightOriginal]);

// Menggunakan template
$pdf->useTemplate($templateId);

// Mengatur font untuk teks
$pdf->SetFont('Helvetica', '', 7);

// Menyesuaikan koordinat dengan ukuran halaman PDF
$scaleX = $pdfWidthOriginal / $pdfWidth;
$scaleY = $pdfHeightOriginal / $pdfHeight;

// Menyesuaikan koordinat berdasarkan skala
$adjustedX = $x * $scaleX;
$adjustedY = $pdfHeightOriginal - ($y * $scaleY); // Pembalikan Y untuk PDF

// Menyisipkan teks pada posisi yang sudah disesuaikan
$pdf->SetXY($adjustedX, $adjustedY);
$pdf->Write(0, $text);

// Menyimpan PDF baru ke file
$outputPath = 'pdf_with_text.pdf';
$pdf->Output($outputPath, 'F');

// Kirim respon JSON
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Teks berhasil disisipkan ke PDF baru.']);
