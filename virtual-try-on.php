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


/* -------------------------------
   Realistic Glasses Rendering
--------------------------------*/
function drawGlasses(landmarks) {
  const leftEye = landmarks[33];
  const rightEye = landmarks[263];
  const eyeMid = {
    x: (leftEye.x + rightEye.x) / 2,
    y: (leftEye.y + rightEye.y) / 2
  };

  // Adjust scaling to match your setup
  const faceWidth = Math.abs(rightEye.x - leftEye.x) * canvasElement.width * 2.5 * glassesSizeMultiplier;
  const faceHeight = faceWidth * 0.45;
  const x = (eyeMid.x * canvasElement.width) - faceWidth / 2;
  const y = (eyeMid.y * canvasElement.height) - faceHeight / 2 + verticalOffset;
  const angle = Math.atan2(rightEye.y - leftEye.y, rightEye.x - leftEye.x);

  const glassesImage = new Image();
  glassesImage.src = glassesPath; // Example: "Images/frames/square-frame-removebg-preview.png"

  // Wait until the image is loaded before drawing
  if (!glassesImage.complete) {
    glassesImage.onload = () => drawGlasses(landmarks);
    return;
  }

  // -----------------------------
  // Draw Base Glasses
  // -----------------------------
  canvasCtx.save();
  canvasCtx.translate(x + faceWidth / 2, y + faceHeight / 2);
  canvasCtx.rotate(angle);
  canvasCtx.translate(-faceWidth / 2, -faceHeight / 2);

  // Draw the main frame
  canvasCtx.drawImage(glassesImage, 0, 0, faceWidth, faceHeight);

  // -----------------------------
  // Create Realistic Lens Effects
  // -----------------------------
  const buffer = document.createElement("canvas");
  buffer.width = faceWidth;
  buffer.height = faceHeight;
  const bctx = buffer.getContext("2d");

  // Start by drawing the glasses frame as a mask
  bctx.drawImage(glassesImage, 0, 0, faceWidth, faceHeight);
  bctx.globalCompositeOperation = "source-in";

  // --- Dynamic reflection gradient based on head angle ---
  const lightDir = (leftEye.x + rightEye.x) / 2;
  const reflection = bctx.createLinearGradient(
    lightDir > 0.5 ? faceWidth : 0, 0,
    lightDir > 0.5 ? 0 : faceWidth, faceHeight
  );
  reflection.addColorStop(0, "rgba(255,255,255,0.35)");
  reflection.addColorStop(0.3, "rgba(255,255,255,0.1)");
  reflection.addColorStop(1, "rgba(255,255,255,0)");
  bctx.fillStyle = reflection;
  bctx.fillRect(0, 0, faceWidth, faceHeight);

  // --- Soft lens tint (light gray or colored glass) ---
  bctx.globalCompositeOperation = "overlay";
  bctx.fillStyle = "rgba(180,200,220,0.15)";
  bctx.fillRect(0, 0, faceWidth, faceHeight);

  // Merge buffer back into the main canvas
  canvasCtx.drawImage(buffer, 0, 0, faceWidth, faceHeight);

  // -----------------------------
  // Add Subtle Shadow for Depth
  // -----------------------------
  canvasCtx.globalCompositeOperation = "multiply";
  const shadowGrad = canvasCtx.createLinearGradient(0, 0, 0, faceHeight);
  shadowGrad.addColorStop(0, "rgba(0,0,0,0.15)");
  shadowGrad.addColorStop(1, "rgba(0,0,0,0)");
  canvasCtx.fillStyle = shadowGrad;
  canvasCtx.fillRect(0, 0, faceWidth, faceHeight);

  canvasCtx.restore();
}

/* -------------------------------
   Global Camera Tone Filter
   (for better blending)
--------------------------------*/
function setRealismFilters() {
  canvasElement.style.filter = `
    brightness(0.97)
    contrast(1.08)
    saturate(0.9)
    drop-shadow(0px 2px 4px rgba(0,0,0,0.4))
  `;
}

/* -------------------------------
   Call This After Camera Starts
--------------------------------*/
// Example:
startCamera().then(() => setRealismFilters());


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
