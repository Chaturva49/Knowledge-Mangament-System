<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Initialize variables
$featuredContent = [];
$totalDocuments = 0;
$totalContributors = 0;
$error = null;

try {
    // Get featured content (most recent uploads)
    $stmt = $pdo->query('
        SELECT d.*, u.username 
        FROM documents d 
        JOIN users u ON d.uploaded_by = u.id 
        ORDER BY d.created_at DESC 
        LIMIT 6
    ');
    $featuredContent = $stmt->fetchAll();

    // Get content statistics
    $stmt = $pdo->query('SELECT COUNT(*) FROM documents');
    $totalDocuments = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(DISTINCT uploaded_by) FROM documents');
    $totalContributors = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Sorry, there was a problem loading the content. Please try again later.";
}

if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE uploaded_by = ?");
    $stmt->execute([$userId]);
    $myDocs = $stmt->fetchAll();
    require_once 'includes/header.php';
    ?>
    <div class="container py-5">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h2 class="mb-4 fw-bold text-primary">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</h2>
                        <h4 class="mb-3">Your Uploaded Documents</h4>
                        <?php if (count($myDocs) > 0): ?>
                            <ul class="list-group mb-4">
                                <?php foreach ($myDocs as $doc): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <strong><?php echo htmlspecialchars($doc['title']); ?></strong>
                                            <span class="text-muted ms-2">(<?php echo htmlspecialchars($doc['filetype']); ?>)</span>
                                        </span>
                                        <span class="text-muted small"><?php echo htmlspecialchars($doc['created_at']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-info">You haven't uploaded any documents yet.</div>
                        <?php endif; ?>
                        <a href="upload.php" class="btn btn-success"><i class="fas fa-upload me-2"></i>Upload New Document</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
} else {
    require_once 'includes/header.php';
}
?>
<!-- Hero Section -->
<div class="hero bg-gradient-primary-to-secondary py-5 mb-5" style="background: linear-gradient(90deg, #4f8cff 0%, #6a82fb 100%); color: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px rgba(80, 120, 255, 0.10);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Welcome to Intelliverse</h1>
        <p class="lead mb-4">Your central hub for learning and sharing knowledge</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="login.php" class="btn btn-light btn-lg shadow-sm mb-2">
                <i class="fas fa-sign-in-alt me-2"></i>Get Started
            </a>
            <a href="search.php" class="btn btn-outline-light btn-lg shadow-sm mb-2">
                <i class="fas fa-search me-2"></i>Browse Content
            </a>
            <a href="register.php" class="btn btn-warning btn-lg shadow-sm mb-2">
                <i class="fas fa-user-plus me-2"></i>Register
            </a>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card stats-card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo $totalDocuments; ?></h2>
                    <p class="text-muted mb-0">Total Documents</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stats-card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo $totalContributors; ?></h2>
                    <p class="text-muted mb-0">Active Contributors</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Content -->
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold"><i class="fas fa-star me-2 text-warning"></i>Featured Content</h2>
        </div>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredContent as $content): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <?php
                        $icon = 'fa-file';
                        if (strpos($content['filetype'], 'pdf') !== false) {
                            $icon = 'fa-file-pdf';
                        } elseif (strpos($content['filetype'], 'word') !== false) {
                            $icon = 'fa-file-word';
                        } elseif (strpos($content['filetype'], 'video') !== false) {
                            $icon = 'fa-file-video';
                        }
                        ?>
                        <i class="fas <?php echo $icon; ?> fa-2x text-primary mb-3"></i>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($content['title']); ?></h5>
                        <p class="card-text text-muted mb-2">
                            <small>
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($content['username']); ?>
                                <br>
                                <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($content['created_at'])); ?>
                            </small>
                        </p>
                        <span class="badge bg-primary mb-3">
                            <?php echo htmlspecialchars($content['category']); ?>
                        </span>
                        <a href="view_content.php?id=<?php echo $content['id']; ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-eye me-2"></i>View Content
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold">Why Choose Intelliverse?</h2>
            <p class="text-muted">Discover the benefits of our platform</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-share-alt fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">Easy Sharing</h4>
                    <p class="text-muted">Share your knowledge with the community through documents and videos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">Smart Search</h4>
                    <p class="text-muted">Find exactly what you need with our powerful search capabilities</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">Secure Platform</h4>
                    <p class="text-muted">Your content is safe and secure with our robust security measures</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guest Welcome Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <h1 class="fw-bold mb-3">Welcome to the Knowledge Management Portal</h1>
                    <p class="lead mb-4">
                        This portal helps you manage, share, and discover knowledge resources easily.<br>
                        <strong>Features:</strong>
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li>• Upload and organize documents and videos</li>
                        <li>• Search and discover learning materials</li>
                        <li>• Secure user accounts and role-based access</li>
                    </ul>
                    <a href="register.php" class="btn btn-primary me-2">Register</a>
                    <a href="login.php" class="btn btn-outline-primary">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-light py-4 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Intelliverse</h5>
                <p class="text-muted">Your central hub for learning and sharing knowledge</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted">&copy; <?php echo date('Y'); ?> Intelliverse. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 