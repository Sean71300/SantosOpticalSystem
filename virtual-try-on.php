<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Glasses Try-On | Santos Optical</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #2D3748;
      --primary-light: #4A5568;
      --secondary: #ED8936;
      --secondary-light: #F6AD55;
      --accent: #48BB78;
      --accent-light: #68D391;
      --danger: #F56565;
      --surface: #FFFFFF;
      --surface-dark: #F7FAFC;
      --text-primary: #1A202C;
      --text-secondary: #718096;
      --border: #E2E8F0;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06);
      --shadow-lg: 0 10px 25px rgba(0,0,0,0.15), 0 6px 10px rgba(0,0,0,0.1);
      --shadow-xl: 0 20px 40px rgba(0,0,0,0.2);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      min-height: 100vh;
      color: var(--text-primary);
    }
    
    .app-container {
      max-width: 1600px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .hero-header {
      text-align: center;
      padding: 40px 20px;
      color: white;
      margin-bottom: 30px;
      animation: fadeInDown 0.6s ease-out;
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .hero-header h1 {
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 800;
      margin-bottom: 12px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
      letter-spacing: -0.02em;
    }
    
    .hero-header p {
      font-size: clamp(1rem, 3vw, 1.3rem);
      opacity: 0.95;
      font-weight: 400;
    }
    
    .progress-bar-container {
      background: var(--surface);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-md);
      animation: fadeIn 0.8s ease-out 0.2s both;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .step-progress {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin-bottom: 12px;
    }
    
    .step-progress::before {
      content: '';
      position: absolute;
      top: 20px;
      left: 5%;
      right: 5%;
      height: 3px;
      background: var(--border);
      z-index: 0;
    }
    
    .progress-line {
      position: absolute;
      top: 20px;
      left: 5%;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--accent-light));
      transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 1;
    }
    
    .step-item {
      flex: 1;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    
    .step-circle {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--surface-dark);
      border: 3px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
      font-weight: 700;
      font-size: 18px;
      color: var(--text-secondary);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    
    .step-item.active .step-circle {
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      border-color: var(--accent);
      color: white;
      transform: scale(1.15);
      box-shadow: 0 0 0 6px rgba(72, 187, 120, 0.15);
    }
    
    .step-item.completed .step-circle {
      background: var(--accent);
      border-color: var(--accent);
      color: white;
    }
    
    .step-item.completed .step-circle::after {
      content: '✓';
      position: absolute;
      font-size: 20px;
    }
    
    .step-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      transition: color 0.3s ease;
    }
    
    .step-item.active .step-label,
    .step-item.completed .step-label {
      color: var(--accent);
    }
    
    .main-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
      animation: fadeIn 1s ease-out 0.4s both;
    }
    
    @media (min-width: 1024px) {
      .main-grid {
        grid-template-columns: 1.2fr 0.8fr;
      }
    }
    
    .card {
      background: var(--surface);
      border-radius: 20px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 1px solid rgba(255,255,255,0.1);
    }
    
    .card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-xl);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      padding: 18px 24px;
      font-weight: 700;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px solid var(--secondary);
    }
    
    .card-header i {
      margin-right: 10px;
      opacity: 0.9;
    }
    
    .card-body {
      padding: 24px;
    }
    
    .camera-container {
      position: relative;
      background: #000;
      border-radius: 16px;
      overflow: hidden;
      aspect-ratio: 4/3;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
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
      inset: 0;
      background: rgba(0,0,0,0.85);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      z-index: 10;
      backdrop-filter: blur(4px);
    }
    
    .camera-overlay.d-none {
      display: none;
    }
    
    .loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid rgba(255,255,255,0.2);
      border-top: 4px solid var(--accent);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin-bottom: 20px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .snapshot-overlay {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: none;
      gap: 12px;
      z-index: 20;
    }
    
    .snapshot-overlay.active {
      display: flex;
    }
    
    .btn-snapshot {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: white;
      border: 4px solid var(--secondary);
      color: var(--secondary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }
    
    .btn-snapshot:hover {
      transform: scale(1.1);
      background: var(--secondary);
      color: white;
      box-shadow: 0 6px 30px rgba(237, 137, 54, 0.6);
    }
    
    .btn-snapshot:active {
      transform: scale(0.95);
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 14px;
      margin: 16px 0;
      transition: all 0.3s ease;
    }
    
    .status-badge::before {
      content: '';
      width: 10px;
      height: 10px;
      border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    
    .status-badge.online {
      background: #C6F6D5;
      color: #22543D;
    }
    
    .status-badge.online::before {
      background: #38A169;
    }
    
    .status-badge.offline {
      background: #FED7D7;
      color: #742A2A;
    }
    
    .status-badge.offline::before {
      background: #E53E3E;
    }
    
    .status-badge.loading {
      background: #FEEBC8;
      color: #744210;
    }
    
    .status-badge.loading::before {
      background: #DD6B20;
    }
    
    .btn {
      padding: 12px 28px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 15px;
      border: none;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
      box-shadow: var(--shadow-sm);
    }
    
    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      color: white;
    }
    
    .btn-primary:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(72, 187, 120, 0.4);
    }
    
    .btn-secondary {
      background: linear-gradient(135deg, var(--secondary), var(--secondary-light));
      color: white;
    }
    
    .btn-secondary:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(237, 137, 54, 0.4);
    }
    
    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
    }
    
    .btn-outline:hover:not(:disabled) {
      background: var(--primary);
      color: white;
    }
    
    .btn-lg {
      padding: 14px 32px;
      font-size: 16px;
      width: 100%;
      margin-bottom: 12px;
    }
    
    .face-shape-cta {
      background: linear-gradient(135deg, #667eea, #764ba2);
      padding: 20px;
      border-radius: 16px;
      text-align: center;
      margin-bottom: 20px;
      box-shadow: var(--shadow-md);
    }
    
    .face-shape-cta h6 {
      color: white;
      font-weight: 700;
      margin-bottom: 12px;
      font-size: 1rem;
    }
    
    .face-shape-cta p {
      color: rgba(255,255,255,0.9);
      font-size: 0.9rem;
      margin-bottom: 16px;
    }
    
    .frame-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
      gap: 12px;
    }
    
    .frame-card {
      background: var(--surface-dark);
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-align: center;
    }
    
    .frame-card:hover {
      border-color: var(--accent);
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
    }
    
    .frame-card.active {
      border-color: var(--accent);
      background: rgba(72, 187, 120, 0.1);
      box-shadow: 0 0 0 4px rgba(72, 187, 120, 0.15);
    }
    
    .frame-img {
      width: 100%;
      height: 40px;
      object-fit: contain;
      margin-bottom: 8px;
    }
    
    .frame-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-primary);
    }
    
    .color-section {
      margin-top: 16px;
    }
    
    .color-category {
      margin-bottom: 20px;
    }
    
    .category-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
    }
    
    .category-title::before {
      content: '';
      width: 4px;
      height: 16px;
      background: var(--accent);
      margin-right: 8px;
      border-radius: 2px;
    }
    
    .color-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
      gap: 10px;
    }
    
    .color-option {
      text-align: center;
    }
    
    .color-swatch {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      border: 3px solid var(--border);
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 0 auto 6px;
      box-shadow: var(--shadow-sm);
      position: relative;
    }
    
    .color-swatch:hover {
      transform: scale(1.1);
      box-shadow: var(--shadow-md);
    }
    
    .color-swatch.active {
      border-color: var(--accent);
      transform: scale(1.15);
      box-shadow: 0 0 0 4px rgba(72, 187, 120, 0.25);
    }
    
    .color-swatch.active::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      font-size: 20px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
    
    .color-name {
      font-size: 11px;
      font-weight: 600;
      color: var(--text-secondary);
    }
    
    .material-toggle {
      display: flex;
      gap: 8px;
      margin-top: 16px;
      padding: 4px;
      background: var(--surface-dark);
      border-radius: 12px;
    }
    
    .material-btn {
      flex: 1;
      padding: 10px 16px;
      border: none;
      background: transparent;
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .material-btn:hover {
      background: rgba(72, 187, 120, 0.1);
      color: var(--accent);
    }
    
    .material-btn.active {
      background: var(--accent);
      color: white;
      box-shadow: var(--shadow-sm);
    }
    
    .control-group {
      margin-bottom: 24px;
    }
    
    .control-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }
    
    .control-label {
      font-weight: 700;
      font-size: 14px;
      color: var(--text-primary);
    }
    
    .control-value {
      font-weight: 700;
      color: var(--accent);
      background: rgba(72, 187, 120, 0.1);
      padding: 4px 12px;
      border-radius: 8px;
      font-size: 13px;
    }
    
    .slider {
      width: 100%;
      height: 6px;
      border-radius: 3px;
      background: var(--border);
      outline: none;
      -webkit-appearance: none;
    }
    
    .slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--accent);
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(72, 187, 120, 0.4);
      transition: all 0.3s ease;
    }
    
    .slider::-webkit-slider-thumb:hover {
      transform: scale(1.2);
      box-shadow: 0 4px 12px rgba(72, 187, 120, 0.6);
    }
    
    .slider::-moz-range-thumb {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--accent);
      cursor: pointer;
      border: none;
      box-shadow: 0 2px 8px rgba(72, 187, 120, 0.4);
    }
    
    .position-controls {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      margin-top: 12px;
    }
    
    .position-btn {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      border: 2px solid var(--border);
      background: var(--surface-dark);
      color: var(--text-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 18px;
    }
    
    .position-btn:hover {
      border-color: var(--accent);
      background: rgba(72, 187, 120, 0.1);
      color: var(--accent);
      transform: scale(1.05);
    }
    
    .position-btn:active {
      transform: scale(0.95);
    }
    
    .helper-text {
      font-size: 12px;
      color: var(--text-secondary);
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .helper-text i {
      color: var(--accent);
    }
    
    .tip-card {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
      border: 2px dashed var(--border);
      border-radius: 16px;
      padding: 20px;
      margin-top: 20px;
    }
    
    .tip-card h6 {
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .tip-card ul {
      margin: 0;
      padding-left: 20px;
      list-style: none;
    }
    
    .tip-card li {
      font-size: 14px;
      color: var(--text-secondary);
      margin-bottom: 8px;
      position: relative;
      padding-left: 20px;
    }
    
    .tip-card li::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: var(--accent);
      font-weight: bold;
    }
    
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.9);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(8px);
      animation: fadeIn 0.3s ease;
    }
    
    .modal-overlay.active {
      display: flex;
    }
    
    .modal-content {
      background: white;
      border-radius: 24px;
      padding: 32px;
      max-width: 600px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: var(--shadow-xl);
      animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .modal-header {
      text-align: center;
      margin-bottom: 24px;
    }
    
    .modal-header h3 {
      font-weight: 800;
      color: var(--primary);
      font-size: 1.8rem;
      margin-bottom: 8px;
    }
    
    .modal-header p {
      color: var(--text-secondary);
      font-size: 0.95rem;
    }
    
    .snapshot-preview {
      width: 100%;
      border-radius: 16px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-lg);
    }
    
    .reference-code {
      background: var(--surface-dark);
      padding: 16px;
      border-radius: 12px;
      margin-bottom: 20px;
      text-align: center;
      border: 2px dashed var(--border);
    }
    
    .reference-code label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: block;
      margin-bottom: 8px;
    }
    
    .reference-code code {
      font-size: 24px;
      font-weight: 800;
      color: var(--primary);
      letter-spacing: 2px;
      font-family: 'Courier New', monospace;
    }
    
    .modal-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    
    .modal-actions .btn {
      flex: 1;
      min-width: 140px;
    }
    
    @media (max-width: 768px) {
      .app-container {
        padding: 12px;
      }
      
      .hero-header {
        padding: 30px 16px;
      }
      
      .progress-bar-container {
        padding: 16px;
      }
      
      .step-circle {
        width: 38px;
        height: 38px;
        font-size: 16px;
      }
      
      .step-label {
        font-size: 11px;
      }
      
      .card-body {
        padding: 20px;
      }
      
      .frame-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
      }
      
      .color-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
      }
      
      .color-swatch {
        width: 45px;
        height: 45px;
      }
      
      .btn-snapshot {
        width: 56px;
        height: 56px;
        font-size: 22px;
      }
      
      .modal-content {
        padding: 24px;
      }
      
      .modal-actions {
        flex-direction: column;
      }
      
      .modal-actions .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="hero-header">
    <h1>Virtual Try-On Studio</h1>
    <p>Find your perfect frames with AI-powered fitting</p>
  </div>

  <div class="app-container">
    <div class="progress-bar-container">
      <div class="step-progress">
        <div class="progress-line" id="progressLine"></div>
        <div class="step-item active" id="step1">
          <div class="step-circle">1</div>
          <div class="step-label">Camera</div>
        </div>
        <div class="step-item" id="step2">
          <div class="step-circle">2</div>
          <div class="step-label">Frame</div>
        </div>
        <div class="step-item" id="step3">
          <div class="step-circle">3</div>
          <div class="step-label">Color</div>
        </div>
        <div class="step-item" id="step4">
          <div class="step-circle">4</div>
          <div class="step-label">Capture</div>
        </div>
      </div>
    </div>

    <div class="main-grid">
      <div class="camera-section">
        <div class="card">
          <div class="camera-container">
            <video id="inputVideo" autoplay muted playsinline></video>
            <canvas id="outputCanvas"></canvas>
            <div class="camera-overlay d-none" id="cameraOverlay">
              <div class="loading-spinner"></div>
              <p style="margin-top: 16px; font-size: 1.1rem;">Initializing camera...</p>
            </div>
            
            <div class="snapshot-overlay" id="snapshotOverlay">
              <button class="btn-snapshot" id="takeSnapshotBtn" title="Capture Photo">
                <i class="fas fa-camera"></i>
              </button>
            </div>
          </div>
        </div>
        
        <div class="status-badge offline" id="statusBadge">
          <span id="statusText">Camera inactive</span>
        </div>

        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-video"></i>Camera Controls</span>
          </div>
          <div class="card-body">
            <button id="startBtn" class="btn btn-primary btn-lg">
              <i class="fas fa-play-circle"></i>
              Start Camera
            </button>
            <button id="calibrateBtn" class="btn btn-outline btn-lg d-none">
              <i class="fas fa-sync-alt"></i>
              Reset Position
            </button>
          </div>
        </div>
      </div>

      <div class="controls-section">
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-glasses"></i>Frame Selection</span>
          </div>
          <div class="card-body">
            <div class="face-shape-cta">
              <h6><i class="fas fa-sparkles"></i> Not sure which frame suits you?</h6>
              <p>Let AI analyze your face shape and recommend the perfect frames</p>
              <a href="face-shape-detector.php" class="btn btn-secondary btn-lg" target="_blank">
                <i class="fas fa-wand-magic-sparkles"></i>
                Detect My Face Shape
              </a>
            </div>
            
            <div class="frame-grid">
              <div class="frame-card active" data-frame="A-TRIANGLE">
                <img src="Images/frames/ashape-frame-removebg-preview.png" alt="A-Shape" class="frame-img">
                <div class="frame-label">A-Shape</div>
              </div>
              <div class="frame-card" data-frame="V-TRIANGLE">
                <img src="Images/frames/vshape-frame-removebg-preview.png" alt="V-Shape" class="frame-img">
                <div class="frame-label">V-Shape</div>
              </div>
              <div class="frame-card" data-frame="ROUND">
                <img src="Images/frames/round-frame-removebg-preview.png" alt="Round" class="frame-img">
                <div class="frame-label">Round</div>
              </div>
              <div class="frame-card" data-frame="SQUARE">
                <img src="Images/frames/square-frame-removebg-preview.png" alt="Square" class="frame-img">
                <div class="frame-label">Square</div>
              </div>
              <div class="frame-card" data-frame="RECTANGLE">
                <img src="Images/frames/rectangle-frame-removebg-preview.png" alt="Rectangle" class="frame-img">
                <div class="frame-label">Rectangle</div>
              </div>
              <div class="frame-card" data-frame="OBLONG">
                <img src="Images/frames/oblong-frame-removebg-preview.png" alt="Oblong" class="frame-img">
                <div class="frame-label">Oblong</div>
              </div>
              <div class="frame-card" data-frame="DIAMOND">
                <img src="Images/frames/diamond-frame-removebg-preview.png" alt="Diamond" class="frame-img">
                <div class="frame-label">Diamond</div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-palette"></i>Colors & Materials</span>
          </div>
          <div class="card-body">
            <div class="color-section">
              <div class="color-category">
                <div class="category-title">Classic Neutrals</div>
                <div class="color-grid">
                  <div class="color-option">
                    <div class="color-swatch active" style="background: #1a1a1a;" data-color="#1a1a1a" data-name="Matte Black"></div>
                    <div class="color-name">Black</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #2d3436;" data-color="#2d3436" data-name="Charcoal"></div>
                    <div class="color-name">Charcoal</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #636e72;" data-color="#636e72" data-name="Slate Gray"></div>
                    <div class="color-name">Slate</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #704214;" data-color="#704214" data-name="Havana"></div>
                    <div class="color-name">Havana</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #8b7355;" data-color="#8b7355" data-name="Taupe"></div>
                    <div class="color-name">Taupe</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #5f4339;" data-color="#5f4339" data-name="Espresso"></div>
                    <div class="color-name">Espresso</div>
                  </div>
                </div>
              </div>

              <div class="color-category">
                <div class="category-title">Modern Tones</div>
                <div class="color-grid">
                  <div class="color-option">
                    <div class="color-swatch" style="background: #2c5f7c;" data-color="#2c5f7c" data-name="Ocean Blue"></div>
                    <div class="color-name">Ocean</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #34495e;" data-color="#34495e" data-name="Navy"></div>
                    <div class="color-name">Navy</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #16a085;" data-color="#16a085" data-name="Teal"></div>
                    <div class="color-name">Teal</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #7f5539;" data-color="#7f5539" data-name="Cognac"></div>
                    <div class="color-name">Cognac</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #95a5a6;" data-color="#95a5a6" data-name="Smoke"></div>
                    <div class="color-name">Smoke</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: #8e44ad;" data-color="#8e44ad" data-name="Plum"></div>
                    <div class="color-name">Plum</div>
                  </div>
                </div>
              </div>

              <div class="color-category">
                <div class="category-title">Premium Metallic</div>
                <div class="color-grid">
                  <div class="color-option">
                    <div class="color-swatch" style="background: linear-gradient(135deg, #bdc3c7, #ecf0f1);" data-color="#bdc3c7" data-name="Brushed Silver"></div>
                    <div class="color-name">Silver</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: linear-gradient(135deg, #b8860b, #d4af37);" data-color="#c5a647" data-name="Champagne Gold"></div>
                    <div class="color-name">Gold</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: linear-gradient(135deg, #cd7f32, #e8a87c);" data-color="#cd7f32" data-name="Rose Gold"></div>
                    <div class="color-name">Rose Gold</div>
                  </div>
                  <div class="color-option">
                    <div class="color-swatch" style="background: linear-gradient(135deg, #4a4a4a, #6b6b6b);" data-color="#5a5a5a" data-name="Gunmetal"></div>
                    <div class="color-name">Gunmetal</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="control-group">
              <div class="control-label">Material Finish</div>
              <div class="material-toggle">
                <button class="material-btn active" data-material="Matte">Matte</button>
                <button class="material-btn" data-material="Glossy">Glossy</button>
                <button class="material-btn" data-material="Pattern">Pattern</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-sliders-h"></i>Adjust Fit</span>
          </div>
          <div class="card-body">
            <div class="control-group">
              <div class="control-header">
                <span class="control-label">Frame Size</span>
                <span class="control-value" id="sizeValue">2.4x</span>
              </div>
              <input type="range" class="slider" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
              <div class="helper-text">
                <i class="fas fa-info-circle"></i>
                Adjust the overall size of the frames
              </div>
            </div>

            <div class="control-group">
              <div class="control-header">
                <span class="control-label">Frame Height</span>
                <span class="control-value" id="heightValue">70%</span>
              </div>
              <div class="position-controls">
                <button class="position-btn" id="heightDown">
                  <i class="fas fa-minus"></i>
                </button>
                <button class="position-btn" id="heightUp">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="helper-text">
                <i class="fas fa-info-circle"></i>
                Make frames taller or shorter
              </div>
            </div>

            <div class="control-group">
              <div class="control-header">
                <span class="control-label">Vertical Position</span>
                <span class="control-value" id="positionValue">0px</span>
              </div>
              <div class="position-controls">
                <button class="position-btn" id="positionDown">
                  <i class="fas fa-arrow-down"></i>
                </button>
                <button class="position-btn" id="positionUp">
                  <i class="fas fa-arrow-up"></i>
                </button>
              </div>
              <div class="helper-text">
                <i class="fas fa-info-circle"></i>
                Move frames up or down on your face
              </div>
            </div>
          </div>
        </div>

        <div class="tip-card">
          <h6><i class="fas fa-lightbulb"></i> Pro Tips</h6>
          <ul>
            <li>Position your face in the center of the camera</li>
            <li>Ensure good lighting for accurate try-on</li>
            <li>Try multiple frames before capturing</li>
            <li>Adjust fit settings for perfect alignment</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="snapshotModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Your Perfect Look! 🎉</h3>
        <p>Here's how you look in your selected frames</p>
      </div>
      
      <img id="snapshotImage" class="snapshot-preview" src="" alt="Your try-on snapshot">
      
      <div class="reference-code">
        <label>Reference Code</label>
        <code id="referenceCode">XXXXX</code>
        <p style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
          Share this code with our sales team
        </p>
      </div>
      
      <div class="modal-actions">
        <button class="btn btn-primary" id="downloadBtn">
          <i class="fas fa-download"></i>
          Download Image
        </button>
        <button class="btn btn-secondary" id="copyCodeBtn">
          <i class="fas fa-copy"></i>
          Copy Code
        </button>
        <button class="btn btn-outline" id="closeModalBtn">
          <i class="fas fa-times"></i>
          Close
        </button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    const statusBadge = document.getElementById('statusBadge');
    const statusText = document.getElementById('statusText');
    const sizeSlider = document.getElementById('sizeSlider');
    const sizeValue = document.getElementById('sizeValue');
    const heightDown = document.getElementById('heightDown');
    const heightUp = document.getElementById('heightUp');
    const heightValue = document.getElementById('heightValue');
    const positionDown = document.getElementById('positionDown');
    const positionUp = document.getElementById('positionUp');
    const positionValue = document.getElementById('positionValue');
    const frameCards = document.querySelectorAll('.frame-card');
    const colorSwatches = document.querySelectorAll('.color-swatch');
    const materialBtns = document.querySelectorAll('.material-btn');
    const snapshotOverlay = document.getElementById('snapshotOverlay');
    const takeSnapshotBtn = document.getElementById('takeSnapshotBtn');
    const snapshotModal = document.getElementById('snapshotModal');
    const snapshotImage = document.getElementById('snapshotImage');
    const referenceCode = document.getElementById('referenceCode');
    const downloadBtn = document.getElementById('downloadBtn');
    const copyCodeBtn = document.getElementById('copyCodeBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const progressLine = document.getElementById('progressLine');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const step4 = document.getElementById('step4');

    const FRAMES = {
      'SQUARE': { path: 'Images/frames/square-frame-removebg-preview.png', label: 'Square' },
      'ROUND': { path: 'Images/frames/round-frame-removebg-preview.png', label: 'Round' },
      'OBLONG': { path: 'Images/frames/oblong-frame-removebg-preview.png', label: 'Oblong' },
      'DIAMOND': { path: 'Images/frames/diamond-frame-removebg-preview.png', label: 'Diamond' },
      'V-TRIANGLE': { path: 'Images/frames/vshape-frame-removebg-preview.png', label: 'V-Shape' },
      'A-TRIANGLE': { path: 'Images/frames/ashape-frame-removebg-preview.png', label: 'A-Shape' },
      'RECTANGLE': { path: 'Images/frames/rectangle-frame-removebg-preview.png', label: 'Rectangle' }
    };

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
          console.log("All frame images loaded");
        }
      };
      img.onerror = () => {
        console.error(`Failed to load frame: ${frameData.label}`);
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
    let currentColor = '#1a1a1a';
    let currentColorName = 'Matte Black';
    let currentMaterial = 'Matte';
    let currentStep = 1;

    const textureCache = new Map();

    function updateProgressBar(step) {
      currentStep = step;
      const steps = [step1, step2, step3, step4];
      const progress = ((step - 1) / 3) * 90;
      progressLine.style.width = progress + '%';
      
      steps.forEach((stepEl, index) => {
        stepEl.classList.remove('active', 'completed');
        if (index < step - 1) {
          stepEl.classList.add('completed');
        } else if (index === step - 1) {
          stepEl.classList.add('active');
        }
      });
    }

    function updateStatus(status, type) {
      statusText.textContent = status;
      statusBadge.className = `status-badge ${type}`;
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
      const cacheKey = `${baseColor}-${materialType}-${width}x${height}`;
      
      if (textureCache.has(cacheKey)) {
        return textureCache.get(cacheKey);
      }

      const textureCanvas = document.createElement('canvas');
      const textureCtx = textureCanvas.getContext('2d');
      textureCanvas.width = width;
      textureCanvas.height = height;
      
      const hex = baseColor.replace('#', '');
      const r = parseInt(hex.substr(0, 2), 16);
      const g = parseInt(hex.substr(2, 2), 16);
      const b = parseInt(hex.substr(4, 2), 16);
      
      if (materialType === 'Matte') {
        const gradient = textureCtx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, `rgb(${Math.max(0, r-15)}, ${Math.max(0, g-15)}, ${Math.max(0, b-15)})`);
        gradient.addColorStop(0.5, baseColor);
        gradient.addColorStop(1, `rgb(${Math.max(0, r-10)}, ${Math.max(0, g-10)}, ${Math.max(0, b-10)})`);
        
        textureCtx.fillStyle = gradient;
        textureCtx.fillRect(0, 0, width, height);
        
        const imageData = textureCtx.getImageData(0, 0, width, height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 8) {
          const noise = (Math.random() - 0.5) * 6;
          data[i] = Math.max(0, Math.min(255, data[i] + noise));
          data[i + 1] = Math.max(0, Math.min(255, data[i + 1] + noise));
          data[i + 2] = Math.max(0, Math.min(255, data[i + 2] + noise));
        }
        textureCtx.putImageData(imageData, 0, 0);
        
      } else if (materialType === 'Glossy') {
        textureCtx.fillStyle = baseColor;
        textureCtx.fillRect(0, 0, width, height);
        
        const shine = textureCtx.createRadialGradient(
          width * 0.4, height * 0.3, 0,
          width * 0.4, height * 0.3, width * 0.6
        );
        shine.addColorStop(0, 'rgba(255, 255, 255, 0.25)');
        shine.addColorStop(0.5, 'rgba(255, 255, 255, 0.08)');
        shine.addColorStop(1, 'rgba(255, 255, 255, 0)');
        textureCtx.fillStyle = shine;
        textureCtx.fillRect(0, 0, width, height);
        
      } else if (materialType === 'Pattern') {
        const blockSize = 4;
        for (let x = 0; x < width; x += blockSize) {
          for (let y = 0; y < height; y += blockSize) {
            const value = Math.sin(x * 0.05) * Math.cos(y * 0.05) * 40;
            const patternR = Math.max(0, Math.min(255, r + value));
            const patternG = Math.max(0, Math.min(255, g + value));
            const patternB = Math.max(0, Math.min(255, b + value));
            
            textureCtx.fillStyle = `rgb(${patternR}, ${patternG}, ${patternB})`;
            textureCtx.fillRect(x, y, blockSize, blockSize);
          }
        }
        
        const shine = textureCtx.createLinearGradient(0, 0, width, height);
        shine.addColorStop(0, 'rgba(255,255,255,0.3)');
        shine.addColorStop(0.5, 'rgba(255,255,255,0.1)');
        shine.addColorStop(1, 'rgba(255,255,255,0)');
        textureCtx.fillStyle = shine;
        textureCtx.fillRect(0, 0, width, height);
      }
      
      textureCache.set(cacheKey, textureCanvas);
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
        
        canvasCtx.shadowColor = 'rgba(0, 0, 0, 0.35)';
        canvasCtx.shadowBlur = 8;
        canvasCtx.shadowOffsetX = 2;
        canvasCtx.shadowOffsetY = 3;
        
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        const frameWidth = Math.max(50, glassesImages[currentFrame].width);
        const frameHeight = Math.max(30, glassesImages[currentFrame].height);
        tempCanvas.width = frameWidth;
        tempCanvas.height = frameHeight;
        
        tempCtx.drawImage(glassesImages[currentFrame], 0, 0, frameWidth, frameHeight);
        
        const textureCanvas = createMaterialTexture(frameWidth, frameHeight, currentColor, currentMaterial);
        
        tempCtx.globalCompositeOperation = 'source-in';
        tempCtx.drawImage(textureCanvas, 0, 0);
        
        canvasCtx.drawImage(
          tempCanvas,
          -glassesWidth / 2,
          -glassesHeight / 2,
          glassesWidth,
          glassesHeight
        );
        
        canvasCtx.shadowColor = 'transparent';
        canvasCtx.shadowBlur = 0;
        canvasCtx.shadowOffsetX = 0;
        canvasCtx.shadowOffsetY = 0;
        
        canvasCtx.restore();
      }
    }

    async function onResults(results) {
      if (!glassesLoaded || isProcessing) return;
      frameCount++;
      if (frameCount % 2 !== 0) return;

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
          maxNumFaces: 1,
          refineLandmarks: false,
          minDetectionConfidence: 0.7,
          minTrackingConfidence: 0.5
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
          video: {
            facingMode: 'user',
            width: { ideal: 640 },
            height: { ideal: 480 },
            aspectRatio: { ideal: 4/3 }
          }
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

    function generateReferenceCode() {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let code = '';
      for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return code;
    }

    takeSnapshotBtn.addEventListener('click', () => {
      const snapshotCanvas = document.createElement('canvas');
      snapshotCanvas.width = canvasElement.width;
      snapshotCanvas.height = canvasElement.height;
      const ctx = snapshotCanvas.getContext('2d');
      ctx.drawImage(canvasElement, 0, 0);
      
      const dataUrl = snapshotCanvas.toDataURL('image/png');
      snapshotImage.src = dataUrl;
      referenceCode.textContent = generateReferenceCode();
      snapshotModal.classList.add('active');
      updateProgressBar(4);
    });

    downloadBtn.addEventListener('click', () => {
      const link = document.createElement('a');
      link.download = `santos-optical-tryon-${Date.now()}.png`;
      link.href = snapshotImage.src;
      link.click();
    });

    copyCodeBtn.addEventListener('click', () => {
      const code = referenceCode.textContent;
      navigator.clipboard.writeText(code).then(() => {
        const originalText = copyCodeBtn.innerHTML;
        copyCodeBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
          copyCodeBtn.innerHTML = originalText;
        }, 2000);
      });
    });

    closeModalBtn.addEventListener('click', () => {
      snapshotModal.classList.remove('active');
    });

    snapshotModal.addEventListener('click', (e) => {
      if (e.target === snapshotModal) {
        snapshotModal.classList.remove('active');
      }
    });

    frameCards.forEach(card => {
      card.addEventListener('click', () => {
        frameCards.forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        currentFrame = card.dataset.frame;
        if (currentStep < 2) updateProgressBar(2);
      });
    });

    colorSwatches.forEach(swatch => {
      swatch.addEventListener('click', () => {
        colorSwatches.forEach(s => s.classList.remove('active'));
        swatch.classList.add('active');
        currentColor = swatch.dataset.color;
        currentColorName = swatch.dataset.name;
        if (currentStep < 3) updateProgressBar(3);
      });
    });

    materialBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        materialBtns.forEach(b => b.classList.remove('active'));
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
        updateStatus("Recalibrating...", "loading");
        setTimeout(() => {
          updateStatus("Recalibrated!", "online");
        }, 1000);
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
        snapshotOverlay.classList.add('active');
        updateHeightDisplay();
        updatePositionDisplay();
        updateProgressBar(1);

        camera = new Camera(videoElement, {
          onFrame: async () => {
            if (faceMesh && !isProcessing) await faceMesh.send({ image: videoElement });
          },
          width: 320,
          height: 240
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

    window.addEventListener('load', () => {
      setTimeout(initializeFaceMesh, 1000);
    });
  </script>
</body>
</html>