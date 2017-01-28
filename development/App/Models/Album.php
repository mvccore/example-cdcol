<?php

namespace App\Models;

class Album extends \MvcCore\Model
{
	/** @var int */
	public $Id;
	/** @var string */
	public $Title;
	/** @var string */
	public $Interpret;
	/** @var int */
	public $Year;

	/**
	 * Get all albums in database as array, keyed by $album->Id.
	 * @return \MvcCore\Model[]
	 */
	public static function GetAll () {
		$rawData = self::GetDb()->query("
			SELECT
				c.id AS Id,
				c.title AS Title,
				c.interpret AS Interpret,
				c.year AS Year
			FROM 
				cds AS c
		")->fetchAll(\PDO::FETCH_ASSOC);
		$result = array();
		foreach ($rawData as $rawItem) {
			$item = (new self)->SetUp($rawItem);
			$result[$item->Id] = $item;
		}
		return $result;
	}

	/**
	 * Get single album instance by given id or null if no record by id in database.
	 * @param int $id
	 * @return \MvcCore\Model|null
	 */
	public static function GetById ($id) {
		$select = self::GetDb()->prepare("
			SELECT
				c.id AS Id,
				c.title AS Title,
				c.interpret AS Interpret,
				c.year AS Year
			FROM 
				cds as c 
			WHERE
				c.id = :id
		");
        $select->execute(array(
            ":id" => $id,
        ));
        $data = $select->fetch(\PDO::FETCH_ASSOC);
		if ($data) {
			return (new self)->SetUp($data);
		}
		return NULL;
    }

	/**
	 * Delete database row by album Id. Return affected rows count.
	 * @return bool
	 */
	public function Delete () {
		$update = $this->db->prepare("
			DELETE FROM
				cds
			WHERE
				id = :id
		");
        return $update->execute(array(
            ":id"	=> $this->Id,
        ));
	}
	/**
	 * Update album with completed Id or insert new one if no Id defined.
	 * Return Id as result.
	 * @return int
	 */
	public function Save () {
		if (isset($this->Id)) {
			$this->update();
		} else {
			$this->Id = $this->insert();
		}
		return $this->Id;
    }

	/**
	 * Update all public defined properties.
	 * @return bool
	 */
	protected function update () {
		$update = $this->db->prepare("
			UPDATE
				cds
			SET
				interpret = :interpret,
				year = :year,
				title = :title
			WHERE
				id = :id
		");
        return $update->execute(array(
			":interpret"	=> $this->Interpret,
            ":year"			=> $this->Year,
            ":title"		=> $this->Title,
            ":id"			=> $this->Id,
        ));
	}

	/**
	 * Insert only filled values, return new album id.
	 * @return int
	 */
	protected function insert() {
		$columnsSql = array();
		$params = array();
		$newValues = $this->GetValues();
		foreach ($newValues as $key => & $value) {
			$keyUnderscored = \MvcCore\Tool::GetUnderscoredFromPascalCase($key);
			$columnsSql[] = $keyUnderscored;
			$params[$keyUnderscored] = $value;
		}
		$insertCommand = $this->db->prepare(
			'INSERT INTO cds (' . implode(',', $columnsSql) . ')
			 VALUES (:' . implode(', :', $columnsSql) . ')'
		);
		$insertCommand->execute($params);
		return (int) $this->db->lastInsertId();
	}
}