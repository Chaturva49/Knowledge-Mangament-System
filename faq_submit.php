<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    if (empty($question) || empty($answer)) {
        $error = 'Both question and answer are required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO faqs (question, answer, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$question, $answer, getCurrentUserId()]);
        header('Location: faqs.php');
        exit();
    }
}
require_once 'includes/header.php';
?>
<div class="container py-5">
    <h1 class="h3 mb-4"><i class="fas fa-plus me-2"></i>Add FAQ</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="question" class="form-label">Question</label>
            <input type="text" class="form-control" id="question" name="question" required>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Answer</label>
            <textarea class="form-control" id="answer" name="answer" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit FAQ</button>
        <a href="faqs.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 