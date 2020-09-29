<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Guestbook</title>
    <link rel="stylesheet" href="https://unpkg.com/mvp.css">
    <link rel="stylesheet" href="stylesheet.css" type="text/css">
</head>

<?php
require 'profanity.php';

session_start();

$_SESSION['storyfill'] = '';
$_SESSION['namefill'] = '';


$storyfilename = "story.txt";
$storyfile = fopen($storyfilename, "r+") or die("Unable to open file!");
$storycontents = fread($storyfile, 1 + filesize($storyfilename));
$numberofparts = substr_count($storycontents, '<br>');


if (isset($_POST['namefield']) && isset($_POST['storyfield']) && (ContributedLast() == false)){
    echo (validateName($_POST['namefield']));
    $_SESSION['storyfill'] = $_POST['storyfield'];
    if (validateName($_POST['namefield'])  === '') {
        $_SESSION['namefill'] = $_POST['namefield'];
        if (validateStory($_POST['storyfield']) === '') {
            echo (validateStory($_POST['storyfield']));
            //add to file
            $date = date('Y-m-d H:i:s');
            $title = 'Part-' . $numberofparts;
            $txt = '<p class="part" data-title="' . $title . '" data-date="' . $date . '">' . htmlspecialchars($_POST["storyfield"]) . '<small> -' . htmlspecialchars($_POST["namefield"]) . '</small> </p><br>';
            fwrite($storyfile, $txt);
            $storycontents = $storycontents . $txt;
            $_SESSION['storyfill'] = 'Thank you for contributing!';
            //save hash to hash file with last contributer hash
            file_put_contents("hash.txt",GetPersonalHash());
            //log old file when this one is full
            if ($numberofparts >= 20) {
                logOldStory($storycontents);
                file_put_contents("story.txt", "");
            }
        }
    }
}

fclose($storyfile);

function logOldStory(string $story)
{
    $oldstoryfilename = "oldstories.html";
    $oldstoryfile = fopen($oldstoryfilename, "r+") or die("Unable to open file!");
    fwrite($oldstoryfile, $story . '<br>The end.<br><hr>');
    fclose($oldstoryfile);
}

function validateStory(string $story): string
{
    if (strlen($story) < 50) {
        return 'Contribution too short. Should be at least 50 characters but you have ' . strlen($story) . '.';
    } else if (strlen($story) > 400) {
        return 'Contribution too long. Should be at most 400 characters but you have ' . strlen($story) . '.';
    } else if (str_word_count($story) < 7) {
        return 'Not enough words. Needs at least 7 but you have ' . str_word_count($story) . '.';
    } else if (containsBadWords($story)) {
        return 'No profanity in this story please.';
    } else return '';
}

function validateName(string $name): string
{
    if (strlen($name) < 3) {
        return 'Name too short. Should be at least 3 characters but you have ' . strlen($name) . '.';
    } else if (strlen($name) > 50) {
        return 'Name too long. Should be at most 50 characters but you have ' . strlen($name) . '.';
    } else if (containsBadWords($name)) {
        return 'No profanity in this story please.';
    } else return '';
}

function GetPersonalHash()
{return hash("sha256",session_id());
}

function GetPreviousHash()
{return file_get_contents('hash.txt');
}

function ContributedLast() : bool
{if (GetPersonalHash() == GetPreviousHash()) {return true;}
return false;
}

?>

<body>
    <h1>Once upon a time...</h1>
    <article>
        <?php
        echo ($storycontents);
        ?>

<?php if (ContributedLast()) {echo('<i>You cannot contribute since you made the last contribution.</i>');} ?>

    </article>

    <form action="index.php" method="POST" <?php if (ContributedLast()) {return 'hidden="true"';} ?>>
        <label for="namefield" maxlength="50">Name:</label><br>
        <input name="namefield" type="text" id="name" value=<?php echo ($_SESSION['namefill']) ?>><br>
        <label for="storyfield">Part <?php echo ($numberofparts) ?>/20 of the story:</label><br>
        <textarea name="storyfield" maxlength="400" rows="5" cols="20" wrap="soft" id="storyfield" class="storyfield" value=<?php echo ($_SESSION['storyfill']) ?>> </textarea>
        <input type="submit" value="Submit">
    </form>



</body>

</html>