<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class StaffDeletePage extends CRUDPage
{

    protected function prepare(): void
    {
        parent::prepare();

        $roomId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        if (!$roomId)
            throw new BadRequestException();

        //když poslal data
        $success = Staff::deleteByID($roomId);

        //přesměruj
        $this->redirect(self::ACTION_DELETE, $success);
    }

    protected function pageBody()
    {
        return "";
    }

}

$page = new StaffDeletePage();
$page->render();

?>