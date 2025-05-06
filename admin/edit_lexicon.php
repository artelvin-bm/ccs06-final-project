<?php
include('../includes/auth_check.php');
include('../config/db.php');
include('../includes/activity_log_functions.php');

// Ensure only admins can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Determine lexicon type
$types = ['positive', 'negative'];
$type = $_GET['type'] ?? 'positive';
if (!in_array($type, $types)) $type = 'positive';

$filepath = "../sentiment/{$type}-words.txt";
$current_words = file_exists($filepath) ? file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $original = trim($_POST['original_word'] ?? '');
    $word = trim($_POST['word'] ?? '');
    $new_word = trim($_POST['new_word'] ?? '');

    $log_message = null;

    if ($action === 'add' && $word !== '' && !in_array($word, $current_words)) {
        $current_words[] = $word;
        sort($current_words);
        $log_message = "Added word '$word' to $type lexicon.";
    } elseif ($action === 'delete') {
        $current_words = array_filter($current_words, fn($w) => $w !== $original);
        $log_message = "Deleted word '$original' from $type lexicon.";
    } elseif ($action === 'edit') {
        if ($original !== '' && $new_word !== '' && $original !== $new_word) {
            $key = array_search($original, $current_words);
            if ($key !== false && !in_array($new_word, $current_words)) {
                $current_words[$key] = $new_word;
                sort($current_words);
                $log_message = "Edited word '$original' to '$new_word' in $type lexicon.";
            }
        }
    }
    
    // Save updated words
    file_put_contents($filepath, implode("\n", $current_words));
    
    // Log the activity
    if ($log_message) {
        log_lexicon_activity($_SESSION['user']['id'], $action, $type, $log_message);
    }
    
    header("Location: edit_lexicon.php?type=$type");
    exit();
}

include('../includes/header.php');
?>

<div class="container mt-5">
    <h2>Edit <?= ucfirst($type) ?> Lexicon</h2>

    <!-- Toggle Buttons -->
    <div class="mb-4">
        <a href="?type=positive" class="btn btn-outline-success <?= $type === 'positive' ? 'active' : '' ?>">Positive</a>
        <a href="?type=negative" class="btn btn-outline-danger <?= $type === 'negative' ? 'active' : '' ?>">Negative</a>
    </div>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="searchBox" class="form-control" placeholder="Search word...">
    </div>

    <!-- Add Word Form -->
    <div class="mb-4">
        <form method="POST" class="d-flex gap-2">
            <input type="hidden" name="action" value="add">
            <input type="text" name="word" class="form-control" placeholder="New word" required>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>

    <!-- Word List -->
    <ul class="list-group" id="wordList" style="max-height: 400px; overflow-y: auto;">
        <?php foreach ($current_words as $word): ?>
            <li class="list-group-item word-item" data-word="<?= htmlspecialchars($word) ?>">
                <span class="word-text"><?= htmlspecialchars($word) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Actions for Selected Word -->
    <form method="POST" id="actionForm" class="mt-3 d-none d-flex gap-2">
        <input type="hidden" name="original_word" id="originalWordInput">
        <input type="hidden" name="action" id="actionTypeInput" value="">

        <input type="text" name="new_word" id="editWordInput" class="form-control d-none" placeholder="Edit word...">

        <button type="submit" class="btn btn-primary d-none" id="saveEditBtn">Save</button>
        <button type="button" class="btn btn-warning btn-sm" id="editBtn">Edit</button>
        <button type="submit" class="btn btn-danger" id="deleteBtn">Delete</button>
    </form>
</div>

<!-- Styles -->
<link rel="stylesheet" href="../assets/css/edit_lexicon.css">

<!-- JavaScript -->
<script src="../assets/js/edit_lexicon.js"></script>

<?php include('../includes/footer.php'); ?>
