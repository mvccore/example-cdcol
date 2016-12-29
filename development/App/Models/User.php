<?php

class App_Models_User extends App_Models_Base
{
	public $Id;
	public $UserName = '';
	public $PasswordHash = '';
	public $FullName = '';
	public static function GetByUserName ($userName) {
		$select = self::getDb()->prepare("
			SELECT
				u.id AS Id,
				u.user_name AS UserName,
				u.password_hash AS PasswordHash,
				u.full_name AS FullName
			FROM
				users as u
			WHERE
				u.user_name = :user_name
		");
        $select->execute(array(
            ":user_name" => $userName,
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
	public static function HashPassword ($password) {
		//return sha1(crypt($password, 'S3F8OI2P3X6ER1F6XY2Q9ZCY' . $_SERVER['SERVER_NAME']));
		return sha1(crypt($password, 'S3F8OI2P3X6ER1F6XY2Q9ZCY'));
	}
	protected function setUp ($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}