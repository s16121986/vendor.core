<?php

namespace Api\Attribute;

use File as BaseFile;
use Api\File as ApiFile;
use Api\File\Util as FileUtil;
use Db;

class AttributeFile extends AbstractAttribute {

	protected $plugins = [];
	protected $qualifiers = [
		'notnull' => false,
		'model' => null,
		'type' => null,
		'fieldName' => false,
		'fieldPreview' => true,
		'multiple' => false
	];

	public function __construct($name, $qualifiers = null) {
		if (isset($qualifiers['mimeType'])) {
			$this->addValidator('File\MimeType', ['mimeType' => $qualifiers['mimeType']]);
			unset($qualifiers['mimeType']);
		}
		parent::__construct($name, $qualifiers);
	}

	protected function fileFactory($data) {
		if (is_string($data)) {
			$file = new ApiFile();
			if (!$file->findByGuid($data))
				return null;
			return $file;
		} else if ($data instanceof BaseFile) {
			if (!$data->hasContent())
				return null;

			return new ApiFile($data->getData());
		} else
			return null;
	}

	protected function hasModel() {
		return $this->model && !$this->model->isEmpty();
	}

	public function addPlugin($plugin, $options = null) {
		if (is_string($plugin)) {
			$cls = 'Api\\Attribute\\File\\Plugin\\' . $plugin;
			$plugin = new $cls($options);
		}
		$key = str_replace('Api\\Attribute\\File\\Plugin\\', '', get_class($plugin));
		$this->plugins[$key] = $plugin;
		return $this;
	}

	protected function initPlugins($file) {
		foreach ($this->plugins as $plugin) {
			$plugin->setFile($file);
			$plugin->init();
		}
	}

	public function getPlugin($key) {
		return $this->plugins[$key];
	}

	public function checkValue($value) {
		if ($this->multiple)
			return is_array($value);

		if (($file = $this->fileFactory($value)))
			return parent::checkValue($file);

		return false; //(is_string($value) || $value instanceof \File);
	}

	public function prepareValue($value) {
		if (!$this->multiple)
			return $this->fileFactory($value);

		$valueTemp = $value;
		$value = [];
		if (is_string($valueTemp))
			$valueTemp = [$valueTemp];
		else if (!is_array($valueTemp))
			return [];

		foreach ($valueTemp as $data) {
			if (($file = $this->fileFactory($data))) {
				$value[] = $file;
			}
		}

		return $value;
	}

	public function write() {
		$value = $this->getValue();

		if (!$value || !$this->hasModel())
			return false;

		if (!is_array($value))
			$value = [$value];

		$files = [];
		foreach ($value as $file) {
			if ($file->guid)
				continue;
			$files[] = $file;
		}

		if (empty($files))
			return true;

		if (!$this->multiple) {
			foreach ($this->select() as $file) {
				$file->setName($files[0]->name);
				$file->setContent($files[0]->getContents());
				$this->initPlugins($file);
				$file->write();
				$this->value = $file;
				return true;
			}
		}

		foreach ($files as $file) {
			$this->initPlugins($file);
			$file->setParent($this->model->id, $this->type);
			$file->write();
		}

		return true;
	}

	public function select() {
		if (!$this->hasModel())
			return [];

		$files = [];

		$q = Db::from(FileUtil::config('table'))
			->order('index')
			->where('parent_id=' . $this->model->id . ' AND type=' . $this->type)
			->query();

		while ($r = $q->fetch()) {
			$files[] = new ApiFile($r);
		}

		return $files;
	}

	public function delete() {
		foreach ($this->select() as $file) {
			$file->delete();
		}

		return true;
	}

}
