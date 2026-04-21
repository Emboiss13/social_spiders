<?php
session_start();
ob_start();

function normalize_language($lang) {
    $supported_languages = ["en", "es"];

    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_languages, true)) {
        return $_GET['lang'];
    }

    if (in_array($lang, $supported_languages, true)) {
        return $lang;
    }

    return "en";
}

function scanner_page_for_language($lang) {
    return $lang === "es" ? "pii-scanner-sp.html" : "pii-scanner-en.html";
}

function callback_translations($lang) {
    $translations = [
        "en" => [
            "nav_home" => "Home",
            "nav_profile" => "Profile",
            "nav_results" => "Results",
            "results_title" => "Results",
            "results_description" => "These are the posts we flagged, think carefully about what personal information you're exposing.",
            "no_results_title" => "Congratulations, no posts were flagged!",
            "no_results_description" => "However don't think you're in the clear yet. There are still a few things our system can't scan for, so make sure to have a check yourself."
        ],
        "es" => [
            "nav_home" => "Inicio",
            "nav_profile" => "Perfil",
            "nav_results" => "Resultados",
            "results_title" => "Resultados",
            "results_description" => "Estas son las publicaciones que hemos marcado. Piensa con cuidado qué información personal estás exponiendo.",
            "no_results_title" => "Felicidades, no se marcó ninguna publicación.",
            "no_results_description" => "Sin embargo, no asumas que ya no hay riesgos. Hay algunas cosas que nuestro sistema aún no puede analizar, así que revisa tus publicaciones también."
        ]
    ];

    return $translations[$lang] ?? $translations["en"];
}

$lang = normalize_language($_SESSION["lang"] ?? "en");
$_SESSION["lang"] = $lang;
$t = callback_translations($lang);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="alternate" hreflang="es" href="/es/">
    <title>PII Scanner - Results</title>
    <link rel="stylesheet" href="styling.css">
</head>
<body>
    <header class="site-header" id="home">
        <nav class="navbar" aria-label="Primary navigation">
            <a class="brand" href="#home">
                <img class="brand__logo" src="assets/logo-placeholder.png" alt="Social Spiders logo">
                <span class="brand__text">Social<br>Spiders</span>
            </a>

            <button class="nav-toggle" type="button" aria-controls="nav-panel" aria-expanded="false" aria-label="Toggle navigation">
                <span class="nav-toggle__line"></span>
                <span class="nav-toggle__line"></span>
                <span class="nav-toggle__line"></span>
            </button>

            <div class="nav-panel" id="nav-panel">
                <ul class="nav-links">
                    <li><a href="<?php echo $lang === "es" ? "home-sp.html" : "home-en.html"; ?>" aria-current="page"><?php echo $lang === "es" ? "Inicio" : "Home"; ?></a></li>
                    <li><a class="is-active" href="<?php echo scanner_page_for_language($lang); ?>"><?php echo $lang === "es" ? "Scanner IPI" : "PII Scanner"; ?></a></li>
                    <li><a href="<?php echo $lang === "es" ? "home-sp.html#objectives" : "home-en.html#objectives"; ?>"><?php echo $lang === "es" ? "Objetivos" : "Objectives"; ?></a></li>
                    <li><a href="<?php echo $lang === "es" ? "home-sp.html#about-us" : "home-en.html#about-us"; ?>"><?php echo $lang === "es" ? "Acerca de Nosotros" : "About Us"; ?></a></li>
                    <li><a href="<?php echo $lang === "es" ? "home-sp.html#privacy" : "home-en.html#privacy"; ?>"><?php echo $lang === "es" ? "Privacidad" : "Privacy"; ?></a></li>
                    <li><a href="#contact-us"><?php echo $lang === "es" ? "Contáctanos" : "Contact Us"; ?></a></li>
                </ul>

                <div class="nav-languages">
                    <a href="callback.php?lang=en" aria-label="English">
                        <img src="assets/flag-english.png" alt="English flag">
                    </a>
                    <a href="callback.php?lang=es" aria-label="Spanish">
                        <img src="assets/flag-mexican.png" alt="Spanish flag">
                    </a>
                </div>
            </div>
        </nav>
    </header>

