<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageBody()
    {
        $html = "";
        $employees = Staff::getAll(['name' => 'ASC']);

        $html .= MustacheProvider::get()->render('logoutPassword',['employees' => $employees]);
        return $html;
    }

}

$page = new IndexPage();
$page->render();

?>