<?php
// virtual-try-on.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Glasses Try-On</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* [Keep all the previous CSS exactly the same] */
    :root {
      --primary: #1a73e8;
      --secondary: #6c757d;
      --success: #28a745;
      --dark: #222;
      --light: #f8f9fa;
      --border: #dee2e6;
    }
    
    * {
      box-sizing: border-box;
    }
    
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
    
    .header {
      text-align: center;
      margin-bottom: 20px;
      color: white;
    }
    
    .header h1 {
      font-weight: 700;
      font-size: 2.2rem;
      margin-bottom: 5px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .header p {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 0;
    }
    
    .main-content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    @media (min-width: 768px) {
      .main-content {
        flex-direction: row;
        align-items: flex-start;
      }
    }
    
    .camera-section {
      flex: 1;
      min-width: 0;
    }
    
    .controls-section {
      flex: 1;
      min-width: 0;
    }
    
    .camera-container {
      position: relative;
      background: black;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      aspect-ratio: 4/3;
    }
    
    video, canvas {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    
    #outputCanvas {
      position: absolute;
      top: 0;
      left: 0;
    }
    
    .camera-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: white;
      z-index: 10;
    }
    
    .card {
      background: white;
      border-radius: 20px;
      border: none;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), #1558b0);
      color: white;
      border: none;
      padding: 15px 20px;
      font-weight: 600;
    }
    
    .card-body {
      padding: 20px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), #1558b0);
      border: none;
      border-radius: 12px;
      padding: 12px 24px;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(26, 115, 232, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4);
    }
    
    .btn-outline-primary {
      border: 2px solid var(--primary);
      color: var(--primary);
      border-radius: 12px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-1px);
    }
    
    .btn-sm {
      padding: 8px 16px;
      font-size: 14px;
    }
    
    .frame-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }
    
    .frame-btn {
      background: white;
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
    }
    
    .frame-btn:hover {
      border-color: var(--primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .frame-btn.active {
      border-color: var(--primary);
      background: #e3f2fd;
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(26, 115, 232, 0.2);
    }
    
    .frame-img {
      width: 45px;
      height: 25px;
      object-fit: contain;
    }
    
    .frame-label {
      font-size: 10px;
      font-weight: 600;
      color: var(--dark);
      text-align: center;
      line-height: 1.2;
    }
    
    .color-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
      gap: 8px;
      margin-top: 10px;
    }
    
    .color-btn {
      width: 40px;
      height: 40px;
      border: 3px solid var(--border);
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .color-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .color-btn.active {
      border-color: var(--primary);
      transform: scale(1.15);
      box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }
    
    .color-btn.active::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      font-size: 14px;
      text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    }
    
    .color-label {
      font-size: 9px;
      text-align: center;
      margin-top: 4px;
      font-weight: 600;
    }
    
    .color-group {
      margin-bottom: 15px;
    }
    
    .color-group-title {
      font-size: 12px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .material-controls {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .material-btn {
      flex: 1;
      padding: 8px 12px;
      border: 2px solid var(--border);
      border-radius: 8px;
      background: white;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 11px;
      font-weight: 600;
    }
    
    .material-btn:hover {
      border-color: var(--primary);
      transform: translateY(-1px);
    }
    
    .material-btn.active {
      border-color: var(--primary);
      background: var(--primary);
      color: white;
    }
    
    .control-group {
      margin-bottom: 20px;
    }
    
    .control-label {
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--dark);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .control-value {
      font-weight: 700;
      color: var(--primary);
      background: #e3f2fd;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 12px;
    }
    
    .form-range {
      width: 100%;
      height: 8px;
      border-radius: 4px;
    }
    
    .form-range::-webkit-slider-thumb {
      background: var(--primary);
      border: none;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    
    .position-controls {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
    }
    
    .position-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    
    .position-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .position-value {
      min-width: 50px;
      font-weight: 700;
      font-size: 16px;
      text-align: center;
      background: #f8f9fa;
      padding: 8px 12px;
      border-radius: 10px;
      border: 2px solid var(--border);
    }
    
    .status-indicator {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
      margin: 10px 0;
    }
    
    .status-online {
      background: #d4edda;
      color: #155724;
      border: 2px solid #c3e6cb;
    }
    
    .status-offline {
      background: #f8d7da;
      color: #721c24;
      border: 2px solid #f5c6cb;
    }
    
    .status-loading {
      background: #fff3cd;
      color: #856404;
      border: 2px solid #ffeaa7;
    }
    
    .loading-spinner {
      width: 30px;
      height: 30px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 10px auto;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .action-buttons {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    
    .action-buttons .btn {
      flex: 1;
      min-width: 120px;
    }
    
    .mobile-tips {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border-radius: 15px;
      padding: 15px;
      margin-top: 20px;
      text-align: center;
    }
    
    .mobile-tips h6 {
      font-weight: 700;
      margin-bottom: 8px;
    }
    
    .mobile-tips ul {
      text-align: left;
      margin: 0;
      padding-left: 20px;
      font-size: 14px;
    }
    
    .mobile-tips li {
      margin-bottom: 5px;
    }
    
    /* Mobile optimizations */
    @media (max-width: 767px) {
      .app-container {
        padding: 10px;
      }
      
      .header h1 {
        font-size: 1.8rem;
      }
      
      .header p {
        font-size: 1rem;
      }
      
      .card-body {
        padding: 15px;
      }
      
      .frame-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
      }
      
      .frame-btn {
        padding: 6px;
      }
      
      .frame-img {
        width: 35px;
        height: 20px;
      }
      
      .frame-label {
        font-size: 9px;
      }
      
      .color-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 6px;
      }
      
      .color-btn {
        width: 35px;
        height: 35px;
      }
      
      .material-controls {
        flex-direction: column;
      }
      
      .position-controls {
        gap: 10px;
      }
      
      .position-btn {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .action-buttons .btn {
        width: 100%;
      }
    }
    
    @media (max-width: 480px) {
      .frame-grid {
        grid-template-columns: repeat(3, 1fr);
      }
      
      .color-grid {
        grid-template-columns: repeat(5, 1fr);
      }
      
      .header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="app-container">
    <div class="header">
      <h1>👓 Virtual Glasses Try-On</h1>
      <p>Find your perfect frame and color in real-time</p>
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
          <div class="card-header">
            Camera Controls
          </div>
          <div class="card-body">
            <div class="action-buttons">
              <button id="startBtn" class="btn btn-primary">
                <i class="bi bi-camera-video me-2"></i>Start Camera
              </button>
              <button id="calibrateBtn" class="btn btn-outline-primary d-none">
                <i class="bi bi-arrow-clockwise me-2"></i>Recalibrate
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="controls-section">
        <div class="card">
          <div class="card-header">
            Frame Styles
          </div>
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
          <div class="card-header">
            Frame Colors & Materials
          </div>
          <div class="card-body">
            <div class="color-group">
              <div class="color-group-title">Classic Colors</div>
              <div class="color-grid">
                <div class="color-option">
                  <div class="color-btn active" style="background: #2c2c2c;" data-color="#2c2c2c" data-color-name="Black" data-material="matte"></div>
                  <div class="color-label">Black</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #6c757d;" data-color="#6c757d" data-color-name="Gray" data-material="matte"></div>
                  <div class="color-label">Gray</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #8B4513;" data-color="#8B4513" data-color-name="Tortoise" data-material="textured"></div>
                  <div class="color-label">Tortoise</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #A52A2A;" data-color="#A52A2A" data-color-name="Brown" data-material="matte"></div>
                  <div class="color-label">Brown</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: linear-gradient(45deg, #c0c0c0, #e8e8e8);" data-color="#c0c0c0" data-color-name="Silver" data-material="metallic"></div>
                  <div class="color-label">Silver</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: linear-gradient(45deg, #daa520, #b8860b);" data-color="#daa520" data-color-name="Gold" data-material="metallic"></div>
                  <div class="color-label">Gold</div>
                </div>
              </div>
            </div>
            
            <div class="color-group">
              <div class="color-group-title">Vibrant Colors</div>
              <div class="color-grid">
                <div class="color-option">
                  <div class="color-btn" style="background: #1a73e8;" data-color="#1a73e8" data-color-name="Blue" data-material="glossy"></div>
                  <div class="color-label">Blue</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #dc3545;" data-color="#dc3545" data-color-name="Red" data-material="glossy"></div>
                  <div class="color-label">Red</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #28a745;" data-color="#28a745" data-color-name="Green" data-material="glossy"></div>
                  <div class="color-label">Green</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #ffc107;" data-color="#ffc107" data-color-name="Yellow" data-material="glossy"></div>
                  <div class="color-label">Yellow</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #6f42c1;" data-color="#6f42c1" data-color-name="Purple" data-material="glossy"></div>
                  <div class="color-label">Purple</div>
                </div>
                <div class="color-option">
                  <div class="color-btn" style="background: #e83e8c;" data-color="#e83e8c" data-color-name="Pink" data-material="glossy"></div>
                  <div class="color-label">Pink</div>
                </div>
              </div>
            </div>

            <div class="control-group">
              <div class="control-label">
                <span>Material Effect</span>
              </div>
              <div class="material-controls">
                <button class="material-btn active" data-material="realistic">Realistic</button>
                <button class="material-btn" data-material="glossy">Glossy</button>
                <button class="material-btn" data-material="metallic">Metallic</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            Adjust Fit
          </div>
          <div class="card-body">
            <div class="control-group">
              <div class="control-label">
                <span>Frame Size</span>
                <span class="control-value" id="sizeValue">2.4x</span>
              </div>
              <input type="range" class="form-range" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
            </div>
            
            <div class="control-group">
              <div class="control-label">
                <span>Frame Height</span>
                <span class="control-value" id="heightValue">70%</span>
              </div>
              <div class="position-controls">
                <button class="btn btn-outline-primary position-btn" id="heightDown">
                  <i class="bi bi-dash"></i>
                </button>
                <span>Shorter - Taller</span>
                <button class="btn btn-outline-primary position-btn" id="heightUp">
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>
            
            <div class="control-group">
              <div class="control-label">
                <span>Vertical Position</span>
                <span class="control-value" id="positionValue">0px</span>
              </div>
              <div class="position-controls">
                <button class="btn btn-outline-primary position-btn" id="positionDown">
                  <i class="bi bi-arrow-down"></i>
                </button>
                <span>Lower - Higher</span>
                <button class="btn btn-outline-primary position-btn" id="positionUp">
                  <i class="bi bi-arrow-up"></i>
                </button>
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
    const videoElement = document.getElementById('inputVideo');
    const canvasElement = document.getElementById('outputCanvas');
    const canvasCtx = canvasElement.getContext('2d');
    const startBtn = document.getElementById('startBtn');
    const calibrateBtn = document.getElementById('calibrateBtn');
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

    // Frame definitions
    const FRAMES = {
      'SQUARE': { path: 'Images/frames/square-frame-removebg-preview.png', label: 'Square' },
      'ROUND': { path: 'Images/frames/round-frame-removebg-preview.png', label: 'Round' },
      'OBLONG': { path: 'Images/frames/oblong-frame-removebg-preview.png', label: 'Oblong' },
      'DIAMOND': { path: 'Images/frames/diamond-frame-removebg-preview.png', label: 'Diamond' },
      'V-TRIANGLE': { path: 'Images/frames/vshape-frame-removebg-preview.png', label: 'V-Shape' },
      'A-TRIANGLE': { path: 'Images/frames/ashape-frame-removebg-preview.png', label: 'A-Shape' },
      'RECTANGLE': { path: 'Images/frames/rectangle-frame-removebg-preview.png', label: 'Rectangle' }
    };

    // Load glasses images
    const glassesImages = {};
    let glassesLoaded = false;
    let loadedImagesCount = 0;
    const totalImages = Object.keys(FRAMES).length;

    Object.entries(FRAMES).forEach(([frameType, frameData]) => {
      const img = new Image();
      img.src = frameData.path;
      img.onload = () => {
        loadedImagesCount++;
        glassesImages[frameType] = img;
        if (loadedImagesCount === totalImages) {
          glassesLoaded = true;
          console.log("✅ All frame images loaded successfully");
        }
      };
      img.onerror = () => {
        console.error(`❌ Failed to load frame: ${frameData.label}`);
        loadedImagesCount++;
      };
    });

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
    let currentMaterial = 'realistic';

    function updateStatus(status, type) {
      statusText.textContent = status;
      statusIndicator.className = `status-indicator status-${type}`;
    }

    function calculateHeadAngle(landmarks) {
      const leftEyeInner = landmarks[133];
      const rightEyeInner = landmarks[362];
      const deltaX = rightEyeInner.x - leftEyeInner.x;
      const deltaY = rightEyeInner.y - leftEyeInner.y;
      return Math.atan2(deltaY, deltaX);
    }

    function calibrateStraightPosition(landmarks) {
      const currentAngle = calculateHeadAngle(landmarks);
      angleOffset = -currentAngle;
      isCalibrated = true;
    }

    function updateHeightDisplay() {
      heightValue.textContent = Math.round(glassesHeightRatio * 100) + '%';
    }

    function updatePositionDisplay() {
      positionValue.textContent = verticalOffset + 'px';
    }

    function createMaterialTexture(width, height, baseColor, materialType) {
      const textureCanvas = document.createElement('canvas');
      const textureCtx = textureCanvas.getContext('2d');
      textureCanvas.width = width;
      textureCanvas.height = height;
      
      // Parse base color
      const hex = baseColor.replace('#', '');
      const r = parseInt(hex.substr(0, 2), 16);
      const g = parseInt(hex.substr(2, 2), 16);
      const b = parseInt(hex.substr(4, 2), 16);
      
      if (materialType === 'realistic') {
        // Realistic plastic/matte texture
        const gradient = textureCtx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, `rgb(${Math.max(0, r-30)}, ${Math.max(0, g-30)}, ${Math.max(0, b-30)})`);
        gradient.addColorStop(0.5, baseColor);
        gradient.addColorStop(1, `rgb(${Math.min(255, r+20)}, ${Math.min(255, g+20)}, ${Math.min(255, b+20)})`);
        
        textureCtx.fillStyle = gradient;
        textureCtx.fillRect(0, 0, width, height);
        
        // Add subtle noise for texture
        const imageData = textureCtx.getImageData(0, 0, width, height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
          const noise = (Math.random() - 0.5) * 10;
          data[i] = Math.max(0, Math.min(255, data[i] + noise));
          data[i + 1] = Math.max(0, Math.min(255, data[i + 1] + noise));
          data[i + 2] = Math.max(0, Math.min(255, data[i + 2] + noise));
        }
        textureCtx.putImageData(imageData, 0, 0);
        
      } else if (materialType === 'glossy') {
        // Glossy plastic texture
        const gradient = textureCtx.createLinearGradient(0, 0, width * 0.3, height * 0.3);
        gradient.addColorStop(0, `rgb(${Math.min(255, r+60)}, ${Math.min(255, g+60)}, ${Math.min(255, b+60)})`);
        gradient.addColorStop(0.3, baseColor);
        gradient.addColorStop(0.7, `rgb(${Math.max(0, r-40)}, ${Math.max(0, g-40)}, ${Math.max(0, b-40)})`);
        gradient.addColorStop(1, `rgb(${Math.max(0, r-20)}, ${Math.max(0, g-20)}, ${Math.max(0, b-20)})`);
        
        textureCtx.fillStyle = gradient;
        textureCtx.fillRect(0, 0, width, height);
        
        // Add highlight
        const highlight = textureCtx.createRadialGradient(width*0.3, height*0.3, 0, width*0.3, height*0.3, width*0.5);
        highlight.addColorStop(0, 'rgba(255,255,255,0.3)');
        highlight.addColorStop(1, 'rgba(255,255,255,0)');
        textureCtx.fillStyle = highlight;
        textureCtx.fillRect(0, 0, width, height);
        
      } else if (materialType === 'metallic') {
        // Metallic texture
        for (let x = 0; x < width; x++) {
          for (let y = 0; y < height; y++) {
            const value = Math.sin(x * 0.1) * Math.cos(y * 0.1) * 30;
            const metallicR = Math.max(0, Math.min(255, r + value));
            const metallicG = Math.max(0, Math.min(255, g + value));
            const metallicB = Math.max(0, Math.min(255, b + value));
            
            textureCtx.fillStyle = `rgb(${metallicR}, ${metallicG}, ${metallicB})`;
            textureCtx.fillRect(x, y, 1, 1);
          }
        }
        
        // Add metallic shine
        const shine = textureCtx.createLinearGradient(0, 0, width, height);
        shine.addColorStop(0, 'rgba(255,255,255,0.4)');
        shine.addColorStop(0.5, 'rgba(255,255,255,0.1)');
        shine.addColorStop(1, 'rgba(255,255,255,0)');
        textureCtx.fillStyle = shine;
        textureCtx.fillRect(0, 0, width, height);
      }
      
      return textureCanvas;
    }

    function drawGlasses(landmarks) {
      const leftEye = landmarks[33];
      const rightEye = landmarks[263];
      let headAngle = calculateHeadAngle(landmarks);
      
      if (isCalibrated) headAngle += angleOffset;
      
      const eyeDist = Math.hypot(
        rightEye.x * canvasElement.width - leftEye.x * canvasElement.width,
        rightEye.y * canvasElement.height - leftEye.y * canvasElement.height
      );

      const glassesWidth = eyeDist * glassesSizeMultiplier;
      const glassesHeight = glassesWidth * glassesHeightRatio;
      let centerX = (leftEye.x * canvasElement.width + rightEye.x * canvasElement.width) / 2;
      let centerY = (leftEye.y * canvasElement.height + rightEye.y * canvasElement.height) / 2;
      centerY += verticalOffset;

      if (centerX > 0 && centerY > 0 && glassesWidth > 10 && glassesImages[currentFrame]) {
        canvasCtx.save();
        canvasCtx.translate(centerX, centerY);
        canvasCtx.rotate(headAngle);
        
        // Create temporary canvas for the frame
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        const frameWidth = glassesImages[currentFrame].width;
        const frameHeight = glassesImages[currentFrame].height;
        tempCanvas.width = frameWidth;
        tempCanvas.height = frameHeight;
        
        // Draw original frame to get the shape
        tempCtx.drawImage(glassesImages[currentFrame], 0, 0);
        
        // Create material texture
        const textureCanvas = createMaterialTexture(frameWidth, frameHeight, currentColor, currentMaterial);
        
        // Apply texture using the frame as a mask
        tempCtx.globalCompositeOperation = 'source-in';
        tempCtx.drawImage(textureCanvas, 0, 0);
        
        // Draw the final textured glasses
        canvasCtx.drawImage(
          tempCanvas,
          -glassesWidth / 2,
          -glassesHeight / 2,
          glassesWidth,
          glassesHeight
        );
        
        canvasCtx.restore();
      }
    }

    async function onResults(results) {
      if (!glassesLoaded || isProcessing) return;
      frameCount++;
      if (isMobile && frameCount % 3 !== 0) return;

      isProcessing = true;
      canvasCtx.save();
      canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
      canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        faceTrackingActive = true;
        if (!isCalibrated && frameCount > 10) calibrateStraightPosition(results.multiFaceLandmarks[0]);
        results.multiFaceLandmarks.forEach(drawGlasses);
        updateStatus(`Active - ${FRAMES[currentFrame].label} (${currentColorName})`, "online");
      } else {
        faceTrackingActive = false;
        updateStatus("Looking for face...", "loading");
      }

      canvasCtx.restore();
      isProcessing = false;
    }

    function resizeCanvasToDisplay() {
      const container = canvasElement.parentElement;
      canvasElement.width = container.clientWidth;
      canvasElement.height = container.clientHeight;
    }

    async function initializeFaceMesh() {
      return new Promise((resolve) => {
        faceMesh = new FaceMesh({
          locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
        });
        faceMesh.setOptions({
          maxNumFaces: 1, refineLandmarks: false, minDetectionConfidence: 0.7, minTrackingConfidence: 0.5
        });
        faceMesh.onResults(onResults);
        faceMesh.initialize().then(resolve).catch(resolve);
      });
    }

    async function startCamera() {
      try {
        updateStatus("Requesting camera access...", "loading");
        cameraOverlay.classList.remove('d-none');
        
        const constraints = {
          video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 }, aspectRatio: { ideal: 4/3 } }
        };
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;
        
        return new Promise((resolve) => {
          videoElement.onloadedmetadata = () => {
            videoElement.play().then(() => {
              cameraOverlay.classList.add('d-none');
              resolve(stream);
            });
          };
        });
      } catch (err) {
        cameraOverlay.classList.add('d-none');
        throw err;
      }
    }

    // Event listeners for frame selection
    frameButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        frameButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFrame = btn.dataset.frame;
      });
    });

    // Event listeners for color selection
    colorButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        colorButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentColor = btn.dataset.color;
        currentColorName = btn.dataset.colorName;
        // Auto-set material based on color type
        const suggestedMaterial = btn.dataset.material;
        if (suggestedMaterial) {
          materialButtons.forEach(mb => mb.classList.remove('active'));
          document.querySelector(`[data-material="${suggestedMaterial}"]`).classList.add('active');
          currentMaterial = suggestedMaterial;
        }
      });
    });

    // Event listeners for material selection
    materialButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        materialButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentMaterial = btn.dataset.material;
      });
    });

    sizeSlider.addEventListener('input', (e) => {
      glassesSizeMultiplier = parseFloat(e.target.value);
      sizeValue.textContent = glassesSizeMultiplier.toFixed(1) + 'x';
    });

    heightDown.addEventListener('click', () => {
      glassesHeightRatio = Math.max(0.4, glassesHeightRatio - 0.05);
      updateHeightDisplay();
    });

    heightUp.addEventListener('click', () => {
      glassesHeightRatio = Math.min(1.0, glassesHeightRatio + 0.05);
      updateHeightDisplay();
    });

    positionDown.addEventListener('click', () => {
      verticalOffset += 2;
      updatePositionDisplay();
    });

    positionUp.addEventListener('click', () => {
      verticalOffset -= 2;
      updatePositionDisplay();
    });

    calibrateBtn.addEventListener('click', () => {
      if (faceTrackingActive) {
        isCalibrated = false;
        updateStatus("Recalibrating... Look straight", "loading");
        setTimeout(() => updateStatus("Recalibrated!", "online"), 1000);
      }
    });

    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        updateStatus("Initializing...", "loading");

        await initializeFaceMesh();
        const stream = await startCamera();
        
        resizeCanvasToDisplay();
        calibrateBtn.classList.remove('d-none');
        updateHeightDisplay();
        updatePositionDisplay();

        camera = new Camera(videoElement, {
          onFrame: async () => {
            if (faceMesh && !isProcessing) await faceMesh.send({ image: videoElement });
          }, width: 320, height: 240
        });

        await camera.start();
        updateStatus("Ready! Try different frames and colors", "online");

        window.addEventListener('resize', resizeCanvasToDisplay);
      } catch (err) {
        startBtn.disabled = false;
        let errorMsg = "Failed to start camera";
        if (err.name === 'NotAllowedError') errorMsg = "Camera permission denied";
        else if (err.name === 'NotFoundError') errorMsg = "No camera found";
        updateStatus(errorMsg, "offline");
      }
    });

    // Initialize
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    window.addEventListener('load', () => setTimeout(initializeFaceMesh, 1000));
  </script>
</body>
</html>