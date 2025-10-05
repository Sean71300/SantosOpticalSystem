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

    // Performance settings
    const MOBILE_SETTINGS = {
      processEveryNFrames: 4, // Process only every 4th frame
      resolution: { width: 256, height: 192 }, // Very low resolution for processing
      frameRate: 15, // Lower FPS
      detectionConfidence: 0.8, // Higher confidence = less false positives
      trackingConfidence: 0.5
    };

    const DESKTOP_SETTINGS = {
      processEveryNFrames: 2,
      resolution: { width: 320, height: 240 },
      frameRate: 24,
      detectionConfidence: 0.7,
      trackingConfidence: 0.5
    };

    const settings = isMobile ? MOBILE_SETTINGS : DESKTOP_SETTINGS;

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
    let lastFaceDetection = null;
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

      // Skip frames based on performance settings
      frameCount++;
      if (frameCount % settings.processEveryNFrames !== 0) {
        return;
      }

      isProcessing = true;

      canvasCtx.save();
      canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
      
      // Only draw video if we have face detection results
      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);
        lastFaceDetection = results.multiFaceLandmarks[0];
        faceTrackingActive = true;
        
        for (const landmarks of results.multiFaceLandmarks) {
          drawGlasses(landmarks);
        }
      } else {
        // No face detected - just show video without processing
        canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
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

        // Ultra-optimized settings for mobile
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: false, // Disable for maximum performance
          minDetectionConfidence: settings.detectionConfidence,
          minTrackingConfidence: settings.trackingConfidence
        });

        faceMesh.onResults(onResults);
        
        // Use setTimeout to avoid blocking the main thread
        setTimeout(() => {
          faceMesh.initialize().then(() => {
            console.log("✅ FaceMesh initialized with mobile optimizations");
            resolve();
          }).catch(err => {
            console.error("❌ FaceMesh initialization failed:", err);
            resolve();
          });
        }, 100);
      });
    }

    startBtn.addEventListener('click', async () => {
      try {
        statusMsg.innerText = "Initializing (optimized for mobile)...";
        startBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        await initializeFaceMesh();

        // Mobile-optimized camera constraints
        const constraints = {
          video: {
            width: { ideal: isMobile ? 640 : 1280 },
            height: { ideal: isMobile ? 480 : 720 },
            aspectRatio: { ideal: 4/3 },
            frameRate: { max: settings.frameRate },
            facingMode: 'user'
          }
        };

        statusMsg.innerText = "Requesting camera access...";
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        
        videoElement.srcObject = stream;

        videoElement.onloadedmetadata = () => {
          videoElement.play();
          statusMsg.innerText = "Camera active — detecting face...";
          
          resizeCanvasToDisplay();
          
          camera = new Camera(videoElement, {
            onFrame: async () => {
              if (faceMesh && !isProcessing) {
                await faceMesh.send({ image: videoElement });
              }
            },
            width: settings.resolution.width,
            height: settings.resolution.height
          });
          
          camera.start().then(() => {
            loadingSpinner.style.display = 'none';
            statusMsg.innerText = "Ready! Look at the camera to try glasses.";
            
            // Update status based on face detection
            setInterval(() => {
              if (faceTrackingActive) {
                statusMsg.innerHTML = "Glasses active ✅ | <small>Face detected</small>";
              } else {
                statusMsg.innerHTML = "Ready! Look at the camera | <small>Searching for face...</small>";
              }
            }, 2000);
          });
        };

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
        } else if (err.name === 'OverconstrainedError') {
          // Fallback to basic constraints if optimized ones fail
          statusMsg.innerText = "Trying fallback camera settings...";
          setTimeout(() => startBtn.click(), 1000);
        }
      }
    });

    // Performance optimizations
    window.addEventListener('load', () => {
      // Reduce garbage collection by reusing objects
      if (isMobile) {
        // Mobile-specific optimizations
        initializeFaceMesh();
      }
    });

    // Prevent battery optimization from slowing us down
    let wakeLock = null;
    if ('wakeLock' in navigator) {
      try {
        wakeLock = await navigator.wakeLock.request('screen');
      } catch (err) {
        console.log('Wake Lock failed:', err);
      }
    }
  </script>
</body>
</html>