<?PHP
/**
 * Central error handling function
 * Redirects user to the scanner page in the active language with error code
 * @param string $code Error code (1 = unknown platform, 2 = OAuth issue, 3 = API issue)
 */
function error($code){

    //error 1 (Unknown platform)

    //error 2 (OAuth issue)

    //error 3 (API issue)

    //return user to home page
    $lang = normalize_language($_SESSION["lang"] ?? "en");

    if (ob_get_length()) {
        ob_clean();
    }

    header("Location: " . scanner_page_for_language($lang) . "?error=" . urlencode($code));
    exit;
}

$posts = [];
$profile = [];

// loads API libraries and dependancies
require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


/**
 * Connects to facebook and pulls user's posts using the Graph API.
 * 
 * Retrieves posts from the user's timeline.
 * Each post returned includes the message content and the timestamp.
 * Posts without a message or timestamp are ignored.
 *
 * @param Facebook\Facebook $connection An instance of the Facebook SDK
 * @param string $access_token User OAuth access token
 * 
 * @return array Returns an array of posts:
 *               [
 *                  'text' => string, // Post message
 *                  'time' => string  // datetime string (YYYY-MM-DDThh:mm:ss)
 *               ]
 */
function getFacebookPosts($connection, $access_token) {

    $posts = [];

    //call to get timeline of posts
    $pull = $connection->get('me/posts', $access_token);

    //checks the me/posts call has a return of 200, else error
    if ($pull->getHttpStatusCode() != 200) {
            error("3");
    }

    //uses graph edge method to handle response
    $pull = $pull->getGraphEdge();

    //extracts useful elements to handle response
    foreach ($pull as $i) {
        //converts to datetime array object
        $date = (array)$i["created_time"];

        //checks message and date are set to prevent errors
        if (isset($i["message"]) && isset($date["date"])) {
            $posts[] = [
                "text" => $i["message"],
                "time" => $date["date"]
            ];
        }
    }

    return $posts;
}

/**
 * Fetches a user's posts from Instagram using the Instagram Basic Display API.
 *
 * This function retrieves all posts for the authenticated user, extracting
 * the caption, timestamp, and media URL for each post. Only posts that have a caption
 * and timestamp are included in the returned array.
 *
 * @param EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay $Instagram
 *        An instance of the InstagramBasicDisplay class with a valid access token.
 *
 * @return array An array of posts:
 *              [
 *                  'text'  => string  // Caption text of the post
 *                  'time'  => string  // Timestamp of the post (YYYY-MM-DDThh:mm:ss)
 *                  'media' => string|null  // URL of the post if available
 *              ]
 */
function getInstagramPosts($Instagram) {
    $posts = [];

    //user posts are fetched and object converted to array
    $pull = $Instagram->getUserMedia();
    $pull = (array)$pull;

    //loops through each post to extract useful attributes
    foreach ($pull["data"] as $i) {
        $i = (array)$i;

        if (isset($i["caption"]) && isset($i["timestamp"])) {
            $posts[] = [
                "text" => $i["caption"],
                "time" => $i["timestamp"],
                "media" => $i["media_url"] ?? null
            ];
        }
    }

    return $posts;
}

/**
 * Recursively fetches a user's tweets from their timeline using the Twitter API.
 *
 * This function retrieves up to 200 tweets per request, not including retweets.
 * If more than 200 tweets exist, it will recursively fetch additional tweets
 * until the oldest tweet is reached.
 *
 * @param Abraham\TwitterOAuth\TwitterOAuth $connection
 *        An authenticated instance of the TwitterOAuth client, initialized with
 *        the user's access tokens.
 * @param array $posts
 *        (Optional) Accumulator for recursive calls. Defaults to an empty array.
 * @param string|null $max_id
 *        (Optional) The maximum tweet ID to fetch in this request for recursive call. Defaults to null (first request).
 *
 * @return array An array of tweets:
 *               [
 *                  'text' => string  // The content of the tweet
 *                  'time' => string  // The timestamp of the tweet (YYYY-MM-DDThh:mm:ss)
 *               ]
 */
