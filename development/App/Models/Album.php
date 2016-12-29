<?php

class App_Models_Album extends App_Models_Base
{
	public $Id;
	public $Title = '';
	public $Interpret = '';
	public $Year = '';
	public static function GetAll() {
		$rawData = self::getDb()->query("
			SELECT
				c.id AS Id,
				c.title AS Title,
				c.interpret AS Interpret,
				c.year AS Year
			FROM 
				cds AS c
		")->fetchAll(PDO::FETCH_ASSOC);
		$result = array();
		foreach ($rawData as $rawItem) {
			$item = new self;
			$item->setUp($rawItem);
			$result[$item->Id] = $item;
		}
		return $result;
	}
	public static function GetById($id) {
		$select = self::getDb()->prepare("
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
        $data = $select->fetch(PDO::FETCH_ASSOC);
		if ($data) {
			$result = new self();
			$result->setUp($data);
			return $result;
		} else {
			return NULL;
		}
    }
	public function Save () {
		if (isset($this->Id)) {
			$this->update();
		} else {
			$this->Id = $this->insert();
		}
		return $this->Id;
    }
	public function Delete () {
		$update = $this->db->prepare("
			DELETE FROM
				cds
			WHERE
				id = :id
		");
        return $update->execute(array(
            ":id"			=> $this->Id,
        ));
	}
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
	protected function insert() {
		$insertCommand = $this->db->prepare("
			INSERT INTO cds (interpret, year, title) 
			VALUES (:interpret, :year, :title)
		");
		$insertCommand->execute(array(
			":interpret"	=> $this->Interpret,
			":year"			=> $this->Year,
			":title"		=> $this->Title,
		));
		$newId = $this->db->lastInsertId();
		return $newId;
	}
	protected function setUp ($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}