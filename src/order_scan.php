<?php

// Start the sessions to send variables to callback.php
session_start();

// loads API libraries
require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// unsets session post and profile variables if multiple scans in one session
if (isset($_SESSION["profile"]) && isset($_SESSION["posts"])) {
    unset($_SESSION["profile"]);
    unset($_SESSION["posts"]);
}

// post requests gets user input variables and creates session variable
$_SESSION["platform"] = $_POST["platform"];

function normalize_language($lang) {
    $supported_languages = ["en", "es"];

    if (in_array($lang, $supported_languages, true)) {
        return $lang;
    }

    return "en";
}

function scanner_page_for_language($lang) {
    return $lang === "es" ? "pii-scanner-sp.html" : "pii-scanner-en.html";
}

// error handling function to return user to the scanner page in the active language
function error($code) {
    $lang = normalize_language($_SESSION["lang"] ?? "en");
    header("Location: " . scanner_page_for_language($lang) . "?error=" . urlencode($code));
    die();
}

// post requests get user input variables and create session variables
$lang = normalize_language($_POST["lang"] ?? ($_SESSION["lang"] ?? "en"));
$_SESSION["lang"] = $lang;

function authFacebook(){
    // initialize instance of Facebook class, and adds getRedirectLoginHelper, used for API calls
    $conection = new Facebook\Facebook([
        'app_id' => $_ENV['META_APP_ID'],
        'app_secret' => $_ENV['META_APP_SECRET'],
        'default_graph_version' => 'v2.10'
    ]);
    $conection = $conection->getRedirectLoginHelper();

    // generates redirect url
    $url = $conection->getLoginUrl($_ENV['CALLBACK_URL']);

    return $url;
}

function authInstagram(){
    // initializes an instance of the IG object with creds for API call
    $Instagram = new EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay([
        'appId' => $_ENV['META_APP_ID'],
        'appSecret' => $_ENV['META_APP_SECRET'],
        'redirectUri' => $_ENV['CALLBACK_URL']
    ]);

    // generates redirect URL with permissions
    $url = $Instagram->getLoginUrl(
        ['user_profile', 'user_media']
    );

    // error function called if the url is not set
    if (!isset($url)) {
        error("2");
    }

    return $url;
}

function authTwitter(){
    /// creates instance of twitterOauth object with keys
    $conection = new Abraham\TwitterOAuth\TwitterOAuth(
        $_ENV['X_CONSUMER_KEY'],
        $_ENV['X_CONSUMER_KEY_SECRET'],
    );

    $request_token = $conection->oauth(
        "oauth/request_token",
        ["oauth_callback" => $_ENV['CALLBACK_ADDRESS']]
    );

    // error response if non 200 code returned from API
    if ($conection->getLastHttpCode() != 200) {
        error("2");
    }

    // sets session variables to send keys to callback.php
    $_SESSION["Oauth_token"] = $request_token["oauth_token"];
    $_SESSION["Oauth_token_secret"] = $request_token["oauth_token"];

    // generates url to redirect user to Twitter sign in
    $url = $conection->url("oauth/authorize",["oauth_token" => $request_token["oauth_token"]]);

    return $url;
}

function main() {
    switch ($_SESSION["platform"]) {
        case "Facebook":
            $url = authFacebook();
            break;
        case "Instagram":
            $url = authInstagram();
            break;
        case "Twitter":
            $url = authTwitter();
            break;

    }

    // redirects user to login page and kills current page
    header("Location: $url"); 
    die();
}

main();

?>
