<?php

function analyze_sentiment($text) {
    $positiveWords = file('../sentiment/positive-words.txt', FILE_IGNORE_NEW_LINES);
    $negativeWords = file('../sentiment/negative-words.txt', FILE_IGNORE_NEW_LINES);

    $words = preg_split("/[\s,.!?;:]+/", strtolower($text));
    $positive = 0;
    $negative = 0;

    foreach ($words as $word) {
        if (in_array($word, $positiveWords)) $positive++;
        if (in_array($word, $negativeWords)) $negative++;
    }

    $total = $positive + $negative;
    $type = "Neutral";
    if ($positive > $negative) $type = "Positive";
    elseif ($negative > $positive) $type = "Negative";

    // Calculate positive and negative percentages
    if ($total > 0) {
        $positive_percentage = round(($positive / $total) * 100, 2);
        $negative_percentage = round(($negative / $total) * 100, 2);
    } else {
        $positive_percentage = 0;
        $negative_percentage = 0;
    }

    return [
        "positive" => $positive,
        "negative" => $negative,
        "positive_percentage" => $positive_percentage,
        "negative_percentage" => $negative_percentage,
        "type" => $type
    ];
}


// check pm bro