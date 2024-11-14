<!DOCTYPE html>
<html>
<head>
  <title>PDF Viewer with QR Code Box</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    #pdf-container {
      width: 100%;
      height: calc(100vh - 90px); /* Menyesuaikan tinggi kontainer PDF dengan tinggi toolbar */
      overflow: auto;
      position: relative;
      border: 2px solid #007bff; /* Border berwarna biru */
      background-color: #f8f9fa; /* Warna latar belakang abu-abu terang */
    }
    #pdf-canvas {
      display: block;
      width: 100%;
      height: auto;
    }
    .page-controls {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: white;
      padding: 5px 10px; /* Mengurangi padding */
      box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .page-controls button {
      margin: 0 2px; /* Mengurangi margin antar tombol */
    }
    .page-controls .form-control {
      margin: 0 2px; /* Mengurangi margin pada input */
      width: auto;
    }
    .qr-box {
      border: 2px dashed #007bff; /* Border berwarna biru */
      position: absolute;
      cursor: move;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="page-controls">
      <button id="first-page" class="btn btn-primary btn-sm">First</button>
      <button id="prev-page" class="btn btn-primary btn-sm">Prev</button>
      <div class="d-flex align-items-center">
        <input type="number" class="form-control form-control-sm" id="page-input" placeholder="Page number" min="1">
        <span id="page-number" class="align-self-center"></span>
        <span id="total-pages" class="align-self-center"></span>
      </div>
      <button id="next-page" class="btn btn-primary btn-sm">Next</button>
      <button id="last-page" class="btn btn-primary btn-sm">Last</button>
      <button id="save-btn" class="btn btn-success btn-sm">Save</button>
    </div>
    <div id="pdf-container">
      <canvas id="pdf-canvas"></canvas>
      <!-- QR code box placeholder -->
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    const url = 'contoh_upload.pdf'; // Ganti dengan path ke PDF Anda
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    let pdfDoc = null;
    let pageNum = 1;
    let qrBox = null; // Menyimpan elemen QR box
    let qrBoxData = {}; // Menyimpan data QR box

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
      pdfDoc = pdf;
      updatePageInfo();
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

        // Clear existing QR box
        if (qrBox) {
          qrBox.remove();
          qrBox = null;
        }

        // QR box event listeners
        canvas.addEventListener('click', function(event) {
          const boundingRect = canvas.getBoundingClientRect();
          const x = event.clientX - boundingRect.left;
          const y = event.clientY - boundingRect.top;

          const scaleX = canvas.width / viewport.width;
          const scaleY = canvas.height / viewport.height;
          const pdfX = x / scaleX;
          const pdfY = (canvas.height - y) / scaleY;

          // Create or update QR box
          if (!qrBox) {
            qrBox = document.createElement('div');
            qrBox.className = 'qr-box';
            qrBox.style.width = '100px'; // Default width
            qrBox.style.height = '100px'; // Default height
            qrBox.style.left = `${x}px`;
            qrBox.style.top = `${y}px`;
            document.getElementById('pdf-container').appendChild(qrBox);

            // Add drag and resize functionality
            addDragAndResize(qrBox);
          } else {
            qrBox.style.left = `${x}px`;
            qrBox.style.top = `${y}px`;
          }

          qrBoxData = {
            x: pdfX,
            y: pdfY,
            width: parseFloat(qrBox.style.width),
            height: parseFloat(qrBox.style.height),
            page: pageNum
          };
        });
      });
    }

    function updatePageInfo() {
      document.getElementById('page-input').value = pageNum;
      document.getElementById('total-pages').textContent = `of ${pdfDoc.numPages}`;
    }

    document.getElementById('first-page').addEventListener('click', function() {
      if (pdfDoc) {
        pageNum = 1;
        renderPage(pageNum);
        updatePageInfo();
      }
    });

    document.getElementById('prev-page').addEventListener('click', function() {
      if (pdfDoc && pageNum > 1) {
        pageNum--;
        renderPage(pageNum);
        updatePageInfo();
      }
    });

    document.getElementById('next-page').addEventListener('click', function() {
      if (pdfDoc && pageNum < pdfDoc.numPages) {
        pageNum++;
        renderPage(pageNum);
        updatePageInfo();
      }
    });

    document.getElementById('last-page').addEventListener('click', function() {
      if (pdfDoc) {
        pageNum = pdfDoc.numPages;
        renderPage(pageNum);
        updatePageInfo();
      }
    });

    document.getElementById('page-input').addEventListener('input', function() {
      const pageInput = this.value;
      const pageNumber = parseInt(pageInput, 10);
      if (pageNumber >= 1 && pageNumber <= pdfDoc.numPages) {
        pageNum = pageNumber;
        renderPage(pageNum);
      }
    });

    document.getElementById('save-btn').addEventListener('click', function() {
      if (qrBoxData.page) {
        fetch('save_qr.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ...qrBoxData,
            pdf_width: canvas.width,
            pdf_height: canvas.height
          })
        }).then(response => response.json())
          .then(data => {
            alert(data.message);
          });
      } else {
        alert('Please select a QR code box position.');
      }
    });

    function addDragAndResize(element) {
      // Make element draggable
      let isDragging = false;
      let startX, startY;

      element.addEventListener('mousedown', function(event) {
        isDragging = true;
        startX = event.clientX - parseInt(window.getComputedStyle(element).left, 10);
        startY = event.clientY - parseInt(window.getComputedStyle(element).top, 10);
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
      });

      function onMouseMove(event) {
        if (isDragging) {
          element.style.left = `${event.clientX - startX}px`;
          element.style.top = `${event.clientY - startY}px`;
        }
      }

      function onMouseUp() {
        isDragging = false;
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
      }

      // Make element resizable
      const resizer = document.createElement('div');
      resizer.style.width = '10px';
      resizer.style.height = '10px';
      resizer.style.background = '#007bff';
      resizer.style.position = 'absolute';
      resizer.style.bottom = '0';
      resizer.style.right = '0';
      resizer.style.cursor = 'se-resize';
      element.appendChild(resizer);

      resizer.addEventListener('mousedown', function(event) {
        event.stopPropagation();
        const startWidth = parseFloat(window.getComputedStyle(element).width);
        const startHeight = parseFloat(window.getComputedStyle(element).height);
        const startX = event.clientX;
        const startY = event.clientY;

        function onMouseMove(event) {
          const width = startWidth + (event.clientX - startX);
          const height = startHeight + (event.clientY - startY);
          element.style.width = `${width}px`;
          element.style.height = `${height}px`;
        }

        function onMouseUp() {
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
      });
    }

    window.addEventListener('resize', () => {
      if (pdfDoc) {
        renderPage(pageNum);
      }
    });
  </script>
</body>
</html>
