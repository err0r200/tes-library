<?php

require_once('fpdf/fpdf.php');
require_once('fpdi/src/autoload.php');

// initiate FPDI
$pdf = new \setasign\Fpdi\Fpdi();
// add a page
$pdf->AddPage();
// set the source file
$pdf->setSourceFile('contoh_upload.pdf');
// import page 1
$tplIdx = $pdf->importPage(1);
// use the imported page and place it at position 10,10 with a width of 100 mm
$pdf->useTemplate($tplIdx);

// $pdf->SetX(358.01);
// $pdf->Image('https://seeklogo.com/images/Q/qr-code-logo-27ADB92152-seeklogo.com.png', 60, 30, 90, 0, 'PNG');

// now write some text above the imported page
$pdf->SetFont('Helvetica');
// $pdf->SetTextColor(255, 0, 0);
$pdf->Text(99, 50, 'Hallo');
// $pdf->SetXY(30, 30);
// $pdf->Write(0, 'This is just a simple text');

$pdf->Output('I', 'generated.pdf');
