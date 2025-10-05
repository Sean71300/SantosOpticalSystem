<?php
include 'ActivityTracker.php';
require_once 'connect.php';

function getRecommendedFrames($shapeID, $limit = 3) {
    global $link;
    
    $sql = "SELECT DISTINCT p.*, 
            (SELECT GROUP_CONCAT(DISTINCT b.BranchName SEPARATOR ', ') 
             FROM ProductBranchMaster pb 
             JOIN BranchMaster b ON pb.BranchCode = b.BranchCode 
             WHERE pb.ProductID = p.ProductID 
             AND (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)
             AND pb.Stocks > 0) as AvailableBranches,
            (SELECT SUM(pb.Stocks) 
             FROM ProductBranchMaster pb 
             WHERE pb.ProductID = p.ProductID 
             AND (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)) as TotalStocks,
            br.BrandName
            FROM productMstr p
            LEFT JOIN brandMaster br ON p.BrandID = br.BrandID
            LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product')
            WHERE p.ShapeID = ?
            AND (p.Avail_FL = 'Available' OR p.Avail_FL IS NULL)
            AND a.ArchiveID IS NULL
            AND p.CategoryType IN ('Frame', 'Sunglasses')
            HAVING TotalStocks > 0
            ORDER BY TotalStocks DESC, p.Model ASC
            LIMIT ?";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $shapeID, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $frames = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $frames[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    return $frames;
}

function mapShapeToID($detectedShape) {
    $shapeMap = [
        'SQUARE'      => 5,
        'ROUND'       => 4,
        'OBLONG'      => 1,
        'DIAMOND'     => 3,
        'V-TRIANGLE'  => 2,
        'A-TRIANGLE'  => 6,
        'RECTANGLE'   => 7
    ];
    
    return $shapeMap[$detectedShape] ?? 1;
}

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

