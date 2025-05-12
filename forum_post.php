<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO forums (title, description, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$title, $description, getCurrentUserId()]);
        header('Location: forums.php');
        exit();
    }
}
require_once 'includes/header.php';
?>
<div class="container py-5">
    <h1 class="h3 mb-4"><i class="fas fa-plus me-2"></i>Start New Discussion</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Discussion</button>
        <a href="forums.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 