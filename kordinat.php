<style>
	html,body { height: 100%; }
	canvas { width: 100%; height: 100%; }
    #pdf-container {
        position: relative;
        border: 1px solid #ccc;
    }
    #pdf-render {
        width: 100%;
        height: auto;
    }
    #toolbar {
        margin-bottom: 10px;
    }
    .qr-box {
        position: absolute;
        border: 2px dashed #000;
        background-image: url('https://seeklogo.com/images/Q/qr-code-logo-27ADB92152-seeklogo.com.png'); /* URL gambar QR code */
        background-size: cover; /* Menyesuaikan ukuran QR code */
        background-repeat: no-repeat;
        display: none;
    }
    .resize-handle {
        width: 10px;
        height: 10px;
        background: #000;
        position: absolute;
        cursor: nwse-resize;
    }
    .resize-handle.top-left {
        top: 0;
        left: 0;
    }
    .resize-handle.top-right {
        top: 0;
        right: 0;
    }
    .resize-handle.bottom-left {
        bottom: 0;
        left: 0;
    }
    .resize-handle.bottom-right {
        bottom: 0;
        right: 0;
    }
</style>

<div class="container">
    <div id="toolbar" class="text-center">
        <button id="first-page" class="btn btn-primary">First</button>
        <button id="prev-page" class="btn btn-secondary">Prev</button>
        <span>Halaman: <input type="number" id="page-num" min="1" value="1" style="width: 50px;"> / <span id="page-count"></span></span>
        <button id="next-page" class="btn btn-secondary">Next</button>
        <button id="last-page" class="btn btn-primary">Last</button>
    </div>

    <div id="pdf-container">
        <canvas id="pdf-render" width="16" height="16"></canvas>
        <div id="qr-box" class="qr-box">
            <div class="resize-handle top-left"></div>
            <div class="resize-handle top-right"></div>
            <div class="resize-handle bottom-left"></div>
            <div class="resize-handle bottom-right"></div>
        </div>
    </div>
    <button onclick="saveQRCodeData()" class="btn btn-success">Save QR Code Data</button>

    <div id="download-link" style="display: none;">
        <a id="download-url" href="" class="btn btn-info">Download Updated PDF</a>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