function getTwitterPosts($connection, $posts = [], $max_id = null) {
    
    //used if on first iteration
    if ($max_id == null) {
        $pull = $connection->get("statuses/user_timeline", [
            "count" => 200,
            "trim_user" => true,
            "exclude_rts" => true
        ]);
    } else {
        $pull = $connection->get("statuses/user_timeline", [
            "count" => 200,
            "trim_user" => true,
            "exclude_rts" => true,
            "max_id" => $max_id
        ]);
    }

    // check for API errors
    if ($connection->getLastHttpCode() != 200) {
        print($connection->getLastHttpCode());
        //error("3");
    }

    //loop through each post
    foreach ($pull as $tweet) {
        $tweet = (array)$tweet;
        if (isset($tweet['text']) && isset($tweet['created_at'])) {

            // convert to datetime format for consistency
            $dt = new DateTime($tweet['created_at']);
            $iso_time = $dt->format(DateTime::ATOM); // YYYY-MM-DDThh:mm:ss

            //extract text and time and put in array
            $posts[] = [
                'text' => $tweet['text'],
                'time' => $iso_time
            ];
        }
    }

    // continue until last post
    if (!empty($pull)) {
        $last_id = end($pull)->id_str;
        // stop if we reached the last tweet
        if ($last_id != $max_id) {
            return twitter_posts($connection, $posts, $last_id);
        }
    }

    return $posts;
}

/**
 * Renders flagged social media posts in HTML.
 *
 * Outputs a results section including a table of flagged posts.
 * If no posts are flagged, a congratulatory message is displayed.
 *
 * @param array $posts An array of posts where each post is an array with keys:
 *                      [
 *                          'text' => string, content of the post
 *                          'time' => string, datetime of the post (YYYY-MM-DDThh:mm:ss)
 *                          'flag' => string (optional), the flagged issue from text analysis
 *                      ]
 */
function parseFlagTypes($flag) {
    if (!isset($flag) || trim($flag) === "" || trim($flag) === "None") {
        return [];
    }

    $parts = preg_split("/,\\s*(?:and\\s+)?|\\s+and\\s+/i", $flag);
    $types = [];

    foreach ($parts as $part) {
        $type = strtoupper(trim($part));
        $type = preg_replace("/[^A-Z0-9]+/", "_", $type);
        $type = trim($type, "_");

        if ($type !== "") {
            $types[] = $type;
        }
    }

    return array_values(array_unique($types));
}

function buildScanResultsPayload($posts, $profileName, $platform, $totalScanned) {
    $findings = [];

    foreach ($posts as $post) {
        $findings[] = [
            "date" => substr($post["time"] ?? "", 0, 10),
            "text" => $post["text"] ?? "",
            "types" => parseFlagTypes($post["flag"] ?? "")
        ];
    }

    return [
        "platform" => $platform,
        "profileName" => $profileName,
        "totalScanned" => $totalScanned,
        "findings" => array_values($findings)
    ];
}

