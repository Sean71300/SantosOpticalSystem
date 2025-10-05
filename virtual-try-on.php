<?php
// virtual-try-on.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Try-On</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- MediaPipe FaceMesh -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/facemesh@0.4.1633559619/facemesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1627447224/camera_utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1627447224/drawing_utils.js"></script>

  <style>
    :root {
      --primary: #6a4c93;
      --secondary: #b8a9c9;
      --light: #f8f9fa;
      --dark: #2b2b2b;
    }

    body {
      background-color: var(--light);
      color: var(--dark);
      font-family: "Poppins", sans-serif;
      text-align: center;
      padding: 2rem;
    }

    .camera-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
      padding: 2rem;
      max-width: 650px;
      margin: 2rem auto;
      position: relative;
    }

    video, canvas {
      width: 100%;
      border-radius: 12px;
      border: 3px solid var(--primary);
    }

    canvas {
      position: absolute;
      top: 0;
      left: 0;
      pointer-events: none;
    }

    .btn-primary {
      background-color: var(--primary);
      border: none;
    }

    .btn-primary:hover {
      background-color: #573a78;
    }

    .status-text {
      margin-top: 1rem;
      color: var(--dark);
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mb-3"><i class="fa-solid fa-camera me-2"></i>Virtual Try-On</h1>
    <p>Allow camera access and see real-time face detection!</p>

    <div class="camera-container">
      <video id="videoElement" autoplay playsinline></video>
      <canvas id="outputCanvas"></canvas>

      <div class="mt-3">
        <button id="startBtn" class="btn btn-primary me-2"><i class="fa-solid fa-play me-1"></i> Start Camera</button>
        <button id="stopBtn" class="btn btn-secondary"><i class="fa-solid fa-stop me-1"></i> Stop Camera</button>
      </div>
      <div class="status-text" id="statusText">Camera is off.</div>
    </div>
  </div>

  <script>
    const video = document.getElementById('videoElement');
    const canvas = document.getElementById('outputCanvas');
    const ctx = canvas.getContext('2d');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const statusText = document.getElementById('statusText');
    let stream, camera;

    // Initialize FaceMesh
    const faceMesh = new FaceMesh({
      locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/facemesh/${file}`,
    });

    faceMesh.setOptions({
      maxNumFaces: 1,
      refineLandmarks: true,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5
    });

    faceMesh.onResults(onResults);

    function onResults(results) {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        for (const landmarks of results.multiFaceLandmarks) {
          drawConnectors(ctx, landmarks, FACEMESH_TESSELATION, { color: '#6a4c93', lineWidth: 0.5 });
          drawConnectors(ctx, landmarks, FACEMESH_RIGHT_EYE, { color: '#ff0000' });
          drawConnectors(ctx, landmarks, FACEMESH_LEFT_EYE, { color: '#00ff00' });
          drawConnectors(ctx, landmarks, FACEMESH_LIPS, { color: '#0000ff' });
        }
      }
    }

    async function startCamera() {
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        statusText.textContent = "Camera active — detecting face...";

        // Start MediaPipe camera feed
        camera = new Camera(video, {
          onFrame: async () => {
            await faceMesh.send({ image: video });
          },
          width: 640,
          height: 480
        });
        camera.start();
      } catch (error) {
        console.error("Camera access denied:", error);
        statusText.textContent = "Unable to access camera. Please allow permission.";
      }
    }

    function stopCamera() {
      if (camera) camera.stop();
      if (stream) stream.getTracks().forEach(track => track.stop());
      video.srcObject = null;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      statusText.textContent = "Camera is off.";
    }

    startBtn.addEventListener('click', startCamera);
    stopBtn.addEventListener('click', stopCamera);
  </script>
</body>
</html>
