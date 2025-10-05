<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Try-On</title>

  <!-- Bootstrap (optional for layout) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f7f9fb;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    video, canvas {
      border-radius: 10px;
      max-width: 90%;
    }
  </style>
</head>

<body>
  <h2 class="mb-3">Virtual Glasses Try-On</h2>
  <video id="video" autoplay muted playsinline width="640" height="480"></video>
  <canvas id="output" width="640" height="480"></canvas>

  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>

  <script>
    const video = document.getElementById("video");
    const canvas = document.getElementById("output");
    const ctx = canvas.getContext("2d");

    const glassesImg = new Image();
    glassesImg.src = "Images/frames/ashape-frame-removebg-preview.png";
    glassesImg.onload = () => console.log("✅ Glasses image loaded.");
    glassesImg.onerror = () => console.error("❌ Failed to load glasses image. Check the path:", glassesImg.src);

    // Initialize FaceMesh
    const faceMesh = new FaceMesh({
      locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
    });

    faceMesh.setOptions({
      maxNumFaces: 1,
      refineLandmarks: true,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5,
    });

    faceMesh.onResults((results) => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        const landmarks = results.multiFaceLandmarks[0];
        const leftEye = landmarks[33];
        const rightEye = landmarks[263];
        const nose = landmarks[1];

        // Debug logs
        console.log("👁️ Left Eye:", leftEye);
        console.log("👁️ Right Eye:", rightEye);
        console.log("👃 Nose:", nose);

        const eyeDistance = Math.sqrt(
          Math.pow(rightEye.x - leftEye.x, 2) + Math.pow(rightEye.y - leftEye.y, 2)
        );

        const glassesWidth = eyeDistance * 2.3 * canvas.width;
        const glassesHeight = glassesWidth * 0.45;

        const x = (leftEye.x + rightEye.x) / 2 * canvas.width - glassesWidth / 2;
        const y = nose.y * canvas.height - glassesHeight * 0.6;

        if (glassesImg.complete) {
          ctx.drawImage(glassesImg, x, y, glassesWidth, glassesHeight);
          console.log("🕶️ Glasses drawn at:", x, y);
        } else {
          console.warn("⚠️ Glasses image not yet loaded.");
        }
      } else {
        console.log("No face detected.");
      }
    });

    // Start camera
    const camera = new Camera(video, {
      onFrame: async () => {
        await faceMesh.send({ image: video });
      },
      width: 640,
      height: 480,
    });

    try {
      camera.start();
      console.log("📸 Camera started successfully.");
    } catch (error) {
      console.error("❌ Camera failed to start:", error);
    }
  </script>
</body>
</html>
