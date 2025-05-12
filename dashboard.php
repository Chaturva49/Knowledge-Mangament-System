<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Require login to access dashboard
requireLogin();

// Get user's documents count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM documents WHERE uploaded_by = ?');
$stmt->execute([getCurrentUserId()]);
$documentsCount = $stmt->fetchColumn();

// Get recent uploads
$stmt = $pdo->prepare('
    SELECT d.*, u.username 
    FROM documents d 
    JOIN users u ON d.uploaded_by = u.id 
    ORDER BY d.created_at DESC 
    LIMIT 5
');
$stmt->execute();
$recentUploads = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Knowledge Portal</title>
    
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
                <h1 class="display-4 mb-4">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </h1>
                <p class="lead">Welcome back, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-file-alt text-primary"></i>
                    <h3><?php echo $documentsCount; ?></h3>
                    <p class="text-muted">Your Documents</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-upload text-success"></i>
                    <h3>Upload</h3>
                    <p class="text-muted">Share New Content</p>
                    <a href="upload.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>New Upload
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-search text-info"></i>
                    <h3>Search</h3>
                    <p class="text-muted">Find Content</p>
                    <a href="search.php" class="btn btn-info btn-sm text-white">
                        <i class="fas fa-search me-1"></i>Search
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Uploads -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Uploads
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentUploads)): ?>
                            <p class="text-muted text-center">No recent uploads found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Uploaded By</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUploads as $upload): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($upload['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo htmlspecialchars($upload['category']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($upload['username']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($upload['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_content.php?id=<?php echo $upload['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 