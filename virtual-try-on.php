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
    .btn-sm {
      padding: 8px 12px;
      font-size: 14px;
    }
    .frame-btn {
      width: 70px;
      height: 70px;
      padding: 5px;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      margin: 3px;
      background: white;
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
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
    .frame-label {
      position: absolute;
      bottom: -20px;
      left: 0;
      right: 0;
      font-size: 11px;
      font-weight: 600;
      color: var(--dark);
      white-space: nowrap;
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
    .controls-container {
      background: #e9ecef;
      border-radius: 8px;
      padding: 15px;
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
      font-size: 14px;
      font-weight: bold;
      color: #333;
      margin-bottom: 10px;
    }
    .control-group {
      margin-bottom: 10px;
    }
    .control-label {
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 5px;
    }
    .position-controls {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 5px;
    }
    .position-value {
      min-width: 40px;
      font-weight: bold;
    }
    .control-row {
      display: flex;
      justify-content: space-between;
      gap: 15px;
    }
    .control-col {
      flex: 1;
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

    <div class="controls-container d-none" id="controlsContainer">
      <div class="control-row">
        <div class="control-col">
          <div class="control-label">Size</div>
          <input type="range" class="form-range" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
          <small id="sizeValue">2.4x</small>
        </div>
        <div class="control-col">
          <div class="control-label">Height</div>
          <div class="position-controls">
            <button class="btn btn-outline-primary btn-sm" id="heightDown">
              <i class="bi bi-dash"></i>
            </button>
            <span class="position-value" id="heightValue">70%</span>
            <button class="btn btn-outline-primary btn-sm" id="heightUp">
              <i class="bi bi-plus"></i>
            </button>
          </div>
        </div>
      </div>
      
      <div class="control-row">
        <div class="control-col">
          <div class="control-label">Vertical Position</div>
          <div class="position-controls">
            <button class="btn btn-outline-primary btn-sm" id="positionDown">
              <i class="bi bi-arrow-down"></i>
            </button>
            <span class="position-value" id="positionValue">0px</span>
            <button class="btn btn-outline-primary btn-sm" id="positionUp">
              <i class="bi bi-arrow-up"></i>
            </button>
          </div>
        </div>
        <div class="control-col">
          <div class="control-label">3D Depth</div>
          <div class="position-controls">
            <button class="btn btn-outline-primary btn-sm" id="depthDown">
              <i class="bi bi-dash"></i>
            </button>
            <span class="position-value" id="depthValue">0.3</span>
            <button class="btn btn-outline-primary btn-sm" id="depthUp">
              <i class="bi bi-plus"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="frame-selector d-none" id="frameSelector">
      <div class="frame-category">CHOOSE FRAME STYLE</div>
      <div class="d-flex flex-wrap justify-content-center">
        <button class="frame-btn active" data-frame="A-TRIANGLE">
          <img src="Images/frames/ashape-frame-removebg-preview.png" alt="A-Shape">
          <div class="frame-label">A-Shape</div>
        </button>
        <button class="frame-btn" data-frame="V-TRIANGLE">
          <img src="Images/frames/vshape-frame-removebg-preview.png" alt="V-Shape">
          <div class="frame-label">V-Shape</div>
        </button>
        <button class="frame-btn" data-frame="ROUND">
          <img src="Images/frames/round-frame-removebg-preview.png" alt="Round">
          <div class="frame-label">Round</div>
        </button>
        <button class="frame-btn" data-frame="SQUARE">
          <img src="Images/frames/square-frame-removebg-preview.png" alt="Square">
          <div class="frame-label">Square</div>
        </button>
        <button class="frame-btn" data-frame="RECTANGLE">
          <img src="Images/frames/rectangle-frame-removebg-preview.png" alt="Rectangle">
          <div class="frame-label">Rectangle</div>
        </button>
        <button class="frame-btn" data-frame="OBLONG">
          <img src="Images/frames/oblong-frame-removebg-preview.png" alt="Oblong">
          <div class="frame-label">Oblong</div>
        </button>
        <button class="frame-btn" data-frame="DIAMOND">
          <img src="Images/frames/diamond-frame-removebg-preview.png" alt="Diamond">
          <div class="frame-label">Diamond</div>
        </button>
      </div>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary px-4">
        <i class="bi bi-camera me-2"></i>Start Camera
      </button>
      <button id="calibrateBtn" class="btn btn-outline-primary px-4 ms-2 d-none">
        <i class="bi bi-arrow-clockwise me-2"></i>Recalibrate
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
    const controlsContainer = document.getElementById('controlsContainer');
    const sizeSlider = document.getElementById('sizeSlider');
    const sizeValue = document.getElementById('sizeValue');
    const heightDown = document.getElementById('heightDown');
    const heightUp = document.getElementById('heightUp');
    const heightValue = document.getElementById('heightValue');
    const positionDown = document.getElementById('positionDown');
    const positionUp = document.getElementById('positionUp');
    const positionValue = document.getElementById('positionValue');
    const depthDown = document.getElementById('depthDown');
    const depthUp = document.getElementById('depthUp');
    const depthValue = document.getElementById('depthValue');
    const frameSelector = document.getElementById('frameSelector');
    const frameButtons = document.querySelectorAll('.frame-btn');
    const statusMsg = document.getElementById('statusMsg');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const mobileTips = document.getElementById('mobileTips');
    const performanceWarning = document.getElementById('performanceWarning');

    // Frame definitions with proper labels
    const FRAMES = {
      'SQUARE': {
        path: 'Images/frames/square-frame-removebg-preview.png',
        label: 'Square'
      },
      'ROUND': {
        path: 'Images/frames/round-frame-removebg-preview.png',
        label: 'Round'
      },
      'OBLONG': {
        path: 'Images/frames/oblong-frame-removebg-preview.png',
        label: 'Oblong'
      },
      'DIAMOND': {
        path: 'Images/frames/diamond-frame-removebg-preview.png',
        label: 'Diamond'
      },
      'V-TRIANGLE': {
        path: 'Images/frames/vshape-frame-removebg-preview.png',
        label: 'V-Shape'
      },
      'A-TRIANGLE': {
        path: 'Images/frames/ashape-frame-removebg-preview.png',
        label: 'A-Shape'
      },
      'RECTANGLE': {
        path: 'Images/frames/rectangle-frame-removebg-preview.png',
        label: 'Rectangle'
      }
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
    Object.entries(FRAMES).forEach(([frameType, frameData]) => {
      const img = new Image();
      img.src = frameData.path;
      img.onload = () => {
        loadedImagesCount++;
        glassesImages[frameType] = img;
        console.log(`✅ ${frameData.label} frame loaded`);
        
        if (loadedImagesCount === totalImages) {
          glassesLoaded = true;
          console.log("✅ All frame images loaded successfully");
        }
      };
      img.onerror = () => {
        console.error(`❌ Failed to load frame: ${frameData.label}`);
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
    let verticalOffset = 0; // Y-axis positioning
    let depthEffect = 0.3; // 3D depth effect strength
    let currentFrame = 'A-TRIANGLE';

    function calculateHeadAngle(landmarks) {
      const leftEyeInner = landmarks[133];
      const rightEyeInner = landmarks[362];
      
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      return Math.atan2(deltaY, deltaX);
    }

    function calculateHeadTilt(landmarks) {
      // Calculate head tilt using nose and forehead points
      const noseTip = landmarks[1];
      const forehead = landmarks[10];
      const chin = landmarks[152];
      
      // Use vertical difference to detect side tilt
      const verticalDiff = forehead.y - chin.y;
      return verticalDiff;
    }

    function calibrateStraightPosition(landmarks) {
      const currentAngle = calculateHeadAngle(landmarks);
      angleOffset = -currentAngle;
      isCalibrated = true;
      console.log("✅ Calibrated! Offset:", angleOffset);
    }

    function updateHeightDisplay() {
      heightValue.textContent = Math.round(glassesHeightRatio * 100) + '%';
    }

    function updatePositionDisplay() {
      positionValue.textContent = verticalOffset + 'px';
    }

    function updateDepthDisplay() {
      depthValue.textContent = depthEffect.toFixed(1);
    }

    function drawGlasses(landmarks) {
      const leftEye = landmarks[33];
      const rightEye = landmarks[263];
      let headAngle = calculateHeadAngle(landmarks);
      const headTilt = calculateHeadTilt(landmarks);
      
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
      let centerX = (leftEye.x * canvasElement.width + rightEye.x * canvasElement.width) / 2;
      let centerY = (leftEye.y * canvasElement.height + rightEye.y * canvasElement.height) / 2;

      // Apply vertical offset
      centerY += verticalOffset;

      // Calculate 3D depth effect based on head tilt
      const depthMultiplier = 1 + (Math.abs(headTilt) * depthEffect * 2);
      const perspectiveShift = headTilt * depthEffect * glassesWidth * 0.5;

      if (centerX > 0 && centerY > 0 && glassesWidth > 10 && glassesImages[currentFrame]) {
        canvasCtx.save();
        canvasCtx.translate(centerX, centerY);
        canvasCtx.rotate(headAngle);
        
        // Apply perspective transformation for 3D effect
        if (Math.abs(headTilt) > 0.05) {
          // Create perspective by scaling and shifting
          const scaleNear = 1 + (Math.abs(headTilt) * depthEffect);
          const scaleFar = 1 - (Math.abs(headTilt) * depthEffect * 0.5);
          
          // Apply different scaling for left and right sides to simulate perspective
          canvasCtx.transform(
            headTilt > 0 ? scaleNear : scaleFar, 0, 0, 1,
            perspectiveShift, 0
          );
        }
        
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
        const frameLabel = FRAMES[currentFrame].label;
        console.log(`🎯 Selected frame: ${frameLabel}`);
      });
    });

    // Size slider handler
    sizeSlider.addEventListener('input', (e) => {
      glassesSizeMultiplier = parseFloat(e.target.value);
      sizeValue.textContent = glassesSizeMultiplier.toFixed(1) + 'x';
    });

    // Height controls
    heightDown.addEventListener('click', () => {
      glassesHeightRatio = Math.max(0.4, glassesHeightRatio - 0.05);
      updateHeightDisplay();
    });

    heightUp.addEventListener('click', () => {
      glassesHeightRatio = Math.min(1.0, glassesHeightRatio + 0.05);
      updateHeightDisplay();
    });

    // Vertical position controls
    positionDown.addEventListener('click', () => {
      verticalOffset += 5;
      updatePositionDisplay();
    });

    positionUp.addEventListener('click', () => {
      verticalOffset -= 5;
      updatePositionDisplay();
    });

    // Depth effect controls
    depthDown.addEventListener('click', () => {
      depthEffect = Math.max(0.1, depthEffect - 0.1);
      updateDepthDisplay();
    });

    depthUp.addEventListener('click', () => {
      depthEffect = Math.min(1.0, depthEffect + 0.1);
      updateDepthDisplay();
    });

    // Calibrate button handler
    calibrateBtn.addEventListener('click', () => {
      if (faceTrackingActive) {
        statusMsg.innerText = "Recalibrating... Look straight at camera";
        isCalibrated = false;
        setTimeout(() => {
          statusMsg.innerText = "Recalibrated! Glasses should now appear straight.";
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
        controlsContainer.classList.remove('d-none');
        frameSelector.classList.remove('d-none');
        calibrateBtn.classList.remove('d-none');
        updateHeightDisplay();
        updatePositionDisplay();
        updateDepthDisplay();

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
            const frameLabel = FRAMES[currentFrame].label;
            if (isCalibrated) {
              statusMsg.innerHTML = `Glasses active ✅ | <small>${frameLabel} Frame - Calibrated</small>`;
            } else {
              statusMsg.innerHTML = `Glasses active ✅ | <small>${frameLabel} Frame - Calibrating...</small>`;
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