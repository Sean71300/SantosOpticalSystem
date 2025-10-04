<?php
include 'ActivityTracker.php';

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
        
        /* Results Section */
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
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px; /* Changed from 50% to make it square with slightly rounded corners */
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin: 20px auto;
        }
        
        .result-details {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .detail-card {
            background: #f8fbff;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid var(--secondary);
            text-align: left;
            flex: 1;
        }
        
        .detail-card h5 {
            color: var(--secondary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .recommendation {
            background: #F8FBFF;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            border-left: 4px solid var(--secondary);
        }
        
        .frame-showcase {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 25px 0;
        }
        
        .frame-item {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .frame-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .frame-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Camera Modal and other styles remain the same */
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
            background: rgba(255,255,255,0.8);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
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
            color: #2a6496; /* or any highlight color you prefer */
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
            
            .result-details {
                flex-direction: column;
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
                <div class="upload-option active" id="uploadOption">
                    <i class="fas fa-upload"></i> Upload Photo
                </div>
                <div class="upload-option" id="cameraOption">
                    <i class="fas fa-camera"></i> Take Photo
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
                
                <div class="text-center">
                    <button class="btn btn-quiz" id="openCameraBtn">
                        <i class="fas fa-camera me-2"></i> Open Camera
                    </button>
                </div>
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

    
    <!-- Fun Facts Section -->
    <div class="fun-facts" style="background: #FFF0F5; border-radius: 12px; padding: 25px; margin: 25px 0;">
        <h4 class="text-center mb-4"><i class="fas fa-star"></i> Fun Facts About Your Face Shape</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="fact-card mb-4" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <h5 style="color: var(--primary);"><i class="fas fa-user-tie"></i> Celebrity Match</h5>
                    <p>
                        <?php
                        $celebrities = [
                            'SQUARE' => "You share your face shape with strong-featured stars like Angelina Jolie and Brad Pitt!",
                            'ROUND' => "Your face shape is similar to charming celebrities like Emma Stone and Leonardo DiCaprio!",
                            'OBLONG' => "You have the elegant proportions seen on stars like Sarah Jessica Parker and Jude Law!",
                            'DIAMOND' => "Your striking features match stars like Rihanna and Johnny Depp!",
                            'V-TRIANGLE' => "You share this distinctive shape with stars like Reese Witherspoon and Chris Hemsworth!",
                            'A-TRIANGLE' => "Your soft angles are similar to celebrities like Jennifer Aniston and Ryan Gosling!",
                            'RECTANGLE' => "Your strong bone structure matches stars like Keira Knightley and Idris Elba!"
                        ];
                        echo htmlspecialchars($celebrities[$result] ?? "Your face shape is seen on many Hollywood A-listers!");
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="fact-card mb-4" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <h5 style="color: var(--primary);"><i class="fas fa-brain"></i> Personality Association</h5>
                    <p>
                        <?php
                        $personalities = [
                            'SQUARE' => "People often perceive square-faced individuals as strong-willed, decisive leaders with a practical approach to life.",
                            'ROUND' => "Round faces are often associated with creative, kind-hearted people who value harmony and relationships.",
                            'OBLONG' => "Oblong faces suggest intelligence, sophistication, and a balanced, methodical approach to challenges.",
                            'DIAMOND' => "Diamond-shaped faces are linked to energetic, expressive personalities who love being the center of attention.",
                            'V-TRIANGLE' => "V-shapes indicate dynamic, ambitious personalities with strong determination and focus.",
                            'A-TRIANGLE' => "A-shapes suggest gentle, empathetic personalities who are great listeners and peacemakers.",
                            'RECTANGLE' => "Rectangular faces are associated with analytical, logical thinkers who value structure and efficiency."
                        ];
                        echo htmlspecialchars($personalities[$result] ?? "Your face shape suggests a balanced, adaptable personality!");
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="fact-card" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
            <h5 style="color: var(--primary);"><i class="fas fa-lightbulb"></i> Did You Know?</h5>
            <p>
                <?php
                $funfacts = [
                    'SQUARE' => "Square faces are the rarest face shape, making up only about 9% of the population!",
                    'ROUND' => "Round faces are considered the most youthful-looking face shape throughout life.",
                    'OBLONG' => "Oblong faces are sometimes called the 'aristocratic' shape due to their elegant proportions.",
                    'DIAMOND' => "Diamond faces are the most symmetrical shape, with cheekbones as the widest point.",
                    'V-TRIANGLE' => "V-shaped faces are considered the most masculine shape, while inverted triangles are more feminine.",
                    'A-TRIANGLE' => "A-shaped faces are sometimes called 'heart-shaped' and are associated with approachability.",
                    'RECTANGLE' => "Rectangular faces are most common among models due to their photogenic bone structure."
                ];
                echo htmlspecialchars($funfacts[$result] ?? "Your unique face shape combination makes you stand out in a crowd!");
                ?>
            </p>
        </div>
    </div>
    
                
                <div class="recommendation">
                    <h4><i class="fas fa-glasses me-2"></i> Perfect Frames For You</h4>
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
                    
                    echo '<p>'.htmlspecialchars($recommendations[$result] ?? "Our opticians recommend visiting our store for personalized frame recommendations.").'</p>';
                    ?>
                    
                    <div class="frame-showcase">
                        <?php
                        $frameExamples = [
                            'SQUARE' => ['round-frame1.jpg', 'oval-frame1.jpg', 'round-frame2.jpg'],
                            'ROUND' => ['rectangular-frame1.jpg', 'square-frame1.jpg', 'wayfarer-frame1.jpg'],
                            'OBLONG' => ['browline-frame1.jpg', 'decorative-frame1.jpg', 'aviator-frame1.jpg'],
                            'DIAMOND' => ['cateye-frame1.jpg', 'oval-frame2.jpg', 'butterfly-frame1.jpg'],
                            'V-TRIANGLE' => ['browline-frame2.jpg', 'clubmaster-frame1.jpg', 'halfrim-frame1.jpg'],
                            'A-TRIANGLE' => ['rounded-square1.jpg', 'rounded-square2.jpg', 'wayfarer-frame2.jpg'],
                            'RECTANGLE' => ['round-frame3.jpg', 'oval-frame3.jpg', 'oversized-frame1.jpg']
                        ];
                        
                        $frames = $frameExamples[$result] ?? ['classic-frame1.jpg', 'classic-frame2.jpg', 'classic-frame3.jpg'];
                        
                        foreach ($frames as $frame) {
                            echo '<div class="frame-item">';
                            echo '<img src="Images/frames/'.htmlspecialchars($frame).'" alt="Recommended frame">';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="product-gallery.php" class="btn btn-quiz">
                        <i class="fas fa-shopping-bag me-2"></i> Shop Frames
                    </a>
                    <a href="face-shape-detector.php" class="btn btn-quiz btn-outline">
                        <i class="fas fa-redo me-2"></i> Try Again
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
            
            // File upload elements
            const fileInput = document.getElementById('fileInput');
            const uploadArea = document.getElementById('uploadArea');
            const uploadPrompt = document.getElementById('uploadPrompt');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const previewImage = document.getElementById('previewImage');
            const changeImageBtn = document.getElementById('changeImageBtn');
            const quizForm = document.getElementById('quizForm');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Camera elements
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
            
            // Initialize
            imagePreviewContainer.style.display = 'none';
            
            // File selection handler
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    
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
            
            // Change image button
            changeImageBtn.addEventListener('click', function() {
                fileInput.value = '';
                uploadPrompt.style.display = 'block';
                imagePreviewContainer.style.display = 'none';
                analyzeBtn.disabled = true;
            });
            
            // Click on upload area
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // Form submission
            quizForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadingOverlay.style.display = 'flex';
                
                // Simulate analysis steps for demo purposes
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
                
                // Submit the form
                setTimeout(() => {
                    this.submit();
                }, 6000);
            });
            
            // Toggle between upload and camera options
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
            
            // Open camera modal
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
            
            // Capture photo
            captureBtn.addEventListener('click', function() {
                const canvas = document.createElement('canvas');
                canvas.width = cameraPreview.videoWidth;
                canvas.height = cameraPreview.videoHeight;
                const ctx = canvas.getContext('2d');
                
                // Draw video frame to canvas
                ctx.drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
                
                // Convert to data URL and create file
                canvas.toBlob(function(blob) {
                    const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
                    
                    // Create a fake FileList
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    
                    // Assign to file input
                    fileInput.files = dataTransfer.files;
                    
                    // Show in preview
                    previewImage.src = URL.createObjectURL(blob);
                    uploadPrompt.style.display = 'none';
                    imagePreviewContainer.style.display = 'block';
                    analyzeBtn.disabled = false;
                    
                    // Switch back to upload view
                    uploadOption.click();
                }, 'image/jpeg');
                
                // Close camera
                closeCamera();
            });
            
            // Close camera
            function closeCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                cameraModal.style.display = 'none';
            }
            
            closeCameraBtn.addEventListener('click', closeCamera);
            
            // Drag and drop functionality
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
    </script>
</body>
</html>
