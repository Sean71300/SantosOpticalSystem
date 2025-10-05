<?php
include 'ActivityTracker.php';
require_once 'connect.php'; // This creates $link variable

/**
 * Get recommended frames for a specific face shape from the database
 */
function getRecommendedFrames($shapeID, $limit = 3) {
    global $link; // Use the $link variable from connect.php
    
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

/**
 * Map detected face shape names to database ShapeID
 */
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

// face-shape-detector.php
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
        // Convert shape names for display
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
        
        .quiz-card:hover {
            transform: translateY(-5px);
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
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
        }
        
        .btn-quiz:hover {
            background: #FF2B5D;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 62, 108, 0.4);
            color: white;
        }
        
        .btn-quiz.btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            box-shadow: none;
        }

        .btn-quiz.btn-outline:hover {
            background: rgba(255, 62, 108, 0.1);
            color: var(--primary);
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
        
        .result-shape {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(255, 62, 108, 0.3);
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
        
        .primary-cta {
            background: linear-gradient(135deg, #FF3E6C, #FF6B8B);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            color: white;
            text-align: center;
        }
        
        .primary-cta h3 {
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .primary-cta p {
            opacity: 0.95;
            margin-bottom: 20px;
        }
        
        .btn-shop-now {
            background: white;
            color: var(--primary);
            padding: 15px 50px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .btn-shop-now:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.3);
            color: var(--primary);
        }
        
        .recommendation {
            background: #F8FBFF;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            border-left: 5px solid var(--secondary);
            text-align: left;
        }
        
        .recommendation h4 {
            color: var(--secondary);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .recommendation-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .why-it-works {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .why-it-works h5 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .why-item {
            display: flex;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .why-item i {
            color: var(--secondary);
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .frame-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .frame-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .frame-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .frame-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .frame-info {
            padding: 15px;
            text-align: center;
        }
        
        .frame-info h6 {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .frame-info p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .fact-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .fact-card h5 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .fact-card p {
            margin: 0;
            line-height: 1.6;
        }
        
        .social-share {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .social-share h5 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .share-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .share-btn:hover {
            transform: scale(1.1);
        }
        
        .share-btn.facebook { background: #1877F2; }
        .share-btn.twitter { background: #1DA1F2; }
        .share-btn.whatsapp { background: #25D366; }
        .share-btn.link { background: #666; }
        
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
            max-width: 600px;
            padding: 20px;
            text-align: center;
            position: relative;
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
            transition: all 0.3s ease;
        }
        
        .btn-capture {
            background: var(--primary);
            color: white;
        }
        
        .btn-capture:hover {
            background: #FF2B5D;
            transform: scale(1.05);
        }
        
        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
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
        
        .image-preview-container {
            display: none;
            width: 100%;
            text-align: center;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin: 0 auto;
            display: block;
        }
        
        .upload-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .upload-option {
            padding: 12px 25px;
            border-radius: 50px;
            background: white;
            border: 2px solid #f0f0f0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .upload-option:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .upload-option.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }
        
        .camera-instructions {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
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
        }
        
        .no-face-detected {
            text-align: center;
            padding: 30px;
        }
        
        .no-face-icon {
            font-size: 4rem;
            color: #FFC107;
            margin-bottom: 20px;
        }
        
        .permission-step {
            font-weight: bold;
            color: #2a6496;
        }
        
        @media (max-width: 768px) {
            .quiz-header {
                padding: 30px 0;
            }
            
            .quiz-header h1 {
                font-size: 2rem;
            }
            
            .quiz-card, .result-card {
                padding: 25px;
            }
            
            .upload-options {
                flex-direction: column;
                align-items: center;
            }
            
            .upload-option {
                width: 100%;
                justify-content: center;
            }
            
            .frame-showcase {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .share-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <?php include "Navigation.php"; ?>
    
    <div class="quiz-header" data-aos="fade">
        <div class="container">
            <h1>Curious which frame style suits you best?</h1>
            <p>Unlock AI-powered recommendations in seconds—just one photo away!</p>
        </div>
    </div>
    
    <div class="quiz-container">
        <?php if (!$showResults): ?>
        <div class="quiz-card" data-aos="fade-up">
            <h2 class="text-center mb-4">Upload a photo or snap a quick selfie to get started.</h2>
            <p class="text-center mb-4">Make sure it's clear, front-facing, and taken in good lighting. For best results, remove your glasses.</p>
            
            <div class="upload-options">
                <div class="upload-option active" id="cameraOption">
                    <i class="fas fa-camera"></i> Take Photo
                </div>
                <div class="upload-option" id="uploadOption">
                    <i class="fas fa-upload"></i> Upload Photo
                </div>
            </div>
            
            <div id="cameraSection" style="display: none;">
                <div class="camera-instructions">
                    <div class="instruction-step permission-step">
                        <div class="step-number">1</div>
                        <p>Allow camera access when prompted to use this feature</p>
                    </div>
                    <div class="instruction-step">
                        <div class="step-number">2</div>
                        <p>Position your face in the center</p>
                    </div>
                    <div class="instruction-step">
                        <div class="step-number">3</div>
                        <p>Make sure lighting is even</p>
                    </div>
                    <div class="instruction-step">
                        <div class="step-number">4</div>
                        <p>Keep a neutral expression</p>
                    </div>
                </div>
                
                <div class="text-center">
                    <button class="btn btn-quiz" id="openCameraBtn">
                        <i class="fas fa-camera me-2"></i> Open Camera
                    </button>
                </div>
            </div>

            <div id="fileUploadSection">
                <form method="post" enctype="multipart/form-data" id="quizForm">
                    <div class="upload-area" id="uploadArea">
                        <div id="uploadPrompt">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <h4>Drag & Drop Your Photo</h4>
                            <p class="text-muted">or click to browse files</p>
                            <div class="supported-formats">
                                <span class="badge bg-light text-dark me-1">JPEG</span>
                                <span class="badge bg-light text-dark me-1">PNG</span>
                                <span class="badge bg-light text-dark">Max 5MB</span>
                            </div>
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
            
        </div>
        <?php else: ?>
        <div class="result-card">
            <?php if ($result === 'NO_FACE_DETECTED'): ?>
                <div class="no-face-detected">
                    <i class="fas fa-exclamation-triangle no-face-icon"></i>
                    <h2 class="result-title">We couldn't detect a face</h2>
                    <div class="alert alert-warning" style="max-width: 600px; margin: 20px auto;">
                        <p><?= htmlspecialchars($error) ?></p>
                        <hr>
                        <p class="mb-0">Please try again with:</p>
                        <ul class="text-start mt-2">
                            <li>A clear, well-lit photo</li>
                            <li>Face centered and fully visible</li>
                            <li>No glasses or obstructions</li>
                            <li>Neutral expression</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="face-shape-detector.php" class="btn btn-quiz">
                            <i class="fas fa-redo me-2"></i> Try Again
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="result-title">All done!</div>
                <div class="text-center mb-4" style="font-size: 1.3rem; color: var(--dark);">
                    Your dominant face shape is <span style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($result) ?></span>
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
                    $defaultImage = 'Images/faces/default-face.jpg';
                    $image = $shapeImages[$result] ?? $defaultImage;
                    ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($result) ?> face shape" class="result-image">
                </div>
                
                <!-- Fun Facts -->
                <div class="fun-facts" style="background: #FFF0F5; border-radius: 12px; padding: 25px; margin: 25px 0;">
                    <h4 class="text-center mb-4"><i class="fas fa-star"></i> Fun Facts About Your Face Shape</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fact-card">
                                <h5><i class="fas fa-user-tie"></i> Celebrity Match</h5>
                                <p>
                                    <?php
                                    $celebrities = [
                                        'SQUARE' => "You share your face shape with strong-featured stars like Angelina Jolie, Dwayne Johnson, Angel Locsin, and Dingdong Dantes!",
                                        'ROUND' => "Your face shape is similar to charming celebrities like Selena Gomez, Leonardo DiCaprio, Nadine Lustre, and James Reid!",
                                        'OBLONG' => "You have the elegant proportions seen on stars like Sarah Jessica Parker, Adam Levine, Liza Soberano, and Piolo Pascual!",
                                        'DIAMOND' => "Your striking features match stars like Rihanna, Ryan Gosling, Heart Evangelista, and Alden Richards!",
                                        'V-TRIANGLE' => "You share this distinctive shape with stars like Scarlett Johansson, Chris Hemsworth, Anne Curtis, and Coco Martin!",
                                        'A-TRIANGLE' => "Your soft angles are similar to celebrities like Reese Witherspoon, Zac Efron, Kathryn Bernardo, and Daniel Padilla!",
                                        'RECTANGLE' => "Your strong bone structure matches stars like Keira Knightley, Henry Cavill, Marian Rivera, and Richard Gutierrez!"
                                    ];
                                    echo htmlspecialchars($celebrities[$result] ?? "Your face shape is seen on many Hollywood A-listers!");
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="fact-card">
                                <h5><i class="fas fa-lightbulb"></i> Did You Know?</h5>
                                <p>
                                    <?php
                                    $funfacts = [
                                        'SQUARE' => "Square faces are among the rarer face shapes, making up only about 9% of the population!",
                                        'ROUND' => "Round faces are considered the most youthful-looking face shape throughout life.",
                                        'OBLONG' => "Oblong faces are sometimes called the 'aristocratic' shape due to their elegant proportions.",
                                        'DIAMOND' => "Diamond faces have the most symmetrical proportions, with cheekbones as the widest point.",
                                        'V-TRIANGLE' => "V-shaped faces are associated with strong, angular features that photograph exceptionally well.",
                                        'A-TRIANGLE' => "A-shaped faces are sometimes called 'heart-shaped' and are associated with approachability.",
                                        'RECTANGLE' => "Rectangular faces are commonly seen among fashion models due to their photogenic bone structure."
                                    ];
                                    echo htmlspecialchars($funfacts[$result] ?? "Your unique face shape combination makes you stand out in a crowd!");
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Frame Recommendations Section -->
                <div class="recommendation">
                    <h4><i class="fas fa-glasses me-2"></i> Perfect Frames For You</h4>
                    <p class="recommendation-text">
                        <?php
                        $recommendations = [
                            'SQUARE' => "Round or oval frames will soften your strong jawline and add balance to your facial features.",
                            'ROUND' => "Angular or rectangular frames will add definition and contrast beautifully with your soft curves.",
                            'OBLONG' => "Deep frames with decorative temples will add width and break up the length of your face.",
                            'DIAMOND' => "Cat-eye or oval frames will emphasize your cheekbones while softening your forehead and chin.",
                            'V-TRIANGLE' => "Frames with heavier top lines will balance your wider jawline perfectly.",
                            'A-TRIANGLE' => "Slightly rounded square frames will complement your face proportions nicely.",
                            'RECTANGLE' => "Oversized or round frames will help soften your angular features."
                        ];
                        echo htmlspecialchars($recommendations[$result] ?? "Our opticians recommend visiting our store for personalized frame recommendations.");
                        ?>
                    </p>
                    
                    <div class="why-it-works">
                        <h5><i class="fas fa-lightbulb"></i> Why This Works</h5>
                        <?php
                        $whyItWorks = [
                            'SQUARE' => [
                                "Curved frames counterbalance your angular jaw",
                                "Soft shapes create visual harmony",
                                "Wider frames add proportion to your strong features"
                            ],
                            'ROUND' => [
                                "Angular frames add structure to soft features",
                                "Rectangular shapes create length",
                                "Sharp lines provide visual contrast"
                            ],
                            'OBLONG' => [
                                "Deeper frames add width to long faces",
                                "Decorative details draw attention horizontally",
                                "Oversized styles balance facial proportions"
                            ],
                            'DIAMOND' => [
                                "Cat-eyes accentuate your best feature (cheekbones)",
                                "Curves soften narrow forehead and chin",
                                "Rimless options highlight natural beauty"
                            ],
                            'V-TRIANGLE' => [
                                "Top-heavy frames balance wider jaw",
                                "Decorative upper portions draw eyes up",
                                "Bold brows add width to forehead"
                            ],
                            'A-TRIANGLE' => [
                                "Rounded squares balance all proportions",
                                "Soft angles complement natural curves",
                                "Medium width frames suit delicate features"
                            ],
                            'RECTANGLE' => [
                                "Round frames soften strong angles",
                                "Oversized styles add presence",
                                "Curved shapes create facial balance"
                            ]
                        ];
                        
                        $reasons = $whyItWorks[$result] ?? ["Professional fitting ensures optimal comfort and appearance"];
                        foreach ($reasons as $reason) {
                            echo '<div class="why-item"><i class="fas fa-check-circle"></i><span>'.htmlspecialchars($reason).'</span></div>';
                        }
                        ?>
                    </div>
                    
                    <?php
                    // Get the ShapeID for the detected face shape
                    $shapeID = mapShapeToID($result);
                    
                    // Get recommended frames from database
                    $recommendedFrames = getRecommendedFrames($shapeID, 3);
                    ?>
                    
                    <?php if (!empty($recommendedFrames)): ?>
                        <div class="frame-showcase">
                            <?php foreach ($recommendedFrames as $frame): ?>
                                <?php
                                // Format price
                                $price = $frame['Price'];
                                $numeric_price = preg_replace('/[^0-9.]/', '', $price);
                                $formatted_price = is_numeric($numeric_price) ? '₱' . number_format((float)$numeric_price, 2) : '₱0.00';
                                
                                // Determine style description based on category
                                $styleDesc = ($frame['CategoryType'] === 'Sunglasses') ? 'Sunglasses' : $frame['Material'];
                                ?>
                                
                                <div class="frame-item" onclick="window.location.href='product-gallery.php?page=1&shape=<?php echo $shapeID; ?>'">
                                    <img src="<?php echo htmlspecialchars($frame['ProductImage']); ?>" 
                                         alt="<?php echo htmlspecialchars($frame['Model']); ?>">
                                    <div class="frame-info">
                                        <h6><?php echo htmlspecialchars($frame['Model']); ?></h6>
                                        <p><?php echo htmlspecialchars($styleDesc); ?></p>
                                        <p class="text-muted small"><?php echo htmlspecialchars($formatted_price); ?></p>
                                        <?php if ($frame['AvailableBranches']): ?>
                                            <p class="text-success small">
                                                <i class="fas fa-check-circle"></i> In Stock
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> 
                            We're currently updating our inventory for your face shape. 
                            Please visit our store or <a href="product-gallery.php">browse all products</a>.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Primary CTA -->
                <div class="primary-cta container my-5">
                    <div class="card text-center border-0 shadow-sm rounded-4 p-4 p-md-5" 
                        style="background-color: var(--light); color: var(--dark);">

                        <h3 class="fw-semibold mb-3" style="color: var(--dark);">
                            <i class="fas fa-shopping-bag me-2" style="color: var(--primary);"></i>
                            Ready to Find Your Perfect Frames?
                        </h3>

                        <p class="mb-4" style="color: var(--dark);">
                            Explore our curated collection designed specifically for 
                            <span style="font-weight: 600; color: var(--primary);">
                                <?= htmlspecialchars($result) ?>
                            </span> face shapes.
                        </p>

                        <?php
                        $shapeID = mapShapeToID($result);
                        $shopUrl = 'product-gallery.php?page=1&shape=' . $shapeID;
                        ?>

                        <a href="<?= htmlspecialchars($shopUrl) ?>" 
                        class="btn px-4 py-2 rounded-pill fw-semibold"
                        style="background-color: var(--primary); color: #fff; transition: background-color 0.3s ease;"
                        onmouseover="this.style.backgroundColor='var(--primary-dark)'"
                        onmouseout="this.style.backgroundColor='var(--primary)'">
                            Shop Recommended Frames
                        </a>
                    </div>
</div>


                
                <!-- Social Share -->
                <div class="social-share">
                    <h5><i class="fas fa-share-alt me-2"></i> Share This Face Shape Tool</h5>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Help your friends find their perfect frames too!</p>
                    <div class="share-buttons">
                        <div class="share-btn facebook" onclick="shareResults('facebook')" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <div class="share-btn twitter" onclick="shareResults('twitter')" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <div class="share-btn whatsapp" onclick="shareResults('whatsapp')" title="Share on WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="share-btn link" onclick="copyLink()" title="Copy Link">
                            <i class="fas fa-link"></i>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="face-shape-detector.php" class="btn btn-quiz btn-outline">
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
                <button class="btn btn-cancel" id="closeCameraBtn">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button class="btn btn-capture" id="captureBtn">
                    <i class="fas fa-camera me-1"></i> Capture
                </button>
            </div>
        </div>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
        <h3 id="loadingText">Analyzing your face shape...</h3>
        <p class="text-muted">This usually takes just a few seconds</p>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            const fileInput = document.getElementById('fileInput');
            const uploadArea = document.getElementById('uploadArea');
            const uploadPrompt = document.getElementById('uploadPrompt');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const previewImage = document.getElementById('previewImage');
            const changeImageBtn = document.getElementById('changeImageBtn');
            const quizForm = document.getElementById('quizForm');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            const uploadOption = document.getElementById('uploadOption');
            const cameraOption = document.getElementById('cameraOption');
            const fileUploadSection = document.getElementById('fileUploadSection');
            const cameraSection = document.getElementById('cameraSection');
            const openCameraBtn = document.getElementById('openCameraBtn');
            const cameraModal = document.getElementById('cameraModal');
            const cameraPreview = document.getElementById('cameraPreview');
            const captureBtn = document.getElementById('captureBtn');
            const closeCameraBtn = document.getElementById('closeCameraBtn');
            let stream = null;
            
            imagePreviewContainer.style.display = 'none';
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    const maxSize = 5 * 1024 * 1024;
                    
                    if (!validTypes.includes(this.files[0].type)) {
                        alert('Please upload a JPEG or PNG image');
                        return;
                    }
                    
                    if (this.files[0].size > maxSize) {
                        alert('Image size should be less than 5MB');
                        return;
                    }
                    
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
                
                const loadingPhrases = [
                    "Detecting facial features...",
                    "Measuring proportions...",
                    "Calculating shape...",
                    "Almost done..."
                ];
                
                let currentPhrase = 0;
                const loadingText = document.getElementById('loadingText');
                const phraseInterval = setInterval(() => {
                    if (currentPhrase < loadingPhrases.length) {
                        loadingText.textContent = loadingPhrases[currentPhrase];
                        currentPhrase++;
                    } else {
                        clearInterval(phraseInterval);
                    }
                }, 1500);
                
                setTimeout(() => {
                    this.submit();
                }, 6000);
            });
            
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
            
            openCameraBtn.addEventListener('click', async function() {
                cameraModal.style.display = 'flex';
                
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: 'user' },
                        audio: false 
                    });
                    cameraPreview.srcObject = stream;
                } catch (err) {
                    console.error("Camera error: ", err);
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
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                uploadArea.style.borderColor = 'var(--primary)';
                uploadArea.style.backgroundColor = 'rgba(255, 62, 108, 0.1)';
            }
            
            function unhighlight() {
                uploadArea.style.borderColor = '#FFD1DC';
                uploadArea.style.backgroundColor = 'rgba(255, 241, 244, 0.5)';
            }
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length) {
                    fileInput.files = files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            }
        });
        
        function shareResults(platform) {
            const url = window.location.href;
            const text = "I just discovered my face shape! Find yours too at Santos Optical.";
            
            switch(platform) {
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'twitter':
                    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'whatsapp':
                    window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
                    break;
            }
        }
        
        function copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy link');
            });
        }
    </script>
</body>
</html>