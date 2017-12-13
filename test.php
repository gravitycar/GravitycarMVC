<?php
require_once('lib/utils/autoloader.php');

$rel = new ManyToMany('Users_Movies');

$user = new Users();
$user->detail('99337e50eba2d159');

$rel->loadLinkedGravitons($user);

foreach($rel->linkedRecords as $graviton) {
    print("{$graviton->title}\n");
}
?>
