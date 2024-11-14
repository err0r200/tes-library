<!DOCTYPE html>
<html>
<head>
  <title>PDF Viewer with QR Code</title>
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
      border: 2px dashed #007bff;
      position: absolute;
      cursor: move;
      box-sizing: border-box;
      display: none; /* Awalnya tidak ditampilkan */
      z-index: 999; /* Pastikan QR box berada di atas canvas */
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
      <button id="save-qr" class="btn btn-success btn-sm">Save QR Code</button>
    </div>
    <div id="pdf-container">
      <canvas id="pdf-canvas"></canvas>
      <!-- QR Box Element -->
      <div id="qr-box" class="qr-box"></div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/interactjs@1.9.22/dist/interact.min.js"></script>
  <script>
    const url = 'web_bsi.pdf'; // Ganti dengan path ke PDF Anda
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    let pdfDoc = null;
    let pageNum = 1;
    let qrBox = document.getElementById('qr-box');
    let isBoxActive = false;

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

        // Menampilkan QR box di tengah-tengah canvas
        qrBox.style.width = '100px';  // Default width, bisa diubah
        qrBox.style.height = '100px'; // Default height, bisa diubah
        qrBox.style.left = `${(canvasWidth - 100) / 2}px`;
        qrBox.style.top = `${(canvasHeight - 100) / 2}px`;
        qrBox.style.display = 'block';
        
        // Event listener untuk klik pada canvas
        canvas.addEventListener('click', function(event) {
          if (isBoxActive) {
            const boundingRect = canvas.getBoundingClientRect();
            const x = event.clientX - boundingRect.left;
            const y = event.clientY - boundingRect.top;
            qrBox.style.left = `${x - qrBox.offsetWidth / 2}px`;
            qrBox.style.top = `${y - qrBox.offsetHeight / 2}px`;
          }
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

    document.getElementById('save-qr').addEventListener('click', function() {
		if (qrBox.style.display === 'block') {
			const boundingRect = qrBox.getBoundingClientRect();
			const canvasRect = canvas.getBoundingClientRect();
			const x = (boundingRect.left - canvasRect.left) * (canvas.width / canvasRect.width);
			const y = (boundingRect.top - canvasRect.top) * (canvas.height / canvasRect.height);
			const width = qrBox.offsetWidth * (canvas.width / canvasRect.width);
			const height = qrBox.offsetHeight * (canvas.height / canvasRect.height);
			

			// Kirim data ke server
			fetch('save.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					x: x,
					y: y,
					width: width,
					height: height,
					page: pageNum,
					pdf_width: canvas.width,
					pdf_height: canvas.height,
					text: 'Upr6ot7FxpJI_bAtju9akHn_Rzci-HpCIF2qqaHCw0pk' // Ganti dengan teks yang diinginkan
				})
			}).then(response => response.json())
			  .then(data => {
				console.log(data);
			  })
			  .catch(error => {
				console.error('Error:', error);
			  });
		}
	});

    interact('#qr-box')
      .draggable({
        listeners: {
          move(event) {
            const target = event.target;
            const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
            target.style.transform = `translate(${x}px, ${y}px)`;
            target.setAttribute('data-x', x);
            target.setAttribute('data-y', y);
          }
        }
      })
      .resizable({
        edges: { left: true, right: true, bottom: true, top: true },
        listeners: {
          move(event) {
            const { x, y } = event.target.dataset;
            const { width, height } = event.rect;
            event.target.style.width = `${width}px`;
            event.target.style.height = `${height}px`;
            event.target.style.transform = `translate(${x}px, ${y}px)`;
          }
        }
      });
  </script>
</body>
</html>
