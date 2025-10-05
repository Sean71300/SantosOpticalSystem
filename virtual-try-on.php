<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Virtual Try-On</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <style>
    body {
      background-color: #f8f9fa;
      color: #333;
      text-align: center;
      padding: 30px;
    }
    #video {
      width: 100%;
      max-width: 480px;
      border-radius: 12px;
      background: #000;
    }
    .btn-start {
      margin-top: 20px;
      background-color: var(--bs-primary);
      color: white;
    }
    .btn-start:hover {
      background-color: #0d6efd;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="mb-3">
      <i class="bi bi-camera"></i> Virtual Try-On
    </h2>
    <p class="text-muted">Click the button below to start your camera.</p>

    <video id="video" autoplay playsinline></video>
    <br />
    <button id="startCamera" class="btn btn-start">Start Camera</button>
    <p id="status" class="mt-3 text-muted"></p>
  </div>

  <!-- ✅ Load Mediapipe FaceMesh & Drawing -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.4/drawing_utils.js"></script>

  <!-- ✅ ES Module Import for CameraUtils -->
  <script type="module">
    import { Camera } from "https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.4/camera_utils.js";

    const video = document.getElementById("video");
    const startButton = document.getElementById("startCamera");
    const status = document.getElementById("status");

    let cameraActive = false;

    startButton.addEventListener("click", async () => {
      if (cameraActive) return;

      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        cameraActive = true;
        status.textContent = "Camera started successfully!";
      } catch (error) {
        console.error(error);
        status.textContent = "Camera access denied or not available.";
        return;
      }

      const faceMesh = new FaceMesh({
        locateFile: (file) =>
          `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/${file}`,
      });

      faceMesh.setOptions({
        maxNumFaces: 1,
        refineLandmarks: true,
        minDetectionConfidence: 0.5,
        minTrackingConfidence: 0.5,
      });

      faceMesh.onResults((results) => {
        console.log("Face detected:", results.multiFaceLandmarks?.length || 0);
      });

      // ✅ Camera class imported correctly now
      const camera = new Camera(video, {
        onFrame: async () => {
          await faceMesh.send({ image: video });
        },
        width: 480,
        height: 360,
      });

      camera.start();
    });
  </script>
</body>
</html>
