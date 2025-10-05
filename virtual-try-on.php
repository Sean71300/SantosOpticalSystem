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
      --dark: #333333;
      --light: #F8F9FA;
      --border: #dee2e6;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      background-color: #FFF5F7;
      color: var(--dark);
      font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    
    .app-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 15px;
    }
    
    .header {
      text-align: center;
      padding: 40px 0;
      background: linear-gradient(135deg, #FF3E6C, #FF6B8B);
      color: white;
      margin-bottom: 30px;
      border-radius: 0 0 20px 20px;
      box-shadow: 0 4px 20px rgba(255, 62, 108, 0.2);
    }
    
    .header h1 {
      font-weight: 800;
      font-size: 2.5rem;
      margin-bottom: 5px;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }
    
    .header p {
      font-size: 1.2rem;
      opacity: 0.9;
      margin-bottom: 0;
    }
    
    /* step-wizard styles removed (wizard UI moved/removed) */
    
    .main-content {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }
    
    @media (min-width: 992px) {
      .main-content {
        grid-template-columns: 260px 1fr 380px; /* left, center, right */
        align-items: start;
        gap: 24px;
      }
      .left-column, .center-column, .right-column { display: block; }
      .left-column { grid-column: 1 / 2; }
      .center-column { grid-column: 2 / 3; }
      .right-column { grid-column: 3 / 4; }
    }
    
    .camera-section {
      position: relative;
    }
    
    .controls-section {
      position: relative;
    }
    
    .camera-container {
      position: relative;
      background: black;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      aspect-ratio: 4/3;
    }

    /* CTA over camera */
    .camera-cta {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 30;
      pointer-events: none; /* allow underlying elements until button enabled */
    }
    .camera-cta button { pointer-events: auto; }
    .camera-cta .btn {
      padding: 18px 28px;
      font-size: 1.05rem;
      border-radius: 999px;
      box-shadow: 0 10px 30px rgba(255,62,108,0.25);
      transform: translateY(0);
      transition: transform 220ms ease, box-shadow 220ms ease;
      background: linear-gradient(135deg, var(--primary), #FF2B5D);
    }
    .camera-cta .btn:active { transform: translateY(1px) scale(0.995); }
    .camera-cta .btn:hover { transform: translateY(-4px); }
    @keyframes pulseCTA { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
    .camera-cta .btn.pulse { animation: pulseCTA 2.2s infinite; }

    .camera-top-actions {
      position: absolute;
      top: 12px;
      right: 12px;
      left: auto;
      z-index: 35;
      display: flex;
      gap: 8px;
      align-items: center;
      background: rgba(255,255,255,0.95);
      padding: 6px;
      border-radius: 999px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    .camera-top-actions .btn-sm {
      background: white;
      color: var(--dark);
      border-radius: 999px;
      padding: 6px 10px;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* Camera status badge (top-left inside camera) */
    .camera-status {
      position: absolute;
      left: 12px;
      top: 12px;
      z-index: 40;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      border-radius: 18px;
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      background: rgba(0,0,0,0.45);
      backdrop-filter: blur(4px);
    }
    .camera-status.status-online { background: rgba(16,185,129,0.95); }
    .camera-status.status-offline { background: rgba(220,38,38,0.9); }

    /* Right column split: frames (left) + colors (right) */
    .right-column-inner {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 12px;
    }
    .frames-area .frame-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }
    .frames-area .frame-btn { padding: 10px; }
    .frames-area .frame-img { width: 72px; height: 48px; }

    /* emphasized colors area */
    .colors-area .card { box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .colors-area .card-header { background: linear-gradient(135deg, var(--secondary), #00b3a0); }

    /* Step badges used in place of tooltip icons */
    .step-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-weight: 800;
      font-size: 12px;
      margin-left: 8px;
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
    
    .snapshot-controls {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: none;
      gap: 10px;
      z-index: 20;
    }
    
    .snapshot-controls.active {
      display: flex;
    }
    
    .btn-snapshot {
      background: white;
      color: var(--primary);
      border: 3px solid var(--primary);
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .btn-snapshot:hover {
      transform: scale(1.1);
      background: var(--primary);
      color: white;
    }
    
    .card {
      background: white;
      border-radius: 20px;
      border: none;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      overflow: visible;
      transition: transform 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-2px);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), #FF2B5D);
      color: white;
      border: none;
      padding: 15px 20px;
      font-weight: 700;
      font-size: 1.1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-body {
      padding: 20px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), #FF2B5D);
      border: none;
      border-radius: 50px;
      padding: 12px 30px;
      font-weight: 700;
      font-size: 16px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
      letter-spacing: 0.5px;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 62, 108, 0.4);
      background: linear-gradient(135deg, #FF2B5D, var(--primary));
    }
    
    .btn-outline-primary {
      border: 2px solid var(--primary);
      color: var(--primary);
      border-radius: 50px;
      padding: 10px 20px;
      font-weight: 700;
      transition: all 0.3s ease;
      background: transparent;
    }
    
    .btn-outline-primary:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
    }
    
    .btn-sm {
      padding: 8px 16px;
      font-size: 14px;
    }
    
    .btn-face-shape {
      background: linear-gradient(135deg, var(--secondary), #00b3a0);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 10px 20px;
      font-weight: 700;
      font-size: 14px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 200, 179, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      margin-bottom: 15px;
      width: 100%;
      justify-content: center;
    }
    
    .btn-face-shape:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 200, 179, 0.4);
      background: linear-gradient(135deg, #00b3a0, var(--secondary));
      color: white;
    }
    
    .frame-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    /* Vertical frames list for right column */
    .vertical-frames {
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: stretch;
    }
    /* Make frame items look more like product list items */
    .right-column .frame-btn {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 12px;
      background: white;
    }
    .right-column .frame-img { width: 64px; height: 36px; }
    .right-column .frame-label { font-size: 13px; text-align: left; flex: 1; }
    .right-column .frame-btn .frame-meta { font-size: 12px; color: #6c757d; }
    .right-column .frame-btn:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    
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
      box-shadow: 0 4px 12px rgba(255, 62, 108, 0.2);
    }
    
    .frame-btn.active {
      border-color: var(--primary);
      background: rgba(255, 62, 108, 0.1);
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(255, 62, 108, 0.3);
    }
    
    .frame-img {
      width: 45px;
      height: 25px;
      object-fit: contain;
    }
    
    .frame-label {
      font-size: 10px;
      font-weight: 700;
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
      box-shadow: 0 6px 15px rgba(255, 62, 108, 0.4);
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
    
    .accordion-button {
      background: white;
      color: var(--dark);
      font-weight: 700;
      font-size: 14px;
      padding: 12px 15px;
      border: none;
      box-shadow: none;
    }
    
    .accordion-button:not(.collapsed) {
      background: rgba(255, 62, 108, 0.05);
      color: var(--primary);
    }
    
    .accordion-button:focus {
      box-shadow: none;
      border-color: transparent;
    }
    
    .accordion-button::after {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23FF3E6C'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    
    .accordion-item {
      border: 1px solid var(--border);
      margin-bottom: 8px;
      border-radius: 10px;
      overflow: hidden;
    }
    
    .accordion-body {
      padding: 15px;
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
      font-weight: 700;
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
      font-weight: 700;
      margin-bottom: 8px;
      color: var(--dark);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .control-value {
      font-weight: 700;
      color: var(--primary);
      background: rgba(255, 62, 108, 0.1);
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
      box-shadow: 0 2px 6px rgba(255, 62, 108, 0.3);
    }
    
    .form-range::-moz-range-thumb {
      background: var(--primary);
      border: none;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      box-shadow: 0 2px 6px rgba(255, 62, 108, 0.3);
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
      box-shadow: 0 4px 12px rgba(255, 62, 108, 0.3);
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
      font-weight: 700;
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
      background: linear-gradient(135deg, #FF3E6C, #FF6B8B);
      color: white;
      border-radius: 15px;
      padding: 15px;
      margin-top: 20px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(255, 62, 108, 0.2);
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
    
    .snapshot-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.9);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .snapshot-modal.active {
      display: flex;
    }
    
    .snapshot-content {
      background: white;
      border-radius: 20px;
      padding: 30px;
      max-width: 600px;
      width: 100%;
      text-align: center;
    }
    
    .snapshot-preview {
      max-width: 100%;
      border-radius: 15px;
      margin: 20px 0;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    
    .snapshot-actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    
    .controls-scroll-indicator {
      position: sticky;
      top: 10px;
      background: linear-gradient(135deg, rgba(255, 62, 108, 0.95), rgba(255, 43, 93, 0.95));
      color: white;
      padding: 10px 15px;
      border-radius: 10px;
      text-align: center;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
      z-index: 10;
    }
    
    .controls-scroll-indicator i {
      margin-left: 8px;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-5px);
      }
      60% {
        transform: translateY(-3px);
      }
    }
    
    @media (max-width: 991px) {
      .controls-scroll-indicator {
        display: none;
      }
      
      .main-content {
        grid-template-columns: 1fr;
      }
    }
    
    @media (max-width: 767px) {
      .app-container {
        padding: 10px;
      }
      
      .header {
        padding: 30px 0;
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
      
      /* step-wizard removed for mobile */
      
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
        flex-direction: column;
      }
      
      .position-controls span {
        order: -1;
        font-size: 0.9rem;
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
      
      .btn-snapshot {
        width: 50px;
        height: 50px;
        font-size: 20px;
      }
      
      .snapshot-controls {
        bottom: 10px;
      }
      .camera-cta .btn { padding: 12px 18px; font-size: 0.95rem; }
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
  <?php include "Navigation.php"; ?>

  <div class="header">
    <div class="container">
      <h1>Virtual Glasses Try-On</h1>
      <p>Find your perfect frame and color in real-time</p>
    </div>
  </div>

  <div class="app-container">
    <!-- New shop-like layout: left = adjustments, center = camera + color/material, right = frames -->
    <div class="main-content">
      <div class="left-column">
        <!-- Camera moved to left for mobile-first flow -->
        <div class="camera-section">
          <div class="camera-container">
            <video id="inputVideo" autoplay muted playsinline></video>
            <canvas id="outputCanvas"></canvas>

            <!-- Top actions: Save & Calibrate -->
            <div class="camera-top-actions">
              <button id="saveBtn" class="btn btn-outline-primary btn-sm me-2" title="Save">
                <i class="fas fa-save me-1"></i>Save
              </button>
              <button id="calibrateBtn" class="btn btn-outline-primary btn-sm" title="Calibrate">
                <i class="fas fa-sync-alt me-1"></i>Calibrate
              </button>
            </div>

            <!-- Centered start CTA over camera -->
            <div class="camera-cta" id="cameraCta">
              <button id="startBtn" class="btn btn-primary btn-lg">
                <i class="fas fa-camera-video me-2"></i>Start Camera
              </button>
            </div>

            <div class="camera-overlay d-none" id="cameraOverlay">
              <div class="loading-spinner"></div>
              <p class="mt-3">Starting camera...</p>
            </div>

            <div class="snapshot-controls" id="snapshotControls">
              <button class="btn-snapshot" id="takeSnapshotBtn" title="Take Photo" data-bs-toggle="tooltip" data-bs-placement="top">
                <i class="fas fa-camera"></i>
              </button>
            </div>
            <!-- small camera status badge (moved inside camera) -->
            <div class="camera-status status-offline" id="cameraStatus">
              <span id="cameraStatusText">Camera is off</span>
            </div>
          </div>
        </div>

        <?php if (preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'])): ?>
        <div class="mobile-tips">
          <h6>Mobile Tips</h6>
          <ul>
            <li>Ensure good lighting for best results</li>
            <li>Hold device steady at eye level</li>
            <li>Keep face centered in frame</li>
            <li>Close other apps for better performance</li>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <div class="center-column">
        </div>

          <!-- Adjust Fit moved to center as step 2 -->
          <div class="card">
            <div class="card-header">
              <span><i class="fas fa-sliders-h me-2"></i>Adjust Fit</span>
              <span class="step-badge">2</span>
            </div>
            <div class="card-body">
              <div class="control-group">
                <div class="control-label">
                  <span>Frame Size</span>
                  <span class="control-value" id="sizeValue">2.4x</span>
                </div>
                <input type="range" class="form-range" id="sizeSlider" min="1.8" max="3.0" step="0.1" value="2.4">
                <small class="text-muted">Drag to make frames larger or smaller</small>
              </div>
              
              <div class="control-group">
                <div class="control-label">
                  <span>Frame Height</span>
                  <span class="control-value" id="heightValue">70%</span>
                </div>
                <div class="position-controls">
                  <button class="btn btn-outline-primary position-btn" id="heightDown">
                    <i class="fas fa-minus"></i>
                  </button>
                  <span>Shorter / Taller</span>
                  <button class="btn btn-outline-primary position-btn" id="heightUp">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
                <small class="text-muted">Adjust frame proportions</small>
              </div>
              
              <div class="control-group">
                <div class="control-label">
                  <span>Vertical Position</span>
                  <span class="control-value" id="positionValue">0px</span>
                </div>
                <div class="position-controls">
                  <button class="btn btn-outline-primary position-btn" id="positionDown">
                    <i class="fas fa-arrow-down"></i>
                  </button>
                  <span>Move Up / Down</span>
                  <button class="btn btn-outline-primary position-btn" id="positionUp">
                    <i class="fas fa-arrow-up"></i>
                  </button>
                </div>
                <small class="text-muted">Move frames higher or lower on face</small>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="right-column">
        <div class="right-column-inner">
          <div class="frames-area">
            <div class="card">
              <div class="card-header">
                <span><i class="fas fa-glasses me-2"></i>Frame Styles</span>
                <span class="step-badge">3</span>
              </div>
              <div class="card-body">
                <a href="https://santosopticalclinic.com/face-shape-detector.php" class="btn-face-shape" target="_blank">
                  <i class="fas fa-face-smile"></i>
                  <span>Discover Your Face Shape</span>
                </a>
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
          </div>

          <div class="colors-area">
            <div class="card">
              <div class="card-header">
                <span><i class="fas fa-palette me-2"></i>Colors & Materials</span>
                <span class="step-badge">5</span>
              </div>
              <div class="card-body">
                <!-- moved colors here -->
                <div class="accordion" id="colorAccordion">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#classicColors" aria-expanded="true">
                        Classic Neutrals
                      </button>
                    </h2>
                    <div id="classicColors" class="accordion-collapse collapse show" data-bs-parent="#colorAccordion">
                      <div class="accordion-body">
                        <div class="color-grid">
                          <div class="color-option">
                            <div class="color-btn active" style="background: #1a1a1a;" data-color="#1a1a1a" data-color-name="Matte Black"></div>
                            <div class="color-label">Black</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #2d3436;" data-color="#2d3436" data-color-name="Charcoal"></div>
                            <div class="color-label">Charcoal</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #636e72;" data-color="#636e72" data-color-name="Slate Gray"></div>
                            <div class="color-label">Slate</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #704214;" data-color="#704214" data-color-name="Havana"></div>
                            <div class="color-label">Havana</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #8b7355;" data-color="#8b7355" data-color-name="Taupe"></div>
                            <div class="color-label">Taupe</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #5f4339;" data-color="#5f4339" data-color-name="Espresso"></div>
                            <div class="color-label">Espresso</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modernTones">
                        Modern Tones
                      </button>
                    </h2>
                    <div id="modernTones" class="accordion-collapse collapse" data-bs-parent="#colorAccordion">
                      <div class="accordion-body">
                        <div class="color-grid">
                          <div class="color-option">
                            <div class="color-btn" style="background: #2c5f7c;" data-color="#2c5f7c" data-color-name="Ocean Blue"></div>
                            <div class="color-label">Ocean</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #34495e;" data-color="#34495e" data-color-name="Navy"></div>
                            <div class="color-label">Navy</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #16a085;" data-color="#16a085" data-color-name="Teal"></div>
                            <div class="color-label">Teal</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #7f5539;" data-color="#7f5539" data-color-name="Cognac"></div>
                            <div class="color-label">Cognac</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #95a5a6;" data-color="#95a5a6" data-color-name="Smoke"></div>
                            <div class="color-label">Smoke</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: #8e44ad;" data-color="#8e44ad" data-color-name="Plum"></div>
                            <div class="color-label">Plum</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#premiumMetallic">
                        Premium Metallic
                      </button>
                    </h2>
                    <div id="premiumMetallic" class="accordion-collapse collapse" data-bs-parent="#colorAccordion">
                      <div class="accordion-body">
                        <div class="color-grid">
                          <div class="color-option">
                            <div class="color-btn" style="background: linear-gradient(135deg, #bdc3c7, #ecf0f1);" data-color="#bdc3c7" data-color-name="Brushed Silver"></div>
                            <div class="color-label">Silver</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: linear-gradient(135deg, #b8860b, #d4af37);" data-color="#c5a647" data-color-name="Champagne Gold"></div>
                            <div class="color-label">Gold</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: linear-gradient(135deg, #cd7f32, #e8a87c);" data-color="#cd7f32" data-color-name="Rose Gold"></div>
                            <div class="color-label">Rose Gold</div>
                          </div>
                          <div class="color-option">
                            <div class="color-btn" style="background: linear-gradient(135deg, #4a4a4a, #6b6b6b);" data-color="#5a5a5a" data-color-name="Gunmetal"></div>
                            <div class="color-label">Gunmetal</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="control-group mt-3">
                  <div class="control-label">
                    <span>Material Effect</span>
                    <span class="step-badge">4</span>
                  </div>
                  <div class="material-controls">
                    <button class="material-btn active" data-material="Matte">Matte</button>
                    <button class="material-btn" data-material="Glossy">Glossy</button>
                    <button class="material-btn" data-material="Pattern">Pattern</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>

  <!-- Snapshot Modal -->
  <div class="snapshot-modal" id="snapshotModal">
    <div class="snapshot-content">
      <h3 style="color: var(--primary); font-weight: 700;">Your Perfect Look!</h3>
      <img id="snapshotImage" class="snapshot-preview" src="" alt="Your snapshot">
      <div class="snapshot-actions">
        <button class="btn btn-primary" id="downloadSnapshotBtn">
          <i class="fas fa-download me-2"></i>Download
        </button>
        <button class="btn btn-outline-primary" id="closeSnapshotBtn">
          <i class="fas fa-times me-2"></i>Close
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
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const videoElement = document.getElementById('inputVideo');
    const canvasElement = document.getElementById('outputCanvas');
    const canvasCtx = canvasElement.getContext('2d');
  const startBtn = document.getElementById('startBtn');
  const calibrateBtn = document.getElementById('calibrateBtn');
  const saveBtn = document.getElementById('saveBtn');
  const cameraOverlay = document.getElementById('cameraOverlay');
  const cameraStatus = document.getElementById('cameraStatus');
  const cameraStatusText = document.getElementById('cameraStatusText');
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
    const snapshotControls = document.getElementById('snapshotControls');
    const takeSnapshotBtn = document.getElementById('takeSnapshotBtn');
    const snapshotModal = document.getElementById('snapshotModal');
    const snapshotImage = document.getElementById('snapshotImage');
    const downloadSnapshotBtn = document.getElementById('downloadSnapshotBtn');
    const closeSnapshotBtn = document.getElementById('closeSnapshotBtn');

  // Step wizard elements (may be removed) - keep references if present
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
          console.log("All frame images loaded successfully");
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

    const textureCache = new Map();

    function updateStepWizard(stepNumber) {
      const steps = [step1, step2, step3, step4].filter(Boolean);
      if (steps.length === 0) return; // step wizard removed in layout
      steps.forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index < stepNumber - 1) {
          step.classList.add('completed');
        } else if (index === stepNumber - 1) {
          step.classList.add('active');
        }
      });
    }

    function updateStatus(status, type) {
      if (cameraStatusText) cameraStatusText.textContent = status;
      if (cameraStatus) {
        cameraStatus.className = `camera-status status-${type}`;
      }
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

    // Snapshot functionality (reusable)
    function showSnapshotFromCanvas() {
      const snapshotCanvas = document.createElement('canvas');
      snapshotCanvas.width = canvasElement.width;
      snapshotCanvas.height = canvasElement.height;
      const ctx = snapshotCanvas.getContext('2d');
      ctx.drawImage(canvasElement, 0, 0);

      const dataUrl = snapshotCanvas.toDataURL('image/png');
      snapshotImage.src = dataUrl;
      snapshotModal.classList.add('active');
      try { updateStepWizard(4); } catch (e) { /* ignore if wizard removed */ }
    }

    takeSnapshotBtn.addEventListener('click', showSnapshotFromCanvas);

    if (saveBtn) saveBtn.addEventListener('click', showSnapshotFromCanvas);

    downloadSnapshotBtn.addEventListener('click', () => {
      const link = document.createElement('a');
      link.download = `santos-optical-tryon-${Date.now()}.png`;
      link.href = snapshotImage.src;
      link.click();
    });

    closeSnapshotBtn.addEventListener('click', () => {
      snapshotModal.classList.remove('active');
    });

    snapshotModal.addEventListener('click', (e) => {
      if (e.target === snapshotModal) {
        snapshotModal.classList.remove('active');
      }
    });

    frameButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        frameButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFrame = btn.dataset.frame;
        updateStepWizard(2);
      });
    });

    colorButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        colorButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentColor = btn.dataset.color;
        currentColorName = btn.dataset.colorName;
        updateStepWizard(3);
      });
    });

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
        setTimeout(() => {
          updateStatus("Recalibrated!", "online");
        }, 1000);
      }
    });

    const cameraCta = document.getElementById('cameraCta');
    // pulse CTA to draw attention
    const ctaBtn = cameraCta ? cameraCta.querySelector('button') : null;
    if (ctaBtn) ctaBtn.classList.add('pulse');
    startBtn.addEventListener('click', async () => {
      try {
        startBtn.disabled = true;
        updateStatus("Initializing...", "loading");

        await initializeFaceMesh();
        const stream = await startCamera();
        
  resizeCanvasToDisplay();
  // hide CTA overlay after camera starts
  if (cameraCta) cameraCta.style.display = 'none';
  if (ctaBtn) ctaBtn.classList.remove('pulse');
  calibrateBtn.classList.remove('d-none');
  if (snapshotControls) snapshotControls.classList.add('active');
        updateHeightDisplay();
        updatePositionDisplay();
        updateStepWizard(1);

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

    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    window.addEventListener('load', () => {
      setTimeout(initializeFaceMesh, 1000);
    });
  </script>
</body>
</html>