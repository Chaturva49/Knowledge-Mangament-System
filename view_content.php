<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: search.php');
    exit();
}

// Get document details
$stmt = $pdo->prepare('
    SELECT d.*, u.username 
    FROM documents d 
    JOIN users u ON d.uploaded_by = u.id 
    WHERE d.id = ?
');
$stmt->execute([$id]);
$document = $stmt->fetch();

if (!$document) {
    header('Location: search.php');
    exit();
}

// Determine file path and type
$isVideo = strpos($document['filetype'], 'video') !== false;
$filePath = $isVideo ? 'uploads/videos/' . $document['filename'] : 'uploads/docs/' . $document['filename'];
$fileExists = file_exists($filePath);

// Get file icon
$icon = 'fa-file';
if (strpos($document['filetype'], 'pdf') !== false) {
    $icon = 'fa-file-pdf';
} elseif (strpos($document['filetype'], 'word') !== false) {
    $icon = 'fa-file-word';
} elseif ($isVideo) {
    $icon = 'fa-file-video';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($document['title']); ?> - Knowledge Portal</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="search.php">Search</a></li>
                        <li class="breadcrumb-item active">View Content</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- File Preview -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="card-title h4 mb-0">
                            <i class="fas <?php echo $icon; ?> me-2"></i>
                            <?php echo htmlspecialchars($document['title']); ?>
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (!$fileExists): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                File not found. It may have been deleted or moved.
                            </div>
                        <?php elseif ($isVideo): ?>
                            <div class="ratio ratio-16x9">
                                <video controls class="rounded">
                                    <source src="<?php echo htmlspecialchars($filePath); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php elseif (strpos($document['filetype'], 'pdf') !== false): ?>
                            <div style="height: 600px;">
                                <iframe src="<?php echo htmlspecialchars($filePath); ?>" width="100%" height="100%" style="border: none;"></iframe>
                            </div>
                        <?php elseif (strpos($document['filetype'], 'word') !== false || preg_match('/\.(docx?|DOCX?)$/', $document['filename'])): ?>
                            <div style="height: 600px;">
                                <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $filePath); ?>" width="100%" height="100%" frameborder="0"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas <?php echo $icon; ?> fa-5x text-primary mb-3"></i>
                                <h5>Document Preview Not Available</h5>
                                <p class="text-muted">Click the download button below to view this document</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- File Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h3 class="card-title h5 mb-0">
                            <i class="fas fa-info-circle me-2"></i>File Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Category</span>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($document['category']); ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>File Type</span>
                                <span class="text-muted">
                                    <?php echo strtoupper(pathinfo($document['filename'], PATHINFO_EXTENSION)); ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Uploaded By</span>
                                <span class="text-muted"><?php echo htmlspecialchars($document['username']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Upload Date</span>
                                <span class="text-muted">
                                    <?php echo date('M d, Y', strtotime($document['created_at'])); ?>
                                </span>
                            </li>
                        </ul>
                        <a href="<?php echo htmlspecialchars($filePath); ?>" class="btn btn-outline-success w-100 mt-3" download>
                            <i class="fas fa-download me-2"></i>Download File
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <a href="search.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to Search
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 