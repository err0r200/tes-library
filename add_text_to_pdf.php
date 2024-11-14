<?php
require_once('fpdf/fpdf.php');
require_once('fpdi/src/autoload.php');
use setasign\Fpdi\Fpdi;
// Mendapatkan data koordinat dan teks dari request
$x = $_POST['x'];
$y = $_POST['y'];
$text = $_POST['text'];

// Path file PDF yang akan dimodifikasi
$filePath = 'contoh_upload.pdf';
$outputFile = 'pdf_output.pdf';

// Inisialisasi FPDI
$pdf = new FPDI();
$pageCount = $pdf->setSourceFile($filePath);

// Pilih halaman pertama (atau halaman lain sesuai kebutuhan)
$pageId = $pdf->importPage(1);
$size = $pdf->getTemplateSize($pageId);

// Tambahkan halaman
$pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
$pdf->useTemplate($pageId);

// Setel font, ukuran, dan warna teks
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0); // Warna hitam

// Letakkan teks di koordinat yang diterima
$pdf->SetXY($x, $y);
$pdf->Write(8, $text);

// Simpan PDF baru dengan teks tambahan
$pdf->Output('F', $outputFile);

echo 'PDF berhasil disimpan dengan teks tambahan.';
?>
