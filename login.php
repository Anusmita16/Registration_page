<?php require_once('auth.php') ?>
<?php require_once('vendor/autoload.php') ?>
<?php
$clientID = "535208806989-5croi7gak9datdqc8791eg2mscbepdmd.apps.googleusercontent.com";
$secret = "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";

// Google API Client
$gclient = new Google_Client();

$gclient->setClientId($clientID);
$gclient->setClientSecret($secret);
$gclient->setRedirectUri('http://localhost/email-verify/home.php');


$gclient->addScope('email');
$gclient->addScope('profile');

if(isset($_GET['code'])){
    // Get Token
    $token = $gclient->fetchAccessTokenWithAuthCode($_GET['code']);

    // Check if fetching token did not return any errors
    if(!isset($token['error'])){
        // Setting Access token
        $gclient->setAccessToken($token['access_token']);

        // store access token
        $_SESSION['access_token'] = $token['access_token'];

        // Get Account Profile using Google Service
        $gservice = new Google_Service_Oauth2($gclient);

        // Get User Data
        $udata = $gservice->userinfo->get();
        foreach($udata as $k => $v){
            $_SESSION['login_'.$k] = $v;
        }
        $_SESSION['ucode'] = $_GET['code'];

        header('location: ./');
        exit;
    }
}

if (isset($_POST["login"]))
{
    $email = $_POST["email"];
    $password = $_POST["password"];

    // connect with database
    $conn = mysqli_connect("localhost", "root", "", "test");

    // check if credentials are okay, and email is verified
    $sql = "SELECT * FROM users WHERE email = '" . $email . "'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0)
    {
        die("Email not found.");
    }

    $user = mysqli_fetch_object($result);

    if (!password_verify($password, $user->password))
    {
        die("Password is not correct");
    }

    if ($user->email_verified_at == null)
    {
        die("Please verify your email <a href='email-verification.php?email=" . $email . "'>from here</a>");
    }

    echo "<p>You're loggedin</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient">
        <div class="container">
            <a class="navbar-brand" href="./">Google OAuth Integration in PHP & Email OTP</a>
            <div>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row">
            <div class="col-auto mx-auto">
            <form method="POST">
                <input type="email" name="email" placeholder="Enter email" required />
                <input type="password" name="password" placeholder="Enter password" required />
                 <input type="submit" name="login" value="Login">
              </form></br>
                <a href="<?= $gclient->createAuthUrl() ?>" class="btn btn btn-primary btn-flat rounded-0">Login with Google</a>
            </div>
        </div>
    </div>
</body>
</html>