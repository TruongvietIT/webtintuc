<?php


class NewsDetail extends Element
{
    public function setup()
    {

        $this->setTemplatePath('view/templates/web/news/')
            ->setTemplateFile('NewsDetail');

        $uri = Context::getInstance()->getFront()->getRequest()->getFullUrl();
        if ($uri != strtolower($uri)) {
            Context::getInstance()->getFront()->getResponse()->setRedirect(strtolower($uri), 301);
        }
        $id = Context::getInstance()->getRoute()->getParam('id');

        if (isset($id)) {
            $newsModel = Context::getInstance()->getFront()->getModel('NewsModel');

            $mode = isset($_GET['mode']) ? $_GET['mode'] : '';

            $type = isset($_GET['type']) ? $_GET['type'] : '';

            $news = $newsModel->getNewsDetail($id, $mode);
            $this->assign('data', $news);
//
//echo "<pre>";
//            var_dump($news[0]);
        }



    }
}