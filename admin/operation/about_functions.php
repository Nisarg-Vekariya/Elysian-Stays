<?php
// about_functions.php

function getHeroData($conn, $pageName) {
    $stmt = $conn->prepare("SELECT title, background_image, search_placeholder FROM page_hero WHERE page_name = ?");
    $stmt->bind_param("s", $pageName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Ensure background_image has proper path
    $data = $result->fetch_assoc();
    if (!empty($data['background_image']) && strpos($data['background_image'], 'http') !== 0) {
        // If it's a relative path, make sure it starts with /
        if (strpos($data['background_image'], '/') !== 0) {
            $data['background_image'] = '/' . $data['background_image'];
        }
    }
    
    return $data;
}

function getAboutContent($conn) {
    $result = $conn->query("SELECT section_name, title, content FROM about_content ORDER BY display_order");
    $content = [];
    while ($row = $result->fetch_assoc()) {
        $content[] = $row;
    }
    return $content;
}

function getAboutFacts($conn) {
    $result = $conn->query("SELECT statistic_value, description, footnote, animation_class FROM about_facts ORDER BY display_order");
    $facts = [];
    while ($row = $result->fetch_assoc()) {
        $facts[] = $row;
    }
    return $facts;
}
