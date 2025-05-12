<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user's content
$userId = getCurrentUserId();
$stmt = $pdo->prepare('
    SELECT d.*, 
           COUNT(DISTINCT v.id) as view_count,
           COUNT(DISTINCT d2.id) as download_count
    FROM documents d
    LEFT JOIN views v ON d.id = v.document_id
    LEFT JOIN downloads d2 ON d.id = d2.document_id
    WHERE d.user_id = ?
    GROUP BY d.id
    ORDER BY d.upload_date DESC
');
$stmt->execute([$userId]);
$documents = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query('SELECT DISTINCT category FROM documents ORDER BY category');
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get file types for filter
$stmt = $pdo->query('SELECT DISTINCT file_type FROM documents ORDER BY file_type');
$fileTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "My Content";
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Content</h1>
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-upload me-2"></i>Upload New Content
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"
                                    <?php echo isset($_GET['category']) && $_GET['category'] === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filetype" class="form-label">File Type</label>
                    <select class="form-select" id="filetype" name="filetype">
                        <option value="">All Types</option>
                        <?php foreach ($fileTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"
                                    <?php echo isset($_GET['filetype']) && $_GET['filetype'] === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="date_desc" <?php echo !isset($_GET['sort']) || $_GET['sort'] === 'date_desc' ? 'selected' : ''; ?>>
                            Upload Date (Newest)
                        </option>
                        <option value="date_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'date_asc' ? 'selected' : ''; ?>>
                            Upload Date (Oldest)
                        </option>
                        <option value="title_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'title_asc' ? 'selected' : ''; ?>>
                            Title (A-Z)
                        </option>
                        <option value="title_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'title_desc' ? 'selected' : ''; ?>>
                            Title (Z-A)
                        </option>
                        <option value="views_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'views_desc' ? 'selected' : ''; ?>>
                            Most Viewed
                        </option>
                        <option value="downloads_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'downloads_desc' ? 'selected' : ''; ?>>
                            Most Downloaded
                        </option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <a href="my-content.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Content List -->
    <?php if (empty($documents)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>You haven't uploaded any content yet.
            <a href="upload.php" class="alert-link">Upload your first document</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Upload Date</th>
                        <th>Views</th>
                        <th>Downloads</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <a href="view_content.php?id=<?php echo $doc['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($doc['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($doc['category']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $doc['file_type'] === 'video' ? 'danger' : 'primary'; ?>">
                                    <?php echo htmlspecialchars($doc['file_type']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($doc['upload_date'])); ?></td>
                            <td><?php echo number_format($doc['view_count']); ?></td>
                            <td><?php echo number_format($doc['download_count']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="view_content.php?id=<?php echo $doc['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_content.php?id=<?php echo $doc['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteContent(<?php echo $doc['id']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        window.location.href = `delete_content.php?id=${id}`;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 