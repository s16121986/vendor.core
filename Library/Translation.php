<?php
use Translation\Language as LangObj;

use Menu\Menu;
use Translation\File;
use Http\Util as HttpUtil;

abstract class Translation{
	
	private static $current = null;
	private static $languages = [];
	private static $dataSource = ['Xml', null];
	private static $data = null;
	private static $autoEnabled = true;
	private static $params = [];
	
	public static function setParams($params) {
		self::$params = $params;
	}
	
	public static function addLanguage($code, $params) {
		$params['code'] = $code;
		$language = new LangObj($params);
		if ($language->default) {
			foreach (self::getLanguages() as $lang) {
				$lang->default = false;
			}
		} elseif (empty(self::$languages)) {
			$language->default = true;
		}
		self::$languages[$code] = $language;
		/*if ($language->default) {
			self::setLanguage($code);
		}*/
	}
	
	public static function setLanguage($code) {
		if (self::$current === $code) {
			return;
		} elseif ($code === 'auto') {
			self::auto();
			$code = self::getCode(true);
		} elseif (!isset(self::$languages[$code])) {
			return false;
		}
		self::$current = $code;
		
		$language = self::getLanguage();
		if ($language->locale) {
			setlocale(LC_ALL, $language->locale);
			setlocale(LC_NUMERIC, 'C');
		}
		if (self::isAutoEnabled()) {
			$_SESSION['lang'] = $code;
		}
	}
	
	public static function getLanguage($code = null) {
		if (null === $code) {
			$code = self::getCode();
		}
		return (isset(self::$languages[$code]) ? self::$languages[$code] : null);
	}
	
	public static function getLanguages() {
		return self::$languages;
	}
	
	public static function getDefault() {
		foreach (self::getLanguages() as $lang) {
			if ($lang->default) {
				return $lang;
			}
		}
		return null;
	}

	public static function getLocale() {
		return self::getLanguage()->locale;
	}
	
	public static function getCode($setDefault = null) {
		if (null === self::$current) {
			if ($setDefault) {
				self::setLanguage($setDefault === true ? self::getDefault()->code : $setDefault);
			} else {
				//return self::getDefault()->code;
			}
		}
		return self::$current;
	}
	
	public static function translate($value, $path = 'item', $code = null) {
		if (null === $code) {
			$code = self::getCode();
		}
		if (null === self::$data) {
			$cls = '\Translation\Data\\' . ucfirst(self::$dataSource[0]);
			$data = new $cls(self::getLanguage($code), self::$dataSource[1]);
			self::$data = array($data);
		}
		foreach (self::$data as $di) {
			$item = $di->getContent($value, $path);
			if (null !== $item) {
				foreach (self::$params as $k => $v) {
					$item = str_replace('%' . $k . '%', $v, $item);
				}
				return $item;
			}
		}
		return $value;
	}
	
	public static function setDataSource($source, $path = null) {
		self::$dataSource = array($source, $path);
	}
	
	public static function addData(AbstractData $data) {
		if (null === self::$data) {
			self::$data = array();
		}
		self::$data = $data;
	}

	public static function getItems($code = null) {
		if (null === $code) {
			$code = self::getCode();
		}
		return Translation\Data::getItems($code);
	}
	
	public static function getMenu($options = array()) {
		$menu = new Menu(array('class' => 'language'));
		$menu->setCurrent(self::getCode());
		if (isset($options['uri']))
			$uri = $options['uri'];
		else {
			$uri = $_SERVER['REQUEST_URI'];
			if (0 === strpos($uri, '/' . self::getCode() . '/')) {
				$uri = substr($uri, 3);
			}
		}
		foreach (self::getLanguages() as $lang) {
			$menu->add(array(
				'text' => $lang->name,
				'href' => (self::getCode() === $lang->code ? null : $lang->code . $uri),
				'action' => $lang->code,
				'class' => $lang->code
			));
		}
		return $menu;
	}
	
	public static function auto() {
		if (self::isAutoEnabled()) {
			if (isset($_SESSION['lang'])) {
				return self::setLanguage($_SESSION['lang']);
			} elseif (($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
				if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
					$array = array_combine($list[1], $list[2]);
					foreach ($array as $n => $v) {
						foreach (self::$languages as $lang) {
							if ($lang->code === $n || $lang->hreflang === $n) {
								$array[$lang->code] = $v ? $v : 1;
								break;
							}
						}
					}
					arsort($array, SORT_NUMERIC);
					foreach ($array as $n => $v) {
						return self::setLanguage($n);
					}
				}
			}
		}
	}
	
	public static function setAutoEnabled($flag) {
		self::$autoEnabled = $flag;
	}
	
	public static function isAutoEnabled() {
		return (self::$autoEnabled && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && count(self::$languages) > 1 && !HttpUtil::isSearchBot());
	}
	
	public static function getFile($code) {
		if (!isset(self::$files[$code])) {
			self::$files[$code] = new File(self::getFilepath() . DIRECTORY_SEPARATOR . $code . '.xml');
		}
		return self::$files[$code];
	}

	public static function getSpellVariants($term) {
		$l = mb_strlen($term, self::$encoding);
		$str = '';
		for ($i = 0; $i < $l; $i++) {
			$chr = mb_substr($term, $i, 1, self::$encoding);
			if ($chr != ' ') {
				$pos = false;
				foreach (self::$languages as $lk => $lang) {
					$pos = mb_strpos($lang, $chr, 0, self::$encoding);
					if (false !== $pos) {
						$toLang = self::$languages[1 - $lk];
						$chr = mb_substr($toLang, $pos, 1, self::$encoding);
						break;
					}
				}
			}
			$str .= $chr;
		}
		$langVariants = array($term, $str);
		return $langVariants;
	}

	public static function getSpellVariantsTemp($term) {
		$langVariants = array();
		$l = mb_strlen($term, self::$encoding);
		foreach (self::$languages as $lk => $lang) {
			$langTemp = self::$languages;
			unset($langTemp[$lk]);

			foreach ($langTemp as $k => $nl) {
				$langVariants[$k] = array(
					'term' => '',
					'mod' => false
				);
				for ($i = 0; $i < $l; $i++) {
					$chr = mb_substr($term, $i, 1, self::$encoding);
					if ($chr == ' ') {
						$langVariants[$k]['term'] .= $chr;
						continue;
					}
					$pos = mb_strpos($lang, $chr, 0, self::$encoding);
					if ($pos === false) {
						$lc = $chr;
					} else {
						$lc = mb_substr($nl, $pos, 1, self::$encoding);
						if ($lc == ' ') {
							//$langVariants[$k]['mod'] = false;
							continue 2;
						}
						$langVariants[$k]['mod'] = true;
					}
					$langVariants[$k]['term'] .= $lc;
				}
			}
		}
		$variants = array($term);
		foreach ($langVariants as $v) {
			if ($v['mod']) {
				$variants[] = $v['term'];
			}
		}
		return $variants;
	}

}
