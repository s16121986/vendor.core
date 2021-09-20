<?php

namespace Api;

use Api\File\Util;
use Db;
use File as BaseFile;
use Api\File\Part as FilePart;
use Exception;

class File extends BaseFile {

	private static $tableParts = 's_files_parts';

	protected $parts = null;

	public static function setTableParts($table) {
		self::$tableParts = $table;
	}

	private function setGuid($guid) {
		return $this
			->set('guid', $guid)
			->set('fullname', Util::getDestination($guid, true))
			->set('path', Util::getPath($guid, true));
	}

	private function find($param, $value) {
		$data = Db::from(Util::$table)
			->where($param . '=?', $value)
			->limit(1)
			->query()
			->fetchRow();

		if (!$data)
			return false;

		$this->data = $data;
		$this->setGuid($data['guid']);

		return true;
	}

	public function findById($id) {
		return $this->find('id', $id);
	}

	public function findByGuid($guid) {
		return $this->find('guid', $guid);
	}

	public function setDestination($destination) {
		if ($this->guid)
			return $this;

		return parent::setDestination($destination);
	}

	public function setParent($id, $type = null) {
		return $this
			->set('parent_id', $id)
			->set('type', $type);
	}

	public function isNew() {
		return (null === $this->id);
	}

	public function write() {
		if ($this->isEmpty())
			return false;

		//if (!$this->guid && null === $this->content && $this->exists())
		//	$this->setContent($this->getContents());

		if (!$this->hasContent())
			throw new Exception('File content empty');

		$isNew = $this->isNew();

		//$guid = $isNew ? Util::getNewGuid() : $this->guid;

		$data = [];

		if ($isNew) {
			$guid = Util::getNewGuid();
			$data['guid'] = $guid;
			$data['path'] = Util::getPath($guid, false);
			$data['fullname'] = Util::getDestination($guid, false);
			$data['created'] = 'CURRENT_TIMESTAMP';

			if ($this->parent_id)
				$data['index'] = Db::from(Util::config('table'), 'MAX(`index`)')
						->where('parent_id=' . $this->parent_id)
						->where('type=?', $this->type)
						->query()->fetchColumn() + 1;
		} else {
			$guid = $this->guid;
			$data['index'] = $this->index ?: 0;
		}

		foreach (['parent_id', 'type', 'name', 'extension', 'mime_type', 'size', 'mtime'] as $key) {
			$data[$key] = $this->$key;
		}

		$id = Db::write(Util::$table, $data, $this->id);
		if (!$id)
			return false;

		$content = $this->getContents(); //store content before change fullname

		$this->set('id', $id);

		//if ($isNew)
		$this->setGuid($guid);

		Util::checkPath($guid);

		Util::fwrite($this, $content);

		if ($this->parts) {
			foreach ($this->parts as $part) {
				Util::fwrite($part);

				Db::insert(self::$tableParts, [
					'file_id' => $id,
					//'name' => $part->name,
					//'fullname' => $part->fullname,
					'size' => $part->size,
					'mtime' => $part->mtime,
					'index' => $part->index
				]);
			}
		}

		return $id;
	}

	public function delete() {
		if ($this->isNew())
			return false;

		if ($this->exists())
			$this->unlink();

		if ($this->getParts()) {
			foreach ($this->parts as $part) {
				if ($part->exists())
					$part->unlink();
			}
			Db::delete(Util::$tableParts, 'file_id=' . $this->id);
		}

		return Db::delete(Util::$table, 'id=' . $this->id);
	}

	public function reset() {
		$this->parts = null;
		return parent::reset();
	}

	public function getParts() {
		if (!$this->id)
			return [];

		else if (null !== $this->parts)
			return $this->parts;

		$this->parts = [];

		$q = Db::from(self::$tableParts, ['index'])
			->where('file_id=?', $this->id)
			->query();
		while ($r = $q->fetch()) {
			$part = new FilePart($this, $r);
			$this->parts[$r['index']] = $part;
		}

		return $this->parts;
	}

	public function addPart($content) {
		if (null === $this->parts)
			$this->parts = [];

		$part = new FilePart($this, ['index' => count($this->parts) + 1]);
		$part->setContent($content);
		$this->parts[] = $part;

		return $this;
	}

	public function getPart($index) {
		$this->getParts();
		return (isset($this->parts[$index]) ? $this->parts[$index] : null);
	}

	public function __toString() {
		return $this->guid;
	}

}
