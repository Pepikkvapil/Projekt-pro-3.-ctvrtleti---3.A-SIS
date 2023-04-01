<?php

abstract class BasePage
{
    public const STATE_FORM_REQUESTED = 0;
    public const STATE_DATA_SENT = 1;

    protected string $title = "";

    public int $state;

    public function findState() : void
    {
        $this->state = $_SERVER['REQUEST_METHOD'] === 'POST' ? self::STATE_DATA_SENT : self::STATE_FORM_REQUESTED;
    }

    protected function prepare() : void
    {}

    protected function sendHttpHeaders() : void
    {}

    protected function extraHTMLHeaders() : string
    {
        return "";
    }

    protected function pageHeader() : string
    {
        $m = MustacheProvider::get();
        return $m->render('header',[]);
    }

    abstract protected function pageBody();

    protected function pageFooter() : string
    {
        $m = MustacheProvider::get();
        return $m->render('footer',[]);
    }

    public function render() : void
    {
        try
        {
            $this->prepare();
            $this->sendHttpHeaders();

            $m = MustacheProvider::get();
            $data = [
                'lang' => AppConfig::get('app.lang'),
                'title' => $this->title,
                'pageHeader' => $this->pageHeader(),
                'pageBody' => $this->pageBody(),
                'pageFooter' => $this->pageFooter()
            ];
            echo $m->render("page", $data);
        }

        catch (BaseException $e)
        {
            if (AppConfig::get('debug'))
                throw $e;

            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }

        catch (Exception $e)
        {
            if (AppConfig::get('debug'))
                throw $e;

            $e = new BaseException("Server error", 500);
            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }
    }
}