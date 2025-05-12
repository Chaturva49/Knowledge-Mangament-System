<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all approved articles
$stmt = $pdo->query('SELECT a.*, u.username FROM articles a JOIN users u ON a.author_id = u.id WHERE a.status = "approved" ORDER BY a.created_at DESC');
$articles = $stmt->fetchAll();

$pageTitle = 'Articles';
require_once 'includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-newspaper me-2"></i>Articles</h1>
        <?php if (isLoggedIn()): ?>
            <a href="article_submit.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Submit Article</a>
        <?php endif; ?>
    </div>
    <?php if (empty($articles)): ?>
        <div class="alert alert-info">No articles published yet.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($articles as $article): ?>
                <a href="article_view.php?id=<?php echo $article['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h5>
                        <small><?php echo date('M d, Y', strtotime($article['created_at'])); ?></small>
                    </div>
                    <p class="mb-1 text-muted">By <?php echo htmlspecialchars($article['username']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 