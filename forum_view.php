<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$forumId = $_GET['id'] ?? null;
if (!$forumId) {
    header('Location: forums.php');
    exit();
}

// Fetch forum
$stmt = $pdo->prepare('SELECT f.*, u.username FROM forums f JOIN users u ON f.created_by = u.id WHERE f.id = ?');
$stmt->execute([$forumId]);
$forum = $stmt->fetch();
if (!$forum) {
    header('Location: forums.php');
    exit();
}

// Handle new post
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $content = trim($_POST['content'] ?? '');
    if (empty($content)) {
        $error = 'Reply cannot be empty.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO forum_posts (forum_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$forumId, getCurrentUserId(), $content]);
        header('Location: forum_view.php?id=' . $forumId);
        exit();
    }
}

// Fetch posts
$stmt = $pdo->prepare('SELECT p.*, u.username FROM forum_posts p JOIN users u ON p.user_id = u.id WHERE p.forum_id = ? ORDER BY p.created_at ASC');
$stmt->execute([$forumId]);
$posts = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="container py-5">
    <a href="forums.php" class="btn btn-link mb-3"><i class="fas fa-arrow-left me-2"></i>Back to Forums</a>
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 mb-2"><?php echo htmlspecialchars($forum['title']); ?></h2>
            <p class="mb-1 text-muted">Started by <?php echo htmlspecialchars($forum['username']); ?> on <?php echo date('M d, Y', strtotime($forum['created_at'])); ?></p>
            <p><?php echo nl2br(htmlspecialchars($forum['description'])); ?></p>
        </div>
    </div>
    <h4 class="mb-3">Discussion</h4>
    <?php if (empty($posts)): ?>
        <div class="alert alert-info">No replies yet. Be the first to reply!</div>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($posts as $post): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></small>
                    </div>
                    <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (isLoggedIn()): ?>
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="content" class="form-label">Your Reply</label>
                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <button type="submit" class="btn btn-primary">Post Reply</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Please <a href="login.php">login</a> to reply.</div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 