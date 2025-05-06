<?php
function log_activity($pdo, $user_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $message]);
}

function log_product_deletion($userId, $productName, $productId, $productImage, $reviews) {
    // Prepare the activity message
    $activity = "Deleted product '{$productName}' (ID: $productId)";
    $activity .= $productImage ? ", image '{$productImage}'" : "";
    $reviewCount = count($reviews);
    $activity .= $reviewCount > 0 ? ", and $reviewCount associated review(s): " : ", no associated reviews";

    // Optionally list the deleted review IDs and excerpts (for traceability)
    if ($reviewCount > 0) {
        foreach ($reviews as $r) {
            $excerpt = substr($r['review'], 0, 50);
            $activity .= "\n - Review ID {$r['id']}: \"" . htmlspecialchars($excerpt) . (strlen($r['review']) > 50 ? '…' : '') . "\"";
        }
    }

    // Log the activity
    global $pdo;
    $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $logStmt->execute([$userId, $activity]);
}

function log_review_deletion($userId, $reviewId, $productName, $reviewText, $voteCount, $sentimentExisted) {
    // Shorten the review text for logging purposes if it's too long
    $shortenedReview = mb_strlen($reviewText) > 100 ? mb_substr($reviewText, 0, 100) . "..." : $reviewText;

    // Prepare the activity log message
    $activity = "Deleted review ID $reviewId from product '$productName', content: \"$shortenedReview\"";
    $activity .= $voteCount ? ", along with $voteCount vote(s)" : ", no votes";
    $activity .= $sentimentExisted ? ", and its sentiment analysis" : ", no sentiment data";

    // Insert log into the activity_logs table
    global $pdo;
    $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $logStmt->execute([$userId, $activity]);
}

function log_user_deletion($adminId, $userIdToDelete, $isSelf, $email = null) {
    global $pdo;

    $logDetails = [];

    if ($isSelf) {
        $logDetails[] = "Deleted own account (ID: $userIdToDelete)";
        $logDetails[] = "Logged out after deleting own account";
    } else {
        $logDetails[] = "Deleted user (ID: $userIdToDelete, Email: $email)";
        $logDetails[] = "Deleted reviews associated with user";
        $logDetails[] = "Deleted votes associated with user";
        $logDetails[] = "Deleted activity logs associated with user";
    }

    $activityLog = implode(" | ", $logDetails);

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$adminId, $activityLog]);
}

function log_lexicon_activity($userId, $action, $wordType, $details = '') {
    global $pdo;

    $actionText = ucfirst($action);
    $message = "$actionText word in $wordType lexicon";
    if ($details) {
        $message .= ": $details";
    }

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$userId, $message]);
}

function log_product_edit_activity($userId, $productId, $changes) {
    global $pdo;

    $activity = "Updated product (ID: $productId). Changes: $changes";
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$userId, $activity]);
}

function log_review_edit_activity($userId, $reviewId, $productName, $changeDescription) {
    global $pdo;

    $activity = "Updated review (ID: $reviewId) on product '$productName': $changeDescription";
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$userId, $activity]);
}

function log_user_edit_activity($adminUserId, $editedUserId, $changeSummary) {
    global $pdo;

    $activity = "Edited user ID: $editedUserId - Changed $changeSummary.";
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$adminUserId, $activity]);
}
