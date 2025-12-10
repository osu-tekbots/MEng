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

$title = (isset($title) ? $title : 'MEng Assessment') . ' | OSU';

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
        array(
            'href' => 'https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.css',
            'integrity' => 'sha384-49/RW1o98YG2C2zlWgS77FLSrXw99u/R5gTv26HOR4VWXy7jVEt8iS/cfDn6UtHE',
            'crossorigin' => 'anonymous'
        ),
        'assets/css/theme.css',
        'assets/css/header.css',
        'assets/css/footer.css',
        'assets/css/global.css',
        'assets/css/layout.css',
        'assets/css/snackbar.css',
        'assets/css/multiselect.css'
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
        array(
            'src' => 'https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.js',
            'integrity' => 'sha384-0y3De3Rxhdkd4JPUzXfzK6J+7DyDlhLosIUV2OnIgn3Lh1i86pheXHOYUHK85Vwz',
            'crossorigin' => 'anonymous'
        ),
        array(
            'src' => 'assets/js/header.js',
            'defer' => 'true'
        ),
        'https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js',
        'assets/js/api.js',
        'assets/js/snackbar.js',
        'assets/js/multiselect.js'
    ), $js
);

// Setup the navigation links. If there are links already defined, merge them AFTER the default links
$navlinks = array(
    'HOME' => ''
);


if($isLoggedIn) {
	if (isset($_SESSION['userType'])){
    if($_SESSION['userIsAdmin']) {
        $navlinks['ADMIN'] = ['Assign Reviews'=> 'assignReviews.php', 'View Reviews'=> 'viewReviews.php', 'Build Rubrics'=> 'createRubric.php'];
    } 
    if($_SESSION['userIsReviewer']) {
      $navlinks['REVIEW'] = ['Reviewer Assignments' =>'reviewerAssignments.php', 'Evaluate Rubrics'=> 'evaluateRubrics.php'];
    } 
    if($_SESSION['userIsStudent']) {
      $navlinks['UPLOAD'] = 'studentUpload.php';
    }
	}
    $navlinks['PROFILE'] = 'profile';
    $navlinks['LOG OUT'] = 'logout';
} else {
    if($configManager->getEnvironment() == 'dev')
        $navlinks['LOG IN'] = 'masq/index.php';
    else
        $navlinks['LOG IN'] = 'login';
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
                <h1 class="d-none d-lg-block" style="font-size: 48px;">MEng Assessment</h1>
				<h5 class="d-lg-none">MEng Assessment</h5>
            </div>
        </a>
        <nav class="navigation d-none d-sm-block">
            <style>
                /* Hide dropdown by default */
                .navigation .dropdown-menu {
                    display: none;
                    position: absolute; /* needed so it floats under the toggle */
                }

                /* Bootstrap JS adds .show when open */
                .navigation .dropdown-menu.show {
                    display: block;
                }
            </style>
            <!-- uses style rules to manage dropdown opening and closing (not bootrstraps internal system) -->
            <ul>
                <?php 
                    $navString = '';
                    foreach ($navlinks as $title => $link) {
                        if (is_array($link)) {
                            $navString .= 
                            '<li class="nav-item dropdown" style="list-style: none;">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown_' . strtolower($title) . '" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                                 . $title . 
                                '</a>
                            <ul class="dropdown-menu" aria-labelledby="dropdown_' . strtolower($title) . '">';
                            foreach ($link as $itemName => $itemHref) {
                                $navString .= '<li><a class="dropdown-item" href="' . $itemHref . '">' . $itemName . '</a></li>';
                            }
                            $navString .= '</ul> </li>';
                            
                        } else {
                            $navString .= '<li class="nav-item" style="list-style: none;"><a class="nav-link" href="' . $link . '">' . $title . '</a></li>';
                        }
                    }
                    echo $navString;
                ?>
            </ul>
        </nav>
        <!-- Small screen version -->
        <nav class="navigation d-block d-sm-none">
            <style>
                /* Hide dropdowns by default */
                .navigation .dropdown-menu {
                display: none;
                position: absolute; /* floats below the toggle */
                }

                /* Show dropdown when JS adds .show */
                .navigation .dropdown-menu.show {
                display: block;
                }
            </style>

            <ul>
                <li class="nav-item dropdown" style="list-style: none;">
                    <a class="nav-link dropdown-toggle" href="#" id="mobileNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="mobileNavDropdown">
                    <?php 
                        $navString = '';
                        foreach ($navlinks as $title => $link) {
                            if (is_array($link)) {
                                foreach ($link as $itemName => $itemHref) {
                                    $navString .= '<li style="list-style: none;"><a class="dropdown-item" href="' . $itemHref . '">' . $itemName . '</a></li>';
                                }
                            } else {
                                $navString .= '<li style="list-style: none;"><a class="dropdown-item" href="' . $link . '">' . $title . '</a></li>';
                            }
                        }
                        echo $navString;
                    ?>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <?php
    endif;
    ?>
    
    <main <?php if (!$contentOnly) {
        echo 'class="extra-padding"';
    } ?>>