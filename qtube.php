<?php
/**
 * QTube Plugin
 *
 * @version 1.0.1
 * @package qtube
 * @author Massimo Giagnoni
 * @copyright Copyright (C) 2008 Massimo Giagnoni. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPLv3
 */

defined( '_JEXEC' ) or die();

jimport( 'joomla.plugin.plugin' );


class plgContentQTube extends JPlugin {
	
	function plgContentQTube(&$subject, $params) {
		parent::__construct($subject, $params);
	}

	function onContentPrepare($context, &$row, &$articleParams, $page=0 ) {
		if (is_object($row))
			$row->text = $this->process($row->text);
		else
			$row = $this->process($row);
		
	}
	
	function process($text)
	{
		if(strpos($text, '{qtube') === false) {return;}
		
		$r = '#{qtube\s*(.*?)}#';
		$text = preg_replace_callback($r, array('plgContentQTube','callback'), $text);
		
		return $text;
	}
	
	function callback($matches) {		
		$r = '#(\w+)\s*:=\s*(?:(?:"([^"]*)")|([^\s]*))#';
		if($r = preg_match_all($r, $matches[1], $m)) {
			
			if(array_search('debug', $m[1]) !== false) {
				return '{qtube ' . str_replace(' debug:=1', '', $matches[1]) . '}';
			}
			$attrs = array('vid'=>'', 'w'=>'', 'h'=>'', 'rel'=>'', 'c1'=>'', 'c2'=>'', 'b'=>'', 'ap'=>'', 'hd'=>'', 'dc'=>'', 'cl'=>'', 'id'=>'');
			for($i=0; $i < $r; $i++) {
				$n= $m[1][$i];
				$v = $m[2][$i] ? $m[2][$i] : $m[3][$i];
				if (array_key_exists($n, $attrs)) {
					$attrs[$n] = htmlspecialchars($v);
				}
			}
			foreach($attrs as $n=>$v) {
				if($v == '') {
					switch($n) {
						case 'cl':
						$v = $this->params->get('class', '');	
						break;
						case 'id':
						$v = $this->params->get('id', '');	
						break;
						case 'w':
						$v = $this->params->get('width', '425');	
						break;
						case 'h':
						$v = $this->params->get('height', '355');
						break;
						case 'rel':
						$v = $this->params->get('related', 1);
						break;
						case 'c1':
						$v = $this->params->get('color1', '');
						break;
						case 'c2':
						$v = $this->params->get('color2', '');
						break;
						case 'b':
						$v = $this->params->get('border', 0);
						break;
						case 'ap':
						$v = $this->params->get('autoplay', 0);
						break;
						case 'hd':
						$v = $this->params->get('hd', 0);
						break;
						case 'dc':
						$v = $this->params->get('dc', 0);
						break;
					}
					$attrs[$n] = $v;
				}
			}
			foreach($attrs as $n=>$v) {
				switch($n) {
					case 'cl':
					if($v != '') { $attrs[$n]= ' class="' . $v . '"'; }
					break;
					case 'id':
					if($v != '') { $attrs[$n]= ' id="' . $v . '"'; }
					break;
					case 'w':
					$attrs[$n]= ' width="' . $v . '"';;	
					break;
					case 'h':
					$attrs[$n] = ' height="' . $v . '"';
					break;
					case 'rel':
					$attrs[$n] = "&amp;rel=$v";
					break;
					case 'c1':
					if($v != '') { $attrs[$n] = "&amp;color1=0x$v"; }
					break;
					case 'c2':
					if($v != '') { $attrs[$n] = "&amp;color2=0x$v"; }
					break;
					case 'b':
					$attrs[$n] = "&amp;border=$v";
					break;
					case 'ap':
					if($v > 0) { 
						$attrs[$n] = "&amp;autoplay=$v"; 
					} else {
						$attrs[$n] = '';
					}
					break;
					case 'hd':
					if($v > 0) { 
						$attrs[$n] = "&amp;hd=$v"; 
					} else {
						$attrs[$n] = '';
					}
					break;
					case 'dc':
						if($v > 0) {
							$attrs[$n] = 'http://www.youtube-nocookie.com/v/';
						} else {
							$attrs[$n] = 'http://www.youtube.com/v/';
						}
					break;
				}
			}
			
			if($attrs['vid'] == '') {
				$r = '{qtube error: video id missing!}';
			} else {
				$url = $attrs['dc'].$attrs['vid'].'&amp;fs=1'.$attrs['rel'].$attrs['c1'].$attrs['c2'].$attrs['b'].$attrs['hd'].$attrs['ap'];
				$r = <<<EOD
<object{$attrs['cl']}{$attrs['id']} type="application/x-shockwave-flash"{$attrs['w']}{$attrs['h']} data="$url">
<param name="movie" value="$url"></param><param name="wmode" value="transparent"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param></object>
EOD;
			}
			return $r;
			
		}
	}
}
?>
