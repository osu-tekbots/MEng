<?php

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Authenticate a user using Oregon State University's CAS server.
 * 
 * This will require the user's ONID username and password. If a there are query string parameters present in the URL
 * of the page making the authentication request, they will not be properly handled by the CAS server. Instead, it
 * is recommended that you save any required query string paramters in a session variable during authentication and
 * read them back out once it is successful.
 * 
 * This function will, on successfuly authentication, set the `$_SESSION['auth']` variable to an associative array with
 * the following keys:
 * - `method`: `'onid'`
 * - `id`: the ONID of the user
 * - `firstName`: the first name of the user
 * - `lastName`: the last name of the user
 * - `email`: the email address of the user
 *
 * @return string the ONID for the user
 */
function authenticateWithONID() {
    /*if (isset($_SESSION['auth']['id'])) {
        return $_SESSION['auth']['id'];
    }*/ // Removed because it may interact poorly with login methods on other sites

    $pageURL = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $pageURL .= 's';
    }
    $pageURL .= '://';

    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
        $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'];
    } else {
        $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
    }

    $ticket = isset($_REQUEST['ticket']) ? $_REQUEST['ticket'] : false;

    
    if ($ticket) {
        $url = 'https://login.oregonstate.edu/cas/serviceValidate?ticket=' . $ticket . '&service=' . $pageURL;
        $html = file_get_contents($url);

        // ini_set("log_errors", 1);
        // ini_set("error_log", __DIR__ . "/../../../.private/logs/php-error.log");
        //error_log(print_r($html, true));

        // Extract first name with fallbacks: firstname -> givenName -> parsed from fullname -> parsed from commonName
        $firstName = extractFromXml('cas:firstname', $html);
        if (empty($firstName)) {
            $firstName = extractFromXml('cas:givenName', $html);
        }
        if (empty($firstName)) {
            $firstName = parseNamePart(extractFromXml('cas:fullname', $html), 'first');
        }
        if (empty($firstName)) {
            $firstName = parseNamePart(extractFromXml('cas:commonName', $html), 'first');
        }

        // Extract last name with fallbacks: lastname -> surname -> parsed from fullname -> parsed from commonName
        $lastName = extractFromXml('cas:lastname', $html);
        if (empty($lastName)) {
            $lastName = extractFromXml('cas:surname', $html);
        }
        if (empty($lastName)) {
            $lastName = parseNamePart(extractFromXml('cas:fullname', $html), 'last');
        }
        if (empty($lastName)) {
            $lastName = parseNamePart(extractFromXml('cas:commonName', $html), 'last');
        }

        // Extract email with fallbacks: email -> osuprimarymail -> eduPersonPrincipalName
        $email = extractFromXml('cas:email', $html);
        if (empty($email)) {
            $email = extractFromXml('cas:osuprimarymail', $html);
        }
        if (empty($email)) {
            $email = extractFromXml('cas:eduPersonPrincipalName', $html);
        }

        // Extract ID with fallback: user -> uid
        $id = strtolower(extractFromXml('cas:user', $html));
        if (empty($id)) {
            $id = strtolower(extractFromXml('cas:uid', $html));
        }

        $_SESSION['auth'] = array(
            'method' => 'onid',
            'id' => $id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email
        );

        return $_SESSION['auth']['id'];
    } else {
        $url = 'https://login.oregonstate.edu/cas/login?service=' . $pageURL;
        echo "<script>location.replace('" . $url . "');</script>";
        die();
    }
}

/**
 * Fetches the value of the key from the XML structure.
 * 
 * Text-based search, not tree based.
 *
 * @param string $key the XML tag name to search for
 * @param string $xml the XML to search
 * @return string|false the contents of the XML tag with the provided key name, or false if not found
 */
function extractFromXml($key, $xml) {
    $pattern = '/\<' . $key . '\>([^<]+)\<\/' . $key . '\>/';
    preg_match($pattern, $xml, $matches);
    if ($matches && count($matches) > 1) {
        return trim($matches[1]);
    }
    return false;
}

/**
 * Parses a first or last name from a "Last, First" formatted string.
 *
 * @param string|false $fullName the full name string in "Last, First" format
 * @param string $part either 'first' or 'last'
 * @return string|false the parsed name part, or false if parsing fails
 */
function parseNamePart($fullName, $part) {
    if (empty($fullName)) {
        return false;
    }
    $parts = explode(',', $fullName);
    if (count($parts) >= 2) {
        return $part === 'last' ? trim($parts[0]) : trim($parts[1]);
    }
    // If no comma, return the whole string for either part
    return trim($fullName);
}
