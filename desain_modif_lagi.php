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
						<button id="saveQRCode" class="btn btn-success">Simpan QR Code</button> <!-- Tombol Simpan -->
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
	

	
	// Fungsi untuk mengirim data ke backend
	function sendDataToBackend(qrData) {
		$.ajax({
			url: 'path/to/your/backend/endpoint', // Ganti dengan endpoint backend Anda
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify(qrData),
			success: function(response) {
				console.log('Data berhasil dikirim:', response);
			},
			error: function(xhr, status, error) {
				console.error('Terjadi kesalahan saat mengirim data:', error);
			}
		});
	}

    // Fungsi untuk menampilkan QR Code di canvas
    function displayQRCodeInCanvas(qrText, qrBox) {
        const qrCodeImage = new Image();
        qrCodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(qrText)}&size=100x100`;
        qrCodeImage.onload = function() {
            const qrCodeDiv = document.createElement('div');
            qrCodeDiv.className = 'qr-code';
            qrCodeDiv.style.width = '100px'; // Ukuran awal
            qrCodeDiv.style.height = '100px'; // Ukuran awal
            qrCodeDiv.style.left = '50px'; // Posisi awal di canvas
            qrCodeDiv.style.top = '50px'; // Posisi awal di canvas
            qrCodeDiv.style.backgroundImage = `url(${qrCodeImage.src})`;
            qrCodeDiv.style.backgroundSize = 'contain';
            qrCodeDiv.style.backgroundRepeat = 'no-repeat';

            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = '×';
            deleteButton.className = 'delete-button';
            deleteButton.onclick = function(e) {
                e.stopPropagation(); // Mencegah event click pada div parent
                qrCodeDiv.remove(); // Menghapus QR Code
                qrBox.classList.remove('disabled'); // Mengaktifkan kembali qr-box
            };

            qrCodeDiv.appendChild(deleteButton);
            canvas.parentNode.appendChild(qrCodeDiv); // Menambahkan div QR Code ke canvas

            // Menonaktifkan qr-box
            qrBox.classList.add('disabled');

            // Menggunakan Interact.js untuk drag dan resize
            interact(qrCodeDiv)
                .draggable({
                    // Mengizinkan drag
                    onmove: dragMoveListener
                })
                .resizable({
                    // Mengizinkan resize
                    edges: { left: true, right: true, bottom: true, top: true },
                    modifiers: [
                        interact.modifiers.restrictSize({
                            min: { width: 50, height: 50 }, // Ukuran minimum
                            max: { width: 150, height: 150 } // Ukuran maksimum
                        })
                    ],
                    inertia: true
                })
                .on('resizemove', function(event) {
                    const target = event.target;
                    const newWidth = event.rect.width;
                    const newHeight = event.rect.height;

                    // Mengatur ukuran
                    target.style.width = newWidth + 'px';
                    target.style.height = newHeight + 'px';

                    // Mengatur posisi
                    target.style.left = (parseFloat(target.style.left) + event.deltaRect.left) + 'px';
                    target.style.top = (parseFloat(target.style.top) + event.deltaRect.top) + 'px';
                });
        };
    }

    function dragMoveListener(event) {
        const target = event.target;
        // Menentukan posisi baru
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        // Menerapkan posisi baru
        target.style.transform = 'translate(' + x + 'px,' + y + 'px)';
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    // Event listener untuk klik pada qr-box
    $('.qr-box').on('click', function() {
        const qrText = $(this).data('qr-text'); // Mengambil data QR
        displayQRCodeInCanvas(qrText, this);
    });

    // Muat PDF saat halaman dimuat
    loadPDF();
</script>
</body>
</html>
