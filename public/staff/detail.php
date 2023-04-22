<?php

class Employee {
    public $employeeId;
    public $name;
    public $surname;
    public $job;
    public $wage;
    public $roomId;
    public $roomName;
    public $keyRooms;

    public function __construct($data) {
        $this->employeeId = $data->employee_id;
        $this->name = $data->name;
        $this->surname = $data->surname;
        $this->job = $data->job;
        $this->wage = $data->wage;
        $this->roomId = $data->room;
        $this->roomName = null; // initialize to null
        $this->keyRooms = []; // initialize to empty array
    }


    public static function findByID($employeeId) {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("
            SELECT `employee_id`, `name`, `surname`, `job`, `wage`, `room_id`
            FROM `employee`
            WHERE `employee_id` = :employeeId
        ");
        $stmt->execute(['employeeId' => $employeeId]);
        $employeeData = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$employeeData)
            return null;

        $employee = new self($employeeData);

        // retrieve room name from the room table
        $stmt = $pdo->prepare("
            SELECT `name`
            FROM `room`
            WHERE `room_id` = :roomId
        ");
        $stmt->execute(['roomId' => $employee->roomId]);
        $roomData = $stmt->fetch(PDO::FETCH_OBJ);

        if ($roomData) {
            $employee->roomName = $roomData->name;
        }

        // retrieve list of rooms associated with the employee's keys
        $stmt = $pdo->prepare("
            SELECT `room`.`name`
            FROM `key`
            JOIN `room` ON `key`.`room_id` = `room`.`room_id`
            WHERE `key`.`employee_id` = :employeeId
        ");
        $stmt->execute(['employeeId' => $employeeId]);
        $roomData = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($roomData as $row) {
            $employee->keyRooms[] = $row->name;
        }

        return $employee;
    }
}

require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends CRUDPage
{
    private $employee;

    protected function prepare(): void
    {
        parent::prepare();

        // get employee ID from GET parameters
        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId) {
            throw new BadRequestException();
        }

        // find employee by ID
        $stmt = PDOProvider::get()->prepare("
            SELECT `employee_id`, `name`, `surname`, `job`, `wage`, `room`
            FROM `employee`
            WHERE `employee_id` = :employeeId
        ");
        $stmt->execute(['employeeId' => $employeeId]);
        $employeeData = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$employeeData) {
            throw new NotFoundException();
        }

        // create a new Employee object from the retrieved data
        $this->employee = new Employee($employeeData);

        // retrieve room name from the room table
        $stmt = PDOProvider::get()->prepare("
            SELECT `name`
            FROM `room`
            WHERE `room_id` = :roomId
        ");
        $stmt->execute(['roomId' => $this->employee->roomId]);
        $roomData = $stmt->fetch(PDO::FETCH_OBJ);

        if ($roomData) {
            $this->employee->roomName = $roomData->name;
        }

        // retrieve list of rooms associated with the employee's keys
        $stmt = PDOProvider::get()->prepare("
            SELECT r.name
            FROM `key` k
            LEFT JOIN `room` r ON r.room_id = k.room
            WHERE k.employee = :employeeId
        ");
        $stmt->execute(['employeeId' => $employeeId]);
        $roomKeys = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->employee->keyRooms = array_map(function($room) {
            return $room->name;
        }, $roomKeys);

        $this->title = "Detail zaměstnance {$this->employee->name} {$this->employee->surname}";
    }

    protected function pageBody()
    {
        // render the page using Mustache template
        return MustacheProvider::get()->render(
            'employeeDetail',
            ['employee' => $this->employee]
        );
    }
}

$page = new EmployeeDetailPage();
$page->render();

?>