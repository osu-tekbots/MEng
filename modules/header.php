<?php

/**
 * This header module should be included in all PHP files that render visible HTML content. It includes all the
 * JavaScript and CSS files and creates the header navigation bar.
 * 
 * Before including the header file, you can specify a `$js` or `$css` variable to add additional JavaScript files
 * and CSS stylesheets to be included when the page loads in the browser. These additional files will be included
 * **after** the default scripts and styles already included in the header.
 */

if (!session_id()) {
    session_start();
}

$baseUrl = $configManager->getBaseUrl();

$title = (isset($title) ? $title : 'MEng') . ' | OSU';

// If the URL contains a query string parameter 'contentOnly=true', then we won't display a header or a footer
if (!isset($contentOnly)) {
    $contentOnly = isset($_GET['contentOnly']) && $_GET['contentOnly'] == 'true';
}

// CSS to include in the page. If you provide a CSS reference as an associative array, the keys are the
// atributes of the <link> tag. If it is a string, the string is assumed to be the href.
if (!isset($css)) {
    $css = array();
}
$css = array_merge(
    array(
        // Stylesheets to use on all pages
        array(
            'href' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css',
            'integrity' => 'sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr',
            'crossorigin' => 'anonymous'
        ),
        array(
            'href' => 'https://use.fontawesome.com/releases/v5.8.1/css/all.css',
            'integrity' => 'sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf',
            'crossorigin' => 'anonymous'
        ),
        'assets/css/theme.css',
        'assets/css/header.css',
        'assets/css/footer.css',
        'assets/css/global.css',
        'assets/css/layout.css'
    ),
    $css
);

// JavaScript to include in the page. If you provide a JS reference as an associative array, the keys are the
// atributes of the <script> tag. If it is a string, the string is assumed to be the src.
if (!isset($js)) {
    $js = array();
}
$js = array_merge( 
    // Scripts to use on all pages
    array(
        array(
            'src' => 'https://code.jquery.com/jquery-3.7.1.slim.js',
            'integrity' => 'sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=',
            'crossorigin' => 'anonymous'
        ),
        array(
            'src' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js',
            'integrity' => 'sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q',
            'crossorigin' => 'anonymous'
        ),
        'https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js',
    ), $js
);

// Setup the navigation links. If there are links already defined, merge them AFTER the default links
$navlinks = array(
    'HOME' => 'home'
);


if($isLoggedIn) {
	if (isset($_SESSION['userType'])){
		if($_SESSION['userType'] == UserType::ADMIN) {
			$navlinks['ADMIN'] = 'admin/';
		}
	}
    $navlinks['PROFILE'] = 'profile';
    $navlinks['SIGN OUT'] = 'signout';
} else {
    if($configManager->getEnvironment() == 'dev')
        $navlinks['SIGN IN'] = 'masq/index.php';
    else
        $navlinks['SIGN IN'] = 'signin';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href="<?php echo $baseUrl ?>" />
    <title><?php echo $title; ?></title>

    <?php
    // Include the CSS Stylesheets
    foreach ($css as $style) {
        if (!is_array($style)) {
            echo "<link rel=\"stylesheet\" href=\"$style\" />";
        } else {
            $link = '<link rel="stylesheet" ';
            foreach ($style as $attr => $value) {
                $link .= $attr . '="' . $value . '" ';
            }
            $link .= '/>';
            echo $link;
        }
    } 
    
    // Include the JavaScript files
    foreach ($js as $script) {
        if (!is_array($script)) {
            echo "<script type=\"text/javascript\" src=\"$script\"></script>";
        } else {
            $link = '<script type="text/javascript" ';
            foreach ($script as $attr => $value) {
                $link .= $attr . '="' . $value . '" ';
            }
            $link .= '></script>';
            echo $link;
        }
    } ?>

</head>
<body>
    <?php 
    if (!$contentOnly): 
    ?>

    <header id="header" class="dark">
        <a class="header-main-link" href="">
            <div class="logo">
                <h1 class="d-none d-lg-block" style="font-size: 36px; margin-bottom: 5px">MEng</h1>
				<h5 class="d-lg-none">MEng</h5>
            </div>
        </a>
        <nav class="navigation d-none d-sm-block">
            <ul>
            <?php 
            foreach ($navlinks as $title => $link) {
                echo "
                <a href='$link'>
                    <li>$title</li>
                </a>
                ";
            }
            ?>

            </ul>
        </nav>
        <nav class="navigation navbar-dark d-sm-none" id=navbarSmall>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navSmall" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse" id="navSmall" style="margin-top: 10px">
            <?php 
            foreach ($navlinks as $title => $link) {
                echo "
                <a href='$link'>
                    <h5>
                    $title
                    </h5>
                </a>
                <br>
                ";
            }
            ?>
            <div>
        </nav>
    </header>

    <?php
    endif;
    ?>
    
    <main <?php if (!$contentOnly) {
        echo 'class="extra-padding"';
    } ?>>