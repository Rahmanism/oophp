<?php
//include('Files.php');
//$instance = new Files();
//$instance->DoSomething();
//Files::DoSomething();
//echo '<pre>'; print_r(Files::CheckImage('Hemmat1.jpg')); echo '</pre>';
//echo '<br>' . Files::GetNumChecked() . '<br>';
//echo '<pre>'; print_r(Files::CheckImage('Files.php')); echo '</pre>';
//echo '<br>' . Files::GetNumChecked() . '<br>';
//
//$instance = new Files();
//echo '<br>' . $instance->GetNumChecked() . '<br>';
//require_once('ParentClass.php');
//require_once('ChildClass.php');
//$parentObject = new ParentClass();
//$result = $parentObject->DoSomething();
//echo $result;
//$childObject = new ChildClass();
//$r = $childObject->DoSomething();
//echo '$r: ' . $r . '<hr>';
//include('MySqlDb_old.php');
//$conn = Connect();
//$rows = get($conn);

include('MySqlDb.php');
$db = new MySqlDb('localhost', 'root', '110110', 'test');
$rows = $db->Query('select * from posts');

//$db->Where('id', 3);
$rows = $db->Get('posts', 2);
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>OOPHP</title>
    </head>
    <body>
        <?php
        foreach ($rows as $row) {
            echo '<h2>' . $row['title'] . '</h2>';
            echo '<p>' . $row['body'] . '</p>';
        }

//                echo '<pre>'; print_r($rows); echo '</pre>';
        ?>
    </body>
</html>
