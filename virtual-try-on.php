<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Virtual Try-On for Unknown Face</title>
  <script defer src="https://cdn.jsdelivr.net/npm/face-api.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      text-align: center;
      padding: 40px;
    }
    .video-container {
      position: relative;
      display: inline-block;
    }
    video {
      border-radius: 10px;
      width: 480px;
      height: 360px;
      object-fit: cover;
    }
    canvas {
      position: absolute;
      top: 0;
      left: 0;
    }
    .status-box {
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      display: inline-block;
      text-align: left;
      margin-top: 10px;
    }
    .btn-primary {
      background-color: #007bff;
      border: none;
    }
  </style>
</head>
<body>
  <h2>Virtual Try-On for Unknown Face</h2>
  <p>Align your face within the frame to see how the glasses look on you.</p>

  <div class="video-container">
    <video id="video" autoplay muted></video>
    <canvas id="overlay"></canvas>
  </div>

  <div class="mt-3">
    <button id="startButton" class="btn btn-primary">Start Virtual Try-On</button>
    <button onclick="window.location.href='result.html'" class="btn btn-secondary">Back to Results</button>
  </div>

  <div id="status" class="status-box mt-3">
    <div><input type="checkbox" id="glassesLoaded" disabled> Glasses image loaded.</div>
    <div><input type="checkbox" id="cameraReady" disabled> Camera ready.</div>
  </div>

  <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const context = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const glassesLoaded = document.getElementById('glassesLoaded');
    const cameraReady = document.getElementById('cameraReady');

    const glassesImg = new Image();
    glassesImg.src = 'Images/frames/ashape-frame-removebg-preview.png; // your glasses image
    glassesImg.onload = () => (glassesLoaded.checked = true);

    // Access webcam
    async function startCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { width: 480, height: 360 }
        });
        video.srcObject = stream;
        cameraReady.checked = true;
      } catch (error) {
        alert('Camera access was blocked or failed.');
      }
    }

    // Load face-api.js models
    async function loadModels() {
      await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
      await faceapi.nets.faceLandmark68TinyNet.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
    }

    async function startVirtualTryOn() {
      const displaySize = { width: video.videoWidth, height: video.videoHeight };
      faceapi.matchDimensions(canvas, displaySize);

      setInterval(async () => {
        const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
          .withFaceLandmarks(true);

        context.clearRect(0, 0, canvas.width, canvas.height);

        if (detections) {
          const resizedDetections = faceapi.resizeResults(detections, displaySize);
          const landmarks = resizedDetections.landmarks;
          const leftEye = landmarks.getLeftEye();
          const rightEye = landmarks.getRightEye();

          const eyeDistance = Math.hypot(
            rightEye[0].x - leftEye[3].x,
            rightEye[0].y - leftEye[3].y
          );

          // adjust multiplier for better fit
          const glassesWidth = eyeDistance * 2.3;  
          const glassesHeight = glassesWidth * (glassesImg.height / glassesImg.width);

          const centerX = (leftEye[0].x + rightEye[3].x) / 2;
          const centerY = (leftEye[0].y + rightEye[3].y) / 2;

          // adjust Y offset to bring glasses lower
          const offsetY = glassesHeight * 0.45;

          const x = centerX - glassesWidth / 2;
          const y = centerY - offsetY;

          context.drawImage(glassesImg, x, y, glassesWidth, glassesHeight);
        }
      }, 100);
    }

    startButton.addEventListener('click', async () => {
      await loadModels();
      await startCamera();
      startVirtualTryOn();
    });
  </script>
</body>
</html>
