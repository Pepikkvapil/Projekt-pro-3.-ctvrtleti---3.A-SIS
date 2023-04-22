<?php

//namespace models;

class Staff
{
    public const DB_TABLE = "employee";

    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;
    public ?string $login; // Add login property
    public ?string $password; // Add password property

    /**
     * @param int|null $employee_id
     * @param string|null $name
     * @param string|null $surname
     * @param string|null $job
     * @param int|null $wage
     * @param int|null $room
     * @param string|null $login
     * @param string|null $password
     */
    public function __construct(?int $employee_id = null, ?string $name = null, ?string $surname = null,
                                ?string $job = null, ?int $wage = null, ?int $room = null,
                                ?string $login = 'login', ?string $password = null)
    {
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this->room = $room;
        $this->login = $login;
        $this->password = $password;
    }

    public static function findByID(int $id) : ?self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `employee_id`= :employeeId");
        $stmt->execute(['employeeId' => $id]);

        if ($stmt->rowCount() < 1)
            return null;

        $employee = new self();
        $employee->hydrate($stmt->fetch());
        return $employee;
    }

    /**
     * @return Staff[]
     */
    public static function getAll($sorting = []) : array
    {
        $sortSQL = "";
        if (count($sorting))
        {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction)
                $SQLchunks[] = "`{$field}` {$direction}";

            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."`" . $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch())
        {
            $employee = new Staff();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['employee_id', 'name', 'surname', 'job', 'wage', 'room'];
        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                if (array_key_exists($field, $data))
                    $this->{$field} = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                if (property_exists($data, $field))
                    $this->{$field} = $data->{$field};
            }
        }
    }

    public function insert() : bool
    {
        $query = "INSERT INTO ".self::DB_TABLE." (`name`, `surname`, `job`, `wage`, `room`, `login`, `password`, `admin`) VALUES (:name, :surname, :job, :wage, :room, :login, :password, :admin)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute([
            'name' => $this->name,
            'surname' => $this->surname,
            'job'=>  $this->job,
            'wage' => $this->wage,
            'room' => $this->room,
            'login' => $this->login,
            'password' => $this->password,
            'admin' => 0
        ]);
        if (!$result)
            return false;

        $this->employee_id = PDOProvider::get()->lastInsertId();
        return true;
    }


    public function update() : bool
    {
        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");

        $query = "UPDATE ".self::DB_TABLE." SET `name` = :name, `surname` = :surname, `job` = :job, `wage` = :wage, `room` = :room, `login` = :login WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([
            'employeeId' => $this->employee_id,
            'name' => $this->name,
            'surname' => $this->surname,
            'job' => $this->job,
            'wage' => $this->wage,
            'room' => $this->room,
            'login' => $this->login
        ]);
    }


    /**
     * @param int $employee_id
     * @param string $newPassword
     * @return bool|array Return true if password was changed, otherwise return array of errors
     */


    public static function updatePassword(int $employee_id, string $newPassword): bool|array
    {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo = PDOProvider::get();
            $stmt = $pdo->prepare("UPDATE employee SET password = :password WHERE employee_id = :id");
            $stmt->execute(['password' => $hashedPassword, 'id' => $employee_id]);

        return true;
    }

    public function delete() : bool
    {
        return self::deleteByID($this->employee_id);
    }

    public function getLogin() {
        // Assuming you have a database connection and a table named "employee"
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT login FROM employee WHERE employee_id = ?");
        $stmt->execute([$this->employee_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['login'] ?? '';
    }


    public static function deleteByID(int $employeeId) : bool
    {
        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['employeeId'=>$employeeId]);
    }

    public function validate(&$errors = []) : bool
    {
        if (!isset($this->name) || (!$this->name))
            $errors['name'] = 'Jméno nesmí být prázdné';

        if (!isset($this->surname) || (!$this->surname))
            $errors['surname'] = 'Příjmení musí být vyplněno';

        if (isset($this->wage) && $this->wage < 0)
            $errors['wage'] = 'Mzda nemůže být záporná';

        return count($errors) === 0;
    }

    public static function readPost() : self
    {
        $employee = new Staff();

        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $employee->name = trim(filter_input(INPUT_POST, 'name'));
        $employee->surname = trim(filter_input(INPUT_POST, 'surname'));
        $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_FLOAT);
        $employee->job = trim(filter_input(INPUT_POST, 'job'));
        $employee->room = trim(filter_input(INPUT_POST, 'room', FILTER_VALIDATE_INT));

        return $employee;

    }
}