function renderResults($posts, $translations, $profileName, $profileImageUrl, $platform, $totalScanned, $lang) {
    $isSpanish = $lang === "es";
    $profileAlt = $isSpanish ? "Imagen de perfil" : "Profile image";
    $tableTitle = $isSpanish ? "Publicaciones Marcadas" : "Flagged Posts";
    $dateHeading = $isSpanish ? "Fecha" : "Date";
    $contentHeading = $isSpanish ? "Contenido de la Publicación" : "Post Content";
    $typeHeading = $isSpanish ? "Tipo" : "Type";
    $scoreHeading = $isSpanish ? "Puntuación General" : "Overall Score";
    $profileImage = $profileImageUrl ?: "assets/profile-placeholder.png";
    $payload = buildScanResultsPayload($posts, $profileName, $platform, $totalScanned);
    $json = json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    echo "<main>";
    echo "<section class='scanner-results' aria-labelledby='scanner-results-title'>";
    echo "<div class='scanner-results__inner'>";
    echo "<div class='scanner-results__overview'>";
    echo "<div class='scanner-results__profile-placeholder' role='img' aria-label='" . htmlspecialchars($profileAlt, ENT_QUOTES, "UTF-8") . "'>";
    echo "<img class='scanner-results__profile-image' src='" . htmlspecialchars($profileImage, ENT_QUOTES, "UTF-8") . "' alt='" . htmlspecialchars($profileAlt, ENT_QUOTES, "UTF-8") . "'>";
    echo "</div>";

    echo "<div class='scanner-results__summary'>";
    echo "<h1 class='scanner-results__title' id='scanner-results-title'>" . ($isSpanish ? "Hola " : "Hey ") . htmlspecialchars($profileName, ENT_QUOTES, "UTF-8") . "!</h1>";
    echo "<p class='scanner-results__subtitle' id='scanner-results-subtitle'>" . ($isSpanish ? "Lo que " : "What ") . "<strong>" . htmlspecialchars($platform, ENT_QUOTES, "UTF-8") . "</strong> " . ($isSpanish ? "dice sobre ti..." : "Says About You...") . "</p>";
    echo "<p class='scanner-results__description' id='scanner-results-description'>" . htmlspecialchars($translations["results_description"], ENT_QUOTES, "UTF-8") . "</p>";
    echo "<ul class='scanner-results__risk-list' id='scanner-results-risk-list' aria-label='" . ($isSpanish ? "Explicaciones de riesgo" : "Risk explanations") . "'></ul>";
    echo "</div>";

    echo "<aside class='result-chart' aria-labelledby='result-chart-title'>";
    echo "<div class='result-chart__donut' id='result-chart-donut' aria-hidden='true'></div>";
    echo "<div class='result-chart__legend' id='result-chart-legend'>";
    echo "<h2 class='result-chart__title' id='result-chart-title'>" . htmlspecialchars($scoreHeading, ENT_QUOTES, "UTF-8") . "</h2>";
    echo "</div>";
    echo "</aside>";
    echo "</div>";

    echo "<div class='scanner-results__table-wrap' aria-labelledby='scanner-results-table-title'>";
    echo "<h2 class='scanner-results__table-title' id='scanner-results-table-title'>" . htmlspecialchars($tableTitle, ENT_QUOTES, "UTF-8") . "</h2>";
    echo "<table class='scanner-results__table'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th scope='col'>" . htmlspecialchars($dateHeading, ENT_QUOTES, "UTF-8") . "</th>";
    echo "<th scope='col'>" . htmlspecialchars($contentHeading, ENT_QUOTES, "UTF-8") . "</th>";
    echo "<th scope='col'>" . htmlspecialchars($typeHeading, ENT_QUOTES, "UTF-8") . "</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody id='scanner-results-table-body'></tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</section>";
    echo "<script id='scan-results-data' type='application/json'>" . $json . "</script>";
}

/**
 * Main function for platform login, post fetching, text analysis, and rendering
 */

