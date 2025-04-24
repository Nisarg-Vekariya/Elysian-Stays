<?php require_once 'header.php';

// Function to get hero section data
function getHeroData($conn, $pageName) {
    $stmt = $conn->prepare("SELECT title, background_image, search_placeholder FROM page_hero WHERE page_name = ?");
    $stmt->bind_param("s", $pageName);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get about content sections
function getAboutContent($conn) {
    $result = $conn->query("SELECT title, content FROM about_content ORDER BY display_order");
    $content = [];
    while ($row = $result->fetch_assoc()) {
        $content[] = $row;
    }
    return $content;
}

// Function to get facts/statistics
function getAboutFacts($conn) {
    $result = $conn->query("SELECT statistic_value, description, footnote, animation_class FROM about_facts ORDER BY display_order");
    $facts = [];
    while ($row = $result->fetch_assoc()) {
        $facts[] = $row;
    }
    return $facts;
}

// Get data from database
$heroData = getHeroData($conn, 'about');
$aboutContent = getAboutContent($conn);
$aboutFacts = getAboutFacts($conn);
?>
<title>About Us</title>
<!-- Hero Section -->
<div class="hero animate__animated animate__fadeIn" id="heroabout" 
    style="<?php 
        $bgImage = !empty($heroData['background_image']) ? 'background-image: url(\''.htmlspecialchars($heroData['background_image']).'\')' : '';
        echo $bgImage;
    ?>">

    <h1 class="animate__animated animate__fadeInDown"><?php echo htmlspecialchars($heroData['title'] ?? 'About Us'); ?></h1>
    <a href="search.php"><div class="search-bar animate__animated animate__zoomIn">
        <input type="text" class="form-control" placeholder="<?php echo htmlspecialchars($heroData['search_placeholder'] ?? 'Click here to search for Destinations or Hotels.'); ?>">
    </div></a>
</div>
    <!-- Main Content -->
    <div class="about-section animate__animated animate__fadeIn">
        <?php if (!empty($aboutContent)): ?>
            <?php foreach ($aboutContent as $section): ?>
                <?php if (!empty($section['title'])): ?>
                    <h2 class="<?php echo strpos($section['title'], 'Story') !== false ? 'h2mod mt-5 animate__animated animate__fadeInDown animate__delay-2s' : 'animate__animated animate__fadeInDown'; ?>">
                        <?php echo htmlspecialchars($section['title']); ?>
                    </h2>
                    <div class="divider animate__animated animate__zoomIn<?php echo strpos($section['title'], 'Story') !== false ? ' animate__delay-2s' : ''; ?>"></div>
                <?php endif; ?>
                
                <?php if (!empty($section['content'])): ?>
                    <?php 
                    // Split content by newlines to create paragraphs
                    $paragraphs = explode("\n", $section['content']);
                    $delayClass = strpos($section['title'], 'Story') !== false ? ' animate__delay-3s' : '';
                    ?>
                    <?php foreach ($paragraphs as $index => $paragraph): ?>
                        <?php if (!empty(trim($paragraph))): ?>
                            <p class="text-center mb-0 text-p animate__animated animate__fadeInUp<?php echo $delayClass; ?>">
                                <?php echo htmlspecialchars(trim($paragraph)); ?>
                            </p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Facts Section -->
    <div class="container facts-section">
        <h2 class="h2mod">At a Glance</h2>
        <div class="divider"></div>
        <div class="row gy-4">
            <?php if (!empty($aboutFacts)): ?>
                <?php foreach ($aboutFacts as $index => $fact): ?>
                    <div class="col-md-3 col-sm-6 d-flex justify-content-center">
                        <div class="fact-box w-100" data-animation="<?php echo htmlspecialchars($fact['animation_class']); ?>">
                            <h3><?php echo htmlspecialchars($fact['statistic_value']); ?></h3>
                            <p><?php echo htmlspecialchars($fact['description']); ?></p>
                            <?php if (!empty($fact['footnote'])): ?>
                                <small><?php echo htmlspecialchars($fact['footnote']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php require_once 'footer.php'; ?>
