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
      margin-bottom: 20px;
    }
    video {
      border-radius: 12px;
      width: 480px;
      height: 360px;
      object-fit: cover;
      background-color: #000;
    }
    canvas {
      position: absolute;
      top: 0;
      left: 0;
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
      margin: 5px;
    }
    .btn-secondary {
      background-color: #6c757d;
      border: none;
      padding: 10px 20px;
      margin: 5px;
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
    .camera-feed {
      display: none;
    }
    .camera-active {
      display: block;
    }
    .status-indicator {
      display: inline-block;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 8px;
    }
    .status-ready {
      background-color: #28a745;
    }
    .status-not-ready {
      background-color: #dc3545;
    }
    .status-loading {
      background-color: #ffc107;
    }
    .error-message {
      color: #dc3545;
      background-color: #f8d7da;
      padding: 10px;
      border-radius: 5px;
      margin: 10px 0;
      display: none;
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
      <li>Allow camera access when prompted</li>
      <li>Position your face in the center of the frame</li>
      <li>Look straight at the camera</li>
      <li>Click "Start Virtual Try-On" to begin</li>
    </ul>
  </div>

  <div class="video-container">
    <div id="cameraPlaceholder" style="width:480px; height:360px; background:#ddd; display:flex; align-items:center; justify-content:center; border-radius:12px;">
      <p>Camera feed will appear here</p>
    </div>
    <video id="video" autoplay muted class="camera-feed"></video>
    <canvas id="overlay" class="camera-feed"></canvas>
  </div>

  <div id="errorMessage" class="error-message"></div>

  <div class="mt-3">
    <button id="startButton" class="btn btn-primary">Start Virtual Try-On</button>
    <button id="stopButton" class="btn btn-secondary">Stop Camera</button>
    <button onclick="window.location.href='result.html'" class="btn btn-secondary">Back to Results</button>
  </div>

  <div id="status" class="status-box mt-3">
    <div>
      <span class="status-indicator" id="cameraStatus"></span>
      <span>Camera: <span id="cameraText">Not Ready</span></span>
    </div>
    <div>
      <span class="status-indicator" id="glassesStatus"></span>
      <span>Glasses: <span id="glassesText">Loading...</span></span>
    </div>
    <div>
      <span class="status-indicator" id="modelsStatus"></span>
      <span>Face Detection: <span id="modelsText">Not Loaded</span></span>
    </div>
  </div>

  <script>
    // DOM Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const context = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const stopButton = document.getElementById('stopButton');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const errorMessage = document.getElementById('errorMessage');
    
    // Status elements
    const cameraStatus = document.getElementById('cameraStatus');
    const cameraText = document.getElementById('cameraText');
    const glassesStatus = document.getElementById('glassesStatus');
    const glassesText = document.getElementById('glassesText');
    const modelsStatus = document.getElementById('modelsStatus');
    const modelsText = document.getElementById('modelsText');
    
    // Global variables
    let stream = null;
    let detectionInterval = null;
    
    // Update status indicator
    function updateStatus(indicator, textElement, status, text) {
      indicator.className = 'status-indicator';
      if (status === 'ready') {
        indicator.classList.add('status-ready');
      } else if (status === 'loading') {
        indicator.classList.add('status-loading');
      } else {
        indicator.classList.add('status-not-ready');
      }
      textElement.textContent = text;
    }
    
    // Initialize status indicators
    updateStatus(cameraStatus, cameraText, 'not-ready', 'Not Ready');
    updateStatus(glassesStatus, glassesText, 'loading', 'Loading...');
    updateStatus(modelsStatus, modelsText, 'not-ready', 'Not Loaded');
    
    // Glasses image
    const glassesImg = new Image();
    glassesImg.crossOrigin = "anonymous";
    // Using a placeholder glasses image - replace with your actual image path
    glassesImg.src = 'https://i.imgur.com/3Q3Zc2Y.png'; 
    
    glassesImg.onload = () => {
      updateStatus(glassesStatus, glassesText, 'ready', 'Loaded');
      console.log("Glasses image loaded successfully");
    };
    
    glassesImg.onerror = () => {
      updateStatus(glassesStatus, glassesText, 'not-ready', 'Failed to Load');
      console.error("Failed to load glasses image");
      showError("Failed to load glasses image. Please check the image path.");
    };

    // Show error message
    function showError(message) {
      errorMessage.textContent = message;
      errorMessage.style.display = 'block';
    }
    
    // Hide error message
    function hideError() {
      errorMessage.style.display = 'none';
    }

    // Access webcam
    async function startCamera() {
      hideError();
      
      try {
        // Stop any existing stream first
        if (stream) {
          stream.getTracks().forEach(track => track.stop());
        }
        
        updateStatus(cameraStatus, cameraText, 'loading', 'Initializing...');
        
        // Request camera access
        stream = await navigator.mediaDevices.getUserMedia({
          video: { 
            width: { ideal: 480 },
            height: { ideal: 360 },
            facingMode: 'user' // Use front camera
          }
        });
        
        video.srcObject = stream;
        
        // Wait for video to be ready
        await new Promise((resolve) => {
          video.onloadedmetadata = () => {
            video.play().then(() => {
              // Set canvas dimensions to match video
              canvas.width = video.videoWidth;
              canvas.height = video.videoHeight;
              
              // Show camera feed and hide placeholder
              cameraPlaceholder.style.display = 'none';
              video.classList.add('camera-active');
              canvas.classList.add('camera-active');
              
              updateStatus(cameraStatus, cameraText, 'ready', 'Ready');
              resolve();
            });
          };
        });
        
        return true;
      } catch (error) {
        console.error("Camera access error:", error);
        updateStatus(cameraStatus, cameraText, 'not-ready', 'Access Failed');
        
        let errorMsg = 'Camera access was blocked or failed. ';
        if (error.name === 'NotFoundError' || error.name === 'OverconstrainedError') {
          errorMsg += 'No camera found.';
        } else if (error.name === 'NotAllowedError') {
          errorMsg += 'Please allow camera access and try again.';
        } else {
          errorMsg += 'Please check your camera connection.';
        }
        
        showError(errorMsg);
        return false;
      }
    }

    // Stop camera
    function stopCamera() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }
      
      if (detectionInterval) {
        clearInterval(detectionInterval);
        detectionInterval = null;
      }
      
      // Hide camera feed and show placeholder
      video.classList.remove('camera-active');
      canvas.classList.remove('camera-active');
      cameraPlaceholder.style.display = 'flex';
      
      updateStatus(cameraStatus, cameraText, 'not-ready', 'Stopped');
      startButton.disabled = false;
      startButton.textContent = "Start Virtual Try-On";
      
      // Clear canvas
      context.clearRect(0, 0, canvas.width, canvas.height);
    }

    // Load face-api.js models
    async function loadModels() {
      try {
        updateStatus(modelsStatus, modelsText, 'loading', 'Loading...');
        
        await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js/models');
        
        updateStatus(modelsStatus, modelsText, 'ready', 'Loaded');
        console.log("Face detection models loaded successfully");
        return true;
      } catch (error) {
        console.error("Error loading face detection models:", error);
        updateStatus(modelsStatus, modelsText, 'not-ready', 'Failed to Load');
        showError("Failed to load face detection models. Please check your internet connection.");
        return false;
      }
    }

    // Start virtual try-on
    async function startVirtualTryOn() {
      if (!stream) {
        showError("Camera is not active. Please start the camera first.");
        return;
      }
      
      const displaySize = { width: video.videoWidth, height: video.videoHeight };
      
      // Start face detection
      detectionInterval = setInterval(async () => {
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

            // Adjusted for better fit
            const glassesWidth = eyeDistance * 1.9;
            const glassesHeight = glassesWidth * (glassesImg.height / glassesImg.width) * 1.6;

            const centerX = (leftEye[0].x + rightEye[3].x) / 2;
            const centerY = (leftEye[0].y + rightEye[3].y) / 2;

            const offsetY = glassesHeight * 0.4;

            const x = centerX - glassesWidth / 2;
            const y = centerY - offsetY;

            context.drawImage(glassesImg, x, y, glassesWidth, glassesHeight);
          }
        } catch (error) {
          console.error("Error during face detection:", error);
        }
      }, 100);
    }

    // Event listeners
    startButton.addEventListener('click', async () => {
      startButton.disabled = true;
      startButton.textContent = "Initializing...";
      hideError();
      
      try {
        // Load models first
        const modelsLoaded = await loadModels();
        if (!modelsLoaded) {
          startButton.disabled = false;
          startButton.textContent = "Start Virtual Try-On";
          return;
        }
        
        // Start camera
        const cameraStarted = await startCamera();
        if (!cameraStarted) {
          startButton.disabled = false;
          startButton.textContent = "Start Virtual Try-On";
          return;
        }
        
        // Start virtual try-on
        startVirtualTryOn();
        startButton.textContent = "Virtual Try-On Active";
      } catch (error) {
        console.error("Error starting virtual try-on:", error);
        startButton.disabled = false;
        startButton.textContent = "Start Virtual Try-On";
        showError("An unexpected error occurred. Please try again.");
      }
    });
    
    stopButton.addEventListener('click', stopCamera);
    
    // Initialize on page load
    window.addEventListener('load', () => {
      console.log("Page loaded, ready to initialize virtual try-on");
    });
  </script>
</body>
</html>