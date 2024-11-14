<?php
$privateKeyPath = 'sert/private_key.pem';
$certificatePath = 'sertficate.pem';
$p12Path = 'sert/certificate.p12';
$password = 'yourpassword';

// Membuat sertifikat P12
$command = "openssl pkcs12 -export -out $p12Path -inkey $privateKeyPath -in $certificatePath -passout pass:$password";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "Sertifikat P12 berhasil dibuat!";
} else {
    echo "Terjadi kesalahan saat membuat sertifikat P12.";
}
?>
