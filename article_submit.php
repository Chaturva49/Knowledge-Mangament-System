<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if (empty($title) || empty($content)) {
        $error = 'Both title and content are required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO articles (title, content, author_id, status) VALUES (?, ?, ?, "pending")');
        $stmt->execute([$title, $content, getCurrentUserId()]);
        header('Location: articles.php');
        exit();
    }
}
require_once 'includes/header.php';
?>
<div class="container py-5">
    <h1 class="h3 mb-4"><i class="fas fa-plus me-2"></i>Submit Article</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="8" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Article</button>
        <a href="articles.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 