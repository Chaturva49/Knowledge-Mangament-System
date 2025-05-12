<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Require login to access upload page
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (empty($title) || empty($category) || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please fill in all fields and select a valid file.';
    } else {
        // Validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'video/mp4'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Invalid file type. Please upload PDF, DOC, DOCX, or MP4 files only.';
        } else {
            // Determine upload directory based on file type
            $uploadDir = strpos($fileType, 'video') !== false ? 'uploads/videos/' : 'uploads/docs/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Save to database
                $stmt = $pdo->prepare('
                    INSERT INTO documents (title, filename, filetype, category, uploaded_by) 
                    VALUES (?, ?, ?, ?, ?)
                ');
                
                if ($stmt->execute([$title, $filename, $fileType, $category, getCurrentUserId()])) {
                    $success = 'File uploaded successfully!';
                } else {
                    $error = 'Error saving file information to database.';
                    unlink($targetPath); // Delete uploaded file if database insert fails
                }
            } else {
                $error = 'Error uploading file. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Knowledge Portal</title>
    
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-white">
                        <h2 class="card-title mb-0">
                            <i class="fas fa-upload me-2"></i>Upload Content
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="lectures">Lectures</option>
                                    <option value="assignments">Assignments</option>
                                    <option value="research">Research</option>
                                    <option value="tutorials">Tutorials</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="file" class="form-label">File</label>
                                <div class="upload-area" id="dropZone">
                                    <input type="file" class="form-control" id="file" name="file" required 
                                           accept=".pdf,.doc,.docx,.mp4" style="display: none;">
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                        <h5>Drag and drop your file here</h5>
                                        <p class="text-muted">or</p>
                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('file').click()">
                                            <i class="fas fa-folder-open me-2"></i>Browse Files
                                        </button>
                                        <p class="text-muted mt-2">Supported formats: PDF, DOC, DOCX, MP4</p>
                                    </div>
                                </div>
                                <div id="fileInfo" class="mt-2" style="display: none;"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- File Upload Script -->
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('fileInfo');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('bg-light');
        }

        function unhighlight(e) {
            dropZone.classList.remove('bg-light');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFileInfo(files[0]);
        }

        fileInput.addEventListener('change', function() {
            updateFileInfo(this.files[0]);
        });

        function updateFileInfo(file) {
            if (file) {
                fileInfo.style.display = 'block';
                fileInfo.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-file me-2"></i>${file.name}
                        <span class="float-end">${formatFileSize(file.size)}</span>
                    </div>
                `;
            } else {
                fileInfo.style.display = 'none';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html> 