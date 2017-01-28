<?php

namespace App\Models;

class User extends \MvcCore\Ext\Auth\User
{
	/** @var int */
	public $Id;
	public $UserName = '';
	public $PasswordHash = '';
	public $FullName = '';
	
	public static function GetUserBySession () {
		$session = static::getSession();
		if (isset($session->uname)) {
			return self::GetByUserName($session->uname);
		}
		return NULL;
	}

	public static function Authenticate ($uniqueUserName = '', $password = '') {
		$hashedPassword = static::GetPasswordHash($password);
		$user = self::GetByUserName($uniqueUserName);
		if ($user && $user->PasswordHash === $hashedPassword) {
			return $user;
		}
		return NULL;
	}

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
		if ($data = $select->fetch(\PDO::FETCH_ASSOC)) {
			return (new self())->setUp($data);
		}
		return NULL;
    }
}