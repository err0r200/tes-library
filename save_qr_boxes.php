<?php
require_once('vendor/autoload.php'); // Pastikan ini mengarah ke autoload.php dari FPDI dan FPDF

use setasign\Fpdi\Tcpdf\Fpdi;
require('phpqrcode/qrlib.php');



// header('Content-Type: application/json');

// Get JSON input

function addQrCodesToPdf($pdfFilePath, $qrBoxDetails, $pdf_width = '', $pdf_height = '') {
	
	// print_r($qrBoxDetails);die;
	$pdfWidthFrontend = $pdf_width;  // Lebar PDF di frontend (viewport)
	$pdfHeightFrontend = $pdf_height; // Tinggi PDF di frontend (viewport)
	
	// Path untuk menyimpan QR code sementara
    $qrCodeFile = 'temp_qrcode.png';

    // Generate QR code
    QRcode::png('u6JaHD6rP7VG6KiN8z_x8JPeKCAJd-0VNa-ZFSpSJEfN', $qrCodeFile, QR_ECLEVEL_L, 10);
	
    $pdf = new FPDI();
    $pageCount = $pdf->setSourceFile($pdfFilePath);
	
	for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplIdx = $pdf->importPage($pageNo);
		$size = $pdf->getTemplateSize($tplIdx); // Ambil ukuran asli halaman PDF
        
		
		$pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
		
		$pdf->useTemplate($tplIdx);
		
		// Iterasi setiap halaman
		foreach ($qrBoxDetails as $pageDetail) {
            if ((int)$pageDetail['page'] === $pageNo) {
				$scaleX = $size['width'] / $pdfWidthFrontend;
				$scaleY = $size['height'] / $pdfHeightFrontend;
				
                foreach ($pageDetail['qrBoxes'] as $qrBox) {
					 
					
					// print_r($qrBox);die;
                    $x = $qrBox['x'];
                    $y = $qrBox['y'];
                    $width = $qrBox['width'];
                    $height = $qrBox['height'];
					
					$adjustedX = $x * $scaleX;
                    $adjustedY = $y * $scaleY;
					$adjustedWidth = $width * $scaleX;
					$adjustedHeight = $height * $scaleY;
					
                    // Contoh menambahkan QR Code sebagai gambar (ubah ke path QR code yang sebenarnya)
					// $qrImagePath = 'qr_code.png';
                    $pdf->Image($qrCodeFile, $adjustedX, $adjustedY, $adjustedWidth, $adjustedHeight);
                }
            }
        }
	}

    // Tambahkan informasi total QR codes dan halaman di akhir file PDF
    $totalQrCodes = 0;
    foreach ($qrBoxDetails as $pageDetail) {
        $totalQrCodes += count($pageDetail['qrBoxes']);
    }

    $pdf->AddPage();
    $pdf->SetFont('Times', 'B', 16);
    // $pdf->Cell(40, 10, 'Total QR Codes: ' . $totalQrCodes);
    $pdf->Cell(10, 10, 'Dokumen Ini Telah Ditandatangani Secara Digital');
    $pdf->Ln(10);

    // Tampilkan total QR code per halaman
    // foreach ($qrBoxDetails as $pageDetail) {
        // $page = $pageDetail['page'];
        // $qrCount = count($pageDetail['qrBoxes']);
        // $pdf->Cell(10, 10, "Page $page: $qrCount QR Code(s)");
        // $pdf->Ln(10);
    // }	

    // Simpan file PDF baru
    $outputFilePath = 'D:\laragon\www\bsi-devel\uad\tes-library\modified_pdf.pdf';
    $pdf->Output($outputFilePath, 'F');
	
	unlink($qrCodeFile);
	echo json_encode(['message' => 'Dokumen Ini Berhasil Di tandatangani', 'file' => $outputFilePath]);
}



$data = json_decode(file_get_contents('php://input'), true);
$qrBoxDetails = $data['qrBoxDetails'];
$pdf_height = $data['canvasHeight'];
$pdf_width = $data['canvasWidth'];


$pdfFilePath = 'web_bsi.pdf';

addQrCodesToPdf($pdfFilePath, $qrBoxDetails, $pdf_width, $pdf_height);



// if (isset($data['qrBoxes']) && is_array($data['qrBoxes'])) {
    // Data for QR Boxes
    // $qrBoxes = $data['qrBoxes'];

    // Path to your PDF file
    // $pdfFilePath = 'path/to/your/input.pdf';
    // $outputPdfFilePath = 'path/to/your/output.pdf';

    // Initialize FPDF and FPDI
    // require('fpdf.php');
    // require('fpdi.php');

    // $pdf = new FPDI();
    // $pdf->setSourceFile($pdfFilePath);

    // Loop through pages and add QR Boxes
    // foreach ($pdf->pages as $pageNumber => $page) {
        // $pdf->AddPage();
        // $pdf->useTemplate($page);

        // foreach ($qrBoxes as $box) {
            // $x = $box['left'];
            // $y = $box['top'];
            // $w = $box['width'];
            // $h = $box['height'];

            // Add QR Code to PDF (you should implement your own logic to insert the QR code)
            // $pdf->Rect($x, $y, $w, $h);
        // }
    // }

    // $pdf->Output('F', $outputPdfFilePath);

    // echo json_encode(['success' => true]);
// } else {
    // echo json_encode(['success' => false, 'message' => 'Invalid data']);
// }
?>