<script>
    const url = 'contoh_upload.pdf';  // Ganti dengan path file PDF Anda
    const dokumenId = 'dada'

    let pdfDoc = null, pageNum = 1, pageIsRendering = false, pageNumIsPending = null;
    const scale = 1.5, canvas = document.querySelector('#pdf-render'), ctx = canvas.getContext('2d');
    const maxQrSize = 100;  // Ukuran maksimum QR code
    let currentPage = 1; // Halaman pertama sebagai default
    let totalPages = 0;  // Total halaman PDF
    

    // Fungsi untuk render halaman PDF
    const renderPage = num => {
        pageIsRendering = true;
        pdfDoc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderCtx).promise.then(() => {
                pageIsRendering = false;

                if (pageNumIsPending !== null) {
                    renderPage(pageNumIsPending);
                    pageNumIsPending = null;
                }
            });

            document.querySelector('#page-num').value = num;
            document.querySelector('#page-count').textContent = pdfDoc.numPages;

            // Update current page
            currentPage = num;
        });
    };


    const queueRenderPage = num => {
        if (pageIsRendering) {
            pageNumIsPending = num;
        } else {
            renderPage(num);
        }
    };

    const showPrevPage = () => {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    };

    const showNextPage = () => {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    };

    const showFirstPage = () => {
        pageNum = 1;
        queueRenderPage(pageNum);
    };

    const showLastPage = () => {
        pageNum = pdfDoc.numPages;
        queueRenderPage(pageNum);
    };

    pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
        pdfDoc = pdfDoc_;
        document.querySelector('#page-count').textContent = pdfDoc.numPages;
        renderPage(pageNum);
    });

    // Navigasi toolbar
    document.querySelector('#prev-page').addEventListener('click', showPrevPage);
    document.querySelector('#next-page').addEventListener('click', showNextPage);
    document.querySelector('#first-page').addEventListener('click', showFirstPage);
    document.querySelector('#last-page').addEventListener('click', showLastPage);
    document.querySelector('#page-num').addEventListener('change', (e) => {
        const num = parseInt(e.target.value);
        if (num > 0 && num <= pdfDoc.numPages) {
            pageNum = num;
            queueRenderPage(num);
        }
    });

    // Membuat QR box yang bisa diubah ukuran dengan mouse, dengan batas maksimal dan sesuai mouse
    let qrBox = document.querySelector('#qr-box');
    let isDrawing = false;
    let isMoving = false;
    let startX, startY;

    canvas.addEventListener('mousedown', function(e) {
        if (!isMoving) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            startX = e.clientX - rect.left;
            startY = e.clientY - rect.top;

            // Set posisi awal box dan tampilkan
            qrBox.style.left = `${startX}px`;
            qrBox.style.top = `${startY}px`;
            qrBox.style.width = '0px';
            qrBox.style.height = '0px';
            qrBox.style.display = 'block';
        }
    });

    canvas.addEventListener('mousemove', function(e) {
        if (isDrawing) {
            const rect = canvas.getBoundingClientRect();
            let currentX = e.clientX - rect.left;
            let currentY = e.clientY - rect.top;

            // Hitung lebar dan tinggi box yang sedang ditarik
            let width = currentX - startX;
            let height = currentY - startY;

            // Batasi lebar dan tinggi sesuai ukuran maksimum QR code
            if (Math.abs(width) > maxQrSize) {
                width = Math.sign(width) * maxQrSize;
            }
            if (Math.abs(height) > maxQrSize) {
                height = Math.sign(height) * maxQrSize;
            }

            // Update ukuran dan posisi box
            qrBox.style.width = `${Math.abs(width)}px`;
            qrBox.style.height = `${Math.abs(height)}px`;
            qrBox.style.left = `${Math.min(startX, startX + width)}px`;
            qrBox.style.top = `${Math.min(startY, startY + height)}px`;
        } else if (isMoving) {
            const rect = canvas.getBoundingClientRect();
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            // Update posisi QR box
            const boxRect = qrBox.getBoundingClientRect();
            qrBox.style.left = `${boxRect.left - rect.left + deltaX}px`;
            qrBox.style.top = `${boxRect.top - rect.top + deltaY}px`;

            startX = e.clientX;
            startY = e.clientY;
        }
    });

    canvas.addEventListener('mouseup', function() {
		const bb = canvas.getBoundingClientRect();
    const x = Math.floor( (event.clientX - bb.left) / bb.width * canvas.width );
    const y = Math.floor( (event.clientY - bb.top) / bb.height * canvas.height );
    
    console.log({ x, y });
	
        if (isDrawing) {
            isDrawing = false;

            // Setelah mouse dilepas, tambahkan gambar QR code dan sesuaikan ukurannya
            qrBox.style.cursor = 'move';
        }
        if (isMoving) {
            isMoving = false;
        }
    });

    // Fungsi untuk memindahkan QR box
    qrBox.addEventListener('mousedown', function(e) {
        e.stopPropagation();
        isMoving = true;
        startX = e.clientX;
        startY = e.clientY;
    });

    document.addEventListener('mouseup', function() {
        if (isMoving) {
            isMoving = false;
        }
    });

    document.addEventListener('mousemove', function(e) {
        if (isMoving) {
            const rect = canvas.getBoundingClientRect();
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            // Update posisi QR box
            const boxRect = qrBox.getBoundingClientRect();
            qrBox.style.left = `${boxRect.left - rect.left + deltaX}px`;
            qrBox.style.top = `${boxRect.top - rect.top + deltaY}px`;

            startX = e.clientX;
            startY = e.clientY;
        }
    });


    function saveQRCodeData() {
    const qrBoxes = document.querySelectorAll('#qr-box');
    const data = [];

    qrBoxes.forEach(box => {
        const rect = box.getBoundingClientRect();
        const canvasRect = document.querySelector('#pdf-render').getBoundingClientRect();
        const qrX = rect.left - canvasRect.left;
        const qrY = rect.top - canvasRect.top;

        // Gunakan currentPage untuk menentukan halaman yang benar
        data.push({
            page: currentPage,
            x: qrX,
            y: qrY,
            
            width: rect.width,
            height: rect.height
        });
    });

    const formData = new FormData();
    formData.append('data', JSON.stringify(data));
    formData.append('dokumen', dokumenId);
    // formData.append('inputPdf', inputPdfFile);

    // Kirim data menggunakan AJAX
    $.ajax({
        url: 'tes',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            const result = JSON.parse(response);
            if (result.status === 'success') {
                // Menampilkan link unduhan
                const downloadLink = document.querySelector('#download-link');
                const downloadUrl = document.querySelector('#download-url');
                downloadUrl.href = result.fileName;
                downloadLink.style.display = 'block';
            } else {
                alert('Gagal menyimpan QR code ke PDF: ' + result.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
        }
    });
}


</script>
