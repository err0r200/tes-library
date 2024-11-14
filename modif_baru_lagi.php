<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code dan PDF Canvas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .qr-box {
            border: 2px dashed #ccc; /* Border dashed untuk box QR */
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            text-align: center;
            position: relative; /* Tambahkan untuk penempatan tombol */
        }
        .qr-box.disabled {
            background-color: #f8d7da; /* Warna latar belakang untuk disabled */
            cursor: not-allowed; /* Menunjukkan tidak bisa diklik */
        }
        #pdfContainer {
            width: 100%;
            height: 100vh; /* Mengatur tinggi penuh viewport */
            overflow: auto; /* Menambahkan scrollbar jika diperlukan */
            position: relative; /* Memungkinkan posisi absolut di dalamnya */
        }
		
        #pdfCanvas {
            border: 1px solid #000;
            width: 100%;
        }
        .qr-code {
            position: absolute;
            cursor: move;
            border: 2px dashed red; /* Border dashed merah untuk QR Code */
            padding: 5px;
            background-color: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
        .delete-button {
            position: absolute;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 14px; /* Ukuran font untuk tombol */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0; /* Hilangkan padding untuk pusatkan isi */
            top: -10px; /* Posisi tombol di atas kotak QR Code */
            right: -10px; /* Posisi tombol di kanan kotak QR Code */
        }
        .toolbar {
            margin-bottom: 10px;
            display: flex;
            justify-content: center; /* Menempatkan toolbar di tengah */
            background-color: #f8f9fa; /* Background untuk toolbar */
            padding: 10px; /* Padding untuk ruang dalam toolbar */
            border-radius: 5px; /* Sudut melengkung */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Bayangan halus */
        }
        .toolbar button {
            margin: 0 5px; /* Jarak horizontal antara tombol */
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-2">
            <div class="card" id="qrBoxContainer">
                <div class="card-body">
                    <h5 class="card-title">QR Code Box</h5>
                    <div class="qr-box" data-qr-text="QR Code 1">QR Code 1</div>
                    <div class="qr-box" data-qr-text="QR Code 2">QR Code 2</div>
                    <div class="qr-box" data-qr-text="QR Code 3">QR Code 3</div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="card" id="pdfCanvasContainer">
                <div class="card-body">
                    <h5 class="card-title">PDF Canvas</h5>
                    <div class="toolbar">
                        <button id="firstPage" class="btn btn-primary">First</button>
                        <button id="prevPage" class="btn btn-primary">Prev</button>
                        <input type="number" id="pageInput" value="1" min="1" style="width: 50px;">
                        / <span id="totalPages">0</span>
                        <button id="nextPage" class="btn btn-primary">Next</button>
                        <button id="lastPage" class="btn btn-primary">Last</button>
						<button id="saveData" class="btn btn-success">Simpan QR Code</button> <!-- Tombol Simpan -->
                    </div>
                    <div id="pdfContainer">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

<script>
    const pdfUrl = 'web_bsi.pdf'; // Ganti dengan path PDF Anda
    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
	
	window.addEventListener('load', () => {
        localStorage.removeItem('qrCodes');
    });

    let pdfDocument;
    let currentPage = 1;

    // Fungsi untuk memuat dan merender PDF
    function loadPDF() {
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        loadingTask.promise.then(function(pdf) {
            pdfDocument = pdf;
            document.getElementById('totalPages').textContent = pdf.numPages; // Menampilkan total halaman
            renderPage(currentPage); // Render halaman pertama
        });
    }

    function renderPage(pageNumber) {
        pdfDocument.getPage(pageNumber).then(function(page) {
            const viewport = page.getViewport({ scale: 1 });
            canvas.height = viewport.height; // Mengatur tinggi sesuai viewport
            canvas.width = viewport.width; // Mengatur lebar sesuai viewport

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            page.render(renderContext);
        });
    }

    // Navigasi Halaman
    document.getElementById('firstPage').addEventListener('click', function() {
        currentPage = 1;
        renderPage(currentPage);
        document.getElementById('pageInput').value = currentPage;
    });

    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
            document.getElementById('pageInput').value = currentPage;
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage < pdfDocument.numPages) {
            currentPage++;
            renderPage(currentPage);
            document.getElementById('pageInput').value = currentPage;
        }
    });

    document.getElementById('lastPage').addEventListener('click', function() {
        currentPage = pdfDocument.numPages;
        renderPage(currentPage);
        document.getElementById('pageInput').value = currentPage;
    });

    document.getElementById('pageInput').addEventListener('change', function() {
        const pageInputValue = parseInt(this.value);
        if (pageInputValue >= 1 && pageInputValue <= pdfDocument.numPages) {
            currentPage = pageInputValue;
            renderPage(currentPage);
        }
    });
	
    // Array untuk menyimpan data QR Code
    const qrCodesData = [];
	
    // Fungsi untuk menyimpan QR Code
    $('#saveData').on('click', function() {
        // Mengambil data QR code dari localStorage
        const qrCodes = JSON.parse(localStorage.getItem('qrCodes')) || [];

        // Siapkan data yang akan dikirim
        const qrCodeData = qrCodes.map(qr => {
            return {
                qrcode: qr.qrcode || "",  // Menggunakan || untuk menangani undefined
                page: qr.page || "",       // Menggunakan || untuk menangani undefined
                x: qr.x || "",
                y: qr.y || "",
                width: qr.width || "",
                height: qr.height || ""
            };
        });

        // Mengirim data ke backend
        fetch('/path/to/your/backend/endpoint', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(qrCodeData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Data saved successfully:', data);
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
    });

    // Fungsi untuk menampilkan QR Code di canvas
    function displayQRCodeInCanvas(qrText, qrBox) {
    const qrCodeImage = new Image();
    qrCodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(qrText)}&size=100x100`;
    qrCodeImage.onload = function() {
        const boxRect = qrBox.getBoundingClientRect();
        const x = boxRect.left - canvas.getBoundingClientRect().left;
        const y = boxRect.top - canvas.getBoundingClientRect().top;

        const qrCode = document.createElement('div');
        qrCode.classList.add('qr-code');
        qrCode.style.position = 'absolute'; // Pastikan posisi absolut
        qrCode.style.left = `${x}px`;
        qrCode.style.top = `${y}px`;
        qrCode.style.width = '100px'; // Ukuran default
        qrCode.style.height = '100px'; // Ukuran default

        const deleteButton = document.createElement('button');
        deleteButton.classList.add('delete-button');
        deleteButton.innerHTML = 'X';
        qrCode.appendChild(deleteButton);
        qrCode.appendChild(qrCodeImage);

        canvas.parentNode.appendChild(qrCode); // Tambahkan ke DOM

        // Menambahkan event listener untuk menggerakkan QR Code
        interact(qrCode)
            .draggable({
                onmove: dragMoveListener
            })
            .resizable({
                edges: { left: true, right: true, bottom: true, top: true },
                onmove: function(event) {
                    let target = event.target;
                    let x = (parseFloat(target.getAttribute('data-x')) || 0);
                    let y = (parseFloat(target.getAttribute('data-y')) || 0);

                    // Update ukuran QR Code
                    target.style.width = `${event.rect.width}px`;
                    target.style.height = `${event.rect.height}px`;

                    // Reposition to maintain the origin
                    x += event.deltaRect.left;
                    y += event.deltaRect.top;

                    target.style.transform = `translate(${x}px, ${y}px)`;
                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);
                }
            });

        // Menangani penghapusan QR Code
        deleteButton.addEventListener('click', function() {
            qrCode.remove(); // Hapus dari DOM
            removeQRCodeFromLocalStorage(qrText); // Hapus dari localStorage
        });

        // Simpan QR code ke localStorage
        qrCodesData.push({ qrcode: qrText, page: currentPage, x: x, y: y, width: 100, height: 100 });
        localStorage.setItem('qrCodes', JSON.stringify(qrCodesData));
    };
    
    // Tangani kesalahan ketika gambar tidak bisa dimuat
    qrCodeImage.onerror = function() {
        console.error("Gambar QR Code gagal dimuat.");
    };
}

    // Menghapus QR Code dari localStorage
    function removeQRCodeFromLocalStorage(qrText) {
        const qrCodes = JSON.parse(localStorage.getItem('qrCodes')) || [];
        const filteredCodes = qrCodes.filter(qr => qr.qrcode !== qrText);
        localStorage.setItem('qrCodes', JSON.stringify(filteredCodes));
    }

    // Menangani klik pada kotak QR
    $('.qr-box').on('click', function() {
        const qrText = $(this).data('qr-text');
        displayQRCodeInCanvas(qrText, this);
        $(this).addClass('disabled'); // Tambahkan kelas disabled
        $(this).off('click'); // Nonaktifkan klik setelah dipilih
    });

    loadPDF(); // Memuat PDF saat halaman dimuat
</script>

</body>
</html>
