<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$filetype = $_GET['filetype'] ?? '';

// Build query
$query = 'SELECT d.*, u.username FROM documents d JOIN users u ON d.uploaded_by = u.id WHERE 1=1';
$params = [];

if ($search) {
    $query .= ' AND (d.title LIKE ? OR d.category LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= ' AND d.category = ?';
    $params[] = $category;
}

if ($filetype) {
    $query .= ' AND d.filetype LIKE ?';
    $params[] = "%$filetype%";
}

$query .= ' ORDER BY d.created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Get unique categories for filter
$stmt = $pdo->query('SELECT DISTINCT category FROM documents ORDER BY category');
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Knowledge Portal</title>
    
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
                    <i class="fas fa-search me-2"></i>Search Content
                </h1>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search by title or category..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"
                                                <?php echo $category === $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="filetype">
                                    <option value="">All File Types</option>
                                    <option value="pdf" <?php echo $filetype === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                                    <option value="doc" <?php echo $filetype === 'doc' ? 'selected' : ''; ?>>DOC</option>
                                    <option value="docx" <?php echo $filetype === 'docx' ? 'selected' : ''; ?>>DOCX</option>
                                    <option value="mp4" <?php echo $filetype === 'mp4' ? 'selected' : ''; ?>>MP4</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                                <a href="search.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Search Results
                            <span class="badge bg-primary ms-2"><?php echo count($results); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($results)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5>No results found</h5>
                                <p class="text-muted">Try adjusting your search criteria</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>Uploaded By</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo htmlspecialchars($result['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $icon = 'fa-file';
                                                    if (strpos($result['filetype'], 'pdf') !== false) {
                                                        $icon = 'fa-file-pdf';
                                                    } elseif (strpos($result['filetype'], 'word') !== false) {
                                                        $icon = 'fa-file-word';
                                                    } elseif (strpos($result['filetype'], 'video') !== false) {
                                                        $icon = 'fa-file-video';
                                                    }
                                                    ?>
                                                    <i class="fas <?php echo $icon; ?> me-1"></i>
                                                    <?php echo strtoupper(pathinfo($result['filename'], PATHINFO_EXTENSION)); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($result['username']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($result['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_content.php?id=<?php echo $result['id']; ?>" 
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