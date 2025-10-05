<?php
// virtual-try-on.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Glasses Try-On | Santos Optical</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="customCodes/custom.css">
  <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #FF3E6C;
      --secondary: #00C8B3;
      --success: #48BB78;
      --dark: #333333;
      --light: #F8F9FA;
      --border: #dee2e6;
    }
    
    body {
      background-color: #FFF5F7;
      font-family: 'Montserrat', sans-serif;
    }
    
    .try-on-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .page-title {
      text-align: center;
      margin: 30px 0;
      color: var(--primary);
    }
    
    .page-title h1 {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 10px;
    }
    
    .page-title p {
      font-size: 1.2rem;
      color: var(--dark);
    }
    
    /* Simple 3-step process */
    .steps-simple {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    
    .step-box {
      background: white;
      padding: 20px 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      text-align: center;
      min-width: 200px;
      border: 3px solid transparent;
      transition: all 0.3s ease;
    }
    
    .step-box.active {
      border-color: var(--primary);
      transform: scale(1.05);
    }
    
    .step-box.completed {
      border-color: var(--success);
    }
    
    .step-number {
      width: 50px;
      height: 50px;
      background: var(--light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: 700;
      margin: 0 auto 10px;
      color: var(--dark);
    }
    
    .step-box.active .step-number {
      background: var(--primary);
      color: white;
    }
    
    .step-box.completed .step-number {
      background: var(--success);
      color: white;
    }
    
    .step-box.completed .step-number::after {
      content: '✓';
    }
    
    .step-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--dark);
    }
    
    .main-layout {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }
    
    @media (min-width: 992px) {
      .main-layout {
        grid-template-columns: 1.2fr 0.8fr;
      }
    }
    
    .card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: none;
      margin-bottom: 20px;
    }
    
    .card-header {
      background: var(--primary);
      color: white;
      padding: 15px 20px;
      border-radius: 20px 20px 0 0;
      font-weight: 700;
      font-size: 1.2rem;
    }
    
    .card-body {
      padding: 25px;
    }
    
    /* Camera Section */
    .camera-wrapper {
      position: relative;
      background: #000;
      border-radius: 15px;
      overflow: hidden;
      aspect-ratio: 4/3;
    }
    
    video, canvas {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    #outputCanvas {
      position: absolute;
      top: 0;
      left: 0;
    }
    
    .camera-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.8);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      z-index: 10;
    }
    
    .camera-overlay.d-none {
      display: none;
    }
    
    .spinner {
      width: 50px;
      height: 50px;
      border: 5px solid rgba(255,255,255,0.3);
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .camera-status {
      text-align: center;
      margin: 15px 0;
    }
    
    .status-badge {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 14px;
    }
    
    .status-badge.off { background: #FED7D7; color: #742A2A; }
    .status-badge.loading { background: #FEEBC8; color: #744210; }
    .status-badge.ready { background: #C6F6D5; color: #22543D; }
    
    .photo-button {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      width: 70px;
      height: 70px;
      background: white;
      border: 4px solid var(--primary);
      border-radius: 50%;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 28px;
      color: var(--primary);
      z-index: 20;
      transition: all 0.3s ease;
    }
    
    .photo-button.show {
      display: flex;
    }
    
    .photo-button:hover {
      background: var(--primary);
      color: white;
      transform: translateX(-50%) scale(1.1);
    }
    
    /* Big simple buttons */
    .big-button {
      width: 100%;
      padding: 18px;
      font-size: 1.2rem;
      font-weight: 700;
      border-radius: 15px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 12px;
    }
    
    .big-button i {
      font-size: 1.5rem;
    }
    
    .btn-start {
      background: var(--primary);
      color: white;
    }
    
    .btn-start:hover {
      background: #FF2B5D;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 62, 108, 0.4);
    }
    
    .btn-face-shape {
      background: var(--secondary);
      color: white;
    }
    
    .btn-face-shape:hover {
      background: #00b3a0;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 200, 179, 0.4);
    }
    
    .btn-reset {
      background: var(--light);
      color: var(--dark);
      border: 2px solid var(--border);
    }
    
    .btn-reset:hover {
      background: var(--dark);
      color: white;
    }
    
    /* Frame selection - bigger and clearer */
    .frames-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }
    
    .frame-item {
      background: var(--light);
      border: 3px solid var(--border);
      border-radius: 12px;
      padding: 15px;
      cursor: pointer;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .frame-item:hover {
      border-color: var(--primary);
      transform: translateY(-3px);
    }
    
    .frame-item.selected {
      border-color: var(--primary);
      background: rgba(255, 62, 108, 0.1);
    }
    
    .frame-img {
      width: 100%;
      height: 40px;
      object-fit: contain;
      margin-bottom: 8px;
    }
    
    .frame-name {
      font-size: 13px;
      font-weight: 600;
      color: var(--dark);
    }
    
    /* Color selection - simpler */
    .color-title {
      font-weight: 700;
      margin: 20px 0 10px;
      color: var(--dark);
      font-size: 1rem;
    }
    
    .colors-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
      gap: 12px;
    }
    
    .color-item {
      text-align: center;
    }
    
    .color-circle {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: 4px solid var(--border);
      margin: 0 auto 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .color-circle:hover {
      transform: scale(1.1);
    }
    
    .color-circle.selected {
      border-color: var(--primary);
      border-width: 5px;
    }
    
    .color-circle.selected::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 24px;
      font-weight: bold;
      text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
    
    .color-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--dark);
    }
    
    /* Simple sliders */
    .control-section {
      margin-bottom: 25px;
    }
    
    .control-title {
      font-weight: 700;
      font-size: 1.1rem;
      margin-bottom: 15px;
      color: var(--dark);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .control-value {
      background: var(--primary);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 1rem;
    }
    
    .slider {
      width: 100%;
      height: 10px;
      border-radius: 5px;
      background: var(--light);
      outline: none;
      -webkit-appearance: none;
    }
    
    .slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: var(--primary);
      cursor: pointer;
    }
    
    .slider::-moz-range-thumb {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: var(--primary);
      cursor: pointer;
      border: none;
    }
    
    .adjust-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .adjust-btn {
      flex: 1;
      padding: 15px;
      background: var(--light);
      border: 2px solid var(--border);
      border-radius: 10px;
      font-size: 1.2rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .adjust-btn:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    /* Modal for photo */
    .photo-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.9);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .photo-modal.show {
      display: flex;
    }
    
    .modal-box {
      background: white;
      border-radius: 20px;
      padding: 30px;
      max-width: 600px;
      width: 100%;
      text-align: center;
    }
    
    .modal-box h3 {
      color: var(--primary);
      font-weight: 800;
      font-size: 2rem;
      margin-bottom: 20px;
    }
    
    .photo-preview {
      width: 100%;
      border-radius: 15px;
      margin-bottom: 20px;
    }
    
    .share-code {
      background: var(--light);
      padding: 20px;
      border-radius: 15px;
      margin-bottom: 20px;
    }
    
    .share-code p {
      margin: 0 0 10px;
      font-weight: 600;
      color: var(--dark);
    }
    
    .share-code code {
      font-size: 2rem;
      font-weight: 800;
      color: var(--primary);
      letter-spacing: 3px;
    }
    
    .modal-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .modal-buttons button {
      flex: 1;
      min-width: 150px;
    }
    
    @media (max-width: 768px) {
      .page-title h1 { font-size: 1.8rem; }
      .steps-simple { gap: 10px; }
      .step-box { min-width: 150px; padding: 15px 20px; }
      .frames-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
      .colors-grid { grid-template-columns: repeat(4, 1fr); }
      .color-circle { width: 50px; height: 50px; }
      .modal-buttons { flex-direction: column; }
      .modal-buttons button { width: 100%; }
    }
  </style>
</head>

<body>
  <?php include "Navigation.php"; ?>

  <div class="try-on-container">
    <div class="page-title">
      <h1>Try On Glasses</h1>
      <p>See how you look in 3 easy steps</p>
    </div>

    <!-- Simple 3 Steps -->
    <div class="steps-simple">
      <div class="step-box active" id="stepCamera">
        <div class="step-number">1</div>
        <div class="step-title">Turn On Camera</div>
      </div>
      <div class="step-box" id="stepChoose">
        <div class="step-number">2</div>
        <div class="step-title">Pick Your Glasses</div>
      </div>
      <div class="step-box" id="stepPhoto">
        <div class="step-number">3</div>
        <div class="step-title">Take a Photo</div>
      </div>
    </div>

    <div class="main-layout">
      <!-- Left: Camera -->
      <div>
        <div class="card">
          <div class="card-header">
            <i class="fas fa-video me-2"></i>Your Camera
          </div>
          <div class="card-body p-0">
            <div class="camera-wrapper">
              <video id="inputVideo" autoplay muted playsinline></video>
              <canvas id="outputCanvas"></canvas>
              
              <div class="camera-overlay d-none" id="cameraOverlay">
                <div class="spinner"></div>
                <p style="margin-top: 20px; font-size: 1.1rem;">Starting camera...</p>
              </div>
              
              <button class="photo-button" id="photoBtn">
                <i class="fas fa-camera"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="camera-status">
          <span class="status-badge off" id="statusBadge">Camera is off</span>
        </div>

        <div class="card">
          <div class="card-body">
            <button class="big-button btn-start" id="startBtn">
              <i class="fas fa-play-circle"></i>
              Start Camera
            </button>
            <button class="big-button btn-reset d-none" id="resetBtn">
              <i class="fas fa-redo"></i>
              Reset Position
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Controls -->
      <div>
        <!-- Face Shape Detector -->
        <div class="card">
          <div class="card-body">
            <a href="face-shape-detector.php" class="big-button btn-face-shape" target="_blank">
              <i class="fas fa-face-smile"></i>
              Find My Face Shape
            </a>
            <p class="text-center text-muted mb-0" style="font-size: 0.9rem;">
              Not sure which glasses suit you? Click here!
            </p>
          </div>
        </div>

        <!-- Frame Selection -->
        <div class="card">
          <div class="card-header">
            <i class="fas fa-glasses me-2"></i>Choose Glasses
          </div>
          <div class="card-body">
            <div class="frames-grid">
              <div class="frame-item selected" data-frame="A-TRIANGLE">
                <img src="Images/frames/ashape-frame-removebg-preview.png" class="frame-img" alt="A-Shape">
                <div class="frame-name">A-Shape</div>
              </div>
              <div class="frame-item" data-frame="V-TRIANGLE">
                <img src="Images/frames/vshape-frame-removebg-preview.png" class="frame-img" alt="V-Shape">
                <div class="frame-name">V-Shape</div>
              </div>
              <div class="frame-item" data-frame="ROUND">
                <img src="Images/frames/round-frame-removebg-preview.png" class="frame-img" alt="Round">
                <div class="frame-name">Round</div>
              </div>
              <div class="frame-item" data-frame="SQUARE">
                <img src="Images/frames/square-frame-removebg-preview.png" class="frame-img" alt="Square">
                <div class="frame-name">Square</div>
              </div>
              <div class="frame-item" data-frame="RECTANGLE">
                <img src="Images/frames/rectangle-frame-removebg-preview.png" class="frame-img" alt="Rectangle">
                <div class="frame-name">Rectangle</div>
              </div>
              <div class="frame-item" data-frame="OBLONG">
                <img src="Images/frames/oblong-frame-removebg-preview.png" class="frame-img" alt="Oblong">
                <div class="frame-name">Oblong</div>
              </div>
              <div class="frame-item" data-frame="DIAMOND">
                <img src="Images/frames/diamond-frame-removebg-preview.png" class="frame-img" alt="Diamond">
                <div class="frame-name">Diamond</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Color Selection -->
        <div class="card">
          <div class="card-header">
            <i class="fas fa-palette me-2"></i>Pick a Color
          </div>
          <div class="card-body">
            <div class="color-title">Popular Colors</div>
            <div class="colors-grid">
              <div class="color-item">
                <div class="color-circle selected" style="background: #1a1a1a;" data-color="#1a1a1a" data-name="Black"></div>
                <div class="color-label">Black</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #704214;" data-color="#704214" data-name="Brown"></div>
                <div class="color-label">Brown</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #34495e;" data-color="#34495e" data-name="Navy"></div>
                <div class="color-label">Navy</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #636e72;" data-color="#636e72" data-name="Gray"></div>
                <div class="color-label">Gray</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #2c5f7c;" data-color="#2c5f7c" data-name="Blue"></div>
                <div class="color-label">Blue</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #16a085;" data-color="#16a085" data-name="Teal"></div>
                <div class="color-label">Teal</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: #8e44ad;" data-color="#8e44ad" data-name="Purple"></div>
                <div class="color-label">Purple</div>
              </div>
              <div class="color-item">
                <div class="color-circle" style="background: linear-gradient(135deg, #bdc3c7, #ecf0f1);" data-color="#bdc3c7" data-name="Silver"></div>
                <div class="color-label">Silver</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Adjust Fit -->
        <div class="card">
          <div class="card-header">
            <i class="fas fa-sliders-h me-2"></i>Adjust Fit
          </div>
          <div class="card-body">
            <div class="control-section">
              <div class="control-title">
                <span>Size</span>
                <span class="control-value" id="sizeDisplay">2.4x</span>
              </div>
              <input type="range" class="slider" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
            </div>

            <div class="control-section">
              <div class="control-title">
                <span>Move Up/Down</span>
                <span class="control-value" id="posDisplay">0</span>
              </div>
              <div class="adjust-buttons">
                <button class="adjust-btn" id="moveUp">
                  <i class="fas fa-arrow-up"></i>
                </button>
                <button class="adjust-btn" id="moveDown">
                  <i class="fas fa-arrow-down"></i>
                </button>
              </div>
            </div>

            <div class="control-section">
              <div class="control-title">
                <span>Height</span>
                <span class="control-value" id="heightDisplay">70%</span>
              </div>
              <div class="adjust-buttons">
                <button class="adjust-btn" id="heightDown">
                  <i class="fas fa-minus"></i>
                </button>
                <button class="adjust-btn" id="heightUp">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Photo Modal -->
  <div class="photo-modal" id="photoModal">
    <div class="modal-box">
      <h3>Your Photo!</h3>
      <img id="photoPreview" class="photo-preview" src="" alt="Your photo">
      
      <div class="share-code">
        <p>Show this code to our staff:</p>
        <code id="shareCode">XXXXX</code>
      </div>
      
      <div class="modal-buttons">
        <button class="big-button btn-start" id="downloadPhoto">
          <i class="fas fa-download"></i>
          Download
        </button>
        <button class="big-button btn-reset" id="closeModal">
          <i class="fas fa-times"></i>
          Close
        </button>
      </div>
    </div>
  </div>

  <?php include "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.min.js"></script>

  <script>
    // Get all elements
    const video = document.getElementById('inputVideo');
    const canvas = document.getElementById('outputCanvas');
    const ctx = canvas.getContext('2d');
    const startBtn = document.getElementById('startBtn');
    const resetBtn = document.getElementById('resetBtn');
    const photoBtn = document.getElementById('photoBtn');
    const cameraOverlay = document.getElementById('cameraOverlay');
    const statusBadge = document.getElementById('statusBadge');
    const sizeSlider = document.getElementById('sizeSlider');
    const sizeDisplay = document.getElementById('sizeDisplay');
    const moveUp = document.getElementById('moveUp');
    const moveDown = document.getElementById('moveDown');
    const posDisplay = document.getElementById('posDisplay');
    const heightUp = document.getElementById('heightUp');
    const heightDown = document.getElementById('heightDown');
    const heightDisplay = document.getElementById('heightDisplay');
    const frameItems = document.querySelectorAll('.frame-item');
    const colorCircles = document.querySelectorAll('.color-circle');
    const photoModal = document.getElementById('photoModal');
    const photoPreview = document.getElementById('photoPreview');
    const shareCode = document.getElementById('shareCode');
    const downloadPhoto = document.getElementById('downloadPhoto');
    const closeModal = document.getElementById('closeModal');
    const stepCamera = document.getElementById('stepCamera');
    const stepChoose = document.getElementById('stepChoose');
    const stepPhoto = document.getElementById('stepPhoto');

    // Frame images
    const FRAMES = {
      'SQUARE': 'Images/frames/square-frame-removebg-preview.png',
      'ROUND': 'Images/frames/round-frame-removebg-preview.png',
      'OBLONG': 'Images/frames/oblong-frame-removebg-preview.png',
      'DIAMOND': 'Images/frames/diamond-frame-removebg-preview.png',
      'V-TRIANGLE': 'Images/frames/vshape-frame-removebg-preview.png',
      'A-TRIANGLE': 'Images/frames/ashape-frame-removebg-preview.png',
      'RECTANGLE': 'Images/frames/rectangle-frame-removebg-preview.png'
    };

    const glassesImages = {};
    let imagesLoaded = 0;
    let allLoaded = false;

    // Load all frame images
    Object.keys(FRAMES).forEach(key => {
      const img = new Image();
      img.src = FRAMES[key];
      img.onload = () => {
        glassesImages[key] = img;
        imagesLoaded++;
        if (imagesLoaded === Object.keys(FRAMES).length) {
          allLoaded = true;
        }
      };
    });

    // State
    let camera = null;
    let faceMesh = null;
    let processing = false;
    let frameCount = 0;
    let currentFrame = 'A-TRIANGLE';
    let currentColor = '#1a1a1a';
    let currentColorName = 'Black';
    let glassesSize = 2.4;
    let verticalPos = 0;
    let glassesHeight = 0.7;
    let angleOffset = 0;
    let calibrated = false;
    let faceDetected = false;

    // Update step status
    function updateSteps(active) {
      [stepCamera, stepChoose, stepPhoto].forEach(s => s.classList.remove('active', 'completed'));
      if (active === 1) {
        stepCamera.classList.add('active');
      } else if (active === 2) {
        stepCamera.classList.add('completed');
        stepChoose.classList.add('active');
      } else if (active === 3) {
        stepCamera.classList.add('completed');
        stepChoose.classList.add('completed');
        stepPhoto.classList.add('active');
      }
    }

    // Update status badge
    function updateStatus(text, type) {
      statusBadge.textContent = text;
      statusBadge.className = `status-badge ${type}`;
    }

    // Calculate head angle
    function getHeadAngle(landmarks) {
      const leftEye = landmarks[133];
      const rightEye = landmarks[362];
      const dx = rightEye.x - leftEye.x;
      const dy = rightEye.y - leftEye.y;
      return Math.atan2(dy, dx);
    }

    // Calibrate straight position
    function calibrate(landmarks) {
      const angle = getHeadAngle(landmarks);
      angleOffset = -angle;
      calibrated = true;
    }

    // Draw glasses on face
    function drawGlasses(landmarks) {
      const leftEye = landmarks[33];
      const rightEye = landmarks[263];
      let angle = getHeadAngle(landmarks);
      
      if (calibrated) angle += angleOffset;
      
      const eyeDistance = Math.hypot(
        rightEye.x * canvas.width - leftEye.x * canvas.width,
        rightEye.y * canvas.height - leftEye.y * canvas.height
      );

      const width = eyeDistance * glassesSize;
      const height = width * glassesHeight;
      let centerX = (leftEye.x * canvas.width + rightEye.x * canvas.width) / 2;
      let centerY = (leftEye.y * canvas.height + rightEye.y * canvas.height) / 2;
      centerY += verticalPos;

      if (centerX > 0 && centerY > 0 && width > 10 && glassesImages[currentFrame]) {
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(angle);
        
        ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
        ctx.shadowBlur = 10;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 4;
        
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        tempCanvas.width = glassesImages[currentFrame].width;
        tempCanvas.height = glassesImages[currentFrame].height;
        
        tempCtx.drawImage(glassesImages[currentFrame], 0, 0);
        
        tempCtx.globalCompositeOperation = 'source-in';
        tempCtx.fillStyle = currentColor;
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        ctx.drawImage(tempCanvas, -width / 2, -height / 2, width, height);
        
        ctx.restore();
      }
    }

    // Process camera frame
    async function onResults(results) {
      if (!allLoaded || processing) return;
      frameCount++;
      if (frameCount % 2 !== 0) return;

      processing = true;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

      if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        faceDetected = true;
        if (!calibrated && frameCount > 10) {
          calibrate(results.multiFaceLandmarks[0]);
        }
        results.multiFaceLandmarks.forEach(drawGlasses);
        updateStatus('Camera Ready', 'ready');
      } else {
        faceDetected = false;
        updateStatus('Looking for face...', 'loading');
      }

      processing = false;
    }

    // Resize canvas
    function resizeCanvas() {
      const container = canvas.parentElement;
      canvas.width = container.clientWidth;
      canvas.height = container.clientHeight;
    }

    // Initialize face detection
    async function initFaceMesh() {
      return new Promise((resolve) => {
        faceMesh = new FaceMesh({
          locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
        });
        faceMesh.setOptions({
          maxNumFaces: 1,
          refineLandmarks: false,
          minDetectionConfidence: 0.7,
          minTrackingConfidence: 0.5
        });
        faceMesh.onResults(onResults);
        faceMesh.initialize().then(resolve).catch(resolve);
      });
    }

    // Start camera
    async function startCamera() {
      try {
        updateStatus('Starting camera...', 'loading');
        cameraOverlay.classList.remove('d-none');
        
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
        });
        video.srcObject = stream;
        
        return new Promise((resolve) => {
          video.onloadedmetadata = () => {
            video.play().then(() => {
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

    // Generate random code
    function generateCode() {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let code = '';
      for (let i = 0; i < 6; i++) {
        code += chars[Math.floor(Math.random() * chars.length)];
      }
      return code;
    }

    // Event listeners
    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        await initFaceMesh();
        await startCamera();
        
        resizeCanvas();
        resetBtn.classList.remove('d-none');
        photoBtn.classList.add('show');
        updateSteps(1);

        camera = new Camera(video, {
          onFrame: async () => {
            if (faceMesh && !processing) await faceMesh.send({ image: video });
          },
          width: 320,
          height: 240
        });

        await camera.start();
        updateStatus('Camera Ready', 'ready');
        window.addEventListener('resize', resizeCanvas);
      } catch (err) {
        startBtn.disabled = false;
        updateStatus(err.name === 'NotAllowedError' ? 'Camera blocked' : 'Camera error', 'off');
      }
    });

    resetBtn.addEventListener('click', () => {
      if (faceDetected) {
        calibrated = false;
        updateStatus('Resetting...', 'loading');
        setTimeout(() => updateStatus('Camera Ready', 'ready'), 1000);
      }
    });

    photoBtn.addEventListener('click', () => {
      const photoCanvas = document.createElement('canvas');
      photoCanvas.width = canvas.width;
      photoCanvas.height = canvas.height;
      const photoCtx = photoCanvas.getContext('2d');
      photoCtx.drawImage(canvas, 0, 0);
      
      photoPreview.src = photoCanvas.toDataURL('image/png');
      shareCode.textContent = generateCode();
      photoModal.classList.add('show');
      updateSteps(3);
    });

    downloadPhoto.addEventListener('click', () => {
      const link = document.createElement('a');
      link.download = `santos-optical-${Date.now()}.png`;
      link.href = photoPreview.src;
      link.click();
    });

    closeModal.addEventListener('click', () => {
      photoModal.classList.remove('show');
    });

    photoModal.addEventListener('click', (e) => {
      if (e.target === photoModal) photoModal.classList.remove('show');
    });

    frameItems.forEach(item => {
      item.addEventListener('click', () => {
        frameItems.forEach(i => i.classList.remove('selected'));
        item.classList.add('selected');
        currentFrame = item.dataset.frame;
        updateSteps(2);
      });
    });

    colorCircles.forEach(circle => {
      circle.addEventListener('click', () => {
        colorCircles.forEach(c => c.classList.remove('selected'));
        circle.classList.add('selected');
        currentColor = circle.dataset.color;
        currentColorName = circle.dataset.name;
      });
    });

    sizeSlider.addEventListener('input', (e) => {
      glassesSize = parseFloat(e.target.value);
      sizeDisplay.textContent = glassesSize.toFixed(1) + 'x';
    });

    moveUp.addEventListener('click', () => {
      verticalPos -= 3;
      posDisplay.textContent = verticalPos;
    });

    moveDown.addEventListener('click', () => {
      verticalPos += 3;
      posDisplay.textContent = verticalPos;
    });

    heightUp.addEventListener('click', () => {
      glassesHeight = Math.min(1.0, glassesHeight + 0.05);
      heightDisplay.textContent = Math.round(glassesHeight * 100) + '%';
    });

    heightDown.addEventListener('click', () => {
      glassesHeight = Math.max(0.4, glassesHeight - 0.05);
      heightDisplay.textContent = Math.round(glassesHeight * 100) + '%';
    });

    // Initialize
    window.addEventListener('load', () => {
      setTimeout(initFaceMesh, 1000);
    });
  </script>
</body>
</html>