$pageTitle = "What's Your Face Shape? | Santos Optical";
$showResults = false;
$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiUrl = "http://72.60.210.48:5000/detect"; 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($_FILES['face_image']['tmp_name'])
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    
    if (isset($data['shape'])) {
        $showResults = true;
        $result = strtoupper($data['shape']);
        if ($result === 'TRIANGLE_SOFT') {
            $result = 'A-TRIANGLE';
        } elseif ($result === 'TRIANGLE') {
            $result = 'V-TRIANGLE';
        }
    } else {
        $showResults = true;
        $error = $data['error'] ?? 'Unable to detect face shape. Please ensure your face is clearly visible in the photo.';
        $result = 'NO_FACE_DETECTED';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
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
    }
    
    body {
        background-color: #FFF5F7;
        color: var(--dark);
    }

    .quiz-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #FF3E6C, #FF6B8B);
        color: white;
        margin-bottom: 30px;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 20px rgba(255, 62, 108, 0.2);
    }
    
    .quiz-header h1 {
        font-weight: 800;
        font-size: 2.5rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }
    
    .quiz-header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    
    .quiz-container {
        max-width: 800px;
        margin: 0 auto 50px;
        padding: 0 20px;
    }
    
    .quiz-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        border: none;
        transition: transform 0.3s ease;
    }
    
    .upload-area {
        border: 3px dashed #FFD1DC;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        background: rgba(255, 241, 244, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        min-height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .upload-area:hover {
        border-color: var(--primary);
        background: rgba(255, 241, 244, 0.8);
    }
    
    .upload-icon {
        font-size: 60px;
        color: var(--primary);
        margin-bottom: 15px;
    }
    
    .btn-quiz {
        background: var(--primary);
        color: white;
        border: none;
        padding: 15px 40px;
        font-weight: 700;
        border-radius: 50px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
    }
    
    .btn-quiz:hover {
        background: #FF2B5D;
        transform: translateY(-2px);
        color: white;
    }
    
    .result-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        text-align: center;
        animation: fadeIn 0.8s ease;
        border-top: 5px solid var(--primary);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .result-title {
        color: var(--primary);
        font-weight: 800;
        font-size: 2.2rem;
        margin-bottom: 20px;
    }
    
    .result-image {
        width: 220px;
        height: 220px;
        object-fit: cover;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        margin: 25px auto;
    }
    
    .recommendation {
        background: #F8FBFF;
        border-radius: 12px;
        padding: 30px;
        margin: 30px 0;
        border-left: 5px solid var(--secondary);
    }
    
    .camera-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 2000;
        justify-content: center;
        align-items: center;
    }
    
    .camera-container {
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 700px;
        padding: 20px;
        text-align: center;
    }
    
    .camera-preview {
        width: 100%;
        background: #333;
        border-radius: 10px;
        margin: 15px 0;
        aspect-ratio: 4/3;
        object-fit: cover;
    }
    
    .camera-controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }
    
    .btn-camera {
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }
    
    .btn-capture {
        background: var(--primary);
        color: white;
    }
    
    .btn-cancel {
        background: #f0f0f0;
        color: #333;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.95);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .loader {
        width: 60px;
        height: 60px;
        border: 6px solid #f3f3f3;
        border-top: 6px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
    }
    
    .virtual-tryon-guide {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin: 15px 0;
        text-align: center;
    }
    
    .tryon-tips {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .tryon-tip {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
    }
    
    .tryon-tip i {
        color: var(--primary);
    }
    </style>
</head>
<body>
    <?php include "Navigation.php"; ?>
    
    <div class="quiz-header">
        <div class="container">
            <h1>Curious which frame style suits you best?</h1>
            <p>Unlock AI-powered recommendations in seconds—just one photo away!</p>
        </div>
    </div>
    
    <div class="quiz-container">
        <?php if (!$showResults): ?>
        <!-- Upload Interface (unchanged) -->
        <p class="text-center text-muted mb-3" style="font-size: 0.9rem;">Choose your method:</p>
        <div class="upload-options">
            <div class="upload-option active" id="cameraOption">
                <i class="fas fa-camera"></i> Take Photo
            </div>
            <div class="upload-option" id="uploadOption">
                <i class="fas fa-upload"></i> Upload Photo
            </div>
        </div>

        <div id="fileUploadSection" style="display: none;">
            <form method="post" enctype="multipart/form-data" id="quizForm">
                <div class="upload-area" id="uploadArea">
                    <div id="uploadPrompt">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <h4>Click Here to Upload Your Photo</h4>
                        <p class="text-muted">or drag & drop your image file</p>
                    </div>
                    
                    <div class="image-preview-container" id="imagePreviewContainer">
                        <img id="previewImage" class="preview-image" src="" alt="Your uploaded photo">
                        <button type="button" class="btn btn-outline-secondary mt-3" id="changeImageBtn">
                            <i class="fas fa-redo me-1"></i> Change Photo
                        </button>
                    </div>
                    
                    <input type="file" name="face_image" accept="image/*" class="d-none" id="fileInput" required>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-quiz" id="analyzeBtn" disabled>
                        <i class="fas fa-search me-2"></i> Analyze My Face Shape
                    </button>
                </div>
            </form>
        </div>

        <div id="cameraSection">
            <div class="camera-instructions">
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <p>Allow camera access when prompted</p>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <p>Position your face in the center</p>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <p>Make sure lighting is even</p>
                </div>
            </div>
            
            <div class="text-center">
                <button class="btn btn-quiz" id="openCameraBtn">
                    <i class="fas fa-camera me-2"></i> Open Camera
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="result-card">
            <?php if ($result === 'NO_FACE_DETECTED'): ?>
                <div class="no-face-detected">
                    <i class="fas fa-exclamation-triangle no-face-icon"></i>
                    <h2 class="result-title">We couldn't detect a face</h2>
                    <div class="alert alert-warning">
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                    <a href="face-shape-detector.php" class="btn btn-quiz">Try Again</a>
                </div>
            <?php else: ?>
                <div class="result-title">All done!</div>
                <div class="text-center mb-4" style="font-size: 1.3rem; color: var(--dark);">
                    Your face shape is <span style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($result) ?></span>
                </div>
                
                <div class="result-image-container">
                    <?php 
                    $shapeImages = [
                        'SQUARE'      => 'Images/faces/square-face.jpg',
                        'ROUND'       => 'Images/faces/round-face.jpg',
                        'OBLONG'      => 'Images/faces/oval-face.jpg',
                        'DIAMOND'     => 'Images/faces/diamond-face.jpg',
                        'V-TRIANGLE'  => 'Images/faces/triangle-face.jpg',
                        'A-TRIANGLE'  => 'Images/faces/triangle-soft-face.jpg',
                        'RECTANGLE'   => 'Images/faces/rectangle-face.jpg'
                    ];
                    $image = $shapeImages[$result] ?? 'Images/faces/default-face.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($result) ?> face shape" class="result-image">
                </div>
                
                <!-- Frame Recommendations Section -->
                <div class="recommendation">
                    <h4><i class="fas fa-glasses me-2"></i> Perfect Frames For You</h4>
                    <p class="recommendation-text">
                        <?php
                        $recommendations = [
                            'SQUARE' => "Round or oval frames will soften your strong jawline and add balance.",
                            'ROUND' => "Angular or rectangular frames will add definition and contrast beautifully.",
                            'OBLONG' => "Deep frames with decorative temples will add width and break up the length.",
                            'DIAMOND' => "Cat-eye or oval frames will emphasize your cheekbones.",
                            'V-TRIANGLE' => "Frames with heavier top lines will balance your wider jawline.",
                            'A-TRIANGLE' => "Slightly rounded square frames will complement your face proportions.",
                            'RECTANGLE' => "Oversized or round frames will help soften your angular features."
                        ];
                        echo htmlspecialchars($recommendations[$result] ?? "Visit our store for personalized recommendations.");
                        ?>
                    </p>
                    
                    <!-- Virtual Try-On Button -->
                    <div class="text-center mt-4">
                        <button class="btn btn-quiz" id="virtualTryOnBtn">
                            <i class="fas fa-camera me-2"></i> Virtual Try-On
                        </button>
                        <div class="virtual-tryon-guide">
                            <p><strong>See how these frames look on you in real-time!</strong></p>
                            <div class="tryon-tips">
                                <div class="tryon-tip">
                                    <i class="fas fa-lightbulb"></i>
                                    <span>Good lighting works best</span>
                                </div>
                                <div class="tryon-tip">
                                    <i class="fas fa-user"></i>
                                    <span>Position face in center</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="face-shape-detector.php" class="btn btn-quiz">
                        <i class="fas fa-redo me-2"></i> Analyze Another Photo
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Camera Modal -->
    <div class="camera-modal" id="cameraModal">
        <div class="camera-container">
            <h3>Take Your Photo</h3>
            <p>Center your face in the frame and click capture</p>
            
            <video id="cameraPreview" class="camera-preview" autoplay playsinline></video>
            
            <div class="camera-controls">
                <button class="btn btn-cancel" id="closeCameraBtn">Cancel</button>
                <button class="btn btn-capture" id="captureBtn">Capture</button>
            </div>
        </div>
    </div>
    
    <!-- Virtual Try-On Modal -->
    <div class="camera-modal" id="virtualTryOnModal">
        <div class="camera-container" style="max-width: 700px;">
            <h3>Virtual Try-On</h3>
            <p>See how the recommended frames look on you!</p>
            
            <div class="position-relative">
                <video id="tryOnPreview" class="camera-preview" autoplay playsinline></video>
                <canvas id="frameOverlay" class="position-absolute top-0 start-0 w-100 h-100"></canvas>
            </div>
            
            <div class="camera-controls">
                <button class="btn btn-cancel" id="closeTryOnBtn">Close</button>
                <button class="btn btn-camera" id="switchCameraBtn">Switch Camera</button>
            </div>
        </div>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
        <h3 id="loadingText">Analyzing your face shape...</h3>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.10.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@0.0.1/dist/face-landmarks-detection.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
            
            // Basic elements
            const fileInput = document.getElementById('fileInput');
            const uploadArea = document.getElementById('uploadArea');
            const uploadPrompt = document.getElementById('uploadPrompt');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const previewImage = document.getElementById('previewImage');
            const changeImageBtn = document.getElementById('changeImageBtn');
            const quizForm = document.getElementById('quizForm');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Upload/Camera toggle
            const uploadOption = document.getElementById('uploadOption');
            const cameraOption = document.getElementById('cameraOption');
            const fileUploadSection = document.getElementById('fileUploadSection');
            const cameraSection = document.getElementById('cameraSection');
            const openCameraBtn = document.getElementById('openCameraBtn');
            const cameraModal = document.getElementById('cameraModal');
            const cameraPreview = document.getElementById('cameraPreview');
            const captureBtn = document.getElementById('captureBtn');
            const closeCameraBtn = document.getElementById('closeCameraBtn');
            
            // Virtual Try-On Elements
            const virtualTryOnBtn = document.getElementById('virtualTryOnBtn');
            const virtualTryOnModal = document.getElementById('virtualTryOnModal');
            const closeTryOnBtn = document.getElementById('closeTryOnBtn');
            const switchCameraBtn = document.getElementById('switchCameraBtn');
            const tryOnPreview = document.getElementById('tryOnPreview');
            const frameOverlay = document.getElementById('frameOverlay');
            
            let stream = null;
            let tryOnStream = null;
            let faceModel = null;
            let isTryOnActive = false;
            let currentFacingMode = 'user';
            let animationFrameId = null;
            
            // Generic frame image based on detected face shape
            const genericFrameImage = '<?= getGenericFrameImage($result) ?>';
            
            imagePreviewContainer.style.display = 'none';
            
            // File upload handling
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        uploadPrompt.style.display = 'none';
                        imagePreviewContainer.style.display = 'block';
                        analyzeBtn.disabled = false;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            changeImageBtn.addEventListener('click', function() {
                fileInput.value = '';
                uploadPrompt.style.display = 'block';
                imagePreviewContainer.style.display = 'none';
                analyzeBtn.disabled = true;
            });
            
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            quizForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadingOverlay.style.display = 'flex';
                setTimeout(() => {
                    this.submit();
                }, 3000);
            });
            
            // Upload/Camera toggle
            uploadOption.addEventListener('click', function() {
                this.classList.add('active');
                cameraOption.classList.remove('active');
                fileUploadSection.style.display = 'block';
                cameraSection.style.display = 'none';
            });
            
            cameraOption.addEventListener('click', function() {
                this.classList.add('active');
                uploadOption.classList.remove('active');
                fileUploadSection.style.display = 'none';
                cameraSection.style.display = 'block';
            });
            
            // Camera functionality
            openCameraBtn.addEventListener('click', async function() {
                cameraModal.style.display = 'flex';
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: 'user' }, audio: false 
                    });
                    cameraPreview.srcObject = stream;
                } catch (err) {
                    alert("Could not access camera. Please check permissions.");
                    cameraModal.style.display = 'none';
                }
            });
            
            captureBtn.addEventListener('click', function() {
                const canvas = document.createElement('canvas');
                canvas.width = cameraPreview.videoWidth;
                canvas.height = cameraPreview.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(function(blob) {
                    const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    
                    previewImage.src = URL.createObjectURL(blob);
                    uploadPrompt.style.display = 'none';
                    imagePreviewContainer.style.display = 'block';
                    analyzeBtn.disabled = false;
                    uploadOption.click();
                }, 'image/jpeg');
                
                closeCamera();
            });
            
            function closeCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                cameraModal.style.display = 'none';
            }
            
            closeCameraBtn.addEventListener('click', closeCamera);
            
            // Virtual Try-On Functions
            virtualTryOnBtn.addEventListener('click', async function() {
                virtualTryOnModal.style.display = 'flex';
                await startVirtualTryOn();
            });
            
            closeTryOnBtn.addEventListener('click', function() {
                stopVirtualTryOn();
                virtualTryOnModal.style.display = 'none';
            });
            
            switchCameraBtn.addEventListener('click', async function() {
                currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
                await restartVirtualTryOn();
            });
            
            async function startVirtualTryOn() {
                try {
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
                            width: 640, height: 480
                        }, audio: false
                    });
                    
                    tryOnPreview.srcObject = tryOnStream;
                    
                    tryOnPreview.onloadedmetadata = () => {
                        isTryOnActive = true;
                        detectFaces();
                    };
                    
                } catch (error) {
                    alert('Could not start virtual try-on. Please check camera permissions.');
                }
            }
            
            function stopVirtualTryOn() {
                isTryOnActive = false;
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                }
                if (tryOnStream) {
                    tryOnStream.getTracks().forEach(track => track.stop());
                    tryOnStream = null;
                }
                const ctx = frameOverlay.getContext('2d');
                ctx.clearRect(0, 0, frameOverlay.width, frameOverlay.height);
            }
            
            async function restartVirtualTryOn() {
                stopVirtualTryOn();
                await startVirtualTryOn();
            }
            
            async function detectFaces() {
                if (!isTryOnActive) return;
                
                const video = tryOnPreview;
                const canvas = frameOverlay;
                const ctx = canvas.getContext('2d');
                
                if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                }
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                const faces = await faceModel.estimateFaces({
                    input: video, returnTensors: false, flipHorizontal: false, predictIrises: false
                });
                
                if (faces.length > 0) {
                    const face = faces[0];
                    await overlayFrameOnFace(ctx, face, genericFrameImage);
                }
                
                animationFrameId = requestAnimationFrame(detectFaces);
            }
            
            async function overlayFrameOnFace(ctx, face, frameImageUrl) {
                const video = tryOnPreview;
                const leftEye = face.annotations.leftEyeUpper0[0];
                const rightEye = face.annotations.rightEyeUpper0[3];
                
                const eyeDistance = Math.sqrt(
                    Math.pow(rightEye[0] - leftEye[0], 2) + 
                    Math.pow(rightEye[1] - leftEye[1], 2)
                );
                
                const frameWidth = eyeDistance * 2.5;
                const frameHeight = frameWidth * 0.4;
                const frameX = (leftEye[0] + rightEye[0]) / 2 - frameWidth / 2;
                const frameY = (leftEye[1] + rightEye[1]) / 2 - frameHeight / 2 - eyeDistance * 0.3;
                
                const frameImg = new Image();
                frameImg.crossOrigin = "anonymous";
                
                return new Promise((resolve) => {
                    frameImg.onload = function() {
                        ctx.save();
                        const angle = Math.atan2(rightEye[1] - leftEye[1], rightEye[0] - leftEye[0]);
                        const centerX = frameX + frameWidth / 2;
                        const centerY = frameY + frameHeight / 2;
                        
                        ctx.translate(centerX, centerY);
                        ctx.rotate(angle);
                        ctx.translate(-centerX, -centerY);
                        ctx.drawImage(frameImg, frameX, frameY, frameWidth, frameHeight);
                        ctx.restore();
                        resolve();
                    };
                    frameImg.src = frameImageUrl;
                });
            }
        });
    </script>
</body>
</html>