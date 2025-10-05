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
    const statusMsg = document.getElementById('statusMsg');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const mobileTips = document.getElementById('mobileTips');
    const performanceWarning = document.getElementById('performanceWarning');

    // Check if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
      mobileTips.classList.remove('d-none');
      performanceWarning.textContent = "Performance mode: Optimized for mobile";
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
    let faceTrackingActive = false;

    function calculateHeadAngle(landmarks) {
      const leftEyeInner = landmarks[133];
      const rightEyeInner = landmarks[362];
      
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      return Math.atan2(deltaY, deltaX);
    }

    function drawGlasses(landmarks) {
      const leftEye = landmarks[33];
      const rightEye = landmarks[263];
      const headAngle = calculateHeadAngle(landmarks);
      
      const eyeDist = Math.hypot(
        rightEye.x * canvasElement.width - leftEye.x * canvasElement.width,
        rightEye.y * canvasElement.height - leftEye.y * canvasElement.height
      );

      const glassesWidth = eyeDist * 2.2;
      const glassesHeight = glassesWidth * 0.5;
      const centerX = (leftEye.x * canvasElement.width + rightEye.x * canvasElement.width) / 2;
      const centerY = (leftEye.y * canvasElement.height + rightEye.y * canvasElement.height) / 2;

      if (centerX > 0 && centerY > 0 && glassesWidth > 10) {
        canvasCtx.save();
        canvasCtx.translate(centerX, centerY);
        canvasCtx.rotate(headAngle);
        canvasCtx.drawImage(
          glassesImg,
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
          refineLandmarks: false, // Disable for performance
          minDetectionConfidence: 0.7,
          minTrackingConfidence: 0.5
        });

        faceMesh.onResults(onResults);
        
        faceMesh.initialize().then(() => {
          console.log("✅ FaceMesh initialized");
          resolve();
        }).catch(err => {
          console.error("❌ FaceMesh initialization failed:", err);
          resolve(); // Continue even if FaceMesh fails
        });
      });
    }

    async function startCamera() {
      try {
        statusMsg.innerText = "Requesting camera access...";
        
        // Try different camera constraints with fallbacks
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
        
        // Try fallback with minimal constraints
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

    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        loadingSpinner.style.display = 'block';
        statusMsg.innerText = "Initializing...";

        // Initialize FaceMesh first
        await initializeFaceMesh();

        // Start camera
        statusMsg.innerText = "Starting camera...";
        const stream = await startCamera();

        statusMsg.innerText = "Camera active — setting up face detection...";
        
        // Resize canvas to match video
        resizeCanvasToDisplay();

        // Determine processing resolution based on device
        const processingWidth = isMobile ? 320 : 640;
        const processingHeight = isMobile ? 240 : 480;

        // Start MediaPipe camera
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
        statusMsg.innerText = "Ready! Look at the camera to try glasses.";

        // Update status based on face detection
        setInterval(() => {
          if (faceTrackingActive) {
            statusMsg.innerHTML = "Glasses active ✅ | <small>Face detected</small>";
          } else {
            statusMsg.innerHTML = "Ready! Look at the camera | <small>Searching for face...</small>";
          }
        }, 3000);

        // Handle window resize
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

    // Pre-initialize on load
    window.addEventListener('load', () => {
      console.log("Page loaded - ready to start camera");
      // Preload FaceMesh but don't block
      setTimeout(() => {
        initializeFaceMesh();
      }, 1000);
    });
  </script>
</body>
</html>