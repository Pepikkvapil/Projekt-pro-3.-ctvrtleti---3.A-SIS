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

        $this->findState();

        // When data is submitted
        if ($this->state === self::STATE_DATA_SENT) {
            $newPassword = filter_input(INPUT_POST, 'newPassword');
            $confirmPassword = filter_input(INPUT_POST, 'confirmPassword');

            if ($newPassword !== $confirmPassword) {
                $this->errors[] = 'Hesla se neshodují.';
            } else {
                $result = Staff::updatePassword($_SESSION['id'], $newPassword);

                if ($result === true) {
                    header('Location: ../home.php');
                    exit;
                } else {
                    $this->errors = $result;
                }
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
