<?php


require_once __DIR__ . "/../../bootstrap/bootstrap.php";
session_start();
class StaffsPage extends CRUDPage
{
    private $alert = [];

    public function __construct()
    {
        $this->title = "Výpis Zaměstnance";
    }



    protected function prepare(): void
    {
        parent::prepare();
        //pokud přišel výsledek, zachytím ho
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');

        if (is_int($crudResult)) {
            $this->alert = [
                'alertClass' => $crudResult === 0 ? 'danger' : 'success'
            ];

            $message = '';
            if ($crudResult === 0)
            {
                $message = 'Operace nebyla úspěšná';
            }
            else if ($crudAction === self::ACTION_DELETE)
            {
                $message = 'Smazání proběhlo úspěšně';
            }
            else if ($crudAction === self::ACTION_INSERT)
            {
                $message = 'Zaměstnanec založen úspěšně';
            }
            else if ($crudAction === self::ACTION_UPDATE)
            {
                $message = 'Úprava zaměstnance byla úspěšná';
            }

            $this->alert['message'] = $message;
        }

    }


    protected function pageBody()
    {
        $html = "";


        //získat data
        $employees = Staff::getAll(['name' => 'ASC']);
        //prezentovat data
        if($_SESSION["admin"]==0)
        {
            $html .= MustacheProvider::get()->render('employeeList',['employees' => $employees]);
        }
        else
        {
            $html .= MustacheProvider::get()->render('employeeAdminList',['employees' => $employees]);
        }

        //zobrazit alert
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        return $html;
    }

}

$page = new StaffsPage();
$page->render();

?>