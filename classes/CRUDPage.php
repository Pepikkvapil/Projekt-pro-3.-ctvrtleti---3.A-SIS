<?php

if(!isset($_SESSION)) {
    session_start();
}

abstract class CRUDPage extends BasePage
{
    public const STATE_FORM_REQUESTED = 0;
    public const STATE_DATA_SENT = 1;

    public const ACTION_INSERT = "insert";
    public const ACTION_UPDATE = "update";
    public const ACTION_DELETE = "delete";

    protected function prepare(): void
    {
        if(isset($_SESSION['id'])) {
            \Tracy\Debugger::barDump([
                'isLoggedIn' => true,
                'ID' => $_SESSION['id'],
                'isAdmin' => $_SESSION['admin']
            ], 'Detail information about login');
        }

        if(!isset($_SESSION['id'])){
            header("Location: ../index.php");
            die;
        }

        parent::prepare();
    }

    protected function redirect(string $action, bool $success) : void
    {
        $data = [
            'action' => $action,
            'success' => $success ? 1 : 0
        ];
        header('Location: list.php?' . http_build_query($data) );
        exit;
    }
}