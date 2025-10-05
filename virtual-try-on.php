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
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .video-container {
      position: relative;
      display: inline-block;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      border-radius: 12px;
      overflow: hidden;
    }
    video {
      border-radius: 12px;
      width: 480px;
      height: 360px;
      object-fit: cover;
      transform: scaleX(-1); /* Mirror the video for more natural feel */
    }
    canvas {
      position: absolute;
      top: 0;
      left: 0;
      transform: scaleX(-1); /* Mirror the canvas to match video */
    }
    .status-box {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      display: inline-block;
      text-align: left;
      margin-top: 15px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      width: 300px;
    }
    .btn-primary {
      background-color: #007bff;
      border: none;
      padding: 10px 20px;
      font-weight: 600;
    }
    .btn-secondary {
      background-color: #6c757d;
      border: none;
      padding: 10px 20px;
    }
    h2 {
      color: #343a40;
      margin-bottom: 10px;
    }
    p {
      color: #6c757d;
      margin-bottom: 25px;
    }
    .instructions {
      background-color: #e9ecef;
      padding: 15px;
      border-radius: 8px;
      margin: 20px auto;
      max-width: 500px;
      text-align: left;
    }
    .instructions h5 {
      margin-top: 0;
      color: #495057;
    }
    .instructions ul {
      padding-left: 20px;
      margin-bottom: 0;
    }
    .instructions li {
      margin-bottom: 5px;
    }
  </style>
</head>
<body>
  <h2>Virtual Try-On for Unknown Face</h2>
  <p>Align your face within the frame to see how the glasses look on you.</p>

  <div class="instructions">
    <h5>How to use:</h5>
    <ul>
      <li>Make sure you're in a well-lit area</li>
      <li>Position your face in the center of the frame</li>
      <li>Look straight at the camera</li>
      <li>Click "Start Virtual Try-On" to begin</li>
    </ul>
  </div>

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
    <div><input type="checkbox" id="modelsLoaded" disabled> Face detection models loaded.</div>
  </div>

  <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const context = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const glassesLoaded = document.getElementById('glassesLoaded');
    const cameraReady = document.getElementById('cameraReady');
    const modelsLoaded = document.getElementById('modelsLoaded');

    const glassesImg = new Image();
    glassesImg.src = 'https://i.imgur.com/3Q3Zc2Y.png'; // Placeholder glasses image
    glassesImg.onload = () => {
      glassesLoaded.checked = true;
      console.log("Glasses image loaded successfully");
    };
    glassesImg.onerror = () => {
      console.error("Failed to load glasses image");
      alert("Failed to load glasses image. Please check the image path.");
    };

    // Access webcam
    async function startCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { width: 480, height: 360 }
        });
        video.srcObject = stream;
        cameraReady.checked = true;
        
        // Wait for video to be ready
        return new Promise((resolve) => {
          video.onloadedmetadata = () => {
            resolve();
          };
        });
      } catch (error) {
        console.error("Camera access error:", error);
        alert('Camera access was blocked or failed. Please allow camera access and try again.');
      }
    }

    // Load face-api.js models
    async function loadModels() {
      try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
        modelsLoaded.checked = true;
        console.log("Face detection models loaded successfully");
      } catch (error) {
        console.error("Error loading face detection models:", error);
        alert("Failed to load face detection models. Please check your internet connection.");
      }
    }

    async function startVirtualTryOn() {
      const displaySize = { width: video.videoWidth, height: video.videoHeight };
      canvas.width = displaySize.width;
      canvas.height = displaySize.height;
      
      console.log("Starting virtual try-on with display size:", displaySize);

      setInterval(async () => {
        try {
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

            // Adjusted for better fit - reduced width, significantly increased height
            const glassesWidth = eyeDistance * 1.9;  // Slightly wider than before
            const glassesHeight = glassesWidth * (glassesImg.height / glassesImg.width) * 1.6; // Increased height multiplier

            const centerX = (leftEye[0].x + rightEye[3].x) / 2;
            const centerY = (leftEye[0].y + rightEye[3].y) / 2;

            // Adjusted Y offset for better positioning
            const offsetY = glassesHeight * 0.4; // Slightly adjusted

            const x = centerX - glassesWidth / 2;
            const y = centerY - offsetY;

            context.drawImage(glassesImg, x, y, glassesWidth, glassesHeight);
            
            // Optional: Draw eye points for debugging
            // context.fillStyle = 'red';
            // leftEye.forEach(point => context.fillRect(point.x-2, point.y-2, 4, 4));
            // rightEye.forEach(point => context.fillRect(point.x-2, point.y-2, 4, 4));
          }
        } catch (error) {
          console.error("Error during face detection:", error);
        }
      }, 100);
    }

    startButton.addEventListener('click', async () => {
      startButton.disabled = true;
      startButton.textContent = "Loading...";
      
      try {
        await loadModels();
        await startCamera();
        startVirtualTryOn();
        startButton.textContent = "Virtual Try-On Active";
      } catch (error) {
        console.error("Error starting virtual try-on:", error);
        startButton.disabled = false;
        startButton.textContent = "Start Virtual Try-On";
      }
    });
  </script>
</body>
</html>