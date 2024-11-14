<!DOCTYPE html>
<html>
<head>
  <title>PDF Viewer with Accurate Coordinates</title>
  <style>
    #pdf-container {
      width: 100%;
      height: 100vh;
      overflow: auto;
      position: relative;
    }
    #pdf-canvas {
      display: block;
      width: 100%;
      height: auto;
    }
  </style>
</head>
<body>
  <div id="pdf-container">
    <canvas id="pdf-canvas"></canvas>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.js"></script>
  <script>
    const url = 'contoh_upload.pdf'; // Ganti dengan path ke PDF Anda
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    let pdfDoc = null;
    let pageNum = 1;

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
      pdfDoc = pdf;
      renderPage(pageNum);
    });

    function renderPage(num) {
      pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: 1 });
        const canvasWidth = canvas.clientWidth;
        const canvasHeight = (viewport.height / viewport.width) * canvasWidth;

        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        const renderContext = {
          canvasContext: ctx,
          viewport: page.getViewport({ scale: canvasWidth / viewport.width })
        };
        page.render(renderContext);

        // Event listener untuk klik pada canvas
        canvas.addEventListener('click', function(event) {
          const boundingRect = canvas.getBoundingClientRect();
          const x = event.clientX - boundingRect.left;
          const y = event.clientY - boundingRect.top;

          // Menghitung koordinat PDF
          const scaleX = canvas.width / viewport.width;
          const scaleY = canvas.height / viewport.height;
          const pdfX = x / scaleX;
          const pdfY = (canvas.height - y) / scaleY;

          // Mengirim data ke server
          fetch('insert_text.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              x: pdfX,
              y: pdfY,
              page: pageNum,
              pdf_width: viewport.width,
              pdf_height: viewport.height,
              text: 'Dokumen ini ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan BSI UAD'
            })
          }).then(response => response.json())
            .then(data => {
              console.log(data);
            });
        });
      });
    }

    // Event listener untuk mengatur ulang ukuran PDF saat jendela diubah ukurannya
    window.addEventListener('resize', () => {
      if (pdfDoc) {
        renderPage(pageNum);
      }
    });
  </script>
</body>
</html>
