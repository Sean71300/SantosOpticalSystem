<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Virtual Try-On</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background:#f8f9fa;
      text-align:center;
      padding:30px;
    }
    #video {
      width:100%;
      max-width:480px;
      border-radius:12px;
      background:#000;
    }
    #overlay {
      position:absolute;
      left:50%;
      top:50%;
      transform:translate(-50%,-50%);
      width:480px;
      height:360px;
      pointer-events:none;
    }
    .btn-start {
      margin-top:20px;
      background-color:var(--bs-primary);
      color:#fff;
    }
  </style>
</head>
<body>
  <div class="container position-relative">
    <h2 class="mb-3"><i class="bi bi-camera"></i> Virtual Try-On</h2>
    <p class="text-muted">Click below to start your camera.</p>
    <video id="video" autoplay playsinline></video>
    <canvas id="overlay"></canvas>
    <br>
    <button id="startCamera" class="btn btn-start">Start Camera</button>
    <p id="status" class="mt-3 text-muted"></p>
  </div>

  <!-- Mediapipe FaceMesh + Drawing -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.4/drawing_utils.js"></script>

  <script>
    const video   = document.getElementById('video');
    const canvas  = document.getElementById('overlay');
    const ctx     = canvas.getContext('2d');
    const button  = document.getElementById('startCamera');
    const status  = document.getElementById('status');

    // 🕶️ Load your glasses image (place glasses.png in the same folder)
    const glassesImg = new Image();
    glassesImg.src = "Images/frames/ashape-frame-removebg-preview.png";

    let running = false;

    button.addEventListener('click', async ()=>{
      if(running) return;
      running = true;

      try{
        const stream = await navigator.mediaDevices.getUserMedia({video:true});
        video.srcObject = stream;
        await video.play();
        status.textContent = "Camera started!";
      }catch(e){
        console.error(e);
        status.textContent = "Cannot access camera.";
        return;
      }

      const faceMesh = new FaceMesh({
        locateFile: (file)=>`https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/${file}`
      });

      faceMesh.setOptions({
        maxNumFaces:1,
        refineLandmarks:true,
        minDetectionConfidence:0.5,
        minTrackingConfidence:0.5
      });

      faceMesh.onResults(drawResults);

      async function processFrame(){
        await faceMesh.send({image:video});
        requestAnimationFrame(processFrame);
      }
      processFrame();
    });

    function drawResults(results){
      ctx.clearRect(0,0,canvas.width,canvas.height);
      canvas.width  = video.videoWidth;
      canvas.height = video.videoHeight;

      if(results.multiFaceLandmarks && results.multiFaceLandmarks.length>0){
        const landmarks = results.multiFaceLandmarks[0];

        // 🧭 Identify key eye landmarks
        const leftEye = landmarks[33];   // right eye outer corner (from user's perspective)
        const rightEye = landmarks[263]; // left eye outer corner

        const eyeCenterX = (leftEye.x + rightEye.x) / 2 * canvas.width;
        const eyeCenterY = (leftEye.y + rightEye.y) / 2 * canvas.height;

        const eyeDistance = Math.hypot(
          (rightEye.x - leftEye.x) * canvas.width,
          (rightEye.y - leftEye.y) * canvas.height
        );

        const glassesWidth = eyeDistance * 2.2; // slightly wider than eyes
        const glassesHeight = glassesWidth * 0.4; // maintain proportion

        const angle = Math.atan2(
          (rightEye.y - leftEye.y) * canvas.height,
          (rightEye.x - leftEye.x) * canvas.width
        );

        ctx.save();
        ctx.translate(eyeCenterX, eyeCenterY);
        ctx.rotate(angle);
        ctx.drawImage(glassesImg, -glassesWidth/2, -glassesHeight/2, glassesWidth, glassesHeight);
        ctx.restore();

        status.textContent = "Face detected! Glasses applied.";
      }else{
        status.textContent = "No face detected.";
      }
    }
  </script>
</body>
</html>
