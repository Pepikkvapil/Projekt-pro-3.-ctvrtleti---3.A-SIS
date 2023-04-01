<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeCreatePage extends CRUDPage
{
    private ?Staff $employee = null;
    private ?array $errors = [];
    private array $rooms = [];

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Založit nového zaměstnance";

        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            $this->rooms = Room::getAll();
        }
        elseif ($this->state === self::STATE_DATA_SENT) {
            // Load the data from the form
            $this->employee = Staff::readPost();
            $this->employee->login = $_POST['login']; // Set login property
            $this->employee->password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Set hashed password property

            // Validate the data, otherwise show the form again with errors
            $this->errors = [];
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk) {
                $this->state = self::STATE_FORM_REQUESTED;
            } else {
                // Insert the employee into the database
                $success = $this->employee->insert();

                // Redirect to the appropriate action
                $this->redirect(self::ACTION_INSERT, $success);
            }

        }
    }

    protected function pageBody()
    {

        // Render the form using the employeeForm template
        return MustacheProvider::get()->render(
            'employeeForm',
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms
            ]
        );
    }
}

$page = new EmployeeCreatePage();
$page->render();

?>