<?php
// contact_functions.php

function getContactHero($conn) {
    $stmt = $conn->prepare("SELECT title, background_image, search_placeholder FROM contact_hero LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Ensure background_image has proper path
    if (!empty($data['background_image']) && strpos($data['background_image'], 'http') !== 0) {
        // If it's a relative path, make sure it starts with /
        if (strpos($data['background_image'], '/') !== 0) {
            $data['background_image'] = '/' . $data['background_image'];
        }
    }
    
    return $data;
}

function getContactNumbers($conn) {
    $result = $conn->query("SELECT region, number FROM contact_numbers ORDER BY display_order");
    $numbers = [];
    while ($row = $result->fetch_assoc()) {
        $numbers[] = $row;
    }
    return $numbers;
}

function getAssistanceCenters($conn) {
    $result = $conn->query("SELECT city, address, phone, email FROM assistance_centers ORDER BY display_order");
    $centers = [];
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
    return $centers;
}

function getRegisteredOffice($conn) {
    $result = $conn->query("SELECT * FROM registered_office LIMIT 1");
    return $result->fetch_assoc();
}

function getPageContent($conn, $section_name) {
    $stmt = $conn->prepare("SELECT title, content FROM page_content WHERE section_name = ?");
    $stmt->bind_param("s", $section_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}