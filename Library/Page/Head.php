<?php
namespace Page;

class Head{
	
	protected $relativePath = '/';
	protected $headAttr = '';
	protected $data = array();
	protected $baseHref;
	protected $meta = array();
	protected $links = array();
	protected $scripts = array();
	protected $contents = array();
	
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}
	
	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function setHeadAttr($attr) {
		$this->headAttr = $attr;
		return $this;
	}
	
	public function setRelativePath($path) {
		$this->relativePath= $path;
		return $this;
	}
	
	public function setOptions(array $options) {
		foreach ($options as $name => $option) {
			$this->addOption($name, $option->value, $option->attributes);
		}
		return $this;
	}
	
	public function addOption($option, $value, $attributes = array()) {
		switch (true) {
			case $option == \PAGE_HEAD_META_NAME::TITLE:
			case $option == \PAGE_HEAD_META_NAME::TITLE_PREFIX:
				$this->$option = $value;
				break;
			case \PAGE_HEAD_META_NAME_OG::valueExists($option):
				$this->addMetaProperty($option, $value, $attributes);
				break;
			case \PAGE_HEAD_LINK_REL::valueExists($option):
				$this->addLinkRel($option, $value, $attributes);
				break;
			case \PAGE_HEAD_META_HTTP_ENQUIV::valueExists($option):
				$this->addMetaHttpEquiv($option, $value);
				break;
			//case \PAGE_HEAD_META_NAME::valueExists($option):
			//case \PAGE_HEAD_LINK_REL::valueExists($option):
			default:
				$this->addMetaName($option, $value, $attributes);
				break;
		}
		return $this;
	}
	
	public function addLink($attributes) {
		$this->links[] = $attributes;
		return $this;
	}
	
	public function addLinkRel($name, $href, $attributes = array()) {
		$attributes['rel'] = $name;
		$attributes['href'] = $href;
		return $this->addLink($attributes);
	}
	
	public function addStyle($href, $attributes = array()) {
		$attributes = array_merge(array(
			'rel' => 'stylesheet',
			'type' => 'text/css'
		), $attributes);
		$attributes['href'] = $this->_url($href, 'css');
		return $this->addLink($attributes);
	}
	
	public function addScript($src, $attributes = array()) {
		$attributes = array_merge(array(
			'type' => 'text/javascript',
			'async' => false
		), $attributes);
		$attributes['src'] = $this->_url($src, 'js');
		$this->scripts[] = $attributes;
		return $this;
	}
	
	public function addMetaName($name, $content, $attributes = array()) {
		$attributes['type'] = 'name';
		$attributes['value'] = $name;
		$attributes['content'] = $content;
		$this->meta['name_' . $name] = $attributes;
		return $this;
	}
	
	public function addMetaProperty($name, $content, $attributes = array()) {
		$attributes['type'] = 'property';
		$attributes['value'] = $name;
		$attributes['content'] = $content;
		$this->meta['property_' . $name] = $attributes;
		return $this;
	}
	
	public function addMetaHttpEquiv($keyValue, $content, $conditionalHttpEquiv = null) {
		$attributes = array(
			'type' => 'http-equiv',
			'value' => $keyValue,
			'content' => $content
		);
		$this->meta['httpenquiv_' . $keyValue] = $attributes;
		return $this;
	}
	
	public function addContent($content) {
		$this->contents[] = $content;
		return $this;
	}
	
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	public function setBaseHref($href) {
		$this->baseHref = $href;
		return $this;
	}
	
	public function getMetaContent($name) {
		switch ($name) {
			case 'title':return $this->title;
		}
		foreach ($this->meta as $meta) {
			if ($meta['value'] === $name) {
				return $meta['content'];
			}
		}
		return '';
	}
	
	public function render($options = array()) {
		$s = '<head' . ($this->headAttr ? ' ' . $this->headAttr : '') . '>'
			. '<title>' . $this->title . $this->title_prefix . '</title>';
		$assoc = array(
			'og:title' => 'title',
			'og:description' => 'description',
			'twitter:title' => 'title',
			'twitter:description' => 'description',
			'twitter:url' => 'og:url',
			'twitter:image' => 'og:image'			
		);
		foreach ($this->meta as $meta) {
			if (!$meta['content'] && isset($assoc[$meta['value']])) {
				$meta['content'] = $this->getMetaContent($assoc[$meta['value']]);
			}
			if ($meta['content']) {
				$meta = array_merge(array($meta['type'] => $meta['value']), $meta);
				unset($meta['type'], $meta['value']);
				$s .= self::_tag('meta', $meta);
			}
		}
		foreach ($this->links as $style) {
			if (isset($options['nostyles']) && $style['rel'] === 'stylesheet') continue;
			$s .= self::_tag('link', $style);
		}
		if (isset($options['noscript'])) {
			$s .= '<script>(function(){window.$q=[];window.$=function(){return {ready:function(fn){window.$q.push(fn);}};};})();</script>';
		} else {
			$s .= $this->renderScripts();
		}
		if ($this->baseHref) {
			$s .= self::_tag('base', array('href' => $this->baseHref));
		}
		$s .= implode('', $this->contents);
		$s .= '</head>';
		return $s;
	}
	
	public function renderScripts($noscript = false) {
		$s = '';
		foreach ($this->scripts as $script) {
			$s .= self::_tag('script', $script, true);
		}
		if ($noscript) {
			$s .= '<script>$(document).ready(function(){for (var i=0,l=$q.length;i<l;i++){$q[i]();}delete window.$q;});</script>';
		}
		return $s;
	}
	
	public function renderStyles() {
		$s = '';
		foreach ($this->links as $style) {
			if ($style['rel'] === 'stylesheet') {
				$s .= self::_tag('link', $style);
			}
		}
		return $s;
	}
	
	private function _url($url, $path = null) {
		if (0 !== strpos($url, '/') && 0 !== strpos($url, 'http')) {
			$url = $this->relativePath . ($path ? $path . '/' : '') . $url;
		}
		return $url;
	}
	
	private static function _tag($tag, $attributes, $close = false) {
		$s = '<' . $tag;
		foreach ($attributes as $k => $v) {
			if (is_bool($v)) {
				if ($v) $s .= ' ' . $k;
			} elseif ($v) {
				$s .= ' ' . $k . '="' . $v . '"';
			}
		}
		$s .= ($close ? '></' . $tag . '>' : ' />');
		return $s;
	}
	
}