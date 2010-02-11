<?php
/****************************************************************************
 * Copyleft meh. [http://meh.doesntexist.org | meh.ffff@gmail.com]          *
 *                                                                          *
 * This file is part of miniLOL. A blog module.                             *
 *                                                                          *
 * miniLOL is free software: you can redistribute it and/or modify          *
 * it under the terms of the GNU Affero General Public License as           *
 * published by the Free Software Foundation, either version 3 of the       *
 * License, or (at your option) any later version.                          *
 *                                                                          *
 * miniLOL is distributed in the hope that it will be useful,               *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of           *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            *
 * GNU Affero General Public License for more details.                      *
 *                                                                          *
 * You should have received a copy of the GNU Affero General Public License *
 * along with miniLOL.  If not, see <http://www.gnu.org/licenses/>.         *
 ****************************************************************************/

define('__VERSION__', '0.3');

session_set_cookie_params(60*60*24*365, '/');
session_start();

require_once("{$_SESSION['miniLOL']['path']['modules']}/Security/utils.php");

if (@!$_SESSION['miniLOL']['admin']) {
    echo "You're doing it wrong.";
    exit;
}

security_waitUnlock();
security_lock();

if (isset($_REQUEST['feed'])) {
    $feed = security_getRequest('feed');

    file_put_contents("{$_SESSION['miniLOL']['path']['root']}/{$feed}", security_getRequest('content'));

    security_unlock();
    exit;
}

$config = simplexml_load_file('resources/config.xml');

$data = DOMDocument::load('resources/data.xml');
$data->preserveWhiteSpace = false;
$data->formatOutput       = true;


if (isset($_REQUEST['comment'])) {
    $post = $data->getElementById($_REQUEST['parent']);

    if (!$post) {
        echo "The post doesn't exist.";
        security_unlock();
        exit;
    }

    if (isset($_REQUEST['post'])) {
        security_unlock();
        exit;
    }

    if (!isset($_REQUEST['id'])) {
        echo "You're doing it wrong.";
        security_unlock();
        exit;
    }
}
else {
    if (isset($_REQUEST['post'])) {
        $id = $data->documentElement->getAttribute('total') + 1;
    
        $post = $data->createElement('post');
        $post->setAttribute('id', $id);
        $post->setAttribute('title', htmlentities(urldecode(security_getRequest('title')), ENT_QUOTES, 'UTF-8'));
        $post->setAttribute('author', htmlentities(urldecode(security_getRequest('author')), ENT_QUOTES, 'UTF-8'));
        $post->setAttribute('date', htmlentities(urldecode(security_getRequest('date')), ENT_QUOTES, 'UTF-8'));

        $data->documentElement->setAttribute('total', $id);

        $content = $data->createCDataSection(str_replace(']]>', ']&#93;>', urldecode(security_getRequest('content'))));
        $post->appendChild($content);
        $data->documentElement->appendChild($post);

        $data->save('resources/data.xml');

        echo 'The post has been added.';

        security_unlock();
        exit;
    }

    if (!isset($_REQUEST['id'])) {
        echo "You're doing it wrong.";
        security_unlock();
        exit;
    }

    $post = $data->getElementById($_REQUEST['id']);

    if (!$post) {
        echo "The post doesn't exist.";
        security_unlock();
        exit;
    }

    if (isset($_REQUEST['edit'])) {
        $post->setAttribute('title', htmlentities(urldecode(security_getRequest('title')), ENT_QUOTES, 'UTF-8'));
        $post->setAttribute('author', htmlentities(urldecode(security_getRequest('author')), ENT_QUOTES, 'UTF-8'));
        $post->setAttribute('date', htmlentities(urldecode(security_getRequest('date')), ENT_QUOTES, 'UTF-8'));

        $post->removeChild($post->firstChild);
        $content = $data->createCDataSection(str_replace(']]>', ']&#93;>', urldecode(security_getRequest('content'))));
        $post->appendChild($content);

        $data->save('resources/data.xml');

        echo 'The post has been modified.';
    }
    else if (isset($_REQUEST['remove'])) {
        $data->documentElement->removeChild($post);
        $data->save('resources/data.xml');
    
        echo 'The post has been removed.';
    }
}

security_unlock();
    
?>
