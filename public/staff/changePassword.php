<?php

require_once __DIR__ . "/../../bootstrap/bootstrap.php";

session_start();

class ChangePassword extends CRUDPage
{
    private array $errors = [];

    public function __construct()
    {
        $this->title = "Change Password";
    }

    protected function prepare(): void
    {
        parent::prepare();
        if (!isset($_SESSION['id'])) {
            header('Location: login.php');
            exit;
        }
        $this->findState();

        //když poslal data
        if($this->state === self::STATE_DATA_SENT) {
            $currentPassword = filter_input(INPUT_POST, 'newPassword');
            $result = Staff::updatePassword($_SESSION['id'], $currentPassword);
            if($result === true){
                header('Location: ../home.php');
            }else{
                $this->errors = $result;
            }
        }
    }

    protected function pageBody(): mixed
    {
        return MustacheProvider::get()->render(
            'passwordChange',
            [
                'errors' => array_values($this->errors)
            ]
        );
    }
}

$page = new ChangePassword();
$page->render();
