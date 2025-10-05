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

  <!-- Only FaceMesh + Drawing -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4/face_mesh.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.4/drawing_utils.js"></script>

  <script>
    const video   = document.getElementById('video');
    const canvas  = document.getElementById('overlay');
    const ctx     = canvas.getContext('2d');
    const button  = document.getElementById('startCamera');
    const status  = document.getElementById('status');
    let running   = false;

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
        if(!running) return;
        await faceMesh.send({image:video});
        requestAnimationFrame(processFrame);
      }
      processFrame();
    });

    function drawResults(results){
      ctx.clearRect(0,0,canvas.width,canvas.height);
      canvas.width  = video.videoWidth;
      canvas.height = video.videoHeight;

      if(results.multiFaceLandmarks){
        for(const landmarks of results.multiFaceLandmarks){
          drawConnectors(ctx,landmarks,FACE_MESH_TESSELATION,{color:'#C0C0C0',lineWidth:1});
          drawConnectors(ctx,landmarks,FACE_MESH_RIGHT_EYE,{color:'blue'});
          drawConnectors(ctx,landmarks,FACE_MESH_LEFT_EYE,{color:'blue'});
        }
        status.textContent = "Face detected!";
      }else{
        status.textContent = "No face detected.";
      }
    }
  </script>
</body>
</html>
