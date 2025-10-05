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
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary px-4">
        <i class="bi bi-camera me-2"></i>Start Camera
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
    const statusMsg = document.getElementById('statusMsg');

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

        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
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
        };
      } catch (err) {
        console.error("❌ Camera startup error:", err);
        statusMsg.innerText = "Unable to access camera. Check browser permissions.";
        startBtn.disabled = false;
      }
    });
  </script>
</body>
</html>
