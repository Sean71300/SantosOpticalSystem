<?php
// virtual-try-on.php
include 'ActivityTracker.php';
require_once 'connect.php';

$pageTitle = "Virtual Try-On | Santos Optical";

// Get face shape from URL parameter
$faceShape = isset($_GET['shape']) ? strtoupper($_GET['shape']) : 'ROUND';

// Map face shape to generic frame image
function getGenericFrameImage($faceShape) {
    $frameMap = [
        'SQUARE'      => 'Images/frames/round.png',
        'ROUND'       => 'Images/frames/rectangular.png',
        'OBLONG'      => 'Images/frames/deep.png',
        'DIAMOND'     => 'Images/frames/cateye.png',
        'V-TRIANGLE'  => 'Images/frames/browline.png',
        'A-TRIANGLE'  => 'Images/frames/rounded-square.png',
        'RECTANGLE'   => 'Images/frames/oval.png'
    ];
    
    return $frameMap[$faceShape] ?? 'Images/frames/default.png';
}

$frameImage = getGenericFrameImage($faceShape);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <style>
    :root {
        --primary: #FF3E6C;
        --secondary: #00C8B3;
        --dark: #333333;
        --light: #F8F9FA;
    }
    
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Montserrat', sans-serif;
    }

    .tryon-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 20px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .tryon-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    .tryon-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border: none;
    }
    
    .camera-preview {
        width: 100%;
        background: #333;
        border-radius: 15px;
        margin: 20px 0;
        aspect-ratio: 4/3;
        object-fit: cover;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .position-relative {
        position: relative;
    }
    
    #frameOverlay {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 10;
        pointer-events: none;
        border-radius: 15px;
    }
    
    .btn-tryon {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
    }
    
    .btn-tryon:hover {
        background: #FF2B5D;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 62, 108, 0.4);
        color: white;
    }
    
    .btn-outline-custom {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-custom:hover {
        background: rgba(255, 62, 108, 0.1);
    }
    
    .instructions {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .instruction-step {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .instruction-step:last-child {
        margin-bottom: 0;
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    
    .frame-info {
        background: linear-gradient(135deg, #FF3E6C, #FF6B8B);
        color: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin: 20px 0;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        color: white;
    }
    
    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255,255,255,0.3);
        border-top: 4px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .camera-controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    @media (max-width: 768px) {
        .tryon-container {
            margin: 15px auto;
            padding: 0 15px;
        }
        
        .tryon-card {
            padding: 20px;
        }
        
        .camera-controls {
            flex-direction: column;
            align-items: center;
        }
        
        .camera-controls .btn {
            width: 100%;
            max-width: 200px;
        }
    }
    </style>
</head>
<body>
    <?php include "Navigation.php"; ?>
    
    <div class="tryon-container">
        <div class="tryon-card">
            <div class="text-center mb-4">
                <h1 class="h2" style="color: var(--primary); font-weight: 700;">
                    <i class="fas fa-glasses me-2"></i>Virtual Try-On
                </h1>
                <p class="text-muted">See how the perfect frames look on you in real-time</p>
            </div>
            
            <div class="frame-info">
                <h4 class="mb-2">Recommended for <?= htmlspecialchars($faceShape) ?> Face Shape</h4>
                <p class="mb-0">This frame style complements your facial features perfectly</p>
            </div>
            
            <div class="instructions">
                <h5 class="mb-3"><i class="fas fa-lightbulb me-2"></i>How to get the best results:</h5>
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <span>Allow camera access when prompted</span>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <span>Position your face in the center of the frame</span>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <span>Ensure good lighting on your face</span>
                </div>
                <div class="instruction-step">
                    <div class="step-number">4</div>
                    <span>Keep a neutral expression for best fit</span>
                </div>
            </div>
            
            <div class="position-relative">
                <video id="tryOnPreview" class="camera-preview" autoplay playsinline></video>
                <canvas id="frameOverlay" class="position-absolute top-0 start-0 w-100 h-100"></canvas>
            </div>
            
            <div class="camera-controls">
                <button class="btn btn-tryon" id="startCameraBtn">
                    <i class="fas fa-camera me-2"></i> Start Camera
                </button>
                <button class="btn btn-tryon btn-outline-custom" id="switchCameraBtn" style="display: none;">
                    <i class="fas fa-sync-alt me-2"></i> Switch Camera
                </button>
                <a href="face-shape-detector.php" class="btn btn-tryon btn-outline-custom">
                    <i class="fas fa-arrow-left me-2"></i> Back to Results
                </a>
            </div>
            
            <div class="mt-4 text-center">
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    The frame will automatically adjust to fit your face. Move around to see different angles.
                </p>
            </div>
        </div>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
        <h4>Loading Virtual Try-On...</h4>
        <p class="mt-2">This may take a few moments</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.10.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@0.0.1/dist/face-landmarks-detection.min.js"></script>
    <script>
        // Virtual Try-On Configuration
        const config = {
            frameImage: '<?= $frameImage ?>',
            faceShape: '<?= $faceShape ?>'
        };

        let tryOnStream = null;
        let faceModel = null;
        let isTryOnActive = false;
        let currentFacingMode = 'user';
        let animationFrameId = null;

        // DOM Elements
        const startCameraBtn = document.getElementById('startCameraBtn');
        const switchCameraBtn = document.getElementById('switchCameraBtn');
        const tryOnPreview = document.getElementById('tryOnPreview');
        const frameOverlay = document.getElementById('frameOverlay');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // Start virtual try-on
        async function startVirtualTryOn() {
            try {
                loadingOverlay.style.display = 'flex';
                
                // Load face detection model
                if (!faceModel) {
                    faceModel = await faceLandmarksDetection.load(
                        faceLandmarksDetection.SupportedPackages.mediapipeFacemesh,
                        { maxFaces: 1 }
                    );
                }

                // Start camera
                tryOnStream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: currentFacingMode,
                        width: 640,
                        height: 480
                    },
                    audio: false
                });

                tryOnPreview.srcObject = tryOnStream;
                
                // Wait for video to be ready
                tryOnPreview.onloadedmetadata = () => {
                    loadingOverlay.style.display = 'none';
                    isTryOnActive = true;
                    startCameraBtn.style.display = 'none';
                    switchCameraBtn.style.display = 'inline-block';
                    detectFaces();
                };

            } catch (error) {
                loadingOverlay.style.display = 'none';
                console.error('Error starting virtual try-on:', error);
                alert('Could not start virtual try-on. Please check camera permissions and try again.');
            }
        }

        // Stop virtual try-on
        function stopVirtualTryOn() {
            isTryOnActive = false;
            
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
            
            if (tryOnStream) {
                tryOnStream.getTracks().forEach(track => track.stop());
                tryOnStream = null;
            }
            
            // Clear canvas
            const ctx = frameOverlay.getContext('2d');
            ctx.clearRect(0, 0, frameOverlay.width, frameOverlay.height);
            
            startCameraBtn.style.display = 'inline-block';
            switchCameraBtn.style.display = 'none';
        }

        // Restart virtual try-on (for camera switch)
        async function restartVirtualTryOn() {
            stopVirtualTryOn();
            await startVirtualTryOn();
        }

        // Face detection and frame overlay
        async function detectFaces() {
            if (!isTryOnActive) return;
            
            const video = tryOnPreview;
            const canvas = frameOverlay;
            const ctx = canvas.getContext('2d');
            
            // Set canvas dimensions to match video
            if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
            }
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Detect faces
            const faces = await faceModel.estimateFaces({
                input: video,
                returnTensors: false,
                flipHorizontal: false,
                predictIrises: false
            });
            
            // If face detected, overlay frame
            if (faces.length > 0) {
                const face = faces[0];
                await overlayFrameOnFace(ctx, face, config.frameImage);
            }
            
            // Continue detection
            animationFrameId = requestAnimationFrame(detectFaces);
        }

        // Overlay frame on detected face
        async function overlayFrameOnFace(ctx, face, frameImageUrl) {
            const video = tryOnPreview;
            
            // Get key facial points
            const leftEye = face.annotations.leftEyeUpper0[0];
            const rightEye = face.annotations.rightEyeUpper0[3];
            const noseBottom = face.annotations.noseTip[0];
            
            // Calculate face dimensions
            const eyeDistance = Math.sqrt(
                Math.pow(rightEye[0] - leftEye[0], 2) + 
                Math.pow(rightEye[1] - leftEye[1], 2)
            );
            
            // Frame dimensions based on face size
            const frameWidth = eyeDistance * 2.5;
            const frameHeight = frameWidth * 0.4;
            
            // Position frame (centered on eyes, slightly above)
            const frameX = (leftEye[0] + rightEye[0]) / 2 - frameWidth / 2;
            const frameY = (leftEye[1] + rightEye[1]) / 2 - frameHeight / 2 - eyeDistance * 0.3;
            
            // Create frame image
            const frameImg = new Image();
            frameImg.crossOrigin = "anonymous";
            
            return new Promise((resolve) => {
                frameImg.onload = function() {
                    // Draw frame with slight rotation based on face angle
                    ctx.save();
                    
                    // Calculate rotation angle from eye positions
                    const angle = Math.atan2(rightEye[1] - leftEye[1], rightEye[0] - leftEye[0]);
                    const centerX = frameX + frameWidth / 2;
                    const centerY = frameY + frameHeight / 2;
                    
                    ctx.translate(centerX, centerY);
                    ctx.rotate(angle);
                    ctx.translate(-centerX, -centerY);
                    
                    // Draw the frame image
                    ctx.drawImage(frameImg, frameX, frameY, frameWidth, frameHeight);
                    
                    ctx.restore();
                    resolve();
                };
                
                frameImg.src = frameImageUrl;
            });
        }

        // Event Listeners
        startCameraBtn.addEventListener('click', startVirtualTryOn);
        
        switchCameraBtn.addEventListener('click', async function() {
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            await restartVirtualTryOn();
        });

        // Stop camera when page is closed
        window.addEventListener('beforeunload', stopVirtualTryOn);
    </script>
</body>
</html>