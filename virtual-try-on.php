<?php
$frame = isset($_GET['frame']) ? htmlspecialchars($_GET['frame']) : 'ashape';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Virtual Try-On</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    background-color: #f8f9fa;
    overflow: hidden;
    font-family: 'Poppins', sans-serif;
  }
  .tryon-container {
    position: relative;
    width: 100%;
    height: 100vh;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  video, canvas {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .top-bar {
    position: absolute;
    top: 0;
    width: 100%;
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    z-index: 10;
  }
  .control-buttons {
    position: absolute;
    bottom: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 1rem;
    z-index: 10;
  }
  .btn-custom {
    border-radius: 50px;
    padding: 0.6rem 1.5rem;
    font-weight: 500;
  }
</style>
</head>
<body>

<div class="tryon-container">
  <div class="top-bar">
    <h5 class="m-0">👓 Virtual Try-On Demo</h5>
    <a href="result.php" class="btn btn-outline-light btn-sm">← Back to Results</a>
  </div>

  <video id="videoInput" autoplay muted playsinline></video>
  <canvas id="outputCanvas"></canvas>

  <div class="control-buttons">
    <button id="changeFrame" class="btn btn-light btn-custom">Change Frame</button>
    <button id="captureBtn" class="btn btn-primary btn-custom">Capture Look</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1675469200/camera_utils.min.js"></script>
<script>
const videoElement = document.getElementById('videoInput');
const canvasElement = document.getElementById('outputCanvas');
const canvasCtx = canvasElement.getContext('2d');

let glassesLoaded = false;
const glassesImg = new Image();
glassesImg.src = "Images/frames/<?php echo $frame; ?>-frame-removebg-preview.png";
glassesImg.onload = () => { 
  glassesLoaded = true; 
  console.log("✅ Glasses image loaded:", glassesImg.src);
};

// Initialize FaceMesh
const faceMesh = new FaceMesh({
  locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/${file}`
});

faceMesh.setOptions({
  maxNumFaces: 1,
  refineLandmarks: true,
  minDetectionConfidence: 0.5,
  minTrackingConfidence: 0.5
});

faceMesh.onResults(onResults);

async function onResults(results) {
  canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
  canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

  if (!results.multiFaceLandmarks || !glassesLoaded) return;

  const landmarks = results.multiFaceLandmarks[0];
  const leftEye = landmarks[33];
  const rightEye = landmarks[263];

  const centerX = (leftEye.x + rightEye.x) / 2 * canvasElement.width;
  const centerY = (leftEye.y + rightEye.y) / 2 * canvasElement.height;
  const width = Math.abs(rightEye.x - leftEye.x) * canvasElement.width * 2;
  const height = width / 3;

  canvasCtx.save();
  canvasCtx.translate(centerX, centerY);
  canvasCtx.drawImage(glassesImg, -width/2, -height/2, width, height);
  canvasCtx.restore();
}

// Camera Setup
let camera;
async function startCamera() {
  try {
    camera = new Camera(videoElement, {
      onFrame: async () => await faceMesh.send({ image: videoElement }),
      width: 1280,
      height: 720
    });
    await camera.start();
    console.log("✅ Camera started");
  } catch (error) {
    console.error("❌ Camera failed:", error);
    alert("Camera access was blocked or failed.");
  }
}
startCamera();

// Capture Image Button
document.getElementById("captureBtn").addEventListener("click", () => {
  const dataURL = canvasElement.toDataURL("image/png");
  const link = document.createElement('a');
  link.download = 'virtual-tryon.png';
  link.href = dataURL;
  link.click();
});

// Change Frame Button (future use)
document.getElementById("changeFrame").addEventListener("click", () => {
  alert("Frame changing feature coming soon!");
});
</script>
</body>
</html>
