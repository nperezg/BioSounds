<?php

namespace BioSounds\Entity;

use BioSounds\Provider\BaseProvider;

class User extends BaseProvider
{
	const TABLE_NAME = "user";
	const ID = "user_id";
	const NAME = "username";
	const FULL_NAME = "name";
	const EMAIL = "email";
	const ROLE = "role_id";
	const ACTIVE = "active";
	const PASSWORD = "password";
	const TAG_COLOR = "color";

    /**
     * @param string $username
     * @return string|null
     * @throws \Exception
     */
	public function getPassword(string $username): ?string
    {
		if (empty($username)) {
            return null;
        }
			
		$this->database->prepareQuery('SELECT password FROM user WHERE username = :username');
		if (empty($result = $this->database->executeSelect([":username" => $username]))) {
		    return null;
        }

		return $result[0]["password"];
	}

    /**
     * @param int $userId
     * @return string|null
     * @throws \Exception
     */
	public function getPasswordByUserId(int $userId): ?string
    {
		if (empty($userId)) {
		    return null;
        }

		$this->database->prepareQuery('SELECT password FROM user WHERE user_id = :userId');
		if (empty($result = $this->database->executeSelect([':userId' => $userId]))) {
		    return null;
        }

		return $result[0][self::PASSWORD];	
	}

    /**
     * @param string $username
     * @return int|null
     * @throws \Exception
     */
	public function getUserId(string $username): ?int
    {
		$this->database->prepareQuery('SELECT user_id FROM user WHERE username = :username');
		if (empty($result = $this->database->executeSelect([":username" => $username]))) {
		    return null;
        }

		return $result[0]['user_id'];
	}

    /**
     * @param int $userId
     * @return string|null
     * @throws \Exception
     */
	public function getUserName(int $userId): ?string
    {
		$this->database->prepareQuery('SELECT username FROM user WHERE user_id = :userId');
		if (empty($result = $this->database->executeSelect([":userId" => $userId]))) {
		    return null;
        }

		return $result[0][self::NAME];
	}

    /**
     * @param int $userId
     * @return string|null
     * @throws \Exception
     */
	public function getTagColor(int $userId): ?string
    {
		$this->database->prepareQuery('SELECT color FROM user WHERE user_id = :userId');
		if (empty($result = $this->database->executeSelect([":userId" => $userId]))) {
		    return null;
        }

		return $result[0][self::TAG_COLOR];	
	}

    /**
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
	public function isUserActive(int $userId): bool
    {
		$this->database->prepareQuery('SELECT active FROM user WHERE user_id = :userId');
		if (empty($result = $this->database->executeSelect([":userId" => $userId]))) {
		    return false;
        }

		return $result[0]["active"] == 1 ? true : false;
	}

    /**
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
	public function isUserAdmin(int $userId): bool
    {
		$this->database->prepareQuery(
		    'SELECT STRCMP(role.name, :roleName) AS result FROM user ' .
            'LEFT JOIN role ON user.role_id = role.role_id WHERE user_id = :userId'
        );

		if (empty($result = $this->database->executeSelect([":userId" => $userId, ":roleName" => Role::ADMIN_ROLE]))) {
            throw new \Exception("User $userId doesn't exist.");
        }
		
		return ($result[0]["result"] == 0 ? true : false);
	}

    /**
     * @return array
     * @throws \Exception
     */
	public function getActiveUsers(): array
    {
		$this->database->prepareQuery('SELECT * FROM user WHERE active = \'1\' ORDER BY active, username');
		return $this->database->executeSelect();
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllUsers() : array
    {
		$this->database->prepareQuery('SELECT * FROM user ORDER BY active DESC, username');
		return $this->database->executeSelect();
	}

    /**
     * @param int $userId
     * @return int
     * @throws \Exception
     */
	public function countOtherAdminUsers(int $userId): int
    {
		$this->database->prepareQuery('SELECT COUNT(*) AS result FROM user WHERE role_id = :adminRoleId AND user_id <> :userId');
		if (empty($result = $this->database->executeSelect([":userId" => $userId, ":adminRoleId" => Role::ADMIN_ID]))) {
		    return 0;
        }

		return $result[0]["result"];
	}

    /**
     * @param array $userData
     * @return bool
     * @throws \Exception
     */
	public function insertUser(array $userData): bool
    {
		if (empty($userData)) {
		    return false;
        }

		$fields = "( ";
		$valuesNames = "( ";
		$values = array();
		
		foreach($userData as $key => $value){
			$fields .= $key;
			$valuesNames .= ":".$key;
			$values[":".$key] = $value;
			if(end($userData) !== $value){
				$fields .= ", ";
				$valuesNames .= ", ";
			}
		}
		$fields .= " )";
		$valuesNames .= " )";

		$this->database->prepareQuery("INSERT INTO user $fields VALUES $valuesNames");
		return $this->database->executeInsert($values);
	}

    /**
     * @param array $userData
     * @return bool
     * @throws \Exception
     */
	public function updateUser(array $userData): bool
    {
		if (empty($userData)) {
            return false;
        }
			
		$userId = $userData["itemID"];
		unset($userData["itemID"]);
		$fields = '';
		$values = [];
		
		foreach ($userData as $key => $value) {
			$fields .= $key . ' = :' . $key;
			$values[':'.$key] = $value;
			if (end($userData) !== $value) {
                $fields .= ', ';
            }

		}
		$values[':userId'] = $userId;
		$this->database->prepareQuery("UPDATE user SET $fields WHERE user_id = :userId");
		return $this->database->executeUpdate($values);
	}
}
