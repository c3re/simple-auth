<?php

//send 403


function checkLogin($user,$pass){
    return (trim($user) === trim(getenv("AUTH_USER"))
        && trim($pass) === trim(getenv("AUTH_PASSWORD")));
}

ob_start();
session_name("simpleauth");
session_start();

if(isset($_COOKIE['login'])) {
    $loginCookie = json_decode(urldecode($_COOKIE['login']), true);
    if (is_array($loginCookie) && isset($loginCookie['user']) && isset($loginCookie['password'])) {
        if (checkLogin($loginCookie['user'], $loginCookie['password'])) {
            $_SESSION['loggedin'] = true;
            exit;
        }
    }
    setcookie("login", "", time() - 3600, "/");
}
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin'] == false;
$_SESSION['loggedin']=boolval($_SESSION['loggedin']);
if($_SESSION['loggedin']) exit;
header("HTTP/1.1 403 Forbidden");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            font-family: sans-serif;
        }
        label>span{
            display: block;
        }
        input{
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form method="POST" id="form">
        <label for="user"><span>Username:</span></label>
        <input type="text" id="user" name="user" required autocapitalize="off" autocomplete="username" autocorrect="off" spellcheck="false">
        <br>
        <label for="pass"><span>Password:</span></label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Login">
    </form>
<script>

    document.getElementById("form").addEventListener("submit", function(event) {
        event.preventDefault();
        var user = document.getElementById("user").value;
        var password = document.getElementById("password").value;
        console.log("user: " + user + ", password: " + password);
        let logincookie={
            "user": user,
            "password": password
        };

        document.cookie="login=" + encodeURIComponent(JSON.stringify(logincookie)) + "; path=/;";
        document.location.reload()
        return false;
    })
</script>
</body>
</html>
