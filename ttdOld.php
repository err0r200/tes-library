<?php
// ob_start();  // Memulai output buffering
require_once 'vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Path to your .p12 certificate file
$certificate = 'cert/test.crt';
$private_key = 'cert/laragon.key';
$key_password = 'bsiuad';  // Password file .p12 jika ada




$tempPdfPath = 'output_pdf_with_qr.pdf';
// Opsi tanda tangan digital
		$info = array(
			'Name' => 'Universitas Ahmad Dahlan',
			'Location' => 'Bantul',
			'Reason' => 'Dokumen Informasi',
			'ContactInfo' => 'Kontak Penandatangan'
		);

// Create a new TCPDF instance
$pdf = new FPDI();
$pdf->AddPage();

$pdf->SetCreator('Universitas Ahmad Dahlan');
$pdf->SetAuthor('Biro Sistem Informasi');
$pdf->SetTitle('Tanda Tangan Elektronik - PDF');
$pdf->SetSubject('Tanda Tangan Elektroni');
$pdf->SetKeywords('PDF,UAD,BSI-UAD');

// echo "<pre>";print_r($pdf);echo "</pre>";die;

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 052', PDF_HEADER_STRING);

$pdf->setSourceFile($tempPdfPath);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx);
$pdf->setSignature(
			$certificate,
			$certificate,
			'',
			'',
			1,  // Signature appearance (1=visible, 2=invisible)
			$info
		);

// ob_end_clean();
// Output the signed PDF

// set font
$pdf->SetFont('helvetica', '', 12);

// add a page
$pdf->AddPage();

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


$pdf->Output('signed_pdf.pdf', 'D');


// echo "<pre>";print_r($pdf);echo "</pre>";die;

// ob_end_clean();  // Mengakhiri dan membersihkan output buffering

// Set document information


// Load the existing PDF



// Set the digital signature

?>
