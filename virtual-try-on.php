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
    }
    .camera-container {
      position: relative;
      display: inline-block;
      width: 640px;
      max-width: 90vw;
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
    }
    .btn-primary:hover {
      background-color: #1558b0;
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
  </style>
</head>

<body>
  <div class="container">
    <h2 class="mb-4 fw-bold">👓 Virtual Try-On</h2>

    <div class="camera-container mb-3">
      <video id="inputVideo" autoplay muted playsinline></video>
      <canvas id="outputCanvas"></canvas>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary px-4">
        <i class="bi bi-camera me-2"></i>Start Camera
      </button>
    </div>

    <div class="loading-spinner" id="loadingSpinner"></div>

    <p class="mt-3 text-muted" id="statusMsg">Camera is off</p>

    <!-- Performance tips for mobile -->
    <div class="alert alert-info mt-3 d-none" id="mobileTips">
      <small>
        <strong>Mobile Tips:</strong> For better performance, ensure good lighting and hold device steady.
        Face detection works best in portrait mode.
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
    const statusMsg = document.getElementById('statusMsg');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const mobileTips = document.getElementById('mobileTips');

    // Check if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
      mobileTips.classList.remove('d-none');
    }

    // Load glasses image with error handling
    const glassesImg = new Image();
    glassesImg.src = "Images/frames/ashape-frame-removebg-preview.png";
    let glassesLoaded = false;
    glassesImg.onload = () => {
      glassesLoaded = true;
      console.log("✅ Glasses image loaded successfully");
    };
    glassesImg.onerror = () => {
      console.error("❌ Failed to load glasses image");
      statusMsg.innerText = "Error loading glasses image";
    };

    let camera = null;
    let faceMesh = null;
    let isProcessing = false;
    let frameCount = 0;

    async function onResults(results) {
      if (!results.multiFaceLandmarks || !glassesLoaded || isProcessing) return;

      isProcessing = true;
      frameCount++;

      // Skip frames on mobile for better performance (process every 3rd frame)
      if (isMobile && frameCount % 3 !== 0) {
        isProcessing = false;
        return;
      }

      canvasCtx.save();
      canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
      
      // Draw the video frame
      canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

      for (const landmarks of results.multiFaceLandmarks) {
        const leftEye = landmarks[33];
        const rightEye = landmarks[263];

        const eyeDist = Math.hypot(
          rightEye.x * canvasElement.width - leftEye.x * canvasElement.width,
          rightEye.y * canvasElement.height - leftEye.y * canvasElement.height
        );

        const glassesWidth = eyeDist * 2.2;
        const glassesHeight = glassesWidth * 0.5;
        const centerX = (leftEye.x * canvasElement.width + rightEye.x * canvasElement.width) / 2;
        const centerY = (leftEye.y * canvasElement.height + rightEye.y * canvasElement.height) / 2;

        // Only draw if we have valid coordinates
        if (centerX > 0 && centerY > 0 && glassesWidth > 10) {
          canvasCtx.drawImage(
            glassesImg,
            centerX - glassesWidth / 2,
            centerY - glassesHeight / 2,
            glassesWidth,
            glassesHeight
          );
        }
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

        // Optimized settings for mobile
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: false, // Disable refinement for better performance
          minDetectionConfidence: 0.7, // Higher confidence threshold
          minTrackingConfidence: 0.5
        });

        faceMesh.onResults(onResults);
        faceMesh.initialize().then(() => {
          console.log("✅ FaceMesh initialized");
          resolve();
        }).catch(err => {
          console.error("❌ FaceMesh initialization failed:", err);
          resolve(); // Resolve anyway to continue
        });
      });
    }

    startBtn.addEventListener('click', async () => {
      try {
        statusMsg.innerText = "Initializing...";
        startBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        // Pre-initialize FaceMesh
        await initializeFaceMesh();

        // Optimize camera settings for mobile
        const constraints = {
          video: {
            width: { ideal: isMobile ? 640 : 1280 },
            height: { ideal: isMobile ? 480 : 960 },
            aspectRatio: { ideal: 4/3 },
            frameRate: { ideal: isMobile ? 24 : 30 }, // Lower FPS for mobile
            facingMode: 'user'
          }
        };

        statusMsg.innerText = "Requesting camera access...";
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        
        videoElement.srcObject = stream;

        videoElement.onloadedmetadata = () => {
          videoElement.play();
          statusMsg.innerText = "Camera active — detecting face...";
          
          // Resize canvas to match display size
          resizeCanvasToDisplay();
          
          camera = new Camera(videoElement, {
            onFrame: async () => {
              if (faceMesh && !isProcessing) {
                await faceMesh.send({ image: videoElement });
              }
            },
            width: isMobile ? 320 : 640, // Lower resolution for processing on mobile
            height: isMobile ? 240 : 480
          });
          
          camera.start().then(() => {
            loadingSpinner.style.display = 'none';
            statusMsg.innerText = "Ready! Look at the camera to try glasses.";
          });
        };

        // Handle window resizing
        window.addEventListener('resize', resizeCanvasToDisplay);
        
      } catch (err) {
        console.error("❌ Camera startup error:", err);
        statusMsg.innerText = "Unable to access camera. Check browser permissions.";
        startBtn.disabled = false;
        loadingSpinner.style.display = 'none';
        
        if (err.name === 'NotAllowedError') {
          statusMsg.innerText = "Camera permission denied. Please allow camera access.";
        } else if (err.name === 'NotFoundError') {
          statusMsg.innerText = "No camera found on this device.";
        }
      }
    });

    // Preload and initialize when page loads
    window.addEventListener('load', () => {
      // Pre-initialize FaceMesh in background
      initializeFaceMesh();
    });
  </script>
</body>
</html>