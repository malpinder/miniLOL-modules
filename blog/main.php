<?php
/*********************************************************************
 *           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE             *
 *                   Version 2, December 2004                        *
 *                                                                   *
 *  Copyleft meh.                                                    *
 *                                                                   *
 *           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE             *
 *  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION  *
 *                                                                   *
 *  0. You just DO WHAT THE FUCK YOU WANT TO.                        *
 *********************************************************************/

define('__VERSION__', '0.2');

session_set_cookie_params(60*60*24*365, '/');
session_start();

if (isset($_REQUEST['retrieve'])) {
    exit;
}

if (!$_SESSION['miniLOL']['admin']) {
    echo "You're doing it wrong.";
    exit;
}

$data = DOMDocument::load('resources/data.xml');

if (isset($_REQUEST['post'])) {
    $id = $data->documentElement->getAttribute('total') + 1;
    
    $post = $data->createElement('post');
    $post->setAttribute('id', $id);
    $post->setAttribute('title', htmlentities(urldecode($_REQUEST['title'])));
    $post->setAttribute('author', htmlentities(urldecode($_REQUEST['author'])));
    $post->setAttribute('date', htmlentities(urldecode($_REQUEST['date'])));

    $data->documentElement->setAttribute('total', $id);

    $content = $data->createCDataSection(str_replace(']]>', ']&#93;>', urldecode($_REQUEST['content'])));
    $post->appendChild($content);
    $data->documentElement->appendChild($post);

    echo "The post has been added.";
    $data->save('resources/data.xml');
    exit;
}

if (!isset($_REQUEST['id'])) {
    echo "You're doing it wrong.";
    exit;
}

if (isset($_REQUEST['edit'])) {

}
else if (isset($_REQUEST['delete'])) {

}

?>
