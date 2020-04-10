<?php
class File extends File\AbstractFile{
	/* dev номер устройства 
	1 ino номер inode * 
	2 mode режим защиты inode 
	3 nlink количество ссылок 
	4 uid userid владельца * 
	5 gid groupid владельца * 
	6 rdev тип устройства, если устройство inode 
	7 size размер в байтах 
	8 atime время последнего доступа (временная метка Unix) 
	9 mtime время последней модификации (временная метка Unix) 
	10 ctime время последнего изменения inode (временная метка Unix) 
	11 blksize размер блока ввода-вывода файловой системы ** 
	12 blocks количество используемых 512-байтных блоков ** */

	protected function init() {
		if ($this->fullname) {
			$name = explode('/', $this->fullname);
			$this
				->_set('name', array_pop($name))
				->_set('path', implode('/', $name));
		} elseif ($this->path) {
			$this->_set('fullname', $this->path . $this->name);
		} elseif ($this->tmp_name) {
			$this->_set('fullname', $this->tmp_name);
		}
		$name = explode('.', $this->name);
		$this
			->_set('extension', strtolower(array_pop($name)))
			->_set('basename', implode('.', $name));
		return $this;

	}

	public function __toString() {
		return $this->name;
	}

}