function main ($translations, $lang){
    $platformDisplay = $_SESSION["platform"] ?? "Social Media";
    $profileName = "there";
    $profileImageUrl = "assets/profile-placeholder.png";

    switch ($_SESSION["platform"] ?? null) {

        case "session":
            
            // pull previous results from session storage
            $posts = $_SESSION['posts'];
            $profileName = $_SESSION['profileName'];
            $profileImageUrl = $_SESSION['profileImageUrl'];
            $platformDisplay = $_SESSION['platformDisplay'];
            $totalScanned = $_SESSION['totalScanned'];

            break;

        case "Facebook":
            // setup connection

            // new connection using env variables
            $Facebook = new Facebook\Facebook([
                'app_id' => $_ENV['META_APP_ID'],
                'app_secret' => $_ENV['META_APP_SECRET'],
                'default_graph_version' => 'v2.10'
            ]);

            $helper = $Facebook->getRedirectLoginHelper();

            //get access token from Facebook object
            $access_token = $helper->getAccessToken();

            //check that the OAuth tokens are set, if not error
            if (!isset($access_token)) {
                error("2");
            }

            //call function to get posts
            $posts = getFacebookPosts($Facebook, $access_token);
            
            //call function to get profile
            $profile = $Facebook->get("/me?fields=id,email,name,link", $access_token)->getGraphNode()->asArray();
            $profileName = $profile["name"] ?? $profileName;

            if (isset($profile["id"])) {
                $profileImageUrl = "https://graph.facebook.com/" . rawurlencode($profile["id"]) . "/picture?type=large&access_token=" . $access_token;
            }

            break;

        case "Instagram":
            
             //initializes an instance of the IG object with creds for API call
            $Instagram = new EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay([
                'appId' => $_ENV['META_APP_ID'],
                'appSecret' => $_ENV['META_APP_SECRET'],
                'redirectUri' => $_ENV['CALLBACK_URL']
            ]);

            //setup connection
            if (!isset($_GET['code'])) {
                error("2");
            }

            //OAuth token fetched using code
            $OAuth_token = $Instagram->getOAuthToken($_GET['code'], true);
            
            //OAuth token generated
            $Instagram->setAccessToken($OAuth_token->access_token);

            //Get posts and profile and render
            $posts = getInstagramPosts($Instagram);

            $profile = (array)$Instagram->_makeCall("me", ["fields"=>"username"]);
            $profileName = $profile["username"] ?? $profileName;

            break;


        case "twitter":
            
            // Check if user denied access
            if (isset($_GET['denied'])) {
                error("2");
            }

            // Validate required OAuth parameters
            if (!isset($_GET['oauth_token']) || $_GET['oauth_token'] !== $_SESSION['Oauth_token']) {
                error("2");
            }

            // Create connection using request token (from session)
            $Twitter = new Abraham\TwitterOAuth\TwitterOAuth(
                $_ENV['X_CONSUMER_KEY'],
                $_ENV['X_CONSUMER_KEY_SECRET'],
                $_SESSION["Oauth_token"],
                $_SESSION["Oauth_token_secret"]
            );

            // Exchange request token for access token
            $access_token = $Twitter->oauth("oauth/access_token", ["oauth_verifier" => 
            $_GET['oauth_verifier']]);

            // Check API response
            if ($Twitter->getLastHttpCode() != 200) {
                error("3");
            }

            // Create new connection with ACCESS token (final step)
            $Twitter = new Abraham\TwitterOAuth\TwitterOAuth(
                $_ENV['X_CONSUMER_KEY'],
                $_ENV['X_CONSUMER_KEY_SECRET'],
                $access_token["oauth_token"],
                $access_token["oauth_token_secret"]
            );


            //get posts and profile data and render
            $posts = getTwitterPosts($Twitter);

            $user = $Twitter->get("account/verify_credentials");
            $profileName = $user->name ?? $user->screen_name ?? $profileName;
            $profileImageUrl = $user->profile_image_url_https ?? $profileImageUrl;
            $platformDisplay = "Twitter/X";

            break;

        default:
            error("1");
    }

    $totalScanned = count($posts);

    // loop through posts and look for bad posts  
    foreach ($posts as $key => $post) {
        $command = "/var/www/html/social-spiders/src/venv/bin/python /var/www/html/social-spiders/src/text_analysis.py " . escapeshellarg($post['text']) . " 2>&1";
        $output = exec($command);
        $posts[$key]["flag"] = $output;
    }

    // render any posts that are flagged from the model
    renderResults(array_values($posts), $translations, $profileName, $profileImageUrl, $platformDisplay, $totalScanned, $lang);

    // store results in session to persist reload or langauge change
    $_SESSION['platform'] = "session";
    $_SESSION['posts'] = $posts;
    $_SESSION['profileName'] = $profileName;
    $_SESSION['profileImageUrl'] = $profileImageUrl;
    $_SESSION['platformDisplay'] = $platformDisplay;
    $_SESSION['totalScanned'] = $totalScanned;
}

