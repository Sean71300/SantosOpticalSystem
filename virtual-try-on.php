<?php
$shape = isset($_GET['shape']) ? htmlspecialchars($shape = $_GET['shape']) : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Virtual Try-On - <?= $shape ?> Face</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Preload glasses for faster rendering -->
  <link rel="preload" as="image" href="https://santosopticalclinic.com/Images/frames/ashape-frame-removebg-preview.png">

  <style>
    body {
      background: #fafafa;
      font-family: "Poppins", sans-serif;
      text-align: center;
      padding-top: 30px;
    }
    #tryon-container {
      position: relative;
      display: inline-block;
    }
    #video {
      position: relative;
      border-radius: 12px;
      width: 600px;
      height: 450px;
      object-fit: cover;
      background: #000;
      z-index: 1;
    }
    #overlay {
      position: absolute;
      left: 0;
      top: 0;
      border-radius: 12px;
      z-index: 2;
      pointer-events: none;
    }
    .debug {
      font-size: 0.9rem;
      color: #555;
      margin-top: 10px;
      text-align: left;
      max-width: 600px;
      margin-inline: auto;
      background: #f5f5f5;
      border-radius: 8px;
      padding: 8px;
      height: 150px;
      overflow-y: auto;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="mb-4 text-primary">Virtual Try-On for <?= $shape ?> Face</h2>
    <p>Align your face within the frame to see how the glasses look on you.</p>

    <div id="tryon-container">
      <video id="video" autoplay playsinline></video>
      <canvas id="overlay" width="600" height="450"></canvas>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary">Start Virtual Try-On</button>
      <a href="results.php" class="btn btn-secondary">Back to Results</a>
    </div>

    <div class="debug" id="debug"></div>
  </div>

  <!-- MediaPipe Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>

  <script>
    const debugBox = document.getElementById("debug");
    const log = msg => {
      console.log(msg);
      debugBox.innerHTML += msg + "<br>";
      debugBox.scrollTop = debugBox.scrollHeight;
    };

    const video = document.getElementById("video");
    const canvas = document.getElementById("overlay");
    const ctx = canvas.getContext("2d");

    // Preload glasses
    const glassesImg = new Image();
    glassesImg.src = "https://santosopticalclinic.com/Images/frames/ashape-frame-removebg-preview.png";
    glassesImg.loading = "eager";
    glassesImg.onload = () => log("✅ Glasses loaded");

    async function startCamera() {
      log("📸 Requesting camera...");
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = stream;

        video.onloadedmetadata = () => {
          log("✅ Camera started");
          if (typeof Camera === "undefined") {
            log("❌ Camera class not defined.");
            return;
          }

          const fpsLimit = 20;
          let lastFrameTime = 0;

          const camera = new Camera(video, {
            onFrame: async () => {
              const now = performance.now();
              if (now - lastFrameTime > 1000 / fpsLimit) {
                lastFrameTime = now;
                await faceMesh.send({ image: video });
              }
            },
            width: 600,
            height: 450
          });

          camera.start();
          log("🎥 Camera feed visible and tracking active");
        };
      } catch (err) {
        log("❌ Camera error: " + err);
        alert("Camera access was blocked or failed. Please allow camera access.");
      }
    }

    const faceMesh = new FaceMesh({
      locateFile: file => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/${file}`
    });

    faceMesh.setOptions({
      maxNumFaces: 1,
      refineLandmarks: true,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5
    });

    faceMesh.onResults(results => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        const landmarks = results.multiFaceLandmarks[0];

        const leftEye = landmarks[468]; // iris center left
        const rightEye = landmarks[473]; // iris center right

        const dx = rightEye.x - leftEye.x;
        const dy = rightEye.y - leftEye.y;
        const eyeDist = Math.sqrt(dx * dx + dy * dy) * canvas.width;
        const centerX = (leftEye.x + rightEye.x) / 2 * canvas.width;
        const centerY = (leftEye.y + rightEye.y) / 2 * canvas.height;

        const glassesWidth = eyeDist * 2.5;
        const glassesHeight = glassesWidth * 0.45;

        ctx.save();
        ctx.translate(centerX, centerY - glassesHeight * 0.2);
        ctx.rotate(Math.atan2(dy, dx));
        ctx.drawImage(glassesImg, -glassesWidth / 2, -glassesHeight / 2, glassesWidth, glassesHeight);
        ctx.restore();
      }
    });

    document.getElementById("startBtn").addEventListener("click", startCamera);
  </script>
</body>
</html>
