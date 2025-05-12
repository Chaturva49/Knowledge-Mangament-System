<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all forums
$stmt = $pdo->query('SELECT f.*, u.username FROM forums f JOIN users u ON f.created_by = u.id ORDER BY f.created_at DESC');
$forums = $stmt->fetchAll();

$pageTitle = 'Discussion Forums';
require_once 'includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-comments me-2"></i>Discussion Forums</h1>
        <?php if (isLoggedIn()): ?>
            <a href="forum_post.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Start New Discussion</a>
        <?php endif; ?>
    </div>
    <?php if (empty($forums)): ?>
        <div class="alert alert-info">No discussions yet. Be the first to start one!</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($forums as $forum): ?>
                <a href="forum_view.php?id=<?php echo $forum['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($forum['title']); ?></h5>
                        <small><?php echo date('M d, Y', strtotime($forum['created_at'])); ?></small>
                    </div>
                    <p class="mb-1 text-muted">Started by <?php echo htmlspecialchars($forum['username']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 