main($t, $lang);

?>

        <footer class="site-footer" id="contact-us">
            <div class="site-footer__backdrop"></div>
            <div class="site-footer__panel">
                <div class="site-footer__grid">
                    <div class="site-footer__brand">
                        <a class="site-footer__brand-link" href="#home">
                            <img class="site-footer__logo" src="assets/footer-logo-placeholder.png" alt="Social Spiders logo">
                            <span class="site-footer__brand-text">Social<br>Spiders</span>
                        </a>
                        <p class="site-footer__tagline">Where social privacy meets security</p>

                        <div class="site-footer__socials" aria-label="Social links">
                            <a href="#linkedin" aria-label="LinkedIn">
                                <img src="assets/icon-linkedin.png" alt="" aria-hidden="true">
                            </a>
                            <a href="#instagram" aria-label="Instagram">
                                <img src="assets/icon-instagram.png" alt="" aria-hidden="true">
                            </a>
                            <a href="#facebook" aria-label="Facebook">
                                <img src="assets/icon-facebook.png" alt="" aria-hidden="true">
                            </a>
                            <a href="#twitter" aria-label="Twitter">
                                <img src="assets/icon-twitter.png" alt="" aria-hidden="true">
                            </a>
                        </div>
                    </div>

                    <div class="site-footer__column">
                        <h2 class="site-footer__heading">Our Services</h2>
                        <ul class="site-footer__list">
                            <li><a href="#financial-security">Financial Security</a></li>
                            <li><a href="#pii-scanner">Social Media Scanner</a></li>
                            <li><a href="#personal-security">Personal Security</a></li>
                            <li><a href="#data-security">Data Security</a></li>
                        </ul>
                    </div>

                    <div class="site-footer__column">
                        <h2 class="site-footer__heading">Page</h2>
                        <ul class="site-footer__list">
                            <li><a href="#about-us">About Us</a></li>
                            <li><a href="#our-team">Our Team</a></li>
                            <li><a href="#pricing">Pricing</a></li>
                            <li><a href="#our-blog">Our Blog</a></li>
                        </ul>
                    </div>

                    <div class="site-footer__column">
                        <h2 class="site-footer__heading">Contact Us</h2>
                        <ul class="site-footer__contact-list">
                            <li>
                                <img src="assets/icon-phone.png" alt="" aria-hidden="true">
                                <span>+44 1929 2102</span>
                            </li>
                            <li>
                                <img src="assets/icon-email.png" alt="" aria-hidden="true">
                                <span>info@socialspiders.co.uk</span>
                            </li>
                            <li>
                                <img src="assets/icon-location.png" alt="" aria-hidden="true">
                                <span>Cardiff, Wales, UK</span>
                            </li>
                        </ul>
                    </div>

                    <div class="site-footer__column">
                        <h2 class="site-footer__heading">Links</h2>
                        <ul class="site-footer__list">
                            <li><a href="#terms">Term Of Use</a></li>
                            <li><a href="#privacy">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>

                <div class="site-footer__divider"></div>
                <p class="site-footer__copyright">copyright social spiders @2026 all right reserved</p>
            </div>
        </footer>
    </main>
    <script src="scanner-results.js"></script>
    <script src="logic.js"></script>
</body>
</html>