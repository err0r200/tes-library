<?php
// ob_start();  // Memulai output buffering
error_reporting(0); // Nonaktifkan semua laporan kesalahan
ini_set('display_errors', 0); // Nonaktifkan tampilan kesalahan
require_once 'vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// $certificate = 'file://'.realpath(dirname(__FILE__)) .'/cert/sertifikat.pem';
// $privateKey = 'file://'.realpath(dirname(__FILE__)) .'\cert\kunci_privat_decrypted.pem';


$certificate = 'cert/certificate.pem';
$privateKey = 'sert/kuncu_privat.pem';
$password = 'bsiuad'; // jika menggunakan password pada file




// Informasi sertifikat
$cert = file_get_contents($certificate);
$privateKey = array(file_get_contents($privateKey), $password);
// $privateKey = file_get_contents($privateKey);





$tempPdfPath = 'output_pdf_with_qr.pdf';

// Create a new TCPDF instance
$pdf = new FPDI();
$pageCount = $pdf->setSourceFile($tempPdfPath);
// $pageCount = $pdf->getNumPages();

// $pdf->SetCreator('Universitas Ahmad Dahlan');
// $pdf->SetAuthor('Biro Sistem Informasi');
// $pdf->SetTitle('Tanda Tangan Elektronik - PDF');
// $pdf->SetSubject('Tanda Tangan Elektroni');
// $pdf->SetKeywords('PDF,UAD,BSI-UAD');

for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    $pdf->AddPage();
    $pdf->useTemplate($pdf->importPage($pageNo));
}

// print_r($pageCount);die;

// Add a signature field (if needed)
try {
    $info = array(
    'Name' => 'Universitas Ahmad Dahlan',
    'Location' => 'Bantul',
    'Reason' => 'Dokumen Informasi',
    'ContactInfo' => 'http://www.uad.ac.id',
    );
	
	$pdf->SetSignature($cert, $privateKey, $password,'',1,$info);
	$pdf->Image('temp_qrcode.png', 180, 60, 15, 15, 'PNG');
    $pdf->setSignatureAppearance(180, 50, 60, 15);
	$pdf->addEmptySignatureAppearance(180, 80, 15, 15);

    // Save the signed PDF
    $signedPdfPath = 'signed_pdf.pdf';

    // Clean the output buffer before sending the PDF
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Output the PDF to the browser
    $pdf->Output($signedPdfPath, 'D'); // 'I' untuk output ke browser

    echo 'PDF signed successfully!';
} catch (Exception $e) {
    echo 'Failed to sign PDF: ',  $e->getMessage(), "\n";
}


// set font
// $pdf->SetFont('Times', '', 12);
// print a line of text
// $text = 'This is a <b color="#FF0000">digitally signed document</b> using the default (example) <b>tcpdf.crt</b> certificate.<br />To validate this signature you have to load the <b color="#006600">tcpdf.fdf</b> on the Arobat Reader to add the certificate to <i>List of Trusted Identities</i>.<br /><br />For more information check the source code of this example and the source code documentation for the <i>setSignature()</i> method.<br /><br /><a href="http://www.tcpdf.org">www.tcpdf.org</a>';
// $pdf->writeHTML($text, true, 0, true, 0);
// define active area for signature appearance

// create content for signature (image and/or text)
// $pdf->Image('temp_qrcode.png', 180, 60, 15, 15, 'PNG');

// define active area for signature appearance
// $pdf->setSignatureAppearance(180, 60, 15, 15);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// *** set an empty signature appearance ***
// $pdf->addEmptySignatureAppearance(180, 80, 15, 15);


// $pdf->Output(realpath(dirname(__FILE__)) .'/signed_pdf.pdf', 'D');

?>
