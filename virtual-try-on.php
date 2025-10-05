<?php
// virtual-try-on.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Virtual Glasses Try-On — Realistic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #1a73e8;
      --secondary: #6c757d;
      --success: #28a745;
      --dark: #222;
      --light: #f8f9fa;
      --border: #dee2e6;
    }
    * { box-sizing: border-box; }
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--dark);
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    .app-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 15px;
    }
    .header { text-align: center; margin-bottom: 20px; color: white; }
    .header h1 { font-weight: 700; font-size: 2.2rem; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .header p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 0; }
    .main-content { display: flex; flex-direction: column; gap: 20px; }
    @media (min-width: 768px) { .main-content { flex-direction: row; align-items: flex-start; } }
    .camera-section { flex: 1; min-width: 0; }
    .controls-section { flex: 1; min-width: 0; }
    .camera-container {
      position: relative; background: black; border-radius: 20px; overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3); aspect-ratio: 4/3;
    }
    video, canvas { width: 100%; height: 100%; object-fit: cover; display: block; }
    #outputCanvas { position: absolute; top: 0; left: 0; z-index: 5; }
    #inputVideo { position: absolute; top: 0; left: 0; z-index: 1; opacity: 0; } /* video under canvas */
    .camera-overlay { position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5);
      display:flex; align-items:center; justify-content:center; flex-direction:column; color:white; z-index: 10; }
    .card { background: white; border-radius: 20px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
    .card-header { background: linear-gradient(135deg, var(--primary), #1558b0); color:white; border:none; padding:15px 20px; font-weight:600; }
    .card-body { padding:20px; }
    .btn-primary { background: linear-gradient(135deg, var(--primary), #1558b0); border:none; border-radius:12px; padding:12px 24px; font-weight:600; font-size:16px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(26,115,232,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(26,115,232,0.4); }
    .btn-outline-primary { border: 2px solid var(--primary); color: var(--primary); border-radius:12px; padding:10px 20px; font-weight:600; transition: all 0.3s ease; }
    .frame-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)); gap:10px; margin-top:10px; }
    .frame-btn { background: white; border:2px solid var(--border); border-radius:12px; padding:8px; cursor:pointer; transition: all 0.3s ease; display:flex; flex-direction:column; align-items:center; gap:5px; }
    .frame-btn.active { border-color: var(--primary); background:#e3f2fd; transform: scale(1.05); box-shadow: 0 6px 15px rgba(26,115,232,0.2); }
    .frame-img { width: 45px; height: 25px; object-fit: contain; }
    .color-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(40px, 1fr)); gap:8px; margin-top:10px; }
    .color-btn { width:40px; height:40px; border:3px solid var(--border); border-radius:50%; cursor:pointer; transition: all 0.3s ease; position:relative; }
    .color-btn.active { border-color: var(--primary); transform: scale(1.15); box-shadow: 0 6px 15px rgba(0,0,0,0.3); }
    .material-controls { display:flex; gap:10px; margin-top:10px; }
    .position-controls { display:flex; align-items:center; justify-content:center; gap:15px; margin-top:10px; }
    .position-btn { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; box-shadow:0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; }
    .status-indicator { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:20px; font-weight:600; font-size:14px; margin:10px 0; }
    .status-online { background: #d4edda; color:#155724; border:2px solid #c3e6cb; }
    .status-offline { background: #f8d7da; color:#721c24; border:2px solid #f5c6cb; }
    .status-loading { background: #fff3cd; color:#856404; border:2px solid #ffeaa7; }
    .loading-spinner { width:30px; height:30px; border:3px solid #f3f3f3; border-top:3px solid var(--primary); border-radius:50%; animation: spin 1s linear infinite; margin:10px auto; }
    @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
    .action-buttons { display:flex; gap:12px; flex-wrap:wrap; }
    .action-buttons .btn { flex:1; min-width:120px; }
    .mobile-tips { background: linear-gradient(135deg, #667eea, #764ba2); color:white; border-radius:15px; padding:15px; margin-top:20px; text-align:center; }
    @media (max-width: 767px) { .frame-grid { grid-template-columns: repeat(4, 1fr); } .color-grid { grid-template-columns: repeat(6, 1fr);} .material-controls { flex-direction:column; } }
  </style>
</head>

<body>
  <div class="app-container">
    <div class="header">
      <h1>👓 Virtual Glasses Try-On</h1>
      <p>Find your perfect frame and color in real-time — realistic rendering enabled</p>
    </div>

    <div class="main-content">
      <div class="camera-section">
        <div class="camera-container">
          <video id="inputVideo" autoplay muted playsinline></video>
          <canvas id="outputCanvas"></canvas>
          <div class="camera-overlay d-none" id="cameraOverlay">
            <div class="loading-spinner"></div>
            <p class="mt-3">Starting camera...</p>
          </div>
        </div>

        <div class="status-indicator status-offline" id="statusIndicator">
          <div class="status-dot"></div>
          <span id="statusText">Camera is off</span>
        </div>

        <div class="card">
          <div class="card-header">Camera Controls</div>
          <div class="card-body">
            <div class="action-buttons">
              <button id="startBtn" class="btn btn-primary"><i class="bi bi-camera-video me-2"></i>Start Camera</button>
              <button id="calibrateBtn" class="btn btn-outline-primary d-none"><i class="bi bi-arrow-clockwise me-2"></i>Recalibrate</button>
              <button id="captureBtn" class="btn btn-outline-primary"><i class="bi bi-camera me-2"></i>Capture</button>
            </div>
          </div>
        </div>
      </div>

      <div class="controls-section">
        <div class="card">
          <div class="card-header">Frame Styles</div>
          <div class="card-body">
            <div class="frame-grid">
              <button class="frame-btn active" data-frame="A-TRIANGLE">
                <img src="Images/frames/ashape-frame-removebg-preview.png" alt="A-Shape" class="frame-img">
                <span class="frame-label">A-Shape</span>
              </button>
              <button class="frame-btn" data-frame="V-TRIANGLE">
                <img src="Images/frames/vshape-frame-removebg-preview.png" alt="V-Shape" class="frame-img">
                <span class="frame-label">V-Shape</span>
              </button>
              <button class="frame-btn" data-frame="ROUND">
                <img src="Images/frames/round-frame-removebg-preview.png" alt="Round" class="frame-img">
                <span class="frame-label">Round</span>
              </button>
              <button class="frame-btn" data-frame="SQUARE">
                <img src="Images/frames/square-frame-removebg-preview.png" alt="Square" class="frame-img">
                <span class="frame-label">Square</span>
              </button>
              <button class="frame-btn" data-frame="RECTANGLE">
                <img src="Images/frames/rectangle-frame-removebg-preview.png" alt="Rectangle" class="frame-img">
                <span class="frame-label">Rectangle</span>
              </button>
              <button class="frame-btn" data-frame="OBLONG">
                <img src="Images/frames/oblong-frame-removebg-preview.png" alt="Oblong" class="frame-img">
                <span class="frame-label">Oblong</span>
              </button>
              <button class="frame-btn" data-frame="DIAMOND">
                <img src="Images/frames/diamond-frame-removebg-preview.png" alt="Diamond" class="frame-img">
                <span class="frame-label">Diamond</span>
              </button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">Frame Colors & Materials</div>
          <div class="card-body">
            <div class="color-group">
              <div class="color-group-title">Classic Colors</div>
              <div class="color-grid">
                <div class="color-option">
                  <div class="color-btn active" style="background: #2c2c2c;" data-color="#2c2c2c" data-color-name="Black"></div>
                  <div class="color-label">Black</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #6c757d;" data-color="#6c757d" data-color-name="Gray"></div>
                  <div class="color-label">Gray</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #8B4513;" data-color="#8B4513" data-color-name="Tortoise"></div>
                  <div class="color-label">Tortoise</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #A52A2A;" data-color="#A52A2A" data-color-name="Brown"></div>
                  <div class="color-label">Brown</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: linear-gradient(45deg, #c0c0c0, #e8e8e8);" data-color="#c0c0c0" data-color-name="Silver"></div>
                  <div class="color-label">Silver</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: linear-gradient(45deg, #daa520, #b8860b);" data-color="#daa520" data-color-name="Gold"></div>
                  <div class="color-label">Gold</div>
                </div>
              </div>
            </div>

            <div class="color-group">
              <div class="color-group-title">Vibrant Colors</div>
              <div class="color-grid">
                <div class="color-option">
                  <div class="color-btn" style="background: #1a73e8;" data-color="#1a73e8" data-color-name="Blue"></div>
                  <div class="color-label">Blue</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #dc3545;" data-color="#dc3545" data-color-name="Red"></div>
                  <div class="color-label">Red</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #28a745;" data-color="#28a745" data-color-name="Green"></div>
                  <div class="color-label">Green</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #ffc107;" data-color="#ffc107" data-color-name="Yellow"></div>
                  <div class="color-label">Yellow</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #6f42c1;" data-color="#6f42c1" data-color-name="Purple"></div>
                  <div class="color-label">Purple</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #e83e8c;" data-color="#e83e8c" data-color-name="Pink"></div>
                  <div class="color-label">Pink</div>
                </div>
              </div>
            </div>

            <div class="control-group">
              <div class="control-label"><span>Material Effect</span></div>
              <div class="material-controls">
                <button class="material-btn active" data-material="Plain">Plain</button>
                <button class="material-btn" data-material="Pattern">Pattern</button>
                <button class="material-btn" data-material="Matte">Matte</button>
                <button class="material-btn" data-material="Glossy">Glossy</button>
                <button class="material-btn" data-material="Metallic">Metallic</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">Adjust Fit</div>
          <div class="card-body">
            <div class="control-group">
              <div class="control-label">
                <span>Frame Size</span>
                <span class="control-value" id="sizeValue">2.4x</span>
              </div>
              <input type="range" class="form-range" id="sizeSlider" min="1.6" max="3.2" step="0.1" value="2.4">
            </div>

            <div class="control-group">
              <div class="control-label">
                <span>Frame Height</span>
                <span class="control-value" id="heightValue">70%</span>
              </div>
              <div class="position-controls">
                <button class="btn btn-outline-primary position-btn" id="heightDown"><i class="bi bi-dash"></i></button>
                <span>Shorter - Taller</span>
                <button class="btn btn-outline-primary position-btn" id="heightUp"><i class="bi bi-plus"></i></button>
              </div>
            </div>

            <div class="control-group">
              <div class="control-label">
                <span>Vertical Position</span>
                <span class="control-value" id="positionValue">0px</span>
              </div>
              <div class="position-controls">
                <button class="btn btn-outline-primary position-btn" id="positionDown"><i class="bi bi-arrow-down"></i></button>
                <span>Lower - Higher</span>
                <button class="btn btn-outline-primary position-btn" id="positionUp"><i class="bi bi-arrow-up"></i></button>
              </div>
            </div>
          </div>
        </div>

        <?php if (preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'])): ?>
        <div class="mobile-tips">
          <h6>📱 Mobile Tips</h6>
          <ul>
            <li>Ensure good lighting for best results</li>
            <li>Hold device steady at eye level</li>
            <li>Keep face centered in frame</li>
            <li>Close other apps for better performance</li>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Mediapipe & Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.min.js"></script>

  <script>
    // --- DOM references ---
    const videoElement = document.getElementById('inputVideo');
    const canvasElement = document.getElementById('outputCanvas');
    const canvasCtx = canvasElement.getContext('2d');

    const startBtn = document.getElementById('startBtn');
    const calibrateBtn = document.getElementById('calibrateBtn');
    const captureBtn = document.getElementById('captureBtn');
    const cameraOverlay = document.getElementById('cameraOverlay');
    const statusIndicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');

    const sizeSlider = document.getElementById('sizeSlider');
    const sizeValue = document.getElementById('sizeValue');
    const heightDown = document.getElementById('heightDown');
    const heightUp = document.getElementById('heightUp');
    const heightValue = document.getElementById('heightValue');
    const positionDown = document.getElementById('positionDown');
    const positionUp = document.getElementById('positionUp');
    const positionValue = document.getElementById('positionValue');

    const frameButtons = document.querySelectorAll('.frame-btn');
    const colorButtons = document.querySelectorAll('.color-btn');
    const materialButtons = document.querySelectorAll('.material-btn');

    // --- Frames map (your existing paths) ---
    const FRAMES = {
      'SQUARE': 'Images/frames/square-frame-removebg-preview.png',
      'ROUND': 'Images/frames/round-frame-removebg-preview.png',
      'OBLONG': 'Images/frames/oblong-frame-removebg-preview.png',
      'DIAMOND': 'Images/frames/diamond-frame-removebg-preview.png',
      'V-TRIANGLE': 'Images/frames/vshape-frame-removebg-preview.png',
      'A-TRIANGLE': 'Images/frames/ashape-frame-removebg-preview.png',
      'RECTANGLE': 'Images/frames/rectangle-frame-removebg-preview.png'
    };

    // --- State ---
    let camera = null;
    let faceMesh = null;
    let isProcessing = false;
    let frameCount = 0;
    let faceTrackingActive = false;
    let angleOffset = 0;
    let isCalibrated = false;
    let glassesSizeMultiplier = 2.4;
    let glassesHeightRatio = 0.7;
    let verticalOffset = 0;
    let currentFrame = 'A-TRIANGLE';
    let currentColor = '#2c2c2c';
    let currentColorName = 'Black';
    let currentMaterial = 'Plain';

    // --- Preload frame images (keep original behavior) ---
    const glassesImages = {};
    let glassesLoaded = false;
    let loadedImagesCount = 0;
    const totalImages = Object.keys(FRAMES).length;
    Object.entries(FRAMES).forEach(([frameType, path]) => {
      const img = new Image();
      img.crossOrigin = "anonymous";
      img.src = path;
      img.onload = () => {
        loadedImagesCount++;
        glassesImages[frameType] = img;
        if (loadedImagesCount === totalImages) {
          glassesLoaded = true;
          console.log("✅ All frame images loaded successfully");
        }
      };
      img.onerror = () => {
        console.error(`❌ Failed to load frame: ${frameType} (${path})`);
        loadedImagesCount++;
      };
    });

    // --- Utility: update status indicator ---
    function updateStatus(status, type) {
      statusText.textContent = status;
      statusIndicator.className = `status-indicator status-${type}`;
    }

    // --- Utility: resize canvas to container ---
    function resizeCanvasToDisplay() {
      const container = canvasElement.parentElement;
      canvasElement.width = container.clientWidth;
      canvasElement.height = container.clientHeight;
    }

    // --- Simple clamp ---
    function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

    // --- Cache for temporary canvases / textures (to avoid regen) ---
    const textureCache = new Map();

    function createMaterialTexture(width, height, baseColor, materialType) {
      const cacheKey = `${baseColor}-${materialType}-${width}x${height}`;
      if (textureCache.has(cacheKey)) return textureCache.get(cacheKey);

      const c = document.createElement('canvas');
      c.width = width;
      c.height = height;
      const ctx = c.getContext('2d');

      // base gradient
      const grad = ctx.createLinearGradient(0, 0, 0, height);
      const hex = baseColor.replace('#', '');
      const r = parseInt(hex.substr(0,2),16);
      const g = parseInt(hex.substr(2,2),16);
      const b = parseInt(hex.substr(4,2),16);

      function rgba(r,g,b,a){ return `rgba(${r},${g},${b},${a})`; }
      grad.addColorStop(0, rgba(Math.max(0,r-20), Math.max(0,g-20), Math.max(0,b-20), 1));
      grad.addColorStop(0.5, baseColor);
      grad.addColorStop(1, rgba(Math.min(255,r+20), Math.min(255,g+20), Math.min(255,b+20), 1));
      ctx.fillStyle = grad;
      ctx.fillRect(0,0,width,height);

      // subtle noise
      const id = ctx.getImageData(0,0,width,height);
      const data = id.data;
      // sparse noise for perf
      for(let i=0;i<data.length;i+=8){
        const n = (Math.random()-0.5)*18;
        data[i] = clamp(data[i]+n,0,255);
        data[i+1] = clamp(data[i+1]+n,0,255);
        data[i+2] = clamp(data[i+2]+n,0,255);
      }
      ctx.putImageData(id,0,0);

      // material overlay
      if (materialType === 'Pattern') {
        ctx.globalAlpha = 0.12;
        for (let x=0;x<width;x+=6){
          ctx.fillStyle = `rgba(255,255,255,${x%12===0?0.08:0.02})`;
          ctx.fillRect(x,0,3,height);
        }
      } else if (materialType === 'Matte') {
        ctx.globalAlpha = 0.07;
        ctx.fillStyle = 'rgba(0,0,0,0.06)';
        ctx.fillRect(0,0,width,height);
      } else if (materialType === 'Glossy') {
        const shine = ctx.createLinearGradient(0,0, width, 0);
        shine.addColorStop(0, 'rgba(255,255,255,0.18)');
        shine.addColorStop(0.5, 'rgba(255,255,255,0.06)');
        shine.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.globalAlpha = 0.5;
        ctx.fillStyle = shine;
        ctx.fillRect(0,0,width,height);
      } else if (materialType === 'Metallic') {
        // metallic sheen stripes
        ctx.globalAlpha = 0.18;
        ctx.fillStyle = 'rgba(255,255,255,0.12)';
        for (let i=-width;i<width*2;i+=20) {
          ctx.beginPath();
          ctx.moveTo(i,0);
          ctx.lineTo(i+10,height);
          ctx.lineTo(i+12,height);
          ctx.lineTo(i+2,0);
          ctx.closePath();
          ctx.fill();
        }
      }

      textureCache.set(cacheKey, c);
      return c;
    }

    // --- Enhanced drawGlasses: drop-in replacement. Uses your images in glassesImages[] ---
    function drawGlasses(landmarks) {
      if (!glassesLoaded) return;

      // landmarks: mediapipe normalized coords
      const leftEye = landmarks[33];   // approx outer points used in your original code earlier
      const rightEye = landmarks[263];
      const leftEyeInner = landmarks[133] || leftEye;
      const rightEyeInner = landmarks[362] || rightEye;

      // head angle (for light direction), using inner-eye vector
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      const headAngle = Math.atan2(deltaY, deltaX);
      let angle = headAngle + angleOffset;
      if (isCalibrated) angle += angleOffset;

      // compute screen positions
      const leftX = leftEye.x * canvasElement.width;
      const rightX = rightEye.x * canvasElement.width;
      const leftY = leftEye.y * canvasElement.height;
      const rightY = rightEye.y * canvasElement.height;

      const eyeDist = Math.hypot(rightX - leftX, rightY - leftY);
      const glassesWidth = eyeDist * glassesSizeMultiplier;
      const glassesHeight = glassesWidth * glassesHeightRatio;

      let centerX = (leftX + rightX) / 2;
      let centerY = (leftY + rightY) / 2 + verticalOffset;

      // bounds check
      if (!(centerX > 0 && centerY > 0 && glassesWidth > 20 && glassesImages[currentFrame])) return;

      canvasCtx.save();

      // position & rotation
      canvasCtx.translate(centerX, centerY);
      canvasCtx.rotate(angle);
      canvasCtx.translate(-glassesWidth/2, -glassesHeight/2);

      // -------------------------
      // 1) Blur background behind lens area (fake refraction)
      // -------------------------
      // create small offscreen of video frame region => blur => draw under lenses
      try {
        const blurCanvas = document.createElement('canvas');
        const bw = Math.round(glassesWidth);
        const bh = Math.round(glassesHeight);
        blurCanvas.width = bw;
        blurCanvas.height = bh;
        const bctx = blurCanvas.getContext('2d');

        // draw the live video frame portion to offscreen (map coordinates)
        // Because the video element may be different size, draw a scaled region:
        // drawImage(source, sx, sy, sw, sh, dx, dy, dw, dh)
        // We'll approximate by drawing full video scaled onto small canvas and then blur -- cheaper
        bctx.drawImage(videoElement, 0, 0, bw, bh);
        // apply blur via filter (browser-supported)
        bctx.filter = 'blur(3px)';
        const blurredImg = bctx.getImageData(0,0,bw,bh);
        // clear and re-put blurred data (bctx filter doesn't persist for getImageData => do simple trick)
        bctx.clearRect(0,0,bw,bh);
        bctx.putImageData(blurredImg, 0, 0);
        // draw blurred area but under the frame silhouette:
        // draw blurred under frame area with low alpha (subtle)
        canvasCtx.globalAlpha = 0.22;
        canvasCtx.drawImage(blurCanvas, 0, 0, glassesWidth, glassesHeight);
        canvasCtx.globalAlpha = 1.0;
        canvasCtx.filter = 'none';
      } catch (e) {
        // if anything fails (cross-origin or perf), silently skip blur
      }

      // -------------------------
      // 2) Prepare frame texture: mask frame shape and fill with material texture
      // -------------------------
      const frameImg = glassesImages[currentFrame];
      // prepare temp canvas same ratio as frame image for better texturing
      const tempCanvas = document.createElement('canvas');
      const tW = Math.max(64, frameImg.width);
      const tH = Math.max(40, frameImg.height);
      tempCanvas.width = tW;
      tempCanvas.height = tH;
      const tctx = tempCanvas.getContext('2d');

      // draw source frame (this gives us the mask/alpha)
      tctx.clearRect(0,0,tW,tH);
      tctx.drawImage(frameImg, 0, 0, tW, tH);

      // draw material texture into separate canvas, then composite using source-in
      const materialCanvas = createMaterialTexture(tW, tH, currentColor, currentMaterial || 'Plain');
      tctx.globalCompositeOperation = 'source-in';
      tctx.drawImage(materialCanvas, 0, 0, tW, tH);
      tctx.globalCompositeOperation = 'source-over';

      // optionally add thin rim highlight for edges (frame shine)
      tctx.globalCompositeOperation = 'lighter';
      const rimGrad = tctx.createLinearGradient(0,0,tW,0);
      rimGrad.addColorStop(0, 'rgba(255,255,255,0.06)');
      rimGrad.addColorStop(0.5, 'rgba(255,255,255,0.02)');
      rimGrad.addColorStop(1, 'rgba(255,255,255,0.06)');
      tctx.fillStyle = rimGrad;
      tctx.globalAlpha = 0.18;
      tctx.fillRect(0,0,tW,tH);
      tctx.globalAlpha = 1.0;
      tctx.globalCompositeOperation = 'source-over';

      // -------------------------
      // 3) Draw ambient occlusion / shadow under the frame for "grounding"
      // -------------------------
      canvasCtx.save();
      canvasCtx.globalAlpha = 0.35;
      canvasCtx.shadowColor = 'rgba(0,0,0,0.45)';
      canvasCtx.shadowBlur = 12;
      canvasCtx.shadowOffsetY = 6;
      // draw the frame silhouette as shadow by drawing the temp canvas but tinting black
      const shadowCanvas = document.createElement('canvas');
      shadowCanvas.width = tW; shadowCanvas.height = tH;
      const sctx = shadowCanvas.getContext('2d');
      sctx.drawImage(frameImg, 0, 0, tW, tH);
      // colorize to black silhouette
      sctx.globalCompositeOperation = 'source-in';
      sctx.fillStyle = 'rgba(0,0,0,0.9)';
      sctx.fillRect(0,0,tW,tH);
      canvasCtx.drawImage(shadowCanvas, 1.5, 2.5, glassesWidth, glassesHeight); // slight offset for realism
      canvasCtx.restore();

      // -------------------------
      // 4) Draw textured frame (final)
      // -------------------------
      canvasCtx.save();
      // multiply scale to target size
      canvasCtx.drawImage(tempCanvas, 0, 0, glassesWidth, glassesHeight);
      canvasCtx.restore();

      // -------------------------
      // 5) LENS effect: tint + specular highlight that moves with headAngle
      // -------------------------
      // We'll overlay a subtle tint and a specular streak whose position is derived from angle
      canvasCtx.save();

      // tint (subtle, based on currentColorName)
      const tintMap = {
        'Black': 'rgba(200,200,210,0.08)',
        'Gray': 'rgba(160,176,181,0.12)',
        'Brown': 'rgba(166,138,107,0.10)',
        'Tortoise': 'rgba(150,110,80,0.09)',
        'Blue': 'rgba(124,169,199,0.10)',
        'Silver': 'rgba(200,200,200,0.08)',
        'Gold': 'rgba(218,165,32,0.06)',
        'Red': 'rgba(180,80,80,0.08)',
        'Green': 'rgba(120,170,120,0.08)',
        'Yellow': 'rgba(220,200,120,0.06)',
        'Purple': 'rgba(150,110,170,0.08)',
        'Pink': 'rgba(230,130,170,0.07)'
      };
      const tint = tintMap[currentColorName] || 'rgba(200,200,200,0.06)';
      canvasCtx.globalAlpha = 0.16;
      canvasCtx.fillStyle = tint;
      canvasCtx.fillRect(0, 0, glassesWidth, glassesHeight);
      canvasCtx.globalAlpha = 1.0;

      // dynamic specular highlight
      const spec = canvasCtx.createLinearGradient(0, 0, glassesWidth, 0);
      // specular intensity depends on head rotation (angle). Use sine mapping to vary position
      const normalizedAngle = clamp((angle / Math.PI) , -1, 1); // -1..1
      const specCenter = 0.5 + normalizedAngle * 0.18; // move highlight left/right
      spec.addColorStop(clamp(specCenter - 0.15, 0,1), 'rgba(255,255,255,0)');
      spec.addColorStop(clamp(specCenter - 0.06, 0,1), 'rgba(255,255,255,0.15)');
      spec.addColorStop(specCenter, 'rgba(255,255,255,0.35)');
      spec.addColorStop(clamp(specCenter + 0.06,0,1), 'rgba(255,255,255,0.12)');
      spec.addColorStop(clamp(specCenter + 0.2,0,1), 'rgba(255,255,255,0)');
      canvasCtx.globalCompositeOperation = 'lighter';
      canvasCtx.globalAlpha = 0.9;
      canvasCtx.fillStyle = spec;
      canvasCtx.fillRect(0, 0, glassesWidth, glassesHeight);
      canvasCtx.globalAlpha = 1.0;
      canvasCtx.globalCompositeOperation = 'source-over';

      // small elliptical highlight (curved lens)
      canvasCtx.save();
      canvasCtx.globalAlpha = 0.28;
      canvasCtx.beginPath();
      const hx = glassesWidth * (0.2 + normalizedAngle * 0.15);
      const hy = glassesHeight * 0.22;
      canvasCtx.ellipse(hx, hy, glassesWidth * 0.2, glassesHeight * 0.12, -0.3, 0, Math.PI*2);
      canvasCtx.fillStyle = 'rgba(255,255,255,0.55)';
      canvasCtx.fill();
      canvasCtx.restore();

      canvasCtx.restore();

      // -------------------------
      // 6) Small edge darkening to boost contrast (subtle)
      // -------------------------
      canvasCtx.save();
      canvasCtx.globalCompositeOperation = 'multiply';
      canvasCtx.globalAlpha = 0.06;
      canvasCtx.fillStyle = 'rgba(0,0,0,1)';
      canvasCtx.fillRect(0, 0, glassesWidth, glassesHeight);
      canvasCtx.globalAlpha = 1.0;
      canvasCtx.globalCompositeOperation = 'source-over';
      canvasCtx.restore();

      // done: restore canvas global transform
      canvasCtx.restore();
    }

    // --- Mediapipe handlers ---
    function calculateHeadAngle(landmarks) {
      const leftEyeInner = landmarks[133] || landmarks[33];
      const rightEyeInner = landmarks[362] || landmarks[263];
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      return Math.atan2(deltaY, deltaX);
    }

    function calibrateStraightPosition(landmarks) {
      const currentAngle = calculateHeadAngle(landmarks);
      angleOffset = -currentAngle;
      isCalibrated = true;
    }

    async function onResults(results) {
      if (!glassesLoaded || isProcessing) return;
      frameCount++;
      isProcessing = true;

      // draw camera frame as the base (we use video element; draw image captured by mediapipe)
      canvasCtx.save();
      canvasCtx.clearRect(0,0,canvasElement.width, canvasElement.height);
      // draw underlying camera frame to canvas as background (keeps one canvas)
      try {
        canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);
      } catch(e) {
        // fallback: draw video element if results.image not available
        try { canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height); } catch(e){}
      }

      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        faceTrackingActive = true;
        const landmarks = results.multiFaceLandmarks[0];
        if (!isCalibrated && frameCount > 10) calibrateStraightPosition(landmarks);
        drawGlasses(landmarks);
        updateStatus(`Active - ${currentFrame} (${currentColorName})`, "online");
      } else {
        faceTrackingActive = false;
        updateStatus("Looking for face...", "loading");
      }

      canvasCtx.restore();
      isProcessing = false;
    }

    // --- Initialize FaceMesh ---
    async function initializeFaceMesh() {
      return new Promise((resolve) => {
        faceMesh = new FaceMesh({
          locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
        });
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: true,
          minDetectionConfidence: 0.7,
          minTrackingConfidence: 0.5
        });
        faceMesh.onResults(onResults);
        faceMesh.initialize().then(resolve).catch(resolve);
      });
    }

    // --- startCamera reused but updated for resize + filters ---
    async function startCamera() {
      try {
        updateStatus("Requesting camera access...", "loading");
        cameraOverlay.classList.remove('d-none');

        const constraints = { video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 }, aspectRatio: { ideal: 4/3 } } };
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;
        await videoElement.play();

        // set canvas size to container
        resizeCanvasToDisplay();

        camera = new Camera(videoElement, {
          onFrame: async () => {
            if (faceMesh && !isProcessing) await faceMesh.send({ image: videoElement });
          },
          width: 640,
          height: 480
        });

        await camera.start();
        cameraOverlay.classList.add('d-none');
        calibrateBtn.classList.remove('d-none');
        updateHeightDisplay();
        updatePositionDisplay();

        // slight global filter to help blending frames and skin tones
        canvasElement.style.filter = 'brightness(0.98) contrast(1.03) saturate(0.95)';

        updateStatus("Ready! Try different frames and colors", "online");
        startBtn.disabled = true;

        window.addEventListener('resize', resizeCanvasToDisplay);
        return stream;
      } catch (err) {
        cameraOverlay.classList.add('d-none');
        throw err;
      }
    }

    // --- small UI helpers ---
    function updateHeightDisplay() { heightValue.textContent = Math.round(glassesHeightRatio * 100) + '%'; }
    function updatePositionDisplay() { positionValue.textContent = verticalOffset + 'px'; }

    // --- UI event wiring ---
    frameButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        frameButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFrame = btn.dataset.frame;
      });
    });

    colorButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        colorButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentColor = btn.dataset.color;
        currentColorName = btn.dataset.colorName;
      });
    });

    materialButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        materialButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentMaterial = btn.dataset.material || 'Plain';
      });
    });

    sizeSlider.addEventListener('input', (e) => {
      glassesSizeMultiplier = parseFloat(e.target.value);
      sizeValue.textContent = glassesSizeMultiplier.toFixed(1) + 'x';
    });

    heightDown.addEventListener('click', () => { glassesHeightRatio = Math.max(0.45, glassesHeightRatio - 0.05); updateHeightDisplay(); });
    heightUp.addEventListener('click', () => { glassesHeightRatio = Math.min(1.05, glassesHeightRatio + 0.05); updateHeightDisplay(); });

    positionDown.addEventListener('click', () => { verticalOffset += 3; updatePositionDisplay(); });
    positionUp.addEventListener('click', () => { verticalOffset -= 3; updatePositionDisplay(); });

    calibrateBtn.addEventListener('click', () => {
      if (faceTrackingActive) {
        isCalibrated = false;
        updateStatus("Recalibrating... Look straight", "loading");
        setTimeout(() => updateStatus("Recalibrated!", "online"), 900);
      }
    });

    // --- Capture local download (no server) ---
    captureBtn.addEventListener('click', () => {
      try {
        const dataUrl = canvasElement.toDataURL('image/png');
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = `tryon_${Date.now()}.png`;
        document.body.appendChild(a);
        a.click();
        a.remove();
      } catch (e) {
        alert('Capture failed: ' + e.message);
      }
    });

    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        updateStatus("Initializing...", "loading");
        await initializeFaceMesh();
        await startCamera();
      } catch (err) {
        startBtn.disabled = false;
        let errorMsg = "Failed to start camera";
        if (err && err.name === 'NotAllowedError') errorMsg = "Camera permission denied";
        else if (err && err.name === 'NotFoundError') errorMsg = "No camera found";
        updateStatus(errorMsg, "offline");
      }
    });

    // initial lightweight boot
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    window.addEventListener('load', () => {
      setTimeout(() => initializeFaceMesh().catch(()=>{}), 500);
      // make sure canvas matches parent at start
      resizeCanvasToDisplay();
    });
  </script>
</body>
</html>
