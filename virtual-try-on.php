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
    }
    video, canvas {
      width: 480px;
      height: 360px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    #outputCanvas {
      position: absolute;
      top: 0;
      left: 0;
    }
    .focus-target {
      position: absolute;
      width: 20px;
      height: 20px;
      background: rgba(255, 255, 255, 0.3);
      border: 2px solid white;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      z-index: 10;
    }
    .btn-primary {
      background-color: var(--primary);
      border: none;
    }
    .btn-primary:hover {
      background-color: #1558b0;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2 class="mb-4 fw-bold">👓 Virtual Try-On</h2>

    <div class="camera-container mb-3">
      <video id="inputVideo" autoplay muted playsinline></video>
      <canvas id="outputCanvas"></canvas>
      <div class="focus-target" id="focusTarget"></div>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary px-4">
        <i class="bi bi-camera me-2"></i>Start Camera
      </button>
      <button id="focusBtn" class="btn btn-outline-primary px-4 ms-2">
        <i class="bi bi-crosshair me-2"></i>Refocus
      </button>
    </div>

    <p class="mt-3 text-muted" id="statusMsg">Camera is off</p>
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
    const focusBtn = document.getElementById('focusBtn');
    const statusMsg = document.getElementById('statusMsg');
    const focusTarget = document.getElementById('focusTarget');

    // Load glasses image
    const glassesImg = new Image();
    glassesImg.src = "Images/frames/ashape-frame-removebg-preview.png";
    let glassesLoaded = false;
    glassesImg.onload = () => {
      glassesLoaded = true;
      console.log("✅ Glasses image loaded successfully");
    };

    let camera = null;
    let faceMesh = null;
    let stream = null;

    // Function to force camera refocus
    function forceRefocus() {
      if (!stream) return;
      
      // Stop all tracks
      stream.getTracks().forEach(track => track.stop());
      
      // Restart camera with focus constraints
      restartCameraWithFocus();
    }

    async function restartCameraWithFocus() {
      try {
        statusMsg.innerText = "Refocusing camera...";
        
        const constraints = {
          video: {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: "user",
            focusMode: "continuous" // Request continuous autofocus
          }
        };

        stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;

        videoElement.onloadedmetadata = () => {
          videoElement.play();
          statusMsg.innerText = "Camera refocused";
          
          // Hide focus target after a delay
          setTimeout(() => {
            focusTarget.style.display = 'none';
          }, 1000);
        };
      } catch (err) {
        console.error("❌ Refocus error:", err);
        statusMsg.innerText = "Refocus failed";
      }
    }

    async function onResults(results) {
      if (!results.multiFaceLandmarks || !glassesLoaded) return;

      canvasCtx.save();
      canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
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

        canvasCtx.drawImage(
          glassesImg,
          centerX - glassesWidth / 2,
          centerY - glassesHeight / 2,
          glassesWidth,
          glassesHeight
        );
      }

      canvasCtx.restore();
    }

    startBtn.addEventListener('click', async () => {
      try {
        statusMsg.innerText = "Requesting camera access...";
        startBtn.disabled = true;
        focusBtn.disabled = false;

        // Show focus target
        focusTarget.style.display = 'block';

        faceMesh = new FaceMesh({
          locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
        });
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: true,
          minDetectionConfidence: 0.5,
          minTrackingConfidence: 0.5
        });
        faceMesh.onResults(onResults);

        const constraints = {
          video: {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: "user"
          }
        };

        stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;

        videoElement.onloadedmetadata = () => {
          videoElement.play();
          statusMsg.innerText = "Camera active — aligning frames...";
          camera = new Camera(videoElement, {
            onFrame: async () => {
              await faceMesh.send({ image: videoElement });
            },
            width: 480,
            height: 360
          });
          camera.start();

          // Hide focus target after camera is stable
          setTimeout(() => {
            focusTarget.style.display = 'none';
          }, 2000);
        };
      } catch (err) {
        console.error("❌ Camera startup error:", err);
        statusMsg.innerText = "Unable to access camera. Check browser permissions.";
        startBtn.disabled = false;
      }
    });

    // Add refocus button event listener
    focusBtn.addEventListener('click', forceRefocus);
    focusBtn.disabled = true;

    // Auto-refocus when blur is detected (simplified)
    let lastFaceDetection = Date.now();
    setInterval(() => {
      if (Date.now() - lastFaceDetection > 3000 && stream) {
        console.log("Face lost - possible blur, attempting refocus");
        focusTarget.style.display = 'block';
        setTimeout(() => {
          forceRefocus();
        }, 500);
      }
    }, 1000);
  </script>
</body>
</html>