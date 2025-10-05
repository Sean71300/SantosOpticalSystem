<?php
// virtual-try-on.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Try-On</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --primary: #1a73e8;
      --dark: #222;
      --light: #f8f9fa;
    }
    body {
      background-color: var(--light);
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      text-align: center;
      padding-top: 40px;
      margin: 0;
      padding: 20px;
    }
    .camera-container {
      position: relative;
      display: inline-block;
      width: 100%;
      max-width: 500px;
      aspect-ratio: 4/3;
    }
    video, canvas {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    #outputCanvas {
      position: absolute;
      top: 0;
      left: 0;
    }
    .btn-primary {
      background-color: var(--primary);
      border: none;
      font-size: 16px;
      padding: 12px 24px;
    }
    .btn-primary:hover {
      background-color: #1558b0;
    }
    .btn-primary:disabled {
      background-color: #6c757d;
    }
    .btn-outline-primary {
      border-color: var(--primary);
      color: var(--primary);
    }
    .btn-outline-primary:hover {
      background-color: var(--primary);
      color: white;
    }
    .frame-btn {
      width: 60px;
      height: 60px;
      padding: 5px;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      margin: 2px;
      background: white;
      cursor: pointer;
      transition: all 0.2s;
    }
    .frame-btn:hover {
      border-color: var(--primary);
      transform: scale(1.05);
    }
    .frame-btn.active {
      border-color: var(--primary);
      border-width: 3px;
      background: #e3f2fd;
    }
    .frame-btn img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .loading-spinner {
      display: none;
      width: 40px;
      height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 10px auto;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .performance-warning {
      font-size: 12px;
      color: #666;
      margin-top: 10px;
    }
    .calibration-notice {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 8px;
      padding: 10px;
      margin: 10px 0;
      font-size: 14px;
    }
    .size-controls {
      background: #e9ecef;
      border-radius: 8px;
      padding: 10px;
      margin: 10px 0;
    }
    .frame-selector {
      background: white;
      border-radius: 8px;
      padding: 15px;
      margin: 10px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .frame-category {
      font-size: 12px;
      font-weight: bold;
      color: #666;
      margin-bottom: 5px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2 class="mb-4 fw-bold">👓 Virtual Try-On</h2>

    <div class="camera-container mb-3">
      <video id="inputVideo" autoplay muted playsinline></video>
      <canvas id="outputCanvas"></canvas>
    </div>

    <div class="calibration-notice d-none" id="calibrationNotice">
      <strong>Tip:</strong> Look straight at the camera, then click "Calibrate Straight Position" below
    </div>

    <div class="size-controls d-none" id="sizeControls">
      <div class="row align-items-center">
        <div class="col">
          <label class="form-label"><small>Glasses Size:</small></label>
          <input type="range" class="form-range" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
        </div>
        <div class="col-auto">
          <small id="sizeValue">2.4x</small>
        </div>
      </div>
    </div>

    <div class="frame-selector d-none" id="frameSelector">
      <div class="frame-category">CHOOSE FRAME STYLE</div>
      <div class="d-flex flex-wrap justify-content-center">
        <button class="frame-btn active" data-frame="A-TRIANGLE" title="A-Shape Triangle">
          <img src="Images/frames/ashape-frame-removebg-preview.png" alt="A-Shape">
        </button>
        <button class="frame-btn" data-frame="V-TRIANGLE" title="V-Shape Triangle">
          <img src="Images/frames/vshape-frame-removebg-preview.png" alt="V-Shape">
        </button>
        <button class="frame-btn" data-frame="ROUND" title="Round">
          <img src="Images/frames/round-frame-removebg-preview.png" alt="Round">
        </button>
        <button class="frame-btn" data-frame="SQUARE" title="Square">
          <img src="Images/frames/square-frame-removebg-preview.png" alt="Square">
        </button>
        <button class="frame-btn" data-frame="RECTANGLE" title="Rectangle">
          <img src="Images/frames/rectangle-frame-removebg-preview.png" alt="Rectangle">
        </button>
        <button class="frame-btn" data-frame="OBLONG" title="Oblong">
          <img src="Images/frames/oblong-frame-removebg-preview.png" alt="Oblong">
        </button>
        <button class="frame-btn" data-frame="DIAMOND" title="Diamond">
          <img src="Images/frames/diamond-frame-removebg-preview.png" alt="Diamond">
        </button>
      </div>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary px-4">
        <i class="bi bi-camera me-2"></i>Start Camera
      </button>
      <button id="calibrateBtn" class="btn btn-outline-primary px-4 ms-2 d-none">
        <i class="bi bi-arrow-clockwise me-2"></i>Calibrate Straight Position
      </button>
    </div>

    <div class="loading-spinner" id="loadingSpinner"></div>

    <p class="mt-3 text-muted" id="statusMsg">Camera is off</p>
    <p class="performance-warning" id="performanceWarning"></p>

    <!-- Performance tips for mobile -->
    <div class="alert alert-info mt-3 d-none" id="mobileTips">
      <small>
        <strong>Mobile Tips:</strong> For better performance, ensure good lighting, hold device steady, and close other apps.
      </small>
    </div>
  </div>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Mediapipe & Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.min.js"></script>

  <script>
    const videoElement = document.getElementById('inputVideo');
    const canvasElement = document.getElementById('outputCanvas');
    const canvasCtx = canvasElement.getContext('2d');
    const startBtn = document.getElementById('startBtn');
    const calibrateBtn = document.getElementById('calibrateBtn');
    const calibrationNotice = document.getElementById('calibrationNotice');
    const sizeControls = document.getElementById('sizeControls');
    const sizeSlider = document.getElementById('sizeSlider');
    const sizeValue = document.getElementById('sizeValue');
    const frameSelector = document.getElementById('frameSelector');
    const frameButtons = document.querySelectorAll('.frame-btn');
    const statusMsg = document.getElementById('statusMsg');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const mobileTips = document.getElementById('mobileTips');
    const performanceWarning = document.getElementById('performanceWarning');

    // Frame definitions
    const FRAMES = {
      'SQUARE': 'Images/frames/square-frame-removebg-preview.png',
      'ROUND': 'Images/frames/round-frame-removebg-preview.png',
      'OBLONG': 'Images/frames/oblong-frame-removebg-preview.png',
      'DIAMOND': 'Images/frames/diamond-frame-removebg-preview.png',
      'V-TRIANGLE': 'Images/frames/vshape-frame-removebg-preview.png',
      'A-TRIANGLE': 'Images/frames/ashape-frame-removebg-preview.png',
      'RECTANGLE': 'Images/frames/rectangle-frame-removebg-preview.png'
    };

    // Check if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
      mobileTips.classList.remove('d-none');
      performanceWarning.textContent = "Performance mode: Optimized for mobile";
    }

    // Load glasses images
    const glassesImages = {};
    let glassesLoaded = false;
    let loadedImagesCount = 0;
    const totalImages = Object.keys(FRAMES).length;

    // Preload all frame images
    Object.entries(FRAMES).forEach(([frameType, framePath]) => {
      const img = new Image();
      img.src = framePath;
      img.onload = () => {
        loadedImagesCount++;
        glassesImages[frameType] = img;
        console.log(`✅ ${frameType} frame loaded`);
        
        if (loadedImagesCount === totalImages) {
          glassesLoaded = true;
          console.log("✅ All frame images loaded successfully");
        }
      };
      img.onerror = () => {
        console.error(`❌ Failed to load frame: ${frameType}`);
        loadedImagesCount++;
      };
    });

    let camera = null;
    let faceMesh = null;
    let isProcessing = false;
    let frameCount = 0;
    let faceTrackingActive = false;
    let angleOffset = 0;
    let isCalibrated = false;
    let glassesSizeMultiplier = 2.4;
    let glassesHeightRatio = 0.7;
    let currentFrame = 'A-TRIANGLE'; // Default frame

    function calculateHeadAngle(landmarks) {
      const leftEyeInner = landmarks[133];
      const rightEyeInner = landmarks[362];
      
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      return Math.atan2(deltaY, deltaX);
    }

    function calibrateStraightPosition(landmarks) {
      const currentAngle = calculateHeadAngle(landmarks);
      angleOffset = -currentAngle;
      isCalibrated = true;
      console.log("✅ Calibrated! Offset:", angleOffset);
    }

    function drawGlasses(landmarks) {
      const leftEye = landmarks[33];
      const rightEye = landmarks[263];
      let headAngle = calculateHeadAngle(landmarks);
      
      // Apply calibration offset if calibrated
      if (isCalibrated) {
        headAngle += angleOffset;
      }
      
      const eyeDist = Math.hypot(
        rightEye.x * canvasElement.width - leftEye.x * canvasElement.width,
        rightEye.y * canvasElement.height - leftEye.y * canvasElement.height
      );

      const glassesWidth = eyeDist * glassesSizeMultiplier;
      const glassesHeight = glassesWidth * glassesHeightRatio;
      const centerX = (leftEye.x * canvasElement.width + rightEye.x * canvasElement.width) / 2;
      const centerY = (leftEye.y * canvasElement.height + rightEye.y * canvasElement.height) / 2;

      if (centerX > 0 && centerY > 0 && glassesWidth > 10 && glassesImages[currentFrame]) {
        canvasCtx.save();
        canvasCtx.translate(centerX, centerY);
        canvasCtx.rotate(headAngle);
        canvasCtx.drawImage(
          glassesImages[currentFrame],
          -glassesWidth / 2,
          -glassesHeight / 2,
          glassesWidth,
          glassesHeight
        );
        canvasCtx.restore();
      }
    }

    async function onResults(results) {
      if (!glassesLoaded || isProcessing) return;

      // Skip frames for performance - process every 3rd frame on mobile
      frameCount++;
      if (isMobile && frameCount % 3 !== 0) {
        return;
      }

      isProcessing = true;

      canvasCtx.save();
      canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
      
      // Draw video background
      canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

      // Draw glasses if face detected
      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        faceTrackingActive = true;
        
        // Auto-calibrate on first face detection if not already calibrated
        if (!isCalibrated && frameCount > 10) {
          calibrateStraightPosition(results.multiFaceLandmarks[0]);
          calibrationNotice.classList.add('d-none');
        }
        
        for (const landmarks of results.multiFaceLandmarks) {
          drawGlasses(landmarks);
        }
      } else {
        faceTrackingActive = false;
      }

      canvasCtx.restore();
      isProcessing = false;
    }

    function resizeCanvasToDisplay() {
      const container = canvasElement.parentElement;
      const displayWidth = container.clientWidth;
      const displayHeight = container.clientHeight;
      
      if (canvasElement.width !== displayWidth || canvasElement.height !== displayHeight) {
        canvasElement.width = displayWidth;
        canvasElement.height = displayHeight;
      }
    }

    async function initializeFaceMesh() {
      return new Promise((resolve) => {
        faceMesh = new FaceMesh({
          locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
        });

        // Optimized settings
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: false,
          minDetectionConfidence: 0.7,
          minTrackingConfidence: 0.5
        });

        faceMesh.onResults(onResults);
        
        faceMesh.initialize().then(() => {
          console.log("✅ FaceMesh initialized");
          resolve();
        }).catch(err => {
          console.error("❌ FaceMesh initialization failed:", err);
          resolve();
        });
      });
    }

    async function startCamera() {
      try {
        statusMsg.innerText = "Requesting camera access...";
        
        const constraints = {
          video: {
            facingMode: 'user',
            width: { ideal: isMobile ? 640 : 1280 },
            height: { ideal: isMobile ? 480 : 720 },
            aspectRatio: { ideal: 4/3 }
          }
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;

        return new Promise((resolve) => {
          videoElement.onloadedmetadata = () => {
            videoElement.play().then(() => {
              console.log("✅ Camera started successfully");
              resolve(stream);
            });
          };
        });

      } catch (err) {
        console.error("Camera error:", err);
        
        try {
          statusMsg.innerText = "Trying fallback camera...";
          const fallbackStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' } 
          });
          videoElement.srcObject = fallbackStream;
          
          return new Promise((resolve) => {
            videoElement.onloadedmetadata = () => {
              videoElement.play().then(() => {
                console.log("✅ Fallback camera started");
                resolve(fallbackStream);
              });
            };
          });
        } catch (fallbackErr) {
          throw fallbackErr;
        }
      }
    }

    // Frame selection handler
    frameButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active class from all buttons
        frameButtons.forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        btn.classList.add('active');
        // Update current frame
        currentFrame = btn.dataset.frame;
        console.log(`🎯 Selected frame: ${currentFrame}`);
      });
    });

    // Size slider handler
    sizeSlider.addEventListener('input', (e) => {
      glassesSizeMultiplier = parseFloat(e.target.value);
      sizeValue.textContent = glassesSizeMultiplier.toFixed(1) + 'x';
    });

    // Calibrate button handler
    calibrateBtn.addEventListener('click', () => {
      if (faceTrackingActive) {
        statusMsg.innerText = "Calibrating straight position...";
        isCalibrated = false;
        calibrationNotice.classList.remove('d-none');
        setTimeout(() => {
          statusMsg.innerText = "Calibrated! Glasses should now appear straight.";
        }, 1000);
      }
    });

    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        loadingSpinner.style.display = 'block';
        statusMsg.innerText = "Initializing...";

        await initializeFaceMesh();

        statusMsg.innerText = "Starting camera...";
        const stream = await startCamera();

        statusMsg.innerText = "Camera active — setting up face detection...";
        
        resizeCanvasToDisplay();

        // Show all controls
        calibrationNotice.classList.remove('d-none');
        calibrateBtn.classList.remove('d-none');
        sizeControls.classList.remove('d-none');
        frameSelector.classList.remove('d-none');

        const processingWidth = isMobile ? 320 : 640;
        const processingHeight = isMobile ? 240 : 480;

        camera = new Camera(videoElement, {
          onFrame: async () => {
            if (faceMesh && !isProcessing) {
              await faceMesh.send({ image: videoElement });
            }
          },
          width: processingWidth,
          height: processingHeight
        });

        await camera.start();

        loadingSpinner.style.display = 'none';
        statusMsg.innerText = "Ready! Try different frame styles below.";

        setInterval(() => {
          if (faceTrackingActive) {
            if (isCalibrated) {
              statusMsg.innerHTML = `Glasses active ✅ | <small>${currentFrame} - Calibrated</small>`;
            } else {
              statusMsg.innerHTML = `Glasses active ✅ | <small>${currentFrame} - Calibrating...</small>`;
            }
          } else {
            statusMsg.innerHTML = "Ready! Look at the camera | <small>Searching for face...</small>";
          }
        }, 3000);

        window.addEventListener('resize', resizeCanvasToDisplay);

      } catch (err) {
        console.error("❌ Startup error:", err);
        loadingSpinner.style.display = 'none';
        startBtn.disabled = false;
        
        if (err.name === 'NotAllowedError') {
          statusMsg.innerText = "❌ Camera permission denied. Please allow camera access in your browser settings.";
        } else if (err.name === 'NotFoundError') {
          statusMsg.innerText = "❌ No camera found on this device.";
        } else if (err.name === 'NotSupportedError') {
          statusMsg.innerText = "❌ Your browser doesn't support camera access.";
        } else {
          statusMsg.innerText = "❌ Failed to start camera. Please refresh and try again.";
        }
      }
    });

    window.addEventListener('load', () => {
      console.log("Page loaded - ready to start camera");
      setTimeout(() => {
        initializeFaceMesh();
      }, 1000);
    });
  </script>
</body>
</html>