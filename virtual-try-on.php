<?php
$shape = isset($_GET['shape']) ? htmlspecialchars($_GET['shape']) : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Try-On - <?= $shape ?> Face</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #fafafa;
      font-family: "Poppins", sans-serif;
      text-align: center;
      padding-top: 30px;
    }
    #video, #overlay {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 12px;
    }
    #tryon-container {
      position: relative;
      display: inline-block;
    }
    #overlay {
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
      <video id="video" playsinline width="600" height="450" style="display:none;"></video>
      <canvas id="overlay" width="600" height="450"></canvas>
    </div>

    <div class="mt-3">
      <button id="startBtn" class="btn btn-primary">Start Virtual Try-On</button>
      <a href="results.php" class="btn btn-secondary">Back to Results</a>
    </div>

    <div class="debug" id="debug"></div>
  </div>

  <!-- MediaPipe & FaceMesh -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>

  <script>
    const debugBox = document.getElementById('debug');
    const log = msg => {
      console.log(msg);
      debugBox.innerHTML += msg + '<br>';
      debugBox.scrollTop = debugBox.scrollHeight;
    };

    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const ctx = canvas.getContext('2d');

    // ✅ Use your frame image here
    const glassesImg = new Image();
    glassesImg.src = "https://santosopticalclinic.com/Images/frames/ashape-frame-removebg-preview.png";
    glassesImg.onload = () => log("✅ Glasses image loaded: " + glassesImg.src);

    async function startCamera() {
      log("📸 Requesting camera access...");
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;

      video.onloadedmetadata = () => {
        log("✅ Camera permission granted");

        // ✅ Use only MediaPipe Camera
        if (typeof Camera === "undefined") {
          log("❌ Camera class not defined. Check camera_utils.js path.");
          return;
        }

        try {
          const camera = new Camera(video, {
            onFrame: async () => {
              await faceMesh.send({ image: video });
            },
            width: 600,
            height: 450
          });
          camera.start();
          log("🎥 Camera started successfully!");
        } catch (err) {
          log("❌ Camera failed: " + err);
        }
      };
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

        // More accurate landmarks (eye centers)
        const leftEyeCenter = landmarks[468]; // iris center left
        const rightEyeCenter = landmarks[473]; // iris center right

        const dx = rightEyeCenter.x - leftEyeCenter.x;
        const dy = rightEyeCenter.y - leftEyeCenter.y;
        const eyeDist = Math.sqrt(dx * dx + dy * dy) * canvas.width;
        const centerX = (leftEyeCenter.x + rightEyeCenter.x) / 2 * canvas.width;
        const centerY = (leftEyeCenter.y + rightEyeCenter.y) / 2 * canvas.height;

        const glassesWidth = eyeDist * 2.5;
        const glassesHeight = glassesWidth * 0.45;

        ctx.save();
        ctx.translate(centerX, centerY - glassesHeight * 0.2); // Move slightly up
        ctx.rotate(Math.atan2(dy, dx));
        ctx.drawImage(glassesImg, -glassesWidth / 2, -glassesHeight / 2, glassesWidth, glassesHeight);
        ctx.restore();
      }
    });

    document.getElementById('startBtn').addEventListener('click', startCamera);
  </script>
</body>
</html>
