<?php
namespace File;

class Manager{

	protected $_options = array();

	protected $_parentId;

	protected $_parentType;

	public static function getByGuid($guid) {
		return new \File(\Db::from('files', '*')
			->where('guid=?', $guid)
			->query()->fetchRow());
	}

	public static function getNewGuid() {
		do {
			$guid = md5(uniqid());
		} while (\Db::from('files')->where('guid=?', $guid)->query()->fetchRow());
		return $guid;
	}

	public function __get($name) {
		return (isset($this->_options[$name]) ? $this->_options[$name] : null);
	}

	public function __construct($parentId, $parentType, $options = null) {
		$this->_parentId = $parentId;
		$this->_parentType = $parentType;
		if ($options) {
			$this->setOptions($options);
		}
	}

	public function setOptions($options) {
		if (is_array($options)) {
			foreach ($options as $k => $v) {
				$this->setOption($k, $v);
			}
		}
		return $this;
	}

	public function setOption($name, $value) {
		$this->_options[$name] = $value;
		return $this;
	}

	public function get() {
		$data = \Db::from('files', '*')
			->where('parent_id=' . $this->_parentId)
			->where('parent_type=' . $this->_parentType)
			->query()->fetchRow();
		return new File($data);
	}

	public function select() {
		$files = array();
		$filesTemp = \Db::from('files', '*')
			->where('parent_id=' . $this->_parentId)
			->where('parent_type=' . $this->_parentType)
			->query()->fetchAll();
		foreach ($filesTemp as $file) {
			$files[] = new \File($file);
		}
		unset($filesTemp);
		return $files;
	}

	public function write(\File $file) {
		if ($file->isNew()) {
			//$file
			//$guid = self::getNewName();

		}
		if (file_exists($file->destination)) {
			unlink($file->destination);
		}

		$data = $file->getData();
		if (!is_array($data)) {
			$data = array($data);
		}
		foreach ($data as $i => $s) {
			$fn = $file->destination . ($i == 0 ? '' : '_' . ($i + 1));
			$fh = fopen($fn, 'w+');
			fwrite($fh, $s);
			fclose($fh);
		}

		$data = array(
			'name' => $file->name,
			'mime_type' => $file->mime_type,
			'size' => filesize($file->destination)
		);
		if ($file->isNew()) {
			$data = array_merge($data, array(
				'author_id' => UserId,
				'parent_id' => $this->_parentId,
				'parent_type' => $this->_parentType,
				'guid' => $file->guid
			));
		}
		\Db::write('files', $data, $file->id);
	}

	public function delete() {
		foreach ($this->select() as $file) {
			$this->deleteFile($file);
		}
		return true;
	}

	protected function deleteFile($file) {
		if (file_exists($file->destination)) {
			unlink($file->destination);
		}
		\Db::delete('files', 'guid="' . $file->guid . '"');
	}

}
?>
