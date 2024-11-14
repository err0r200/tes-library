<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Coordinate Capture</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #pdf-canvas {
            border: 1px solid #000;
            width: 100%;
            height: auto;
            position: relative;
        }
        #qr-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .qr-box {
            position: absolute;
            border: 2px dashed red;
            background: rgba(255, 255, 255, 0.7);
            cursor: move;
            pointer-events: all;
            z-index: 2;
        }
        .delete-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
        }
        .toolbar {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .toolbar .btn {
            margin: 0 5px;
        }
        .toolbar .input-group {
            width: 150px;
            margin: 0 5px;
        }
        .toolbar .input-group .form-control {
            text-align: center;
        }
        .toolbar .input-group .input-group-text {
            padding: 0 10px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Toolbar -->
        <div class="toolbar mb-3">
            <button id="first-page" class="btn btn-primary">First</button>
            <button id="prev-page" class="btn btn-primary">Prev</button>
            <div class="input-group">
                <input type="number" id="page-input" class="form-control" min="1" placeholder="Page">
                <div class="input-group-append">
                    <span class="input-group-text">/ <span id="page-count">1</span></span>
                </div>
            </div>
            <button id="next-page" class="btn btn-primary">Next</button>
            <button id="last-page" class="btn btn-primary">Last</button>
            <button id="add-qr-box" class="btn btn-info">Add QR Code Box</button>
            <button id="save-pdf" class="btn btn-success">Save</button>
			
        </div>

        <!-- PDF Canvas and QR Box Container -->
        <div class="row">
            <div class="col">
                <canvas id="pdf-canvas"></canvas>
                <div id="qr-container"></div>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.11/dist/interact.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        const url = 'web_bsi.pdf'; // Ganti dengan path ke file PDF Anda
        const MAX_QR_BOXES = 2; // Maksimum QR Code Box per halaman

        let pdfDoc = null;
        let pageNum = 1;
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const pageNumInput = document.getElementById('page-input');
        const pageCountSpan = document.getElementById('page-count');
        const qrContainer = document.getElementById('qr-container');
        let qrBoxesByPage = {}; // Object to store QR boxes data by page
        let currentPageViewport = null; // Store the current page viewport

        // Clear localStorage on page load
        window.addEventListener('load', () => {
            localStorage.removeItem('qrBoxesByPage');
        });

        // Load PDF
        pdfjsLib.getDocument(url).promise.then(pdf => {
            pdfDoc = pdf;
            pageCountSpan.textContent = pdf.numPages;
            pageNumInput.value = pageNum;
            renderPage(pageNum);
        });

        function renderPage(num) {
            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale: 1 });
                currentPageViewport = viewport; // Update current page viewport
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                page.render(renderContext).promise.then(() => {
                    pageNumInput.value = num;
                    loadQrBoxesForPage(num); // Load QR boxes for the current page
                });
            });
        }

        // Navigation functions
        document.getElementById('first-page').addEventListener('click', () => {
            pageNum = 1;
            renderPage(pageNum);
        });

        document.getElementById('prev-page').addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum--;
            renderPage(pageNum);
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            renderPage(pageNum);
        });

        document.getElementById('last-page').addEventListener('click', () => {
            pageNum = pdfDoc.numPages;
            renderPage(pageNum);
        });

        pageNumInput.addEventListener('change', () => {
            const newPageNum = parseInt(pageNumInput.value, 10);
            if (newPageNum >= 1 && newPageNum <= pdfDoc.numPages) {
                pageNum = newPageNum;
                renderPage(pageNum);
            }
        });

        // Add QR Code Box
        document.getElementById('add-qr-box').addEventListener('click', () => {
            if (!qrBoxesByPage[pageNum]) {
                qrBoxesByPage[pageNum] = [];
            }
            if (qrBoxesByPage[pageNum].length >= MAX_QR_BOXES) {
                alert('Jumlah QR Code Box per halaman sudah mencapai maksimum.');
                return;
            }

            const qrBox = document.createElement('div');
            qrBox.className = 'qr-box';
            qrBox.dataset.id = Date.now(); // Unique ID for each QR box
            qrBox.style.width = '100px'; // Default width
            qrBox.style.height = '100px'; // Default height

            // Calculate initial position to center the QR box relative to the canvas
            const canvasRect = canvas.getBoundingClientRect();
            const containerRect = qrContainer.getBoundingClientRect();

            const initialLeft = (canvasRect.width / 2) - (parseFloat(qrBox.style.width) / 2);
            const initialTop = (canvasRect.height / 2) - (parseFloat(qrBox.style.height) / 2);

            qrBox.style.left = `${initialLeft}px`;
            qrBox.style.top = `${initialTop}px`;
            qrContainer.appendChild(qrBox);

            // Add to QR boxes data
            qrBoxesByPage[pageNum].push({
                id: qrBox.dataset.id,
                width: parseFloat(qrBox.style.width),
                height: parseFloat(qrBox.style.height),
                x: initialLeft,
                y: initialTop
            });
            localStorage.setItem('qrBoxesByPage', JSON.stringify(qrBoxesByPage));

            // Add delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'delete-btn';
            deleteBtn.textContent = 'X';
            deleteBtn.addEventListener('click', () => {
                qrContainer.removeChild(qrBox);
                qrBoxesByPage[pageNum] = qrBoxesByPage[pageNum].filter(box => box.id !== qrBox.dataset.id);
                localStorage.setItem('qrBoxesByPage', JSON.stringify(qrBoxesByPage));
            });
            qrBox.appendChild(deleteBtn);

            // Set up interact.js for drag and resize
            setupInteract(qrBox);
        });

        // Load QR boxes for the current page
        function loadQrBoxesForPage(num) {
            qrContainer.innerHTML = ''; // Clear existing QR boxes
            const savedQrBoxes = JSON.parse(localStorage.getItem('qrBoxesByPage')) || {};
            qrBoxesByPage = savedQrBoxes;

            if (qrBoxesByPage[num]) {
                qrBoxesByPage[num].forEach(boxData => {
                    const qrBox = document.createElement('div');
                    qrBox.className = 'qr-box';
                    qrBox.dataset.id = boxData.id;
                    qrBox.style.width = `${boxData.width}px`;
                    qrBox.style.height = `${boxData.height}px`;
                    qrBox.style.left = `${boxData.x}px`;
                    qrBox.style.top = `${boxData.y}px`;

                    // Add delete button
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.textContent = 'X';
                    deleteBtn.addEventListener('click', () => {
                        qrContainer.removeChild(qrBox);
                        qrBoxesByPage[num] = qrBoxesByPage[num].filter(box => box.id !== qrBox.dataset.id);
                        localStorage.setItem('qrBoxesByPage', JSON.stringify(qrBoxesByPage));
                    });
                    qrBox.appendChild(deleteBtn);

                    qrContainer.appendChild(qrBox);

                    // Set up interact.js for drag and resize
                    setupInteract(qrBox);
                });
            }
        }

        // Setup interact.js for a QR box
        function setupInteract(element) {
            interact(element)
				.draggable({
					listeners: {
						move(event) {
							const target = event.target;
							const deltaX = event.dx;
							const deltaY = event.dy;

							// Update left and top directly
							let newLeft = (parseFloat(target.style.left) || 0) + deltaX;
							let newTop = (parseFloat(target.style.top) || 0) + deltaY;

							target.style.left = `${newLeft}px`;
							target.style.top = `${newTop}px`;

							// Update QR box position in qrBoxesByPage
							const box = qrBoxesByPage[pageNum].find(box => box.id === target.dataset.id);
							if (box) {
								box.x = newLeft;
								box.y = newTop;
							}
							localStorage.setItem('qrBoxesByPage', JSON.stringify(qrBoxesByPage));
						}
					}
				})
				.resizable({
					edges: { left: true, right: true, bottom: true, top: true },
					listeners: {
						move(event) {
							const target = event.target;
							let width = event.rect.width;
							let height = event.rect.height;

							// Set new width and height
							target.style.width = `${width}px`;
							target.style.height = `${height}px`;

							// Update QR box dimensions in qrBoxesByPage
							const box = qrBoxesByPage[pageNum].find(box => box.id === target.dataset.id);
							if (box) {
								box.width = width;
								box.height = height;
							}
							localStorage.setItem('qrBoxesByPage', JSON.stringify(qrBoxesByPage));
						}
					}
				});

        }
		
		function getQrBoxDetailsByPage() {
			const qrBoxesByPage = JSON.parse(localStorage.getItem('qrBoxesByPage')) || {};
			let qrBoxDetails = [];

			for (let page in qrBoxesByPage) {
				if (qrBoxesByPage.hasOwnProperty(page)) {
					qrBoxDetails.push({
						page: page,  // Halaman
						qrBoxes: qrBoxesByPage[page].map(box => ({
							x: box.x,
							y: box.y,
							width: box.width,
							height: box.height
						}))
					});
				}
			}

			return qrBoxDetails;
		}

		
		function sendQrBoxDetailsToServer() {
			const qrBoxDetails = getQrBoxDetailsByPage();

			$.ajax({
				url: 'save_qr_boxes.php', // Ganti dengan URL backend kamu
				type: 'POST',
				data: JSON.stringify(qrBoxDetails),
				contentType: 'application/json',
				success: function(response) {
					console.log('Data QR box berhasil dikirim:', response);
				},
				error: function(xhr, status, error) {
					console.error('Gagal mengirim data:', error);
				}
			});
		}

        // Save PDF function (implement your PDF saving logic here)
        document.getElementById('save-pdf').addEventListener('click', () => {
           sendQrBoxDetailsToServer();
        });
    </script>
</body>
</html>
