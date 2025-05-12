<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare('SELECT f.*, u.username FROM faqs f JOIN users u ON f.created_by = u.id WHERE f.question LIKE ? OR f.answer LIKE ? ORDER BY f.created_at DESC');
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query('SELECT f.*, u.username FROM faqs f JOIN users u ON f.created_by = u.id ORDER BY f.created_at DESC');
}
$faqs = $stmt->fetchAll();

$pageTitle = 'FAQs';
require_once 'includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h1>
        <?php if (isLoggedIn()): ?>
            <a href="faq_submit.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add FAQ</a>
        <?php endif; ?>
    </div>
    <form class="mb-4" method="get">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search FAQs..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <?php if (empty($faqs)): ?>
        <div class="alert alert-info">No FAQs found.</div>
    <?php else: ?>
        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>">
                            <?php echo htmlspecialchars($faq['question']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            <div class="text-muted small mt-2">Added by <?php echo htmlspecialchars($faq['username']); ?> on <?php echo date('M d, Y', strtotime($faq['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 