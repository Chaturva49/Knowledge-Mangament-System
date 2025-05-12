<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: articles.php');
    exit();
}

$stmt = $pdo->prepare('SELECT a.*, u.username FROM articles a JOIN users u ON a.author_id = u.id WHERE a.id = ? AND a.status = "approved"');
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) {
    header('Location: articles.php');
    exit();
}

$pageTitle = htmlspecialchars($article['title']);
require_once 'includes/header.php';
?>
<div class="container py-5">
    <a href="articles.php" class="btn btn-link mb-3"><i class="fas fa-arrow-left me-2"></i>Back to Articles</a>
    <div class="card">
        <div class="card-body">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="mb-3 text-muted">By <?php echo htmlspecialchars($article['username']); ?> on <?php echo date('M d, Y', strtotime($article['created_at'])); ?></div>
            <div><?php echo nl2br(htmlspecialchars($article['content'])); ?></div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?> 