<?php
/****************************************************************************
 * Copyleft meh. [http://meh.doesntexist.org | meh.ffff@gmail.com]          *
 *                                                                          *
 * This file is part of miniLOL. A PHP implementation.                      *
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

class Theme
{
    public $miniLOL;

    private $_name;
    private $_path;
    private $_info;
    private $_styles;
    private $_template;
    
    public function __construct ($miniLOL)
    {
        $this->miniLOL = $miniLOL;
    }

    public function load ($name)
    {
        $path = ROOT."/themes/{$name}";

        $this->_name   = $name;
        $this->_path   = realpath($path);
        $this->_info   = array();
        $this->_styles = array();

        $xml   = DOMDocument::load($this->path().'/theme.xml');
        $xpath = new DOMXpath($xml);

        foreach ($xml->documentElement->attributes as $attribute) {
            $this->_info[$attribute->name] = $attribute->value;
        }

        foreach ($xpath->query('/theme/styles/style') as $style) {
            array_push($this->_styles, $style->getAttribute('name'));
        }

        $this->_html = file_get_contents("{$this->_path}/template.html");

        $this->_template = array(
            'list' => array(
                'default' => array(
                    'global' => '<div #{attributes}>#{data}</div>',

                    'before' => '#{data}',
                    'after'  => '#{data}',

                    'link' => '<div class="#{class}" id="#{id}">#{before}<a href="#{href}" target="#{target}" #{attributes}>#{text}</a>#{after}</div>',
                    'item' => '<div class="#{class}" id="#{id}">#{before}<span #{attributes}>#{text}</span>#{after}</div>',
                    'nest' => '<div class="#{class}" style="#{style}">#{data}</div>',
                    'data' => '<div class="data">#{before}#{data}#{after}</div>'
                ),

                'table' => array(
                    'global' => '<table #{attributes}>#{data}</table>',

                    'before' => '#{data}',
                    'after'  => '#{data}',

                    'link' => '<tr><td>#{before}</td><td><a href="#{href}" target="#{target}" #{attributes}>#{text}</a></td><td>#{after}</td></tr>',
                    'item' => '<tr><td>#{before}</td><td>#{text}</td><td>#{after}</td></tr>',
                    'nest' => '<div class="#{class}" style="#{style}">#{data}</div>',
                    'data' => '<div class="data">#{before}#{data}#{after}</div>'
                )
            )
        );

        // Reading and parsing additional list templates
        foreach ($xpath->query('/theme/templates/*') as $template) {
            $this->_template[$template->nodeName] =& XMLToArray($template);
        }
    }

    public function name ()
    {
        return $this->_name;
    }

    public function path ($relative=false)
    {
        return ($relative) ? "themes/{$this->name()}" : $this->_path;
    }

    public function info ()
    {
        return $this->_info;
    }

    public function styles ()
    {
        return $this->_styles;
    }

    public function html ()
    {
        return $this->_html;
    }

    public function template ($what, $name)
    {
        return $this->_template[$what][$name];
    }

    public function menus ($menu, $layer=0)
    {
        $template = '';
    }

    public function _menus_layer ($template, $layer)
    {

    }

    public function pages ($page, $data=null)
    {
        $output = '';
        
        foreach ($page->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                try { $output .= $this->{"_pages_{$node->nodeName}"}($node, $data); } catch (Exception $e) { }
                break;

                case XML_CDATA_SECTION_NODE:
                case XML_TEXT_NODE:
                $output .= $node->nodeValue;
                break;
            }
        }

        return $output;
    }

    private function _pages_list ($element, $data)
    {
        $list = $element->cloneNode(false);
        $data = (is_array($data)) ? $data : array($element);

        if (!($listBefore = $list->getAttribute('before'))) {
            if (!($listBefore = $data[0]->getAttribute('before'))) {
                $listBefore = '';
            }
        } $list->removeAttribute('before');

        if (!($listAfter = $list->getAttribute('after'))) {
            if (!($listAfter = $data[0]->getAttribute('after'))) {
                $listAfter = '';
            }
        } $list->removeAttribute('after');

        if (!($listArgs = $list->getAttribute('arguments'))) {
            if (!($listArgs = $data[0]->getAttribute('arguments'))) {
                $listArgs = '';
            }
        } $list->removeAttribute('arguments');

        if (!($listType = $list->getAttribute('type'))) {
            if (!($listType = $data[0]->getAttribute('type'))) {
                $listType = '';
            }
        } $list->removeAttribute('type');

        if (!($listMenu = $list->getAttribute('menu'))) {
            if (!($listMenu = $data[0]->getAttribute('menu'))) {
                $listMenu = '';
            }
        } $list->removeAttribute('menu');

        if (!($listTemplate = $list->getAttribute('template'))) {
            if (!($listTemplate = $data[0]->getAttribute('template'))) {
                $listTemplate = '';
            }
        } $list->removeAttribute('template');

        if (!$this->template('list', $listTemplate)) {
            $listTemplate = 'default';
        }

        $output = '';

        foreach ($element->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                if ($node->nodeName == 'link') {
                    $link = $node->cloneNode(true);

                    if (!($href = $link->getAttribute('href'))) {
                        $href = '';
                    } $link->removeAttribute('href');

                    if (!($target = $link->getAttribute('target'))) {
                        $target = '';
                    } $link->removeAttribute('target');

                    if (!($text = $link->firstChild->nodeValue)) {
                        $text = $href;
                    }

                    if (!($before = $link->getAttribute('before'))) {
                        if (!($before = $listBefore)) {
                            $before = '';
                        }
                    } $link->removeAttribute('before');

                    if (!($after = $link->getAttribute('after'))) {
                        if (!($after = $listAfter)) {
                            $after = '';
                        }
                    } $link->removeAttribute('after');

                    if (!($domain = $link->getAttribute('domain'))) {
                        $domain = '';
                    } $link->removeAttribute('domain');

                    if (!($args = $link->getAttribute('arguments'))) {
                        $args = '';
                    } $link->removeAttribute('arguments');

                    if (!($menu = $link->getAttribute('menu'))) {
                        $menu = '';
                    } $link->removeAttribute('menu');

                    if (!($title = $link->getAttribute('title'))) {
                        $title = '';
                    } $link->removeAttribute('title');

                    $out = isURL($href);

                    if (!($linkClass = $link->getAttribute('class'))) {
                        $linkClass = '';
                    } $link->removeAttribute('class');

                    if (!($linkId = $link->getAttribute('id'))) {
                        $linkId = '';
                    } $link->removeAttribute('id');

                    if ($target || $out) {
                        $href = (!$out) ? "data/{$href}" : $href;

                        if (!$target) {
                            $target = '_blank';
                        }
                    }
                    else {
                        if (!($ltype = $link->getAttribute('type'))) {
                            if (!($ltype = $listType)) {
                                $ltype = '';
                            }
                        } $link->removeAttribute('type');

                        if ($domain == 'in' || $href[0] == '#') {
                            if ($href[0] != '#') {
                                $href = "#{$href}";
                            }
                        }
                        else {
                            $href = "#page={$href}";
                        }

                        $href[0] = '?';

                        if (!empty($args)) {
                            $args = preg_replace('/[ ,]+/', '&amp;', $args);
                        }

                        if ($ltype) {
                            $ltype = "&type={$ltype}";
                        }

                        if ($this->miniLOL->resources->get('miniLOL.menus')->enabled() && $menu) {
                            $menu = "&amp;menu={$menu}";
                        }

                        $target = '';

                        if ($title) {
                            $title = interpolate($title, array(
                                'text' => $text,
                                'href' => $href
                            ));

                            $title = "&title={urlencode($title)}";
                        }

                        $href = "{$href}{$args}{$ltype}{$menu}{$title}";
                    }

                    $output .= interpolate($this->_template['list'][$listTemplate]['link'], array_merge(ObjectFromAttributes($link->attributes), array(
                        'class'      => $linkClass,
                        'id'         => $linkId,
                        'attributes' => StringFromAttributes($link->attributes),
                        'before'     => interpolate($this->_template['list'][$listTemplate]['before'], array('data' => $before)),
                        'after'      => interpolate($this->_template['list'][$listTemplate]['after'], array('data' => $after)),
                        'href'       => $href,
                        'target'     => $target,
                        'text'       => $text,
                        'title'      => $title
                    )));
                }
                else if ($node->nodeName == 'item') {
                    $item = $node->cloneNode(true);

                    if (!($text = $item->firstChild->nodeValue)) {
                        $text = '';
                    }

                    if (!($before = $item->getAttribute('before'))) {
                        if (!($before = $listBefore)) {
                            $before = '';
                        }
                    } $item->removeAttribute('before');

                    if (!($after = $item->getAttribute('after'))) {
                        if (!($after = $listAfter)) {
                            $after = '';
                        }
                    } $item->removeAttribute('after');

                    if (!($itemClass = $item->getAttribute('class'))) {
                        $itemClass = '';
                    } $item->removeAttribute('class');

                    if (!($itemId = $item->getAttribute('id'))) {
                        $itemId = '';
                    } $item->removeAttribute('id');

                    $output .= interpolate($this->_template['list'][$listTemplate]['item'], array_merge(ObjectFromAttributes($item->attributes), array(
                        'class'      => $itemClass,
                        'id'         => $itemId,
                        'attributes' => StringFromAttributes($item->attributes),
                        'before'     => interpolate($this->_template['list'][$listTemplate]['before'], array('data' => $before)),
                        'after'      => interpolate($this->_template['list'][$listTemplate]['after'], array('data' => $after)),
                        'text'       => $text
                    )));
                }
                else if ($node->nodeName == 'list') {
                    $output .= _pages_list($node, array($element));
                }
                else if ($node->nodeName == 'nest') {
                    $toParse = $node->cloneNode(true);

                    if (!($before = $node->getAttribute('before'))) {
                        if (!($before = $listBefore)) {
                            $before = '';
                        }
                    }

                    if (!($after = $node->getAttribute('after'))) {
                        if (!($after = $listAfter)) {
                            $after = '';
                        }
                    }

                    $output .= interpolate($this->_template['list'][$listTemplate]['nest'], array(
                        'class'  => $node->getAttribute('class'),
                        'style'  => $node->getAttribute('style'),
                        'before' => interpolate($this->_template['list'][$listTemplate]['before'], array('data' => $before)),
                        'after'  => interpolate($this->_template['list'][$listTemplate]['after'], array('data' => $after)),
                        'data'   => $this->pages($toParse, array($element))
                    ));
                }
            }
            else if ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE) {
                if (!preg_replace('/[\s\n]+/', '', $node->nodeValue)) {
                    continue;
                }

                $output .= interpolate($this->_template['list'][$listTemplate]['data'], array(
                    'data' => $node->nodeValue
                ));
            }
        }

        return interpolate($this->_template['list'][$listTemplate]['global'], array_merge(ObjectFromAttributes($list->attributes), array(
            'attributes' => StringFromAttributes($list->attributes),
            'data'       => $output
        )));
    }

    private function _pages_include ($element, $data)
    {
        $output = '';

        return $output;
    }

    public function output ($content, $menu)
    {
        $template = $this->html();
        $template = preg_replace("#(id=['\"]{$this->_info['content']}['\"][^>]*>)#", '${1}'.$content, $template);
        $template = preg_replace("#(id=['\"]{$this->_info['menu']}['\"][^>]*>)#", '${1}'.$menu, $template);

        return $template;
    }
}

?>
