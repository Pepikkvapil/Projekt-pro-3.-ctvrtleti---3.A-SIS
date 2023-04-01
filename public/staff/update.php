<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class StaffUpdatePage extends CRUDPage
{
    private ?Staff $staff;
    private array $errors = [];
    private array $rooms = [];
    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit místnost";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            $roomId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
            if (!$roomId)
                throw new BadRequestException();

            //jdi dál
            $this->staff = Staff::findByID($roomId);
            if (!$this->staff)
                throw new NotFoundException();


            $this->rooms = Room::getAll();
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            //načti je
            $this->staff = Staff::readPost();

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = $this->staff->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {

                //ulož je
               $success = $this->staff->update();

                //přesměruj
               $this->redirect(self::ACTION_UPDATE, $success);
            }
        }


    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeForm',
            [
                'employee' => $this->staff,
                'errors' => $this->errors,
                'rooms' => $this->rooms
            ]
        );
    }

}

$page = new StaffUpdatePage();
$page->render();

?>