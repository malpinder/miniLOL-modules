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

require(ADAPTERS.'/modules/Word Filter/Filters.php');

class WordFilterModule extends Module
{
    public function name ()
    {
        return 'Word Filter';
    }

    private $_filters;

    public function __construct ($miniLOL)
    {
        parent::__construct($miniLOL);

        $this->_filters = new Filters($miniLOL);
        $this->_filters->load('modules/Word Filter/resources/words.xml');

        $miniLOL->events->observe(':go', array($this, 'event'));
    }

    public function event ($event)
    {
        $this->miniLOL->set('content', $this->execute($this->miniLOL->get('content')));
    }

    public function execute ($content)
    {
        $html = str_get_html($content);

        foreach ($html->find('text') as $text) {
            $text->innertext = $this->_filters->apply($text);
        }

        return $html->save();
    }
